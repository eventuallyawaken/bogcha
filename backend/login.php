<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// OPTIONS request uchun
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ============================================================
// login.php — Foydalanuvchi tizimga kirishi
// ============================================================

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Faqat POST'
    ]);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);

$phone = trim($body['phone'] ?? '');
$phone = preg_replace('/[^0-9+]/', '', $phone);

$password = trim($body['password'] ?? '');

if (!$phone || !$password) {
    echo json_encode([
        'success' => false,
        'error' => 'Telefon va parol talab qilinadi'
    ]);
    exit;
}

$db = getDB();

$stmt = $db->prepare('
    SELECT * FROM users 
    WHERE phone = ? 
    AND is_active = 1 
    LIMIT 1
');

$stmt->execute([$phone]);

$user = $stmt->fetch();

$valid = false;

if ($user) {

    if (password_verify($password, $user['password_hash'])) {
        $valid = true;
    }

    // demo uchun
    if (!$valid && $user['password_hash'] === $password) {
        $valid = true;
    }
}

if (!$valid) {
    echo json_encode([
        'success' => false,
        'error' => 'Noto\'g\'ri login yoki parol'
    ]);
    exit;
}

// session
$_SESSION['user_id'] = $user['id'];

$_SESSION['user'] = [
    'id'       => $user['id'],
    'name'     => $user['full_name'],
    'phone'    => $user['phone'],
    'role'     => $user['role'],
    'child_id' => $user['child_id'],
];

// log
logActivity(
    $user['id'],
    'login',
    'Tizimga kirdi (' . $user['role'] . ')'
);

echo json_encode([
    'success' => true,
    'user' => $_SESSION['user']
]);