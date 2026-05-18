<?php
require_once 'config.php';
$user = requireStaff(); // Only admin or staff can manage restrictions

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

if ($method === 'GET') {
    // Get current restrictions
    $stmt = $db->query('
        SELECT c.*, u.full_name as restricted_by_name 
        FROM camera_restrictions c
        JOIN users u ON c.restricted_by = u.id
        WHERE c.restricted_until > NOW()
    ');
    echo json_encode(['success' => true, 'restrictions' => $stmt->fetchAll()]);
    exit;
}

if ($method === 'POST') {
    $targetRole = $_POST['target_role'] ?? ''; // 'all' or 'parent'
    $durationMinutes = (int)($_POST['duration_minutes'] ?? 0);
    $action = $_POST['action'] ?? 'add'; // 'add' or 'remove'

    if ($action === 'remove') {
        if (!$targetRole) {
            echo json_encode(['success' => false, 'error' => 'target_role is missing']);
            exit;
        }
        // If staff tries to remove admin's restriction, might fail, but let's allow or restrict by role:
        if ($user['role'] !== 'admin' && $targetRole === 'all') {
            echo json_encode(['success' => false, 'error' => 'Faqat admin barcha kameralarni cheklashni bekor qila oladi.']);
            exit;
        }
        
        $stmt = $db->prepare("DELETE FROM camera_restrictions WHERE target_role = ?");
        $stmt->execute([$targetRole]);
        echo json_encode(['success' => true, 'message' => 'Cheklov olib tashlandi']);
        exit;
    }

    if ($action === 'add') {
        if ($durationMinutes <= 0) {
            echo json_encode(['success' => false, 'error' => 'Vaqt noto\'g\'ri kiritildi']);
            exit;
        }
        if ($user['role'] !== 'admin' && $targetRole === 'all') {
            echo json_encode(['success' => false, 'error' => 'Faqat admin barcha kameralarni cheklay oladi.']);
            exit;
        }
        if ($user['role'] === 'staff' && $targetRole !== 'parent') {
            // Staff can only restrict 'parent'
            echo json_encode(['success' => false, 'error' => 'Xodim faqat ota-onalar kamerasini cheklay oladi.']);
            exit;
        }

        $restrictedBy = $user['id'];
        $stmt = $db->prepare("
            INSERT INTO camera_restrictions (target_role, restricted_by, restricted_until) 
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE))
            ON DUPLICATE KEY UPDATE 
                restricted_by = VALUES(restricted_by),
                restricted_until = VALUES(restricted_until)
        ");
        $stmt->execute([$targetRole, $restrictedBy, $durationMinutes]);

        echo json_encode(['success' => true, 'message' => 'Kamera cheklandi']);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Not supported action']);
?>
