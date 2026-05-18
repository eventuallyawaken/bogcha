<?php
// bot_api.php — Telegram bot uchun ma'lumot API
// GET ?action=davomat&chat_id=XXX
// GET ?action=tarix&chat_id=XXX
require_once 'config.php';

// Bot dan kelgan so'rovlar (session kerak emas)
$action = $_GET['action']  ?? '';
$chatId = (int)($_GET['chat_id'] ?? 0);
if (!$chatId) { echo json_encode(['success'=>false,'error'=>'chat_id kerak']); exit; }

$db = getDB();

// Foydalanuvchini chatId orqali topish
$stmt = $db->prepare('SELECT u.*, c.full_name AS child_name, c.id AS child_id FROM users u LEFT JOIN children c ON c.id = u.child_id WHERE u.telegram_chat_id = ? AND u.role = "parent" LIMIT 1');
$stmt->execute([$chatId]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success'=>false,'error'=>'Topilmadi. /start orqali ro\'yxatdan o\'ting']);
    exit;
}

switch ($action) {
    case 'davomat':
        $today = date('Y-m-d');
        $stmt  = $db->prepare('SELECT * FROM attendance WHERE child_id = ? AND date = ? LIMIT 1');
        $stmt->execute([$user['child_id'], $today]);
        $att = $stmt->fetch();
        echo json_encode([
            'success'    => true,
            'child_name' => $user['child_name'],
            'attendance' => $att ?: ['status'=>null,'date'=>$today,'note'=>null],
        ]);
        break;

    case 'tarix':
        $stmt = $db->prepare('SELECT date, status, note FROM attendance WHERE child_id = ? ORDER BY date DESC LIMIT 30');
        $stmt->execute([$user['child_id']]);
        echo json_encode([
            'success'    => true,
            'child_name' => $user['child_name'],
            'records'    => $stmt->fetchAll(),
        ]);
        break;

    default:
        echo json_encode(['success'=>false,'error'=>'Noma\'lum action']);
}
