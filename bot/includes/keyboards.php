<?php

include_once 'config.php';

$start_key = json_encode([
    'keyboard' => [
        [['text' => ($from_id == $config['admin'] or in_array($from_id, $config['admins']) or in_array($from_id, $admins)) ? '👮‍♂️ مدیریت' : '']],
        [['text' => ($user['get_test_account'] == false) ? (($test_account_settings['status']) ? '🎁 دریافت سرویس تست' : '') : '']],
        [['text' => ($sql->query("SELECT * FROM `representations` WHERE `from_id` = $from_id AND `status` = 1")->num_rows > 0) ? '🗝 نمایندگی' : '']],
        [['text' => '🛍 سرویس های من'], ['text' => '🛒 خرید سرویس']],
        [['text' => '👤 حساب کاربری'], ['text' => '🛒 تعرفه خدمات'], ['text' => '💸 شارژ حساب']],
        [['text' => '📮 پشتیبانی آنلاین'], ['text' => '🔗 راهنمای اتصال']],
        [['text' => ($settings['refral_status']) ? '💸 کسب درآمد (زیرمجموعه گیری)' : '']]
    ],
    'resize_keyboard' => true
]);

$back = json_encode([
    'keyboard' => [
        [['text' => '🏠']]
    ],
    'resize_keyboard' => true
]);

$send_phone = json_encode([
    'keyboard' => [
        [['text' => '🔑 تایید و ارسال شماره', 'request_contact' => true]],
        [['text' => '🏠']]
    ],
    'resize_keyboard' => true
]);

$representation_key = json_encode([
    'inline_keyboard' => [
        [['text' => '🛒 خرید سرویس', 'callback_data' => 'repre_buy_service']],
        [['text' => '🛍 سرویس های من', 'callback_data' => 'my_services'], ['text' => '👤 حساب کاربری', 'callback_data' => 'my_profile']],
        [['text' => '🏓 کد هدیه', 'callback_data' => 'use_gift_code']]
    ]
]);

$education = json_encode([
    'inline_keyboard' => [
        [['text' => '🍏 ios', 'callback_data' => 'edu_ios'], ['text' => '📱android', 'callback_data' => 'edu_android']],
        [['text' => '🖥 mac', 'callback_data' => 'edu_mac'], ['text' => '💻 windows', 'callback_data' => 'edu_windos']],
        [['text' => '📚  مشاهده کانال آموزشی', 'url' => $settings['education_channel']]]
    ]
]);

$buy_again_key = json_encode([
    'inline_keyboard' => [
        [['text' => '📚 آموزش اتصال', 'callback_data' => 'education_key']]
    ]
]);

$select_payment_method = json_encode([
    'inline_keyboard' => [
        [['text' => '💲ارزی', 'callback_data' => 'Arz'], ['text' => '💳 کارت به کارت', 'callback_data' => 'CardToCard']],
        [['text' => '❌ منصرف شدم', 'callback_data' => 'cancel_pay']]
    ]
]);

$gift = json_encode([
    'inline_keyboard' => [
        [['text' => '🏓 کد هدیه', 'callback_data' => 'use_gift_code']]
    ]
]);

$back_to_services = json_encode([
    'inline_keyboard' => [
        [['text' => '🔙', 'callback_data' => 'back_to_services']]
    ]
]);

$back_to_profile = json_encode([
    'inline_keyboard' => [
        [['text' => '🔙', 'callback_data' => 'back_to_profile']]
    ]
]);

$back_to_single_plan_manager = json_encode([
    'inline_keyboard' => [
        [['text' => '🔙', 'callback_data' => 'back_to_single_plan_manager']]
    ]
]);

$back_to_multiple_plan_manager = json_encode([
    'inline_keyboard' => [
        [['text' => '🔙', 'callback_data' => 'back_to_multiple_plan_manager']]
    ]
]);

$back_panel = json_encode([
    'keyboard' => [
        [['text' => '🔙 بازگشت به پنل مدیریت']]
    ],
    'resize_keyboard' => true
]);

$back_to_test_account_settings = json_encode([
    'inline_keyboard' => [
        [['text' => '🔙 بازگشت', 'callback_data' => 'back_to_manage_test_account']]
    ]
]);

$back_to_payment_settings = json_encode([
    'inline_keyboard' => [
        [['text' => '🔙 بازگشت', 'callback_data' => 'back_to_payment_settings']]
    ]
]);

$back_to_refral_settings = json_encode([
    'inline_keyboard' => [
        [['text' => '🔙 بازگشت', 'callback_data' => 'back_to_refral_settings']]
    ]
]);

$back_to_channels = json_encode([
    'inline_keyboard' => [
        [['text' => '🔙 بازگشت', 'callback_data' => 'back_to_channels']]
    ]
]);

$panel = json_encode([
    'keyboard' => [
        [['text' => '👤 آمار کلی ربات']],
        [['text' => '⏱ دریافت/آپلود بکاپ ربات (دیتابیس)']],
        [['text' => '🛡مدیریت پلن ها'], ['text' => '🌐 مدیریت پنل ها']],
        [['text' => '💬 مدیریت متون ربات'], ['text' => '🔑 مدیریت نمایندگان']],
        [['text' => '🛒 مدیریت سرویس ها'], ['text' => '👥 مدیریت کاربران']],
        [['text' => '👮‍♀ مدیریت ادمین ها'], ['text' => '📢 مدیریت کانال ها']],
        [['text' => '⚙ تنظیمات ربات'], ['text' => '🎁 مدیریت کد تخفیف/هدیه']],
        [['text' => '💬 سیستم پیام همگانی']],
        [['text' => '⚠️ ریست کلی ربات (پاکسازی) ⚠️']],
        [['text' => '🏠']]
    ],
    'resize_keyboard' => true
]);

$backup_key = json_encode([
    'inline_keyboard' => [
        [['text' => '📥 آپلود بکاپ', 'callback_data' => 'upload_backup'], ['text' => '📤 دریافت بکاپ', 'callback_data' => 'get_backup']],
        [['text' => '📁 لیست بکاپ های اضافه شده توسط شما', 'callback_data' => 'backup_lists']]
    ]
]);

$send_all_key = json_encode([
    'inline_keyboard' => [
        [['text' => '👤 ارسال به همه کاربران ربات', 'callback_data' => 'send_to_all']],
        [['text' => '📥 ارسال به کاربران نماینده', 'callback_data' => 'send_to_2'], ['text' => '📥 ارسال به کاربران عادی', 'callback_data' => 'send_to_1']],
        [['text' => '📚 ارسال به دیگر کاربران . . .', 'callback_data' => 'send_to_more']]
    ]
]);

$select_send_type = json_encode([
    'inline_keyboard' => [
        [['text' => '🛒 ارسال به کاربران سرویس دار', 'callback_data' => 'send_to_3']],
        [['text' => '0️⃣ ارسال به کاربران بدون سرویس', 'callback_data' => 'send_to_7']],
        [['text' => '🌍 ارسال به لوکیشن های مختلف', 'callback_data' => 'send_to_4']],
        [['text' => '🚫 ارسال به کاربران مسدود شده', 'callback_data' => 'send_to_5']],
        [['text' => '✅ ارسال به کاربران حساب باز', 'callback_data' => 'send_to_6']],
        [['text' => '🔙 بازگشت', 'callback_data' => 'back_to_send_all']]
    ]
]);

$status_keys = json_encode([
    'inline_keyboard' => [
        [['text' => '📞 دریافت لیست شمارات', 'callback_data' => 'get_phones_list'], ['text' => '👤 دریافت لیست کاربران', 'callback_data' => 'get_users_list']],
        [['text' => '💸 دریافت لیست واریزی', 'callback_data' => 'get_payments_list']]
    ]
]);

$manage_panels = json_encode([
    'keyboard' => [
        [['text' => '⚙️ مدیریت پنل ها'], ['text' => '🌐 افزودن پنل']],
        [['text' => '🔙 بازگشت به پنل مدیریت']]
    ],
    'resize_keyboard' => true
]);

$select_add_panel = json_encode([
    'inline_keyboard' => [
        [['text' => '2️⃣ مرزبان', 'callback_data' => 'add_marzban'], ['text' => '1️⃣ ثنایی', 'callback_data' => 'add_sanaei']]
    ]
]);

$manage_plans = json_encode([
    'keyboard' => [
        [['text' => '🛡 مدیریت پلن بچه'], ['text' => '🛡 مدیریت پلن مادر']],
        [['text' => '➕ افزودن پلن بچه'], ['text' => '➕ افزودن پلن مادر']],
        [['text' => '🔙 بازگشت به پنل مدیریت']]
    ],
    'resize_keyboard' => true
]);

$manage_settings = json_encode([
    'keyboard' => [
        [['text' => '👥 مدیریت زیرمجموعه گیری']],
        [['text' => '🧭 مدیریت اکانت تست'], ['text' => '💸 مدیریت شارژ حساب']],
        [['text' => '➕ تنظیمات کلی ']],
        [['text' => '🔙 بازگشت به پنل مدیریت']]
    ],
    'resize_keyboard' => true
]);

$manage_users = json_encode([
    'keyboard' => [
        [['text' => '🌐 اطلاعات کاربر']],
        [['text' => '➖ کسر موجودی کاربر'], ['text' => '➕ افزایش موجودی کاربر']],
        [['text' => '❌ مسدود کردن کاربر'], ['text' => '✅ آزاد کردن کاربر']],
        [['text' => '💬 ارسال پیام به کاربر']],
        [['text' => '🔙 بازگشت به پنل مدیریت']]
    ],
    'resize_keyboard' => true
]);

$manage_channels = json_encode([
    'inline_keyboard' => [
        [['text' => '⚙️ مدیریت کانال ها', 'callback_data' => 'manage_channels'], ['text' => '➕ افزودن کانال', 'callback_data' => 'add_channel']]
    ]
]);

$confirm_reset_bot = json_encode([
    'inline_keyboard' => [
        [['text' => '❌ تایید نمیکنم', 'callback_data' => 'noconfirm_reset_bot'], ['text' => '✅ تایید میکنم', 'callback_data' => 'confirm_reset_bot']]
    ]
]);