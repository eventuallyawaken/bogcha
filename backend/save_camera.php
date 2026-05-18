<?php
// save_camera.php — Kamera URL ni saqlash (admin)
// POST: { group_id, camera_url, camera_type }
require_once 'config.php';
requireStaff();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false,'error'=>'POST kerak']); exit; }
$b = json_decode(file_get_contents('php://input'), true);
$groupId  = (int)($b['group_id']   ?? 0);
$url      = trim($b['camera_url']  ?? '');
$type     = $b['camera_type']      ?? 'hls';
if (!$groupId || !$url) { echo json_encode(['success'=>false,'error'=>'group_id va camera_url kerak']); exit; }
$db = getDB();
$db->prepare('UPDATE groups SET camera_url=?, camera_type=? WHERE id=?')->execute([$url, $type, $groupId]);
echo json_encode(['success'=>true, 'message'=>'Kamera saqlandi']);
