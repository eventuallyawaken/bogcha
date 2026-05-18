// ============================================================
// lang.js — 3 Tilli Tarjima Tizimi (UZ / RU / EN)
// ============================================================
// Bu fayl barcha sahifalar uchun tarjimalarni saqlaydi.
// Yangi so'z qo'shish uchun 3 ta tilga ham tarjimani kiriting.

const TRANSLATIONS = {
  uz: {
    // Nav
    nav_about: "Biz haqimizda", nav_groups: "Guruhlar",
    nav_contact: "Aloqa", nav_login: "Kirish",
    // Hero
    hero_tag: "🏫 Furqat tumani, Tomosha qishlog'i",
    hero_title: "Farzandlaringiz xavfsizligi – bizning ustuvorligimiz",
    hero_subtitle: "Onlayn kamera, davomat va bildirishnomalar bir joyda.",
    hero_btn: "Kirish", hero_btn2: "Ko'proq bilish",
    // Stats
    stat_children: "ta bola", stat_groups: "ta guruh", stat_years: "yillik tajriba",
    // Features
    feat_section_tag: "Imkoniyatlar", feat_section_title: "Ota-onalar uchun maxsus funksiyalar",
    feat_camera_title: "Online Kamera",   feat_camera_desc: "Farzandingizni real vaqtda kuzating",
    feat_att_title: "Davomat",            feat_att_desc: "Kunlik davomat darhol Telegram orqali bildiriladi",
    feat_notify_title: "Bildirishnomalar",feat_notify_desc: "Telegram va email orqali barcha xabarlar",
    feat_grades_title: "Baholar",         feat_grades_desc: "Farzandingizning o'qish natijalari",
    // Groups
    groups_tag: "Guruhlar", groups_title: "Bizning guruhlar",
    group1_name: "🌸 Nilufar", group1_teacher: "O'qituvchi: Nilufar Hasanova", group1_age: "3–4 yosh",
    group2_name: "🌿 Bahor",   group2_teacher: "O'qituvchi: Mohira Toshmatova", group2_age: "4–5 yosh",
    group3_name: "⭐ Yulduzcha",group3_teacher: "O'qituvchi: Dildora Mirzayeva",group3_age: "5–6 yosh",
    // Contact
    contact_tag: "Aloqa", contact_title: "Biz bilan bog'laning",
    contact_addr_title: "Manzil", contact_addr: "Furqat tumani, Tomosha qishlog'i",
    contact_phone_title: "Telefon", contact_hours_title: "Ish vaqti",
    contact_hours: "Dush–Juma, 08:00–18:00",
    // Footer
    footer_desc: "Farzandlaringiz xavfsiz va qo'llab-quvvatlangan muhitda o'sishiga yordam beramiz.",
    footer_links: "Havolalar", footer_contact: "Aloqa",
    footer_rights: "© 2025 Kindergarten. Barcha huquqlar himoyalangan.",
    // Login
    login_title: "Tizimga kirish", login_subtitle: "Telefon va parolni kiriting",
    login_phone: "Telefon raqam (+998...)", login_pass: "Parol",
    login_btn: "Kirish", login_back: "← Bosh sahifaga",
    login_error: "Noto'g'ri login yoki parol",
    login_help: "Parolni yo'qotdingizmi?",
    login_help_link: "Bog'chaga qo'ng'iroq qiling",
    // Dashboard (parent)
    dash_home: "Bosh sahifa", dash_child: "Farzandim",
    dash_attendance: "Davomat", dash_cameras: "Kameralar",
    dash_grades: "Baholar", dash_messages: "Xabarlar",
    dash_logout: "Chiqish",
    dash_welcome: "Xush kelibsiz",
    dash_child_profile: "Bolam profili",
    dash_today_att: "Bugungi davomat",
    dash_month_att: "Oylik davomat",
    dash_present: "Keldi ✅", dash_absent: "Kelmadi ❌", dash_sick: "Kasal 🤒",
    dash_group: "Guruh", dash_age: "Yoshi", dash_birthday: "Tug'ilgan kuni",
    // Admin
    admin_dash: "Bosh sahifa", admin_att: "Davomat olish",
    admin_cameras: "Barcha kameralar", admin_notify: "Bildirishnoma",
    admin_children: "Bolalar ro'yxati",
    admin_select_group: "Guruhni tanlang",
    admin_save_att: "💾 Saqlash va Yuborish",
    admin_att_date: "Sana",
    admin_broadcast_title: "Bildirishnoma yuborish",
    admin_broadcast_target: "Kimga:",
    admin_target_all: "Hammaga",
    admin_broadcast_msg: "Xabar matni",
    admin_send_telegram: "📤 Telegram orqali yuborish",
    admin_send_email: "📧 Email orqali yuborish",
    admin_send_both: "📤 Ikkalasiga yuborish",
    // Camera
    cam_no_url: "📹 Kamera ulanmagan",
    cam_help: "Administrator kamera URL sini kiritishi kerak",
    // Status
    status_present: "Keldi", status_absent: "Kelmadi",
    status_sick: "Kasal", status_excused: "Sababli",
  },

  ru: {
    nav_about: "О нас", nav_groups: "Группы",
    nav_contact: "Контакт", nav_login: "Войти",
    hero_tag: "🏫 Туман Фурқат, кишлак Томоша",
    hero_title: "Безопасность ваших детей — наш приоритет",
    hero_subtitle: "Онлайн-камера, посещаемость и уведомления в одном месте.",
    hero_btn: "Войти", hero_btn2: "Узнать больше",
    stat_children: "детей", stat_groups: "группы", stat_years: "лет опыта",
    feat_section_tag: "Возможности", feat_section_title: "Специальные функции для родителей",
    feat_camera_title: "Онлайн камера",   feat_camera_desc: "Наблюдайте за ребёнком в реальном времени",
    feat_att_title: "Посещаемость",       feat_att_desc: "Ежедневная посещаемость отправляется в Telegram",
    feat_notify_title: "Уведомления",     feat_notify_desc: "Все сообщения через Telegram и email",
    feat_grades_title: "Оценки",          feat_grades_desc: "Результаты обучения вашего ребёнка",
    groups_tag: "Группы", groups_title: "Наши группы",
    group1_name: "🌸 Нилуфар", group1_teacher: "Учитель: Нилуфар Хасанова", group1_age: "3–4 года",
    group2_name: "🌿 Бахор",   group2_teacher: "Учитель: Мохира Тошматова",  group2_age: "4–5 лет",
    group3_name: "⭐ Юлдузча", group3_teacher: "Учитель: Дилдора Мирзаева",  group3_age: "5–6 лет",
    contact_tag: "Контакт", contact_title: "Свяжитесь с нами",
    contact_addr_title: "Адрес", contact_addr: "Туман Фурқат, кишлак Томоша",
    contact_phone_title: "Телефон", contact_hours_title: "Часы работы",
    contact_hours: "Пн–Пт, 08:00–18:00",
    footer_desc: "Помогаем вашим детям расти в безопасной и поддерживающей среде.",
    footer_links: "Ссылки", footer_contact: "Контакт",
    footer_rights: "© 2025 Kindergarten. Все права защищены.",
    login_title: "Вход в систему", login_subtitle: "Введите телефон и пароль",
    login_phone: "Номер телефона (+998...)", login_pass: "Пароль",
    login_btn: "Войти", login_back: "← На главную",
    login_error: "Неверный логин или пароль",
    login_help: "Забыли пароль?", login_help_link: "Позвоните в детский сад",
    dash_home: "Главная", dash_child: "Мой ребёнок",
    dash_attendance: "Посещаемость", dash_cameras: "Камеры",
    dash_grades: "Оценки", dash_messages: "Сообщения", dash_logout: "Выйти",
    dash_welcome: "Добро пожаловать",
    dash_child_profile: "Профиль ребёнка", dash_today_att: "Посещаемость сегодня",
    dash_month_att: "Посещаемость за месяц",
    dash_present: "Пришёл ✅", dash_absent: "Не пришёл ❌", dash_sick: "Болен 🤒",
    dash_group: "Группа", dash_age: "Возраст", dash_birthday: "Дата рождения",
    admin_dash: "Главная", admin_att: "Отметить посещаемость",
    admin_cameras: "Все камеры", admin_notify: "Уведомления", admin_children: "Дети",
    admin_select_group: "Выберите группу", admin_save_att: "💾 Сохранить и отправить",
    admin_att_date: "Дата", admin_broadcast_title: "Отправить уведомление",
    admin_broadcast_target: "Кому:", admin_target_all: "Всем",
    admin_broadcast_msg: "Текст сообщения",
    admin_send_telegram: "📤 Отправить в Telegram",
    admin_send_email: "📧 Отправить на Email",
    admin_send_both: "📤 Отправить обоим",
    cam_no_url: "📹 Камера не подключена", cam_help: "Администратор должен добавить URL камеры",
    status_present: "Пришёл", status_absent: "Не пришёл", status_sick: "Болен", status_excused: "По уважит. причине",
  },

  en: {
    nav_about: "About", nav_groups: "Groups",
    nav_contact: "Contact", nav_login: "Login",
    hero_tag: "🏫 Furqat District, Tomosha Village",
    hero_title: "Your children's safety is our priority",
    hero_subtitle: "Live cameras, attendance tracking, and notifications — all in one place.",
    hero_btn: "Login", hero_btn2: "Learn More",
    stat_children: "children", stat_groups: "groups", stat_years: "years experience",
    feat_section_tag: "Features", feat_section_title: "Special features for parents",
    feat_camera_title: "Live Camera",   feat_camera_desc: "Watch your child in real time",
    feat_att_title: "Attendance",       feat_att_desc: "Daily attendance sent via Telegram instantly",
    feat_notify_title: "Notifications", feat_notify_desc: "All messages through Telegram and email",
    feat_grades_title: "Grades",        feat_grades_desc: "Your child's academic results",
    groups_tag: "Groups", groups_title: "Our Groups",
    group1_name: "🌸 Nilufar", group1_teacher: "Teacher: Nilufar Hasanova", group1_age: "Ages 3–4",
    group2_name: "🌿 Bahor",   group2_teacher: "Teacher: Mohira Toshmatova", group2_age: "Ages 4–5",
    group3_name: "⭐ Yulduzcha",group3_teacher: "Teacher: Dildora Mirzayeva",group3_age: "Ages 5–6",
    contact_tag: "Contact", contact_title: "Get In Touch",
    contact_addr_title: "Address", contact_addr: "Furqat District, Tomosha Village",
    contact_phone_title: "Phone", contact_hours_title: "Working Hours",
    contact_hours: "Mon–Fri, 08:00–18:00",
    footer_desc: "Helping your children grow in a safe and supportive environment.",
    footer_links: "Links", footer_contact: "Contact",
    footer_rights: "© 2025 Kindergarten. All rights reserved.",
    login_title: "Sign In", login_subtitle: "Enter your phone and password",
    login_phone: "Phone number (+998...)", login_pass: "Password",
    login_btn: "Sign In", login_back: "← Back to Home",
    login_error: "Incorrect login or password",
    login_help: "Forgot your password?", login_help_link: "Call the kindergarten",
    dash_home: "Home", dash_child: "My Child",
    dash_attendance: "Attendance", dash_cameras: "Cameras",
    dash_grades: "Grades", dash_messages: "Messages", dash_logout: "Sign Out",
    dash_welcome: "Welcome",
    dash_child_profile: "Child Profile", dash_today_att: "Today's Attendance",
    dash_month_att: "Monthly Attendance",
    dash_present: "Present ✅", dash_absent: "Absent ❌", dash_sick: "Sick 🤒",
    dash_group: "Group", dash_age: "Age", dash_birthday: "Birthday",
    admin_dash: "Dashboard", admin_att: "Take Attendance",
    admin_cameras: "All Cameras", admin_notify: "Notifications", admin_children: "Children",
    admin_select_group: "Select a group", admin_save_att: "💾 Save & Notify",
    admin_att_date: "Date", admin_broadcast_title: "Send Notification",
    admin_broadcast_target: "To:", admin_target_all: "Everyone",
    admin_broadcast_msg: "Message text",
    admin_send_telegram: "📤 Send via Telegram",
    admin_send_email: "📧 Send via Email",
    admin_send_both: "📤 Send to Both",
    cam_no_url: "📹 Camera not connected", cam_help: "Administrator must add the camera URL",
    status_present: "Present", status_absent: "Absent", status_sick: "Sick", status_excused: "Excused",
  }
};

// ============================================================
// Language switcher logic
// ============================================================
let currentLang = localStorage.getItem('lang') || 'uz';

function applyLang(lang) {
  currentLang = lang;
  localStorage.setItem('lang', lang);
  const t = TRANSLATIONS[lang];
  if (!t) return;

  // data-lang="key" bo'lgan barcha elementlarni yangilash
  document.querySelectorAll('[data-lang]').forEach(el => {
    const key = el.getAttribute('data-lang');
    if (t[key] !== undefined) el.textContent = t[key];
  });

  // Placeholder uchun
  document.querySelectorAll('[data-lang-placeholder]').forEach(el => {
    const key = el.getAttribute('data-lang-placeholder');
    if (t[key] !== undefined) el.placeholder = t[key];
  });

  // Til tugmalarini yangilash
  document.querySelectorAll('.lang-btn').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.langCode === lang);
  });

  // HTML lang attributini o'zgartirish
  document.documentElement.lang = lang;
}

// Sahifa yuklanganda
document.addEventListener('DOMContentLoaded', () => {
  // Til tugmalariga click listener
  document.querySelectorAll('.lang-btn').forEach(btn => {
    btn.addEventListener('click', () => applyLang(btn.dataset.langCode));
  });
  // Saqlangan tilni qo'llash
  applyLang(currentLang);
});

// Boshqa fayllardan foydalanish uchun
function t(key) {
  return (TRANSLATIONS[currentLang] || TRANSLATIONS['uz'])[key] || key;
}
