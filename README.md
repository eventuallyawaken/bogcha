# 🏫 Kindergarten — O'rnatish Qo'llanmasi

> **Manzil:** Furqat tumani, Tomosha qishlog'i

---

## 📦 Kerakli dasturlar

| Dastur | Maqsad | Yuklab olish |
|--------|--------|-------------|
| **XAMPP** | PHP + MySQL server | [xampp.org](https://www.apachefriends.org) |
| **Python 3.10+** | Telegram bot | [python.org](https://python.org) |
| **VS Code** | Kod tahrirlash | Allaqachon bor |

---

## 🚀 1-QADAM: XAMPP o'rnatish

1. XAMPP ni yuklab oling va o'rnating
2. XAMPP Control Panel ni oching
3. **Apache** va **MySQL** ni **Start** qiling ✅
4. Brauzerda `http://localhost` oching — ishlayotganini tekshiring

---

## 🗄️ 2-QADAM: Ma'lumotlar bazasini yaratish

1. Brauzerda `http://localhost/phpmyadmin` oching
2. Chap tomonda **"New"** ni bosing
3. Database nomi: **`bogcha`** kiriting → **Create**
4. Yuqoridan **"Import"** ni bosing
5. **"Choose file"** → `C:\Users\user\Desktop\bogcha\database\schema.sql` tanlang
6. Pastda **"Go"** tugmasini bosing ✅

---

## 📁 3-QADAM: Fayllarni XAMPP ga ko'chirish

Sayt fayllarini XAMPP papkasiga nusxalang:

```
Manba:      C:\Users\user\Desktop\bogcha\
Ko'chirish: C:\xampp\htdocs\bogcha\
```

**Qanday qilinadi:**
1. `bogcha` papkasini nusxalang (Ctrl+C)
2. `C:\xampp\htdocs\` ga o'ting
3. Joylang (Ctrl+V)

---

## ⚙️ 4-QADAM: Config faylini sozlash

`C:\xampp\htdocs\bogcha\backend\config.php` faylini oching:

```php
// 🤖 TELEGRAM_BOT_TOKEN — tokeningizni kiriting
define('TELEGRAM_BOT_TOKEN', 'BU_YERGA_TOKEN');

// 📧 EMAIL — Gmail hisobingizni kiriting
define('SMTP_USER', 'sizning@gmail.com');
define('SMTP_PASS', '16-belgilik-app-password');
```

---

## 🤖 5-QADAM: Telegram Bot yaratish

1. Telegramda **@BotFather** ni oching
2. `/newbot` yuboring
3. Bot nomini kiriting: `Kindergarten Furqat`
4. Username kiriting: `kindergarten_furqat_bot`
5. Token oling (shunday ko'rinadi: `1234567890:ABCdef...`) 8615527855:AAHqCQ8FqpCNAaE44bUJKMlsothE0-B3_JI
6. Tokenni `config.php` va `bot/bot.py` ga kiriting

---

## 🐍 6-QADAM: Telegram Botni ishga tushirish

```bash
# 1. Bot papkasiga o'ting
cd C:\Users\user\Desktop\bogcha\bot

# 2. Kerakli kutubxonalarni o'rnating (bir marta)
pip install -r requirements.txt

# 3. Botni ishga tushiring
python bot.py
```

Bot `✅ Bot tayyor!` desa — ishlayapti!

---

## 🌐 7-QADAM: Saytni ochish

Brauzerda: `http://localhost/bogcha/`

---

## 👤 Demo login ma'lumotlari

| Rol | Login | Parol |
|-----|-------|-------|
| **Ota-ona** | +998901001001 | parent123 |
| **Xodim** | +998901002001 | staff123 |
| **Admin** | +998900000001 | admin123 |

> ⚠️ Haqiqiy ishlatishda parollarni o'zgartiring!

---

## 📹 8-QADAM: Kamera ulash (ixtiyoriy)

1. Admin panelga kiring (xodim hisobi bilan)
2. **Kameralar** bo'limiga o'ting
3. Pastdagi formada:
   - Guruhni tanlang
   - Kamera URL sini kiriting (kamera ilovangizdan oling)
   - Turni tanlang (HLS / MJPEG / Iframe)
4. **Saqlash** bosing

**Kamera URL ni qayerdan olish:**
- Hikvision: http://kamera-ip/Streaming/Channels/1/httpPreview
- TP-Link / Imou: Ilovada "Share" → URL
- Boshqa: Kamera sozlamalaridan stream URL ni toping

---

## 📧 Email sozlash (Gmail)

1. Google hisobingizga kiring
2. **Manage Account → Security → 2-Step Verification** ni yoqing
3. **App passwords** ga kiring
4. `Mail` va qurilmani tanlang → Parol oling (16 belgi)
5. Bu parolni `config.php` dagi `SMTP_PASS` ga kiriting

---

## ❓ Muammolar

**XAMPP ishlamayapti?**
→ Apache port 80 band bo'lishi mumkin. XAMPP da portni 8080 ga o'zgartiring, keyin `http://localhost:8080/bogcha/` oching.

**Database ulanmayapti?**
→ `config.php` da DB_PASS bo'sh qolganini tekshiring.

**Telegram bot javob bermayapti?**
→ `bot.py` da BOT_TOKEN to'g'ri ekanligini tekshiring.

---

## 📞 Fayl tuzilishi

```
bogcha/
├── index.html              ← Bosh sahifa (ommaviy)
├── login.html              ← Kirish
├── dashboard-parent.html   ← Ota-ona paneli
├── dashboard-admin.html    ← Admin/xodim paneli
├── css/style.css           ← Barcha stillar
├── js/lang.js              ← 3 tilli tarjima
├── backend/
│   ├── config.php          ← 🔧 SOZI: DB + Token + Email
│   ├── login.php
│   ├── get_data.php
│   ├── attendance.php      ← Davomat + Telegram
│   ├── broadcast.php       ← Bildirishnoma
│   └── ...
├── bot/
│   ├── bot.py              ← 🤖 Telegram bot
│   └── requirements.txt
└── database/
    └── schema.sql          ← DB tuzilishi + namuna ma'lumotlar
```
