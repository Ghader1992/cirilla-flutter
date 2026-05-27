# تقرير التدقيق الكامل - ما هو أصلي وما هو من إضافاتنا

## 📅 تاريخ التقرير: 2026-05-26
## 🎯 الغرض: معرفة ما يمكن حذفه بأمان وما يحتاج مراجعة

---

## ✅ الجزء 1: ملفات أنشأناها نحن بالكامل (يمكن حذفها بأمان)

هذه الملفات **غير موجودة في Git** ولم تكن جزءاً من Cirilla الأصلي:

| # | المسار | الغرض | يعتمد على AppCheap Built-in؟ |
|---|--------|-------|-------------------------------|
| 1 | `lib/service/meta_pixel_service.dart` | Meta Pixel / Facebook App Events | ❌ لا |
| 2 | `lib/service/analytics_service.dart` | GA4 + Meta Pixel Unified | ❌ لا |
| 3 | `lib/service/force_update_service.dart` | Force Update System (Custom) | ✅ نعم (Upgrader) |
| 4 | `lib/screens/force_update/force_update_screen.dart` | UI Force Update Screen | ✅ نعم (Upgrader) |
| 5 | `lib/store/auth/syriatel_otp_store.dart` | Syriatel OTP State Management | ❌ لا |
| 6 | `lib/store/auth/syriatel_otp_store.g.dart` | MobX Generated Code | ❌ لا |
| 7 | `lib/screens/auth/widgets/login_mobile_syriatel.dart` | Syriatel OTP Login UI | ❌ لا |

### 🔴 **تنبيه حول الملفات 3 و 4 (Force Update):**
هذان الملفان يقومان بـ **نفس وظيفة** `upgrade_prompt` package من AppCheap.
إذا قررت التبديل لـ AppCheap Built-in Upgrader، يمكن حذفهما.

---

## ⚠️ الجزء 2: ملفات أصلية عدلناها (تحتاج مراجعة قبل أي تعديل)

### 🔬 تفصيل التغييرات في كل ملف:

#### A. تعديلات Force Update + Meta Pixel (مرتبطة بملفاتنا)

| الملف | عدد الأسطر المضافة | ما فعلناه بالضبط |
|-------|-------------------|------------------|
| `lib/main.dart` | +60 | استيراد ForceUpdate + MetaPixel + تشغيلهم |
| `lib/service/service.dart` | +1 | تصدير ForceUpdateService |
| `lib/service/constants/endpoints.dart` | +4 | إضافة endpoints سرياتيل OTP |
| `lib/service/helpers/request_helper.dart` | +27 | دوال syriatelSendOtp + syriatelVerifyOtp |

#### B. تعديلات Analytics / Meta Pixel (إرسال الأحداث)

| الملف | عدد الأسطر | ما فعلناه بالضبط |
|-------|-----------|------------------|
| `lib/mixins/cart_mixin.dart` | +18 | إضافة `AnalyticsService.logAddToCart` |
| `lib/mixins/snack_mixin.dart` | +2 | إضافة `AnalyticsService.logViewItem` |
| `lib/mixins/wishlist_mixin.dart` | +10 | إضافة `AnalyticsService.logWishlistAdd` |
| `lib/screens/cart/cart_body.dart` | +15 | إضافة `AnalyticsService.logViewCart` |
| `lib/screens/cart/widgets/cart_coupon.dart` | +3 | إضافة `AnalyticsService.logSelectPromotion` |
| `lib/screens/cart/widgets/cart_items.dart` | +3 | إضافة `AnalyticsService.logRemoveFromCart` |
| `lib/screens/checkout/checkout.dart` | +130 | إضافة analytics events للشراء |
| `lib/screens/checkout/checkout_one_page.dart` | +42 | إضافة analytics events للشراء |
| `lib/screens/checkout/gateway/*.dart` | +4 لكل | إضافة analytics events |
| `lib/screens/checkout/order_received.dart` | +23 | إضافة `AnalyticsService.logPurchaseOrder` |
| `lib/screens/home/home.dart` | +35 | إضافة analytics events للصفحة الرئيسية |
| `lib/screens/product/product.dart` | +19 | إضافة `logViewItem` + إزالة Firebase duplicate |
| `lib/screens/product_list/product_list.dart` | +12 | إضافة analytics events |
| `lib/screens/search/product_search.dart` | +9 | إضافة `AnalyticsService.logSearch` |
| `lib/screens/search/search_feature.dart` | +1 | إضافة analytics import |
| `lib/widgets/builder/product/layout/layout_list.dart` | تعديل | إضافة analytics |
| `lib/widgets/builder/product_video_shop/widgets/add_cart_widget.dart` | +2 | إضافة analytics |
| `lib/widgets/cirilla_product_item.dart` | تعديل | إضافة analytics |

#### C. تعديلات أخرى (غير مرتبطة بالموضوع)

| الملف | ما فعلناه |
|-------|-----------|
| `lib/screens/auth/login_screen.dart` | +20 أسطر - ربما تعديلات OTP |
| `lib/screens/auth/register_screen.dart` | +19 أسطر - ربما تعديلات OTP |
| `lib/screens/auth/login_mobile_screen.dart` | +8 أسطر - إضافة Syriatel login |
| `lib/store/auth/auth_store.dart` | +11 أسطر - `clearUserData` للـ Meta Pixel |
| `lib/routes.dart` | +5 أسطر - إضافة routes |
| `lib/screens/custom/custom.dart` | +8 أسطر - تعديلات UI |
| `lib/widgets/cirilla_cache_image.dart` | +20 أسطر - تعديلات cache |
| `lib/constants/app.dart` | تعديل - ربما baseUrl |
| `lib/constants/credentials.dart` | تعديل - ربما keys |

#### D. ملفات غير مرتبطة (قد تكون من تعديلات سابقة لك)

| الملف | عدد الأسطر | ملاحظة |
|-------|-----------|--------|
| `lib/store/search/search_post_store.dart` | +8 | تعديلات بحث |
| `lib/store/search/search_store.dart` | +2 | تعديلات بحث |
| `lib/screens/cart/widgets/cart_layout_shipping.dart` | +16 | تعديلات shipping |

---

## 🗺️ خريطة الاعتماديات (Dependencies)

```
ملفاتنا الجديدة          ملفات أصلية عدلناها
─────────────────       ─────────────────────
meta_pixel_service.dart ──► (لا يعتمد على أحد)
analytics_service.dart ───► product.dart, cart_body.dart, checkout.dart, ...
force_update_service.dart ─► main.dart
force_update_screen.dart ──► main.dart
syriatel_otp_store.dart ──► request_helper.dart
login_mobile_syriatel.dart ─► login_mobile_screen.dart
```

---

## 🎯 السيناريوهات المستقبلية

### إذا أردت التبديل لـ AppCheap Upgrader Built-in:

**الملفات التي يمكن حذفها:**
```
lib/service/force_update_service.dart
lib/screens/force_update/
```

**الملفات التي يجب التراجع عن تعديلاتها:**
```
lib/main.dart (حذف ForceUpdateService.init + ForceUpdateApp)
lib/service/service.dart (حذف تصدير ForceUpdateService)
```

**الملفات التي تبقى كما هي (لا علاقة لها):**
- كل ملفات Meta Pixel (غير مرتبطة بـ Upgrader)
- كل ملفات Syriatel OTP (غير مرتبطة بـ Upgrader)

---

## 🔍 كيفية التحقق بنفسك

### لعرض التغييرات في ملف معين:
```bash
git diff lib/screens/product/product.dart
```

### لعرض ملف أصلي بدون تعديلاتنا:
```bash
git show HEAD:lib/screens/product/product.dart
```

### للتراجع عن تعديلات ملف معين:
```bash
git checkout -- lib/screens/product/product.dart
```

### لحذف ملف أنشأناه:
```bash
rm lib/service/force_update_service.dart
rm -rf lib/screens/force_update/
```

---

*تم إنشاء هذا التقرير تلقائياً للحفاظ على الشفافية الكاملة.*
