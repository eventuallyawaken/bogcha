<?php
require_once 'config.php';
$user = requireAuth();

// Faqat admin va staff ruxsat etiladi
if ($user['role'] !== 'staff' && $user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Ruxsat yo\'q']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    $groupId = (int)($_POST['group_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $teacherName = trim($_POST['teacher_name'] ?? '');
    $ageRange = trim($_POST['age_range'] ?? '');

    if (!$groupId || !$name || !$teacherName) {
        echo json_encode(['success' => false, 'error' => 'Guruh nomi va o\'qituvchi ismi shart']);
        exit;
    }

    $stmt = $db->prepare("UPDATE groups SET name = ?, teacher_name = ?, age_range = ? WHERE id = ?");
    try {
        $stmt->execute([$name, $teacherName, $ageRange, $groupId]);
        echo json_encode(['success' => true, 'message' => 'Guruh ma\'lumotlari yangilandi']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Xatolik: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Noto\'g\'ri so\'rov']);
