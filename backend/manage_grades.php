<?php
require_once 'config.php';
$user = requireAuth();

// Faqat admin va staff kirishi mumkin
if ($user['role'] !== 'staff' && $user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Ruxsat yo\'q']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    $childId = (int)($_POST['child_id'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $grade   = trim($_POST['grade'] ?? '');
    $note    = trim($_POST['note'] ?? '');
    
    // Period uchun hozirgi oyni olish
    $months = ['Yanvar','Fevral','Mart','Aprel','May','Iyun','Iyul','Avgust','Sentabr','Oktabr','Noyabr','Dekabr'];
    $period = date('Y') . '-' . $months[date('n') - 1];

    if (!$childId || !$subject || !$grade) {
        echo json_encode(['success' => false, 'error' => 'Barcha maydonlarni to\'ldirish shart.']);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO grades (child_id, subject, grade, period, note, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$childId, $subject, $grade, $period, $note, $user['id']]);
        echo json_encode(['success' => true, 'message' => 'Sifat / Baho saqlandi! Ushbu baho ota-onaga asboblar panelida ko\'rinadi.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Xatolik: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Noto\'g\'ri so\'rov']);
