<?php
// check_auth.php — Sessiya borligini va yaroqliligini tekshirish
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Tizimga kiring']);
    exit;
}

echo json_encode([
    'success' => true,
    'user' => $_SESSION['user']
]);
