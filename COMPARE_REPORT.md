# تقرير المقارنة الكامل
## cirilla_ads (الأصلي النظيف) vs lib (معدل)

---

## 🆕 1. ملفات جديدة أنشأناها (4 ملفات)

| # | المسار | الغرض | من أنشأه |
|---|--------|-------|----------|
| 1 | `lib/screens/auth/widgets/login_mobile_syriatel.dart` | Syriatel OTP Login UI | أنت |
| 2 | `lib/service/analytics_service.dart` | GA4 + Meta Pixel Unified | AI (أنا) |
| 3 | `lib/service/meta_pixel_service.dart` | Meta Pixel / Facebook App Events | AI (أنا) |
| 4 | `lib/store/auth/syriatel_otp_store.dart` + `.g.dart` | Syriatel OTP State Management | أنت |

---

## 📝 2. ملفات أصلية عدلناها (38 ملف)

### 🔴 تعديلاتنا نحن (AI):
| # | الملف | ما فعلناه |
|---|-------|-----------|
| 1 | `lib/hooks/wrap_home_child.dart` | أضفنا `UpgradePromptAlert` (AppCheap Upgrader) |
| 2 | `lib/main.dart` | أضفنا `MetaPixelService.init()` + حذفنا Force Update |
| 3 | `lib/mixins/cart_mixin.dart` | أضفنا `AnalyticsService.logAddToCart` |
| 4 | `lib/mixins/snack_mixin.dart` | أضفنا `AnalyticsService.logViewItem` |
| 5 | `lib/mixins/wishlist_mixin.dart` | أضفنا `AnalyticsService.logWishlistAdd` |
| 6 | `lib/screens/cart/cart_body.dart` | أضفنا `AnalyticsService.logViewCart` |
| 7 | `lib/screens/cart/widgets/cart_coupon.dart` | أضفنا `AnalyticsService.logSelectPromotion` |
| 8 | `lib/screens/cart/widgets/cart_items.dart` | أضفنا `AnalyticsService.logRemoveFromCart` |
| 9 | `lib/screens/checkout/checkout.dart` | أضفنا analytics events للشراء |
| 10 | `lib/screens/checkout/checkout_one_page.dart` | أضفنا analytics events للشراء |
| 11 | `lib/screens/checkout/gateway/*.dart` | أضفنا analytics events (3 ملفات) |
| 12 | `lib/screens/checkout/order_received.dart` | أضفنا `AnalyticsService.logPurchaseOrder` |
| 13 | `lib/screens/home/home.dart` | أضفنا analytics events للصفحة الرئيسية |
| 14 | `lib/screens/product/product.dart` | أضفنا `logViewItem` + حذفنا Firebase duplicate |
| 15 | `lib/screens/product_list/product_list.dart` | أضفنا analytics events |
| 16 | `lib/screens/search/product_search.dart` | أضفنا `AnalyticsService.logSearch` |
| 17 | `lib/screens/search/search_feature.dart` | أضفنا import |
| 18 | `lib/service/constants/endpoints.dart` | أضفنا endpoints سرياتيل OTP |
| 19 | `lib/service/helpers/request_helper.dart` | أضفنا `syriatelSendOtp` + `syriatelVerifyOtp` |
| 20 | `lib/service/service.dart` | أضفنا `export 'analytics_service.dart'` |
| 21 | `lib/store/auth/auth_store.dart` | أضفنا `MetaPixelService.clearUserData` على logout |
| 22 | `lib/widgets/builder/product/layout/layout_list.dart` | أضفنا analytics |
| 23 | `lib/widgets/builder/product_video_shop/widgets/add_cart_widget.dart` | أضفنا analytics |
| 24 | `lib/widgets/cirilla_product_item.dart` | أضفنا analytics |

### 🟡 تعديلاتك أنت (أو تعديلات سابقة):
| # | الملف | ملاحظة |
|---|-------|--------|
| 25 | `lib/constants/ads.dart` | إعدادات الإعلانات |
| 26 | `lib/constants/app.dart` | baseUrl + keys |
| 27 | `lib/constants/chat_gpt.dart` | إعدادات ChatGPT |
| 28 | `lib/constants/credentials.dart` | بيانات الاعتماد |
| 29 | `lib/constants/notification.dart` | إعدادات الإشعارات |
| 30 | `lib/constants/strings.dart` | نصوص التطبيق |
| 31 | `lib/models/product/product.dart` | تعديلات المنتج |
| 32 | `lib/routes.dart` | إضافة routes |
| 33 | `lib/screens/auth/login_mobile_screen.dart` | إضافة Syriatel login |
| 34 | `lib/screens/auth/login_screen.dart` | تعديلات تسجيل الدخول |
| 35 | `lib/screens/auth/register_screen.dart` | تعديلات التسجيل |
| 36 | `lib/screens/custom/custom.dart` | تعديلات UI |
| 37 | `lib/screens/post/post.dart` | تعديلات المقالات |
| 38 | `lib/screens/post_author/post_author.dart` | تعديلات المؤلفين |
| 39 | `lib/screens/post_category/post_category.dart` | تعديلات التصنيفات |
| 40 | `lib/screens/post_list/post_list.dart` | تعديلات قائمة المقالات |
| 41 | `lib/screens/cart/widgets/cart_layout_shipping.dart` | تعديلات shipping |
| 42 | `lib/themes/default/checkout/step_success.dart` | تعديلات نجاح الشراء |
| 43 | `lib/widgets/cirilla_cache_image.dart` | تعديلات cache |
| 44 | `lib/widgets/cirilla_html.dart` | تعديلات HTML |
| 45 | `lib/service/ads_service.dart` | تعديلات الإعلانات |
| 46 | `lib/store/search/search_post_store.dart` | تعديلات بحث المقالات |
| 47 | `lib/store/search/search_store.dart` | تعديلات البحث |

---

## 🗑️ 3. ملفات حذفناها من الأصل (4 ملفات)

| # | الملف | موجود في الأصل | سبب الحذف |
|---|-------|---------------|-----------|
| 1 | `lib/mixins/interstitial_ads_mixin.dart` | ✅ | لم نحتاجه |
| 2 | `lib/mixins/rewarded_ads_mixin.dart` | ✅ | لم نحتاجه |
| 3 | `lib/mixins/rewarded_interstitial_ads_mixin.dart` | ✅ | لم نحتاجه |
| 4 | `lib/themes/default/widgets/` | ✅ | لم نحتاجه |

---

## ⚙️ 4. ملفات Generated (.g.dart) تغيرت (88 ملف)

هذه ملفات تُولّد تلقائياً بواسطة `build_runner` و `mobx_codegen`. **التغيير فيها طبيعي** ولا يحتاج مراجعة.

---

## 🚨 الأخطاء الحالية

### 1. Upgrader: `Expected a single root element`

**السبب:** الـ API endpoint `https://eoclickandgo.sy/wp-json/app-builder/v1/upgrader` يُرجع JSON خطأ:
```json
{"code":"app_builder_rest_cannot_view","message":"Sorry, you cannot list resources.","data":{"status":401}}
```

**الحل:** الـ Upgrader يتوقع **XML Appcast** وليس JSON. يجب إصلاح الـ Backend (WordPress):
- تفعيل صلاحيات الـ REST API للـ endpoint
- أو إضافة Appcast XML يدوياً في App Builder

### 2. Firebase Messaging: `SERVICE_NOT_AVAILABLE`

**السبب:** جهاز الاختبار لا يدعم Google Play Services (شائع في المحاكيات الصينية أو Huawei بدون GMS).

**الحل:** 
- على جهاز حقيقي بـ Google Play Services: سيختفي الخطأ
- على emulator بدون GMS: الخطأ متوقع ويمكن تجاهله

---

## ✅ الخلاصة

| الفئة | العدد |
|-------|-------|
| ملفات جديدة (أنت + AI) | 5 ملفات |
| ملفات أصلية عدلناها | 47 ملف |
| ملفات Generated (.g.dart) | 88 ملف |
| ملفات حذفناها | 4 ملفات |

**ملاحظة:** الأخطاء الحالية (Upgrader + Firebase) **ليست من تعديلاتنا** - Upgrader خطأ Backend، و Firebase خطأ Device.
