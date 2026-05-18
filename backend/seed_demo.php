<?php
// backend/seed_demo.php
// Saqlangan va duplicate yaratmaydigan tarzda demo ishlarni tayyorlaydi.

require_once 'config.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $db = getDB();
    
    // Parollar
    $parentPassHash = password_hash('parent123', PASSWORD_DEFAULT);
    $staffPassHash  = password_hash('staff123', PASSWORD_DEFAULT);

    echo "1. Eski demo ma'lumotlarni tozalash...\n";
    // Delete existing demo users
    $db->exec("DELETE FROM users WHERE phone IN ('+998958137978', '+998958937978', '+998951111111', '+998952222222', '+998953333333', '+998954444444')");
    
    echo "2. Guruh va bolalarni tekshirish...\n";
    // Ensure we have at least one group
    $stmt = $db->query("SELECT id FROM groups LIMIT 1");
    $groupId = $stmt->fetchColumn();
    
    if (!$groupId) {
        $db->exec("INSERT INTO groups (name, teacher_name, age_range) VALUES ('⭐ Maxsus Guruh', 'Muallima', '4-5 yosh')");
        $groupId = $db->lastInsertId();
    }

    echo "3. 5 ta demo bola kiritilmoqda...\n";
    // We add 5 children if they don't exist by these exact names
    $childrenData = [
        ['Alibek Mashrapov', 'male'],
        ['Guli Toxirova', 'female'],
        ['Murod Aliyev', 'male'],
        ['Zarina Qurbonova', 'female'],
        ['Hasanboy Karimov', 'male']
    ];
    
    $childIds = [];
    foreach ($childrenData as $c) {
        $name = $c[0];
        $gender = $c[1];
        // Check if exists
        $stmt = $db->prepare('SELECT id FROM children WHERE full_name = ? AND group_id = ?');
        $stmt->execute([$name, $groupId]);
        $id = $stmt->fetchColumn();
        
        if (!$id) {
            $ins = $db->prepare('INSERT INTO children (full_name, group_id, birth_date, gender) VALUES (?, ?, ?, ?)');
            $ins->execute([$name, $groupId, '2020-05-10', $gender]);
            $id = $db->lastInsertId();
        }
        $childIds[] = $id;
    }

    echo "4. Ota-onalar va Xodimlar yaratilmoqda...\n";
    
    // Parents
    $parentsData = [
        ['Ismoil Mashrapov', '+998958137978', $childIds[0]], // Asosiy test parent
        ['Nasiba Toxirova',  '+998951111111', $childIds[1]],
        ['Alisher Aliyev',   '+998952222222', $childIds[2]],
        ['Malika Qurbonova', '+998953333333', $childIds[3]],
        ['Dilshod Karimov',  '+998954444444', $childIds[4]],
    ];
    
    $insUser = $db->prepare('INSERT INTO users (full_name, phone, password_hash, role, child_id) VALUES (?, ?, ?, "parent", ?)');
    foreach ($parentsData as $p) {
        $insUser->execute([$p[0], $p[1], $parentPassHash, $p[2]]);
    }
    
    // Staff
    $insStaff = $db->prepare('INSERT INTO users (full_name, phone, password_hash, role, child_id) VALUES (?, ?, ?, "staff", NULL)');
    $insStaff->execute(['Asila Muallima', '+998958937978', $staffPassHash]); // Asosiy test xodim
    $insStaff->execute(['Nodira Tarbiyachi', '+998959999999', $staffPassHash]);

    echo "======================================\n";
    echo "HOZIRGINA QO'SHILGAN TEST LOGINLAR:\n";
    echo "OTA-ONA LOGIN: +998958137978 | PAROL: parent123 \n";
    echo "XODIM LOGIN: +998958937978 | PAROL: staff123 \n";
    echo "======================================\n";
    echo "Muvaffaqiyatli yakunlandi!\n";

} catch (PDOException $e) {
    echo "Ma'lumotlar bazasida xatolik: " . $e->getMessage() . "\n";
}
