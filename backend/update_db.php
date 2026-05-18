<?php
require_once 'config.php';

try {
    $db = getDB();

    // 1. Create camera_restrictions table
    $db->exec("CREATE TABLE IF NOT EXISTS camera_restrictions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        target_role VARCHAR(20) NOT NULL UNIQUE, -- 'all' (by admin) or 'parent' (by staff)
        restricted_by INT NOT NULL,
        restricted_until DATETIME NOT NULL,
        FOREIGN KEY (restricted_by) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "✅ 'camera_restrictions' table checked/created.\n";
    
    // Add activity_logs table for Admin Stats
    $db->exec("CREATE TABLE IF NOT EXISTS activity_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NULL,
        action VARCHAR(100) NOT NULL,
        details TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )");
    echo "✅ 'activity_logs' table checked/created.\n";
    
    // Add birth_date column to children table if it does not exist
    try {
        $db->exec("ALTER TABLE children ADD COLUMN birth_date DATE NULL");
    } catch (PDOException $e) {
        if ($e->getCode() != '42S21') echo "Warning on altering children table: " . $e->getMessage() . "\n";
    }
    echo "✅ 'children' table updated with birth_date.\n";

    // Add telegram_chat_id column to users table if it does not exist
    try {
        $db->exec("ALTER TABLE users ADD COLUMN telegram_chat_id BIGINT DEFAULT NULL");
    } catch (PDOException $e) {
        if ($e->getCode() != '42S21') echo "Warning on altering users table: " . $e->getMessage() . "\n";
    }
    echo "✅ 'users' table updated with telegram_chat_id.\n";

    echo "🎉 Database update completed successfully.\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
