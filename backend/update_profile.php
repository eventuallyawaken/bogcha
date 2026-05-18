<?php
require_once 'config.php';
$user = requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    $db = getDB();
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$phone && !$password) {
        echo json_encode(['success' => false, 'error' => 'O\'zgartirish uchun ma\'lumot yo\'q']);
        exit;
    }

    $updates = [];
    $params = [];
    if ($phone) {
        $updates[] = "phone = ?";
        $params[] = $phone;
    }
    if ($password) {
        $updates[] = "password_hash = ?";
        $params[] = password_hash($password, PASSWORD_DEFAULT);
    }
    
    $params[] = $user['id'];
    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    
    try {
        $stmt->execute($params);
        echo json_encode(['success' => true, 'message' => 'Sozlamalar yangilandi']);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'error' => 'Bu telefon raqam allaqachon band']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Xatolik yuz berdi: ' . $e->getMessage()]);
        }
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Faqat POST ruxsat etilgan']);
?>
