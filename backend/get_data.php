<?php
// ============================================================
// get_data.php — Dashboard uchun ma'lumotlarni qaytaradi
// GET ?type=parent_data → bola, davomat, baholar
// GET ?type=children&group_id=X → guruh bolalari (admin)
// GET ?type=groups → barcha guruhlar (admin)
// GET ?type=cameras → kameralar (admin)
// ============================================================
require_once 'config.php';

$type = $_GET['type'] ?? '';
$db   = getDB();

// 🔓 Public endpoints
if ($type === 'landing_stats') {
    $total_children = $db->query('SELECT COUNT(*) FROM children')->fetchColumn();
    $total_groups = $db->query('SELECT COUNT(*) FROM groups')->fetchColumn();
    echo json_encode(['success' => true, 'stats' => [
        'children' => $total_children,
        'groups' => $total_groups
    ]]);
    exit;
}

if ($type === 'public_groups') {
    $stmt = $db->query("SELECT id, name, teacher_name, age_range FROM groups ORDER BY id");
    echo json_encode(['success' => true, 'groups' => $stmt->fetchAll()]);
    exit;
}

// 🔒 Protected endpoints limit
$user = requireAuth();

switch ($type) {

    // --- Ota-ona uchun: bola ma'lumoti + davomat + baholar ---
    case 'parent_data':
        if ($user['role'] !== 'parent' || !$user['child_id']) {
            echo json_encode(['success' => false, 'error' => 'Farzand topilmadi']);
            exit;
        }
        $childId = (int)$user['child_id'];

        // Bola ma'lumotlari
        $stmt = $db->prepare('
            SELECT c.*, g.name AS group_name, g.teacher_name, g.camera_url, g.camera_type
            FROM children c
            JOIN groups g ON c.group_id = g.id
            WHERE c.id = ?
        ');
        $stmt->execute([$childId]);
        $child = $stmt->fetch();

        // Check camera restrictions for parent_data
        try {
            $rstmt = $db->query("
                SELECT u.full_name as restricted_by_name 
                FROM camera_restrictions c
                JOIN users u ON c.restricted_by = u.id
                WHERE c.restricted_until > NOW() AND c.target_role IN ('all', 'parent')
                LIMIT 1
            ");
            if ($rest = $rstmt->fetch()) {
                $child['camera_url'] = null;
                $child['restrict_message'] = "Shu odam tomonidan cheklandi: " . $rest['restricted_by_name'];
            }
        } catch (PDOException $e) {}

        // Joriy oy davomati
        $month = $_GET['month'] ?? date('Y-m');
        $stmt  = $db->prepare('
            SELECT date, status, note FROM attendance
            WHERE child_id = ? AND DATE_FORMAT(date, "%Y-%m") = ?
            ORDER BY date
        ');
        $stmt->execute([$childId, $month]);
        $attendance = $stmt->fetchAll();

        // Davomat statistikasi
        $stmt = $db->prepare('
            SELECT
                COUNT(*) AS total,
                SUM(status = "present") AS present,
                SUM(status = "absent") AS absent,
                SUM(status = "sick") AS sick
            FROM attendance
            WHERE child_id = ? AND DATE_FORMAT(date, "%Y-%m") = ?
        ');
        $stmt->execute([$childId, $month]);
        $stats = $stmt->fetch();

        // Baholar (so'nggi)
        $stmt = $db->prepare('
            SELECT subject, grade, period, note, created_at
            FROM grades
            WHERE child_id = ?
            ORDER BY created_at DESC
            LIMIT 20
        ');
        $stmt->execute([$childId]);
        $grades = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'child'   => $child,
            'attendance' => $attendance,
            'stats'   => $stats,
            'grades'  => $grades,
        ]);
        break;

    // --- Admin/staff uchun: guruh bolalari ---
    case 'children':
        requireStaff();
        $groupId  = (int)($_GET['group_id'] ?? 0);
        $date     = $_GET['date'] ?? date('Y-m-d');
        if (!$groupId) {
            echo json_encode(['success' => false, 'error' => 'group_id kerak']);
            exit;
        }
        $stmt = $db->prepare('
            SELECT c.id, c.full_name, c.photo, c.gender,
                   a.status AS att_status, a.note AS att_note
            FROM children c
            LEFT JOIN attendance a ON a.child_id = c.id AND a.date = ?
            WHERE c.group_id = ?
            ORDER BY c.full_name
        ');
        $stmt->execute([$date, $groupId]);
        echo json_encode(['success' => true, 'children' => $stmt->fetchAll()]);
        break;

    // --- Barcha guruhlar ---
    case 'groups':
        requireStaff();
        $stmt = $db->query('SELECT * FROM groups ORDER BY id');
        echo json_encode(['success' => true, 'groups' => $stmt->fetchAll()]);
        break;

    // --- Kameralar (admin barcha, ota-ona faqat o'z guruhi) ---
    case 'cameras':
        $restrictions = [];
        try {
            $restrictions = $db->query("
                SELECT target_role, u.full_name as restricted_by_name 
                FROM camera_restrictions c
                JOIN users u ON c.restricted_by = u.id
                WHERE c.restricted_until > NOW()
            ")->fetchAll();
        } catch (PDOException $e) {}

        $allRestrictedBy = null;
        $parentRestrictedBy = null;
        foreach ($restrictions as $r) {
            if ($r['target_role'] === 'all') $allRestrictedBy = $r['restricted_by_name'];
            if ($r['target_role'] === 'parent') $parentRestrictedBy = $r['restricted_by_name'];
        }

        if ($user['role'] === 'parent') {
            // Faqat o'z guruhining kamerasi
            $stmt = $db->prepare('
                SELECT g.id, g.name, g.camera_url, g.camera_type
                FROM children c JOIN groups g ON c.group_id = g.id
                WHERE c.id = ?
            ');
            $stmt->execute([$user['child_id']]);
            $cameras = $stmt->fetchAll();

            if ($allRestrictedBy || $parentRestrictedBy) {
                $restrictedBy = $allRestrictedBy ?: $parentRestrictedBy;
                foreach ($cameras as &$cam) {
                    $cam['camera_url'] = null;
                    $cam['restrict_message'] = "Shu odam tomonidan cheklandi: " . $restrictedBy;
                }
            }
            echo json_encode(['success' => true, 'cameras' => $cameras]);
        } else {
            requireStaff();
            $stmt = $db->query('SELECT id, name, camera_url, camera_type FROM groups ORDER BY id');
            $cameras = $stmt->fetchAll();

            if ($user['role'] === 'staff' && $allRestrictedBy) {
                foreach ($cameras as &$cam) {
                    $cam['camera_url'] = null;
                    $cam['restrict_message'] = "Shu odam tomonidan cheklandi: " . $allRestrictedBy;
                }
            }
            echo json_encode(['success' => true, 'cameras' => $cameras]);
        }
        logActivity($user['id'], 'view_cameras', 'Kameralar bo\'limiga tashrif buyurdi');
        break;

    // --- Admin dashboard: umumiy statistika ---
    case 'dashboard_stats':
        requireStaff();
        $today = date('Y-m-d');
        $total   = $db->query('SELECT COUNT(*) FROM children')->fetchColumn();
        $present = $db->query("SELECT COUNT(*) FROM attendance WHERE date='$today' AND status='present'")->fetchColumn();
        $absent  = $db->query("SELECT COUNT(*) FROM attendance WHERE date='$today' AND status='absent'")->fetchColumn();
        $sick    = $db->query("SELECT COUNT(*) FROM attendance WHERE date='$today' AND status='sick'")->fetchColumn();
        $parents = $db->query("SELECT COUNT(*) FROM users WHERE role='parent'")->fetchColumn();
        echo json_encode(['success' => true, 'stats' => compact('total','present','absent','sick','parents')]);
        break;

    // --- Admin Kuzatuv Loglari ---
    case 'activity_logs':
        if ($user['role'] !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Faqat admin uchun']);
            exit;
        }
        $stmt = $db->query('
            SELECT a.id, a.action, a.details, a.created_at, u.full_name, u.role 
            FROM activity_logs a
            LEFT JOIN users u ON a.user_id = u.id
            ORDER BY a.created_at DESC LIMIT 50
        ');
        echo json_encode(['success' => true, 'logs' => $stmt->fetchAll()]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Noma\'lum type']);
}
