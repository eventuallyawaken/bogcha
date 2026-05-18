<?php
// ============================================================
// broadcast.php — Ota-onalarga bildirishnoma yuborish
// POST: { group_id (null=hammaga), title, message, channels: ["telegram","email"] }
// ============================================================
require_once 'config.php';

// PHPMailer uchun (agar o'rnatilgan bo'lsa)
// 📦 O'rnatish: composer require phpmailer/phpmailer
// Agar yo'q bo'lsa, email qismi o'tkazib yuboriladi
$mailerAvailable = file_exists(__DIR__ . '/../vendor/autoload.php');
if ($mailerAvailable) {
    require_once __DIR__ . '/../vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
}

$staffUser = requireStaff();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Faqat POST']); exit;
}

$body     = json_decode(file_get_contents('php://input'), true);
$groupId  = $body['group_id'] ?? null;   // null = hammaga
$title    = trim($body['title']   ?? 'Bildirishnoma');
$message  = trim($body['message'] ?? '');
$channels = $body['channels']     ?? ['telegram'];

if (!$message) {
    echo json_encode(['success' => false, 'error' => 'Xabar matni bo\'sh']); exit;
}

$db = getDB();

// Ota-onalar ro'yxatini olish
$sql = '
    SELECT u.id, u.full_name, u.email, u.telegram_chat_id,
           c.full_name AS child_name, g.name AS group_name
    FROM users u
    LEFT JOIN children c ON c.id = u.child_id
    LEFT JOIN groups g ON g.id = c.group_id
    WHERE u.role = "parent" AND u.is_active = 1
';
$params = [];
if ($groupId) {
    $sql .= ' AND c.group_id = ?';
    $params[] = (int)$groupId;
}
$stmt = $db->prepare($sql);
$stmt->execute($params);
$parents = $stmt->fetchAll();

$telegramSent = 0;
$emailSent    = 0;
$errors       = [];

foreach ($parents as $parent) {
    // --- Telegram ---
    if (in_array('telegram', $channels) && $parent['telegram_chat_id']) {
        $msg  = "🏫 <b>Kindergarten — {$title}</b>\n\n";
        $msg .= "Hurmatli {$parent['full_name']},\n\n";
        $msg .= htmlspecialchars($message);
        if ($parent['group_name']) $msg .= "\n\n🌸 Guruh: {$parent['group_name']}";
        if (sendTelegram((int)$parent['telegram_chat_id'], $msg)) {
            $telegramSent++;
        }
    }

    // --- Email ---
    if (in_array('email', $channels) && $parent['email'] && $mailerAvailable) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = SMTP_PORT;
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
            $mail->addAddress($parent['email'], $parent['full_name']);
            $mail->isHTML(true);
            $mail->Subject = "Kindergarten — {$title}";
            $mail->Body    = "
            <div style='font-family:Poppins,sans-serif;max-width:600px;margin:0 auto;padding:24px'>
              <h2 style='color:#4C83F0'>🏫 Kindergarten</h2>
              <p>Hurmatli <strong>{$parent['full_name']}</strong>,</p>
              <div style='background:#F0F4FF;border-radius:12px;padding:20px;margin:16px 0'>
                <p>{$message}</p>
              </div>
              " . ($parent['group_name'] ? "<p style='color:#6B7280'>🌸 Guruh: {$parent['group_name']}</p>" : "") . "
              <hr style='margin:24px 0;border-color:#E5E7EB'>
              <p style='color:#9CA3AF;font-size:12px'>Kindergarten — Furqat tumani, Tomosha qishlog'i</p>
            </div>";
            $mail->send();
            $emailSent++;
        } catch (Exception $e) {
            $errors[] = "Email ({$parent['email']}): " . $e->getMessage();
        }
    } elseif (in_array('email', $channels) && $parent['email'] && !$mailerAvailable) {
        // PHPMailer yo'q — PHP mail() bilan urinish
        $headers = "From: " . SMTP_FROM_NAME . " <" . SMTP_USER . ">\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $text = "Kindergarten — {$title}\n\nHurmatli {$parent['full_name']},\n\n{$message}";
        if (mail($parent['email'], "Kindergarten — {$title}", $text, $headers)) {
            $emailSent++;
        }
    }
}

// Xabarnoma tarixini saqlash
$db->prepare('
    INSERT INTO notifications (sent_by, group_id, title, message, channels, recipient_count)
    VALUES (?, ?, ?, ?, ?, ?)
')->execute([
    $staffUser['id'], $groupId ?: null, $title, $message,
    implode(',', $channels), count($parents)
]);

echo json_encode([
    'success'      => true,
    'recipients'   => count($parents),
    'telegram_sent'=> $telegramSent,
    'email_sent'   => $emailSent,
    'errors'       => $errors,
    'message'      => "✅ {$telegramSent} ta Telegram, {$emailSent} ta Email yuborildi.",
]);
