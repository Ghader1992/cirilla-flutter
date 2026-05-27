# Syriatel OTP - WordPress Plugin

## التثبيت

1. انسخ الملف `syriatel-otp.php` إلى مجلد:
   ```
   wp-content/plugins/syriatel-otp/
   ```

2. فعل الـ Plugin من لوحة التحكم.

3. عدل الثوابت في أعلى الملف:
   ```php
   define('SYRIATEL_RELAY_URL', 'https://your-syria-host.com/relay/send-sms.php');
   define('SYRIATEL_RELAY_SECRET', 'ChangeThisToRandom256BitSecretKeyHere123!');
   define('SYRIATEL_OTP_PEPPER', 'AnotherRandomPepperForOtpHash456!');
   ```

## الاختبار (بـ Postman أو curl)

### 1. إرسال OTP
```bash
curl -X POST https://eoclickandgo.sy/wp-json/app-builder/v1/syriatel-send-otp \
  -H "Content-Type: application/json" \
  -d '{"phone":"0991234567"}'
```

### 2. التحقق من OTP
```bash
curl -X POST https://eoclickandgo.sy/wp-json/app-builder/v1/syriatel-verify-otp \
  -H "Content-Type: application/json" \
  -d '{"phone":"0991234567","otp":"123456"}'
```

## ملاحظات الأمان
- احذف `debug_msg_id` من الـ response قبل الإنتاج.
- استخدم `SYRIATEL_RELAY_SECRET` قوي وعشوائي (256-bit).
- لا ترفع هذا الملف على Git مع الـ Secrets الحقيقية.
