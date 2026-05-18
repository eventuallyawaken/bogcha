-- ============================================================
-- KINDERGARTEN — Ma'lumotlar Bazasi
-- XAMPP da ishlatish uchun:
--   phpMyAdmin ni oching → "bogcha" nomli DB yarating →
--   shu faylni import qiling
-- ============================================================



-- ------------------------------------------------------------
-- 1. Guruhlar jadvali
-- ------------------------------------------------------------
CREATE TABLE groups (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    teacher_name VARCHAR(150),
    description TEXT,
    age_range VARCHAR(50),
    -- 📹 Kamera URL ni shu yerga qo'ying (har guruh uchun alohida)
    camera_url VARCHAR(500) DEFAULT NULL,
    camera_type ENUM('hls','mjpeg','iframe') DEFAULT 'hls',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- 2. Bolalar jadvali
-- ------------------------------------------------------------
CREATE TABLE children (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(150) NOT NULL,
    group_id INT NOT NULL,
    birth_date DATE,
    -- 🖼️ Bola fotosini o'zgartirish: images/children/ papkasiga foto.jpg qo'ying
    photo VARCHAR(300) DEFAULT 'images/children/default.png',
    gender ENUM('male','female') DEFAULT 'male',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- 3. Foydalanuvchilar jadvali (ota-onalar + xodimlar + admin)
-- ------------------------------------------------------------
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,       -- login sifatida ishlatiladi
    email VARCHAR(150),
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('parent','staff','admin') DEFAULT 'parent',
    child_id INT DEFAULT NULL,               -- faqat ota-onalar uchun
    telegram_chat_id BIGINT DEFAULT NULL,    -- bot /start dan to'ldiriladi
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (child_id) REFERENCES children(id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- 4. Davomat jadvali
-- ------------------------------------------------------------
CREATE TABLE attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    child_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present','absent','sick','excused') DEFAULT 'absent',
    note VARCHAR(300),
    recorded_by INT,                         -- xodim ID
    notification_sent TINYINT DEFAULT 0,     -- Telegram yuborilganmi?
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_child_date (child_id, date),
    FOREIGN KEY (child_id) REFERENCES children(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- 5. Baholar jadvali
-- ------------------------------------------------------------
CREATE TABLE grades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    child_id INT NOT NULL,
    -- Fan nomlari: o'zingiz o'zgartirishingiz mumkin
    subject VARCHAR(100) NOT NULL,
    grade VARCHAR(10) NOT NULL,              -- masalan: "5", "A", "Yaxshi"
    period VARCHAR(50),                      -- masalan: "2025-May"
    note VARCHAR(300),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (child_id) REFERENCES children(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- 6. Bildirishnomalar tarixi
-- ------------------------------------------------------------
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sent_by INT NOT NULL,
    group_id INT DEFAULT NULL,               -- NULL = hammaga
    title VARCHAR(200),
    message TEXT NOT NULL,
    channels VARCHAR(100) DEFAULT 'telegram', -- 'telegram', 'email', 'both'
    recipient_count INT DEFAULT 0,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sent_by) REFERENCES users(id),
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- 7. Kuzatuv (Activity Logs) jadvali
-- ------------------------------------------------------------
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    details VARCHAR(300),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================
-- DEMO MA'LUMOTLAR (Namuna uchun)
-- ============================================================

-- Guruhlar
INSERT INTO groups (name, teacher_name, description, age_range) VALUES
('Nilufar', 'Nilufar Hasanova', 'Kichkintoylar guruhi — erkin o\'yin va ijod', '3-4 yosh'),
('Bahor', 'Mohira Toshmatova', 'O\'rta guruh — harf va raqam o\'rganish', '4-5 yosh'),
('Yulduzcha', 'Dildora Mirzayeva', 'Katta guruh — maktabga tayyorlov', '5-6 yosh');

-- Bolalar — Nilufar guruhi (group_id=1)
INSERT INTO children (full_name, group_id, birth_date, gender) VALUES
('Aziza Karimova',    1, '2022-03-15', 'female'),
('Bobur Toshmatov',   1, '2022-07-22', 'male'),
('Dilnoza Yusupova',  1, '2021-11-08', 'female'),
('Eldor Rakhimov',    1, '2022-01-30', 'male'),
('Feruza Nazarova',   1, '2022-05-17', 'female');

-- Bolalar — Bahor guruhi (group_id=2)
INSERT INTO children (full_name, group_id, birth_date, gender) VALUES
('Gulnora Mirzayeva', 2, '2021-02-14', 'female'),
('Hamza Abdullayev',  2, '2020-09-03', 'male'),
('Iroda Askarova',    2, '2021-06-25', 'female'),
('Jasur Kholmatov',   2, '2020-12-11', 'male'),
('Kamola Ergasheva',  2, '2021-04-19', 'female');

-- Bolalar — Yulduzcha guruhi (group_id=3)
INSERT INTO children (full_name, group_id, birth_date, gender) VALUES
('Lobar Sultonova',   3, '2019-08-07', 'female'),
('Mansur Tursunov',   3, '2019-11-23', 'male'),
('Nodira Ismoilova',  3, '2020-02-16', 'female'),
('Otabek Xolmatov',   3, '2019-05-04', 'male'),
('Parizod Haydarova', 3, '2020-01-28', 'female');

-- Foydalanuvchilar
-- ⚠️ Parollar: password_hash("parent123") — PHP da o'zgartiriladi
-- Demo uchun: telefon raqam = login, parol = parent123 (ota-onalar), staff123 (xodimlar)
INSERT INTO users (full_name, phone, email, password_hash, role, child_id) VALUES
-- Ota-onalar (Nilufar guruhi)
('Dilrabo Karimova',    '+998901001001', 'dilrabo@mail.uz',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent', 1),
('Sherzod Toshmatov',   '+998901001002', 'sherzod@mail.uz',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent', 2),
('Muazzam Yusupova',    '+998901001003', 'muazzam@mail.uz',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent', 3),
('Ulugbek Rakhimov',    '+998901001004', 'ulugbek@mail.uz',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent', 4),
('Barno Nazarova',      '+998901001005', 'barno@mail.uz',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent', 5),
-- Ota-onalar (Bahor guruhi)
('Zulfiya Mirzayeva',   '+998901001006', 'zulfiya@mail.uz',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent', 6),
('Sanjar Abdullayev',   '+998901001007', 'sanjar@mail.uz',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent', 7),
('Malika Askarova',     '+998901001008', 'malika@mail.uz',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent', 8),
('Nodir Kholmatov',     '+998901001009', 'nodir@mail.uz',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent', 9),
('Maftuna Ergasheva',   '+998901001010', 'maftuna@mail.uz',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent', 10),
-- Ota-onalar (Yulduzcha guruhi)
('Lola Sultonova',      '+998901001011', 'lola@mail.uz',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent', 11),
('Ravshan Tursunov',    '+998901001012', 'ravshan@mail.uz',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent', 12),
('Shahlo Ismoilova',    '+998901001013', 'shahlo@mail.uz',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent', 13),
('Jasur Xolmatov',      '+998901001014', 'jasurx@mail.uz',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent', 14),
('Hulkar Haydarova',    '+998901001015', 'hulkar@mail.uz',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent', 15),
-- Xodimlar (parol: staff123)
('Nilufar Hasanova',    '+998901002001', 'nilufar.h@mail.uz','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', NULL),
('Mohira Toshmatova',   '+998901002002', 'mohira.t@mail.uz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', NULL),
('Dildora Mirzayeva',   '+998901002003', 'dildora.m@mail.uz','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', NULL),
-- Admin (login: +998900000001, parol: admin123)
('Administrator',       '+998900000001', 'admin@kindergarten.uz','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL);

-- Demo baholar
INSERT INTO grades (child_id, subject, grade, period, created_by) VALUES
(1, 'Harf o\'rganish', '5', '2025-May', 16),
(1, 'Raqamlar',        '4', '2025-May', 16),
(1, 'Chizmachilik',    '5', '2025-May', 16),
(2, 'Harf o\'rganish', '4', '2025-May', 16),
(2, 'Raqamlar',        '5', '2025-May', 16),
(2, 'Chizmachilik',    '4', '2025-May', 16);

-- ============================================================
-- ESLATMA: Demo parollar hammasi "parent123"
-- Haqiqiy ishlatishda har bir ota-onaga alohida parol bering
-- backend/create_user.php faylidan foydalaning (ishchi versiya)
-- ============================================================
