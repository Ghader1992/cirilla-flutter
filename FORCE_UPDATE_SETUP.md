# إعداد Force Update - إجبار المستخدمين على التحديث

## 📋 نظرة عامة

تم إضافة نظام Force Update الكامل للتطبيق. هذا النظام يتيح لك إجبار المستخدمين على تحديث التطبيق في أي وقت تريده في المستقبل.

---

## ✅ ما تم تفعيله

### 1. **Remote Config عبر API**
- يقرأ إعدادات Force Update من ملف JSON على خادمك
- يمكنك تغيير الإعدادات في أي وقت بدون تحديث التطبيق
- يستخدم Caching لتحسين الأداء

### 2. **Google Play In-App Updates**
- تحديث فوري (Immediate) - يظهر مباشرة للمستخدم
- تحديث مرن (Flexible) - يحمل في الخلفية
- يعمل فقط على Android

### 3. **شاشة Force Update احترافية**
- واجهة مستخدم جميلة
- دعم اللغة العربية
- زر "تحديث الآن" + زر "لاحقاً" (اختياري)

### 4. **Store Redirect**
- يفتح Google Play تلقائياً
- يفتح App Store على iOS

---

## 🚀 كيفية الاستخدام

### الخطوة 1: إنشاء ملف الإعدادات JSON

أنشئ ملف `app-config.json` على خادمك (WordPress):

```json
{
  "force_update_enabled": false,
  "min_required_version": "2.7.3",
  "update_title": "تحديث مطلوب",
  "update_message": "هناك تحديث جديد متاح للتطبيق.\nقم بتحديث التطبيق للاستمتاع بميزات جديدة وأداء محسّن.",
  "update_button_text": "تحديث الآن",
  "later_button_text": "لاحقاً",
  "use_google_play_update": true,
  "show_later_button": false
}
```

### الخطوة 2: رفع الملف على خادمك

ارفع الملف إلى:
```
https://eoclickandgo.sy/app-config.json
```

أو عدل الرابط في `lib/service/force_update_service.dart`:
```dart
static const String _configUrl = 'https://your-domain.com/app-config.json';
```

### الخطوة 3: تفعيل Force Update

عندما تريد إجبار المستخدمين على التحديث:

1. ارفع النسخة الجديدة إلى Google Play
2. انتظر حتى تكون متاحة
3. عدل ملف `app-config.json`:

```json
{
  "force_update_enabled": true,
  "min_required_version": "2.7.3",
  "update_title": "تحديث إجباري",
  "update_message": "يجب تحديث التطبيق للاستمرار في الاستخدام.",
  "show_later_button": false
}
```

---

## ⚙️ خيارات الإعداد

| الخيار | القيمة | الوصف |
|--------|--------|-------|
| `force_update_enabled` | `true` / `false` | تفعيل/تعطيل الإجبار على التحديث |
| `min_required_version` | `"2.7.3"` | أقل نسخة مسموح بها |
| `update_title` | `"تحديث مطلوب"` | عنوان الشاشة |
| `update_message` | `"..."` | رسالة للمستخدم |
| `update_button_text` | `"تحديث الآن"` | نص زر التحديث |
| `later_button_text` | `"لاحقاً"` | نص زر "لاحقاً" |
| `use_google_play_update` | `true` / `false` | استخدام Google Play Update API |
| `show_later_button` | `true` / `false` | إظهار زر "لاحقاً" |

---

## 📱 سيناريوهات الاستخدام

### السيناريو 1: تحديث إجباري قاسي
```json
{
  "force_update_enabled": true,
  "min_required_version": "2.7.3",
  "show_later_button": false,
  "use_google_play_update": true
}
```
المستخدم لا يستطيع استخدام التطبيق بدون تحديث.

### السيناريو 2: تحديث إجباري ناعم
```json
{
  "force_update_enabled": true,
  "min_required_version": "2.7.3",
  "show_later_button": true,
  "use_google_play_update": true
}
```
المستخدم يمكنه الضغط على "لاحقاً" والاستمرار في الاستخدام.

### السيناريو 3: تعطيل Force Update
```json
{
  "force_update_enabled": false
}
```
لا يوجد إجبار على التحديث.

---

## 🔧 للمطورين

### تعديل رابط API

في `lib/service/force_update_service.dart`:
```dart
static const String _configUrl = 'https://your-domain.com/app-config.json';
```

### تعديل Package Name (Android)

في `lib/service/force_update_service.dart`:
```dart
static const String _androidPackage = 'io.rnlab.cirilla';
```

### إضافة iOS App ID

في `lib/service/force_update_service.dart`:
```dart
static const String _iosAppId = 'YOUR_IOS_APP_ID';
```

---

## 📊 ملاحظات مهمة

### للمستخدمين الحاليين (النسخة القديمة):
- ❌ **لا يمكن إجبارهم مباشرة** (لأن الكود غير موجود في نسختهم)
- ✅ **استخدم Push Notifications** لدفعهم للتحديث
- ✅ **استخدم ملف app-config.json** - النسخ القديمة ستقرأ منه عند التشغيل

### للمستخدمين الجدد (النسخة الجديدة):
- ✅ **سيكون لديهم كود Force Update**
- ✅ **يمكنك إجبارهم في أي وقت** عن طريق تغيير ملف JSON

### الـ Cache:
- الإعدادات تُحفظ في SharedPreferences
- يتم تحديثها كل ساعة
- يمكنك تغيير هذا في الكود إذا أردت

---

## 🆘 استكشاف الأخطاء

### المشكلة: الأحداث لا تظهر في Test Events
**الحل:** انتظر 2-5 دقائق. الأحداث تظهر في Overview فوراً ولكن Test Events يأخذ وقتاً.

### المشكلة: Force Update لا يعمل
**الحلول:**
1. تأكد من أن ملف JSON صالح
2. تأكد من الرابط صحيح
3. تأكد من أن `force_update_enabled: true`
4. تأكد من أن `min_required_version` أعلى من النسخة الحالية

### المشكلة: Google Play Update لا يعمل
**الحلول:**
1. يعمل فقط على Android
2. يتطلب Google Play Services
3. يتطلب أن يكون التطبيق منزل من Google Play (لا يعمل على Debug)

---

## ✅ المهام المكتملة

- ✅ Meta Pixel / Facebook App Events
- ✅ App Tracking Transparency (iOS)
- ✅ Advanced Matching (Email/Name)
- ✅ Force Update Service
- ✅ Google Play In-App Updates
- ✅ Remote Config via API
- ✅ Force Update Screen

---

## 📞 دعم

لأي استفسار أو مشكلة، راجع الكود في:
- `lib/service/force_update_service.dart`
- `lib/screens/force_update/force_update_screen.dart`
- `lib/main.dart`
