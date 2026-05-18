<?php
// ============================================================
// config.php — Barcha sozlamalar shu yerda
// ============================================================
// ⚠️ Bu faylni hech kim bilan ulashmang (parollar saqlanadi)

// --- Ma'lumotlar bazasi ---
// 🖊️ Agar XAMPP da DB nomingiz boshqacha bo'lsa, o'zgartiring
define('DB_HOST', '127.0.0.1:3307');
define('DB_NAME', 'bogcha');
define('DB_USER', 'root');
define('DB_PASS', '');          // XAMPP da sukut bo'yicha bo'sh

// --- Telegram Bot ---
// 🤖 @BotFather dan bot yaratib, tokenini shu yerga qo'ying
// Misol: '1234567890:ABCdefGHIjklMNOpqrsTUVwxyz'
define('TELEGRAM_BOT_TOKEN', 'YOUR_BOT_TOKEN_HERE');

// --- Email (Gmail SMTP) ---
// 📧 Gmail hisobingizdan App Password oling:
//    Google Account → Security → 2FA → App Passwords → "Mail" tanlang
// 🖊️ Emailingiz va app parolini kiriting
define('SMTP_HOST',      'smtp.gmail.com');
define('SMTP_PORT',      587);
define('SMTP_USER',      'your-email@gmail.com');  // Gmail email
define('SMTP_PASS',      'your-app-password');     // App Password (16 belgi)
define('SMTP_FROM_NAME', 'Kindergarten');

// --- Sessiya ---
session_start();

// --- Database ulanish funksiyasi ---
function getDB(): PDO {
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        die(json_encode([
            'success' => false,
            'error' => 'Database ulanmadi. XAMPP va MySQL ishga tushurilganmi?'
        ]));
    }
}

// --- CORS va JSON header ---
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

// --- Foydalanuvchi login qilganligini tekshirish ---
function requireAuth(): array {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        die(json_encode(['success' => false, 'error' => 'Kirish talab qilinadi']));
    }
    return $_SESSION['user'];
}

// --- Faqat admin/staff uchun ---
function requireStaff(): array {
    $user = requireAuth();
    if (!in_array($user['role'], ['staff', 'admin'])) {
        http_response_code(403);
        die(json_encode(['success' => false, 'error' => 'Ruxsat yo\'q']));
    }
    return $user;
}

// --- Telegram xabar yuborish ---
function sendTelegram(int $chatId, string $message, int $retries = 2): bool {
    if (!$chatId || TELEGRAM_BOT_TOKEN === 'YOUR_BOT_TOKEN_HERE') return false;
    $url  = 'https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/sendMessage';
    $data = ['chat_id' => $chatId, 'text' => $message, 'parse_mode' => 'HTML'];
    
    for ($i = 0; $i < $retries; $i++) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
        ]);
        $result = curl_exec($ch);
        $error  = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($result !== false && $httpCode >= 200 && $httpCode < 300) {
            $res = json_decode($result, true);
            if (!empty($res['ok'])) return true;
        }
        
        if ($i === $retries - 1 && isset($_SESSION['user_id'])) {
            $failedReason = $error ?: "HTTP $httpCode";
            logActivity($_SESSION['user_id'], 'telegram_error', "Telegram API Xatolik: $failedReason");
        }
        usleep(500000); // 0.5s kutib qayta urinish
    }
    return false;
}
// --- Tizim loglar uchun maxsus funksiya ---
function logActivity($userId, $action, $details = '') {
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $action, $details]);
    } catch (PDOException $e) {} // Log yozish xatosi tizimga ta'sir qilmasligi kerak
}
