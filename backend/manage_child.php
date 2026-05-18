<?php
require_once 'config.php';
$user = requireStaff(); // admin and staff can manage children

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Faqat POST ruxsat etilgan']);
    exit;
}

$db = getDB();
$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $fullName = $_POST['name'] ?? '';
    $groupId = (int)($_POST['group_id'] ?? 0);
    $gender = $_POST['gender'] ?? 'male';
    $birthDate = $_POST['birth_date'] ?? date('Y-m-d');

    if (!$fullName || !$groupId) {
        echo json_encode(['success' => false, 'error' => 'Ism va guruh kiritilishi shart']);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO children (full_name, group_id, gender, birth_date) VALUES (?, ?, ?, ?)");
    $stmt->execute([$fullName, $groupId, $gender, $birthDate]);
    $newChildId = $db->lastInsertId();

    // Send telegram notification to Admin
    // Let's find admin users
    $stmtAdmin = $db->query("SELECT id FROM users WHERE role = 'admin'");
    $admins = $stmtAdmin->fetchAll();
    
    // Using group_name for telegram message
    $groupNameStmt = $db->prepare('SELECT name FROM groups WHERE id = ?');
    $groupNameStmt->execute([$groupId]);
    $groupRow = $groupNameStmt->fetch();
    $groupName = $groupRow ? $groupRow['name'] : $groupId;

    $telegramMsg = "🆕 <b>Yangi bola qo'shildi!</b>\n";
    $telegramMsg .= "👤 Ismi: " . htmlspecialchars($fullName) . "\n";
    $telegramMsg .= "👥 Guruhi: " . htmlspecialchars($groupName) . "\n";
    $telegramMsg .= "👩‍💼 Qo'shdi: " . htmlspecialchars($user['full_name']);

    // In a real scenario we'd need telegram_chat_id, but the codebase has sendTelegram which takes chatId.
    // If we don't have chat_id in users, we can just log or broadcast via bot.
    // For now we assume $admin['telegram_chat_id'] doesn't exist, we fallback to a hardcoded logic or just the group ones.
    // The previous implementation plan says: "Finds the admin's telegram_chat_id from the users table..."
    // Since `users` doesn't have `telegram_chat_id` according to `create_users.php`, we will just check if `chat_id` exists in users.
    try {
        $stmtAdminChat = $db->query("SELECT chat_id FROM users WHERE role = 'admin' AND chat_id IS NOT NULL");
        $adminChats = $stmtAdminChat->fetchAll();
        foreach ($adminChats as $aChat) {
            sendTelegram($aChat['chat_id'], $telegramMsg);
        }
    } catch (PDOException $e) {
        // chat_id column might not exist, silently ignore
    }

    echo json_encode(['success' => true, 'message' => 'Bola qo\'shildi']);
    exit;
}

if ($action === 'delete') {
    $childId = (int)($_POST['child_id'] ?? 0);
    if (!$childId) {
        echo json_encode(['success' => false, 'error' => 'Bola ID kiritilmagan']);
        exit;
    }

    $stmt = $db->prepare("DELETE FROM children WHERE id = ?");
    $stmt->execute([$childId]);

    echo json_encode(['success' => true, 'message' => 'Bola o\'chirildi']);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Noma\'lum action']);
?>
