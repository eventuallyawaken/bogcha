<?php
// save_telegram.php — Telegram chat_id ni saqlash
// Bot /start dan telefon yuborganda chaqiriladi
// POST: { phone, chat_id }
require_once 'config.php';

// Bu endpointga faqat bot murojaat qiladi (session kerak emas)
$b      = json_decode(file_get_contents('php://input'), true);
$phone  = trim($b['phone']    ?? '');
$chatId = (int)($b['chat_id'] ?? 0);

if (!$phone || !$chatId) {
    echo json_encode(['success' => false, 'error' => 'phone va chat_id kerak']);
    exit;
}

// Telefon formatini normallashtirish
$phone = preg_replace('/[^0-9+]/', '', $phone);
if (!str_starts_with($phone, '+')) { $phone = '+' . $phone; }

$db   = getDB();
$stmt = $db->prepare('SELECT u.*, c.full_name AS child_name, g.name AS group_name FROM users u LEFT JOIN children c ON c.id = u.child_id LEFT JOIN groups g ON g.id = c.group_id WHERE u.phone = ? AND u.role = "parent" LIMIT 1');
$stmt->execute([$phone]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'error' => "Telefon topilmadi: $phone"]);
    exit;
}

// chat_id ni saqlash
$db->prepare('UPDATE users SET telegram_chat_id = ? WHERE id = ?')
   ->execute([$chatId, $user['id']]);

echo json_encode([
    'success'    => true,
    'name'       => $user['full_name'],
    'child_name' => $user['child_name'] ?? '—',
    'group_name' => $user['group_name'] ?? '—',
]);
