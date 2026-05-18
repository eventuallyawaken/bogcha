<?php
// Ushbu fayl ma'lumotlar bazasini va kerakli foydalanuvchilarni yaratadi.
// Fayllarni xampp/htdocs/bogcha jildiga tashlaganingizdan so'ng,
// brauzerda http://localhost/bogcha/create_users.php ni bosing.

require_once 'backend/config.php';

echo "<h2>Ma'lumotlar bazasini o'rnatish boshlandi...</h2>";

try {
    // Bazani yaratish uchun bazasiz ulanamiz
    $dsn_no_db = 'mysql:host=' . DB_HOST . ';charset=utf8mb4';
    $pdo_init = new PDO($dsn_no_db, DB_USER, DB_PASS);
    $pdo_init->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Bazani yaratamiz
    $pdo_init->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Baza ('" . DB_NAME . "') muvaffaqiyatli tekshirildi/yaratildi.<br>";
    
    // Bazaga ulanamiz
    $db = getDB();
    
    // Jadvallarni yaratish
    $db->exec("CREATE TABLE IF NOT EXISTS users (
      id            INT AUTO_INCREMENT PRIMARY KEY,
      full_name     VARCHAR(100) NOT NULL,
      phone         VARCHAR(20)  NOT NULL UNIQUE,
      password_hash VARCHAR(255) NOT NULL,
      role          ENUM('parent','staff','admin') DEFAULT 'parent',
      child_id      INT DEFAULT NULL,
      is_active     TINYINT(1) DEFAULT 1,
      created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ 'users' jadvali muvaffaqiyatli tekshirildi/yaratildi.<br>";

    // Foydalanuvchilar ma'lumotlari
    $users = [
        ['+998901234567', 'Test Ota-ona', 'parent123', 'parent'],
        ['+998901234568', 'Admin User',   'admin123',  'admin'],
        ['+998901234569', 'Xodim',        'staff123',  'staff'],
    ];

    echo "<h3>Foydalanuvchilarni qo'shish:</h3>";
    echo "<ul>";
    
    foreach ($users as [$phone, $name, $pass, $role]) {
        // Telefon raqam bo'yicha bazada bor-yo'qligini tekshiramiz
        $stmt_check = $db->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt_check->execute([$phone]);
        
        if ($stmt_check->fetch()) {
            echo "<li>⚠️ $name ($phone) allaqachon mavjud.</li>";
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (phone, full_name, password_hash, role) VALUES (?,?,?,?)");
            $stmt->execute([$phone, $name, $hash, $role]);
            echo "<li>✅ $name qo'shildi! Telefon: <b>$phone</b> | Parol: <b>$pass</b> | Rol: <b>$role</b></li>";
        }
    }
    echo "</ul>";

    echo "<h3>🎉 Barchasi tayyor!</h3>";
    echo "<p>Endi asosiy sahifaga yoki login ekraniga o'tib, yuqoridagi ma'lumotlar bilan tizimga kirishingiz mumkin.</p>";
    echo "<p><a href='login.html'>Login sahifasiga o'tish</a></p>";

    echo "<p style='color:red;'><b>DIQQAT:</b> Xavfsizlik uchun bu sahifadan bir marta foydalangandan so'ng, `create_users.php` faylini o'chirib tashlashni unutmang!</p>";

} catch (PDOException $e) {
    echo "<h3 style='color:red;'>❌ Xatolik yuz berdi!</h3>";
    echo "<p>MySQL ishlashida xatolik. MySQL/XAMPP ishga tushganiga ishonch hosil qiling.</p>";
    echo "<p>Error details: " . $e->getMessage() . "</p>";
}
?>
