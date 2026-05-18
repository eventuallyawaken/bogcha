<?php
// ============================================================
// attendance.php — Davomat saqlash + Telegram bildirishnoma
// POST: { group_id, date, attendance: [{child_id, status, note}] }
// ============================================================
require_once 'config.php';

$staffUser = requireStaff();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Faqat POST']); exit;
}

$body     = json_decode(file_get_contents('php://input'), true);
$groupId  = (int)($body['group_id']  ?? 0);
$date     = $body['date'] ?? date('Y-m-d');
$records  = $body['attendance'] ?? [];

if (!$groupId || empty($records)) {
    echo json_encode(['success' => false, 'error' => 'group_id va davomat ro\'yxati kerak']); exit;
}

$db = getDB();

// Guruh nomini olish
$gStmt = $db->prepare('SELECT name FROM groups WHERE id = ?');
$gStmt->execute([$groupId]);
$group = $gStmt->fetchColumn();

$saved     = 0;
$notified  = 0;
$errors    = [];

foreach ($records as $rec) {
    $childId  = (int)($rec['child_id'] ?? 0);
    $status   = $rec['status'] ?? 'absent';
    $note     = $rec['note']   ?? '';

    if (!$childId || !in_array($status, ['present','absent','sick','excused'])) continue;

    try {
        // INSERT OR UPDATE davomat
        $stmt = $db->prepare('
            INSERT INTO attendance (child_id, date, status, note, recorded_by)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE status=VALUES(status), note=VALUES(note), recorded_by=VALUES(recorded_by)
        ');
        $stmt->execute([$childId, $date, $status, $note, $staffUser['id']]);
        $saved++;

        // Bola va ota-ona ma'lumotlari
        $pStmt = $db->prepare('
            SELECT c.full_name AS child_name,
                   u.telegram_chat_id, u.full_name AS parent_name
            FROM children c
            JOIN users u ON u.child_id = c.id AND u.role = "parent"
            WHERE c.id = ?
        ');
        $pStmt->execute([$childId]);
        $parent = $pStmt->fetch();

        if ($parent && $parent['telegram_chat_id']) {
            // Telegram xabar matni
            $dateFormatted = date('d.m.Y', strtotime($date));
            $statusText = match($status) {
                'present'  => '✅ <b>Keldi</b>',
                'absent'   => '❌ <b>Kelmadi</b>',
                'sick'     => '🤒 <b>Kasal</b>',
                'excused'  => '📋 <b>Sababli</b>',
                default    => $status
            };
            $msg  = "🏫 <b>Kindergarten — Davomat</b>\n\n";
            $msg .= "👦 <b>{$parent['child_name']}</b>\n";
            $msg .= "📅 Sana: {$dateFormatted}\n";
            $msg .= "📊 Holat: {$statusText}\n";
            if ($note) $msg .= "📝 Izoh: {$note}\n";
            $msg .= "\n🌸 Guruh: {$group}";

            if (sendTelegram((int)$parent['telegram_chat_id'], $msg)) {
                $notified++;
                // Notification_sent ni yangilash
                $db->prepare('UPDATE attendance SET notification_sent=1 WHERE child_id=? AND date=?')
                   ->execute([$childId, $date]);
            }
        }
    } catch (Exception $ex) {
        $errors[] = "child_id={$childId}: " . $ex->getMessage();
    }
}

echo json_encode([
    'success'       => true,
    'saved'         => $saved,
    'notified'      => $notified,
    'telegram_note' => $notified > 0
        ? "{$notified} ta ota-onaga Telegram orqali xabar yuborildi."
        : "Telegram xabarlari yuborilmadi (chat_id yo'q yoki token noto'g'ri).",
    'errors'        => $errors,
]);
