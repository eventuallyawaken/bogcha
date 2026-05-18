<?php
// logout.php — Sessiyani tugatish
require_once 'config.php';
session_destroy();
echo json_encode(['success' => true]);
