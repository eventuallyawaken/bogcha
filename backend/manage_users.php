<?php
require_once 'config.php';
$reqUser = requireStaff(); // admin or staff

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$db = getDB();

// 1. Read Users
if ($method === 'GET' && $action === 'list') {
    $role = $_GET['role'] ?? 'parent'; // staff or parent
    if ($role === 'staff' && $reqUser['role'] !== 'admin') {
        echo json_encode(['success' => false, 'error' => 'Xodimlar ruyxatini faqat admin ko\'ra oladi']);
        exit;
    }
    $stmt = $db->prepare("SELECT id, full_name, phone, role, created_at, is_active FROM users WHERE role = ?");
    $stmt->execute([$role]);
    $users = $stmt->fetchAll();
    echo json_encode(['success' => true, 'users' => $users]);
    exit;
}

// 2. Create or Update User
if ($method === 'POST' && ($action === 'create' || $action === 'update')) {
    $userId = (int)($_POST['user_id'] ?? 0);
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'parent';

    if (!$fullName || !$phone) {
        echo json_encode(['success' => false, 'error' => 'Ism va telefon kiritilishi shart']);
        exit;
    }

    if ($role === 'admin') {
        echo json_encode(['success' => false, 'error' => 'Admin yasash taqiqlangan']);
        exit;
    }

    if ($role === 'staff' && $reqUser['role'] !== 'admin') {
        echo json_encode(['success' => false, 'error' => 'Sizga xodim qo\'shish/tahrirlash ruxsat etilmagan']);
        exit;
    }

    // Phone cleanup formatting
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    if (!str_starts_with($phone, '+')) { $phone = '+' . $phone; }

    if ($action === 'create') {
        if (!$password) {
            echo json_encode(['success' => false, 'error' => 'Yangi foydalanuvchi uchun parol kerak']);
            exit;
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (full_name, phone, password_hash, role) VALUES (?, ?, ?, ?)");
        try {
            $stmt->execute([$fullName, $phone, $hash, $role]);
            echo json_encode(['success' => true, 'message' => 'Muvaffaqiyatli saqlandi!']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) echo json_encode(['success' => false, 'error' => 'Bu telefon raqam allaqachon band']);
            else echo json_encode(['success' => false, 'error' => 'Xato: ' . $e->getMessage()]);
        }
    } else {
        // Update user
        if (!$userId) { echo json_encode(['success' => false, 'error' => 'User ID yo\'q']); exit; }

        $updates = ["full_name = ?", "phone = ?", "role = ?"];
        $params = [$fullName, $phone, $role];

        if ($password) {
            $updates[] = "password_hash = ?";
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }
        
        $params[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        try {
            $stmt->execute($params);
            echo json_encode(['success' => true, 'message' => 'Muvaffaqiyatli yangilandi!']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) echo json_encode(['success' => false, 'error' => 'Bu telefon raqam allaqachon band']);
            else echo json_encode(['success' => false, 'error' => 'Xato: ' . $e->getMessage()]);
        }
    }
    exit;
}

// 3. Delete user
if ($method === 'POST' && $action === 'delete') {
    $userId = (int)($_POST['user_id'] ?? 0);
    // Let's get the role of this user first
    $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $u = $stmt->fetch();
    
    if (!$u) { echo json_encode(['success' => false, 'error' => 'Topilmadi']); exit; }

    if ($u['role'] === 'admin') { echo json_encode(['success'=>false, 'error'=>'Adminni o\'chirib bo\'lmaydi']); exit; }
    if ($u['role'] === 'staff' && $reqUser['role'] !== 'admin') {
        echo json_encode(['success'=>false, 'error'=>'Faqat admin xodimni o\'chira oladi']); exit;
    }

    $db->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    echo json_encode(['success' => true, 'message' => 'O\'chirildi']);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Noto\'g\'ri parametr']);
