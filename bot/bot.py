# ============================================================
# Kindergarten Telegram Bot
# ============================================================
# O'rnatish: pip install python-telegram-bot requests
# Ishga tushirish: python bot.py
#
# 🤖 Bot yaratish:
#   1. Telegramda @BotFather ni oching
#   2. /newbot yuboring
#   3. Bot nomini kiriting (masalan: KindergartenFurqatBot)
#   4. Username kiriting (masalan: kindergarten_furqat_bot)
#   5. Token olasiz — quyidagi BOT_TOKEN ga qo'ying
# ============================================================

import logging
import requests
from telegram import Update, ReplyKeyboardMarkup, KeyboardButton
from telegram.ext import (
    Application, CommandHandler, MessageHandler,
    filters, ContextTypes, ConversationHandler
)

# ============================================================
# SOZLAMALAR — shu yerlarni o'zgartiring
# ============================================================

# 🤖 @BotFather dan olgan tokeningiz
BOT_TOKEN = "8615527855:AAHqCQ8FqpCNAaE44bUJKMlsothE0-B3_JI"

# 🌐 Sayt backend manzili (XAMPP da localhost, serverda domain)
API_BASE = "https://bogcha.infinityfreeapp.com/backend"

# ============================================================
# Logging
# ============================================================
logging.basicConfig(
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    level=logging.INFO
)
logger = logging.getLogger(__name__)

# Conversation states
WAITING_PHONE = 1

# ============================================================
# /start buyrug'i — Ota-ona ro'yxatdan o'tadi
# ============================================================
async def start(update: Update, context: ContextTypes.DEFAULT_TYPE):
    user = update.effective_user
    chat_id = update.effective_chat.id

    # Telefon raqam so'rash tugmasi
    keyboard = [[KeyboardButton("📱 Telefon raqamimni yuborish", request_contact=True)]]
    reply_markup = ReplyKeyboardMarkup(keyboard, one_time_keyboard=True, resize_keyboard=True)

    await update.message.reply_text(
        f"Salom, {user.first_name}! 👋\n\n"
        "🏫 <b>Kindergarten</b> botiga xush kelibsiz!\n\n"
        "Bu bot orqali:\n"
        "✅ Farzandingizning davomat bildirishnomalarini olasiz\n"
        "📢 Bog'chadan barcha xabarlarni qabul qilasiz\n\n"
        "Boshlash uchun telefon raqamingizni yuboring 👇",
        parse_mode='HTML',
        reply_markup=reply_markup
    )
    return WAITING_PHONE

# ============================================================
# Telefon raqam qabul qilindi
# ============================================================
async def contact_received(update: Update, context: ContextTypes.DEFAULT_TYPE):
    contact = update.message.contact
    phone = contact.phone_number
    chat_id = update.effective_chat.id

    if not phone.startswith('+'):
        phone = '+' + phone

    try:
        response = requests.post(
            f"{API_BASE}/save_telegram.php",
            json={"phone": phone, "chat_id": chat_id},
            timeout=10
        )

        logger.info("STATUS: %s", response.status_code)
        logger.info("BODY: %s", response.text)

        data = response.json()

        if data.get("success"):
            parent_name = data.get("name", "")
            child_name = data.get("child_name", "")
            group_name = data.get("group_name", "")

            await update.message.reply_text(
                f"✅ <b>Muvaffaqiyatli ulandi!</b>\n\n"
                f"👤 Siz: {parent_name}\n"
                f"👦 Farzandingiz: {child_name}\n"
                f"🌸 Guruh: {group_name}\n\n"
                "Endi barcha bildirishnomalar shu chatga keladi!\n\n"
                "📋 Buyruqlar:\n"
                "/davomat — Bugungi davomat\n"
                "/tarix — Oxirgi 30 kun\n"
                "/help — Yordam",
                parse_mode='HTML'
            )
        else:
            await update.message.reply_text(
                f"❌ Telefon raqam topilmadi: {phone}\n\n"
                "Iltimos admin bilan bog'laning:\n"
                "📞 +998 90 123-45-67"
            )

    except Exception as e:
        logger.error(f"save_telegram xatosi: {e}")
        await update.message.reply_text(
            "❌ Serverga ulanib bo'lmadi. Qaytadan urinib ko'ring yoki admin bilan bog'laning."
        )

    return ConversationHandler.END

# ============================================================
# /davomat — Bugungi davomat holati
# ============================================================
async def davomat(update: Update, context: ContextTypes.DEFAULT_TYPE):
    chat_id = update.effective_chat.id
    try:
        res  = requests.get(f"{API_BASE}/bot_api.php?action=davomat&chat_id={chat_id}", timeout=10)
        data = res.json()
        if data.get("success"):
            a = data["attendance"]
            status_map = {
                "present": "✅ Keldi",
                "absent":  "❌ Kelmadi",
                "sick":    "🤒 Kasal",
                "excused": "📋 Sababli"
            }
            status_text = status_map.get(a.get("status"), "Belgilanmagan")
            msg = (
                f"📊 <b>Bugungi davomat</b>\n\n"
                f"👦 {data['child_name']}\n"
                f"📅 {a.get('date', '—')}\n"
                f"📌 Holat: {status_text}\n"
            )
            if a.get("note"):
                msg += f"📝 Izoh: {a['note']}"
        else:
            msg = "ℹ️ Bugungi davomat hali olinmagan yoki siz ro'yxatdan o'tmagansiz."
        await update.message.reply_text(msg, parse_mode='HTML')
    except Exception as e:
        await update.message.reply_text("❌ Ma'lumot olishda xato")

# ============================================================
# /tarix — Oxirgi 30 kun davomati
# ============================================================
async def tarix(update: Update, context: ContextTypes.DEFAULT_TYPE):
    chat_id = update.effective_chat.id
    try:
        res  = requests.get(f"{API_BASE}/bot_api.php?action=tarix&chat_id={chat_id}", timeout=10)
        data = res.json()
        if data.get("success"):
            records = data.get("records", [])
            if not records:
                await update.message.reply_text("📋 Davomat tarixi yo'q")
                return
            lines = [f"📊 <b>Oxirgi davomatlar</b> — {data.get('child_name','')}\n"]
            icons = {"present":"✅","absent":"❌","sick":"🤒","excused":"📋"}
            for r in records[-20:]:  # oxirgi 20 ta
                icon = icons.get(r["status"], "—")
                lines.append(f"{icon} {r['date']}")
            present = sum(1 for r in records if r["status"] == "present")
            total   = len(records)
            lines.append(f"\n📈 Davomat: {present}/{total} kun ({round(present/total*100) if total else 0}%)")
            await update.message.reply_text("\n".join(lines), parse_mode='HTML')
        else:
            await update.message.reply_text("❌ Ma'lumot topilmadi. /start orqali ro'yxatdan o'ting.")
    except Exception as e:
        await update.message.reply_text("❌ Xato yuz berdi")

# ============================================================
# /help — Yordam
# ============================================================
async def help_command(update: Update, context: ContextTypes.DEFAULT_TYPE):
    await update.message.reply_text(
        "🏫 <b>Kindergarten Bot</b>\n\n"
        "Buyruqlar:\n"
        "/start — Botga ulanish\n"
        "/davomat — Bugungi davomat\n"
        "/tarix — Oxirgi 30 kunlik davomat\n"
        "/help — Shu xabar\n\n"
        "📞 Bog'lanish: <a href='tel:+998901234567'>+998 90 123-45-67</a>",
        parse_mode='HTML'
    )

# ============================================================
# Noma'lum xabar
# ============================================================
async def unknown(update: Update, context: ContextTypes.DEFAULT_TYPE):
    await update.message.reply_text(
        "❓ Buyruqni tushunmadim.\n"
        "/help — buyruqlar ro'yxati"
    )

# ============================================================
# MAIN — Botni ishga tushirish
# ============================================================
def main():
    if BOT_TOKEN == "YOUR_BOT_TOKEN_HERE":
        print("❌ BOT_TOKEN o'rnatilmagan! bot.py faylini oching va tokeningizni kiriting.")
        return

    print("🤖 Telegram bot ishga tushmoqda...")
    print(f"   API: {API_BASE}")

    app = Application.builder().token(BOT_TOKEN).build()

    # /start + telefon raqam conversation
    conv_handler = ConversationHandler(
        entry_points=[CommandHandler("start", start)],
        states={
            WAITING_PHONE: [
                MessageHandler(filters.CONTACT, contact_received),
            ]
        },
        fallbacks=[CommandHandler("help", help_command)],
    )

    app.add_handler(conv_handler)
    app.add_handler(CommandHandler("davomat", davomat))
    app.add_handler(CommandHandler("tarix",   tarix))
    app.add_handler(CommandHandler("help",    help_command))
    app.add_handler(MessageHandler(filters.TEXT & ~filters.COMMAND, unknown))

    print("✅ Bot tayyor! Telegramda /start yuboring.")
    print("   To'xtatish uchun: Ctrl+C")
    app.run_polling(allowed_updates=Update.ALL_TYPES)

if __name__ == "__main__":
    main()
