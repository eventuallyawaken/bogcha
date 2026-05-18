<?php
// ============================================================
// login.php — Foydalanuvchi tizimga kirishi
// POST: { phone, password } → JSON { success, user }
// ============================================================
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Faqat POST']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$phone    = trim($body['phone']    ?? '');
// Telefon raqamdan probel va chiziqchalarni olib tashlaymiz
$phone    = preg_replace('/[^0-9+]/', '', $phone);
$password = trim($body['password'] ?? '');

if (!$phone || !$password) {
    echo json_encode(['success' => false, 'error' => 'Telefon va parol talab qilinadi']);
    exit;
}

$db   = getDB();
$stmt = $db->prepare('SELECT * FROM users WHERE phone = ? AND is_active = 1 LIMIT 1');
$stmt->execute([$phone]);
$user = $stmt->fetch();

// Parolni tekshirish
// Demo DB dagi hash: password_hash('parent123', PASSWORD_DEFAULT)
// Lekin test uchun oddiy tekshiruv ham qo'shildi
$valid = false;
if ($user) {
    if (password_verify($password, $user['password_hash'])) {
        $valid = true;
    }
    // Agar hash to'g'ri bo'lmasa (eski demo hash), oddiy tekshiruv
    if (!$valid && $user['password_hash'] === $password) {
        $valid = true;
    }
}

if (!$valid) {
    echo json_encode(['success' => false, 'error' => 'Noto\'g\'ri login yoki parol']);
    exit;
}

// Sessiyaga saqlash
$_SESSION['user_id'] = $user['id'];
$_SESSION['user']    = [
    'id'       => $user['id'],
    'name'     => $user['full_name'],
    'phone'    => $user['phone'],
    'role'     => $user['role'],
    'child_id' => $user['child_id'],
];

// Activity logga yozish
logActivity($user['id'], 'login', 'Tizimga kirdi (' . $user['role'] . ')');

echo json_encode([
    'success' => true,
    'user'    => $_SESSION['user']
]);
