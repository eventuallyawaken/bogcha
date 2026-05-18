<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Faqat POST so\'rovi qabul qilinadi']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$phone = trim($body['phone'] ?? '');
$phone = preg_replace('/[^0-9+]/', '', $phone);

if (!$phone) {
    echo json_encode(['success' => false, 'error' => 'Telefon raqamini kiriting.']);
    exit;
}

if ($phone !== '+998916886811') {
    echo json_encode(['success' => false, 'error' => "Faqat ro'yxatdan o'tgan maxsus raqam (+998916886811) orqali tiklash mumkin."]);
    exit;
}

$db = getDB();

// Faqat admin rolidagi telefonni tekshiramiz
$stmt = $db->prepare("SELECT * FROM users WHERE phone = ? AND role = 'admin' LIMIT 1");
$stmt->execute([$phone]);
$admin = $stmt->fetch();

if (!$admin) {
    echo json_encode(['success' => false, 'error' => 'Kiritilgan telefon raqami tizimdagi Adminga tegishli emas.']);
    exit;
}

if (empty($admin['telegram_chat_id'])) {
    echo json_encode([
        'success' => false, 
        'error' => 'Siz botdan ro\'yxatdan o\'tmagansiz! Parolni tiklash uchun avval botimizga (/start) yozing.'
    ]);
    exit;
}

$newPassword = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 8);
$hashedPass = password_hash($newPassword, PASSWORD_DEFAULT);

// Parolni yangi xavfsiz kodga almashtiramiz
$upd = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
$upd->execute([$hashedPass, $admin['id']]);

// Bot orqali yuborish
$msg = "🔐 <b>Admin Kirish Paroli Tiklandi!</b>\n\n";
$msg .= "Yangi parolingiz: <code>$newPassword</code>\n\n";
$msg .= "Bot orqali tizimni boshqarishingiz va xavfsizlik uchun hisobga kirgach sozlamalardan o'zgartirishingiz mumkin.";

$sent = sendTelegram($admin['telegram_chat_id'], $msg);

if ($sent) {
    logActivity($admin['id'], 'password_reset', 'Admin bot orqali parolni tikladi.');
    echo json_encode(['success' => true, 'message' => 'Yangi parol sizning Telegram botingizga yuborildi! Kuting yoki tekshiring.']);
} else {
    echo json_encode(['success' => false, 'error' => 'Botga xabar yuborib bo\'lmadi. Asosiy telegram_chat_id muammosi bo\'lishi mumkin.']);
}
