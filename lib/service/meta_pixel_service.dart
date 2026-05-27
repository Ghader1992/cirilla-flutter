import 'dart:convert';
import 'dart:io';

import 'package:app_tracking_transparency/app_tracking_transparency.dart';
import 'package:crypto/crypto.dart';
import 'package:facebook_app_events/facebook_app_events.dart';
import 'package:flutter/foundation.dart';
import 'package:cirilla/models/models.dart';

/// Meta Pixel Service for Mobile (Android/iOS)
/// Tracks events to Facebook/Meta Pixel using Facebook App Events SDK
/// 
/// Features:
/// - Automatic App Tracking Transparency handling for iOS 14.5+
/// - Advanced Matching with email/phone hashing
/// - All standard e-commerce events
class MetaPixelService {
  static final FacebookAppEvents _facebookAppEvents = FacebookAppEvents();
  static bool _initialized = false;

  // ─── Initialization ───────────────────────────────────────────────────────

  /// Initialize Meta Pixel and request tracking permission on iOS
  static Future<void> init() async {
    if (kIsWeb) return;
    if (_initialized) {
      debugPrint('[MetaPixelService] Already initialized');
      return;
    }
    
    debugPrint('[MetaPixelService] Starting initialization...');
    
    try {
      // Enable auto-logging for testing
      await _facebookAppEvents.setAutoLogAppEventsEnabled(true);
      debugPrint('[MetaPixelService] Auto-log enabled');
      
      // Get App ID to verify
      final appId = await _facebookAppEvents.getApplicationId();
      debugPrint('[MetaPixelService] Facebook App ID: $appId');
      
      // Get Anonymous ID
      final anonId = await _facebookAppEvents.getAnonymousId();
      debugPrint('[MetaPixelService] Anonymous ID: $anonId');
      
      // Handle App Tracking Transparency for iOS 14.5+
      await _requestTrackingPermission();
      
      // Enable advertiser tracking
      await _facebookAppEvents.setAdvertiserTracking(enabled: true);
      debugPrint('[MetaPixelService] Advertiser tracking enabled');
      
      // Log App Launch
      await _facebookAppEvents.logEvent(
        name: 'fb_mobile_activate_app',
      );
      debugPrint('[MetaPixelService] App launch event logged');
      
      _initialized = true;
      debugPrint('[MetaPixelService] ✅ Initialized successfully');
    } catch (e, stackTrace) {
      debugPrint('[MetaPixelService] ❌ Initialization error: $e');
      debugPrint('[MetaPixelService] Stack trace: $stackTrace');
    }
  }

  /// Request App Tracking Transparency permission (iOS only)
  /// 
  /// IMPORTANT: Only enables advertiser tracking if user explicitly allows it.
  /// This complies with Apple App Store guidelines.
  static Future<void> _requestTrackingPermission() async {
    if (!Platform.isIOS) {
      debugPrint('[MetaPixelService] Not iOS, skipping ATT');
      return;
    }
    
    try {
      final TrackingStatus status = await AppTrackingTransparency.trackingAuthorizationStatus;
      debugPrint('[MetaPixelService] ATT initial status: $status');
      
      TrackingStatus finalStatus = status;
      
      if (status == TrackingStatus.notDetermined) {
        debugPrint('[MetaPixelService] Requesting ATT permission...');
        finalStatus = await AppTrackingTransparency.requestTrackingAuthorization();
        debugPrint('[MetaPixelService] ATT result: $finalStatus');
      }
      
      // Only enable tracking if user authorized
      final bool trackingEnabled = finalStatus == TrackingStatus.authorized;
      await _facebookAppEvents.setAdvertiserTracking(enabled: trackingEnabled);
      debugPrint('[MetaPixelService] Advertiser tracking set to: $trackingEnabled');
      
      // Get advertising ID (IDFA) - will be all zeros if denied
      final uuid = await AppTrackingTransparency.getAdvertisingIdentifier();
      debugPrint('[MetaPixelService] IDFA: $uuid');
      
    } catch (e) {
      debugPrint('[MetaPixelService] ATT Error: $e');
      // Disable tracking on error to be safe
      await _facebookAppEvents.setAdvertiserTracking(enabled: false);
    }
  }

  // ─── Advanced Matching ────────────────────────────────────────────────────

  /// Set user data for Advanced Matching
  /// Call this after user login/registration
  static Future<void> setUserData({
    String? email,
    String? phone,
    String? firstName,
    String? lastName,
    String? city,
    String? country,
  }) async {
    debugPrint('[MetaPixelService] Setting user data for Advanced Matching...');
    
    try {
      // Send data to Facebook (package handles hashing internally)
      await _facebookAppEvents.setUserData(
        email: email?.toLowerCase().trim(),
        phone: phone?.replaceAll(RegExp(r'[^0-9]'), ''),
        firstName: firstName?.toLowerCase().trim(),
        lastName: lastName?.toLowerCase().trim(),
        city: city?.toLowerCase().trim(),
        country: country?.toLowerCase().trim(),
      );
      debugPrint('[MetaPixelService] User data set successfully');
      
      // Also set User ID for better tracking
      if (email != null) {
        final userId = _hashData(email.toLowerCase().trim());
        await _facebookAppEvents.setUserID(userId);
        debugPrint('[MetaPixelService] User ID set: $userId');
      }
    } catch (e) {
      debugPrint('[MetaPixelService] Error setting user data: $e');
    }
  }

  /// Clear user data on logout
  static Future<void> clearUserData() async {
    debugPrint('[MetaPixelService] Clearing user data...');
    await _facebookAppEvents.clearUserData();
    await _facebookAppEvents.clearUserID();
  }

  /// Hash data using SHA-256
  static String _hashData(String data) {
    final bytes = utf8.encode(data);
    final digest = sha256.convert(bytes);
    return digest.toString();
  }

  // ─── Screen/Page View ─────────────────────────────────────────────────────

  static Future<void> logViewContent({
    required String contentType,
    required String contentId,
    String? contentName,
    double? value,
    String? currency,
  }) async {
    debugPrint('[MetaPixelService] 📄 logViewContent: id=$contentId, type=$contentType, value=$value');
    try {
      await _facebookAppEvents.logViewContent(
        type: contentType,
        id: contentId,
        currency: currency,
        price: value,
      );
      debugPrint('[MetaPixelService] ✅ ViewContent logged');
      // Flush to ensure event is sent immediately
      await _facebookAppEvents.flush();
    } catch (e) {
      debugPrint('[MetaPixelService] ❌ Error logging ViewContent: $e');
    }
  }

  // ─── E-Commerce: Product ──────────────────────────────────────────────────

  static Future<void> logAddToCart({
    required String contentId,
    required String contentName,
    String contentType = 'product',
    double? value,
    String? currency,
    int quantity = 1,
  }) async {
    debugPrint('[MetaPixelService] 🛒 logAddToCart: id=$contentId, qty=$quantity, value=$value');
    try {
      final content = {
        'id': contentId,
        'quantity': quantity,
        'item_price': value,
      };
      
      await _facebookAppEvents.logAddToCart(
        id: contentId,
        type: contentType,
        currency: currency ?? 'USD',
        price: value ?? 0.0,
        content: content,
      );
      debugPrint('[MetaPixelService] ✅ AddToCart logged');
      // Flush to ensure event is sent immediately
      await _facebookAppEvents.flush();
    } catch (e) {
      debugPrint('[MetaPixelService] ❌ Error logging AddToCart: $e');
    }
  }

  static Future<void> logRemoveFromCart({
    required String contentId,
    String? contentName,
  }) async {
    debugPrint('[MetaPixelService] 🗑️ logRemoveFromCart: id=$contentId');
    try {
      await _facebookAppEvents.logEvent(
        name: 'fb_mobile_remove_from_cart',
        parameters: {
          'fb_content_id': contentId,
          if (contentName != null) 'content_name': contentName,
        },
      );
      debugPrint('[MetaPixelService] ✅ RemoveFromCart logged');
    } catch (e) {
      debugPrint('[MetaPixelService] ❌ Error logging RemoveFromCart: $e');
    }
  }

  static Future<void> logViewCart({
    required double value,
    required String currency,
    required int numItems,
  }) async {
    debugPrint('[MetaPixelService] 🛒 logViewCart: items=$numItems, value=$value');
    try {
      await _facebookAppEvents.logEvent(
        name: 'fb_mobile_view_cart',
        valueToSum: value,
        parameters: {
          'fb_currency': currency,
          'fb_num_items': numItems,
        },
      );
      debugPrint('[MetaPixelService] ✅ ViewCart logged');
    } catch (e) {
      debugPrint('[MetaPixelService] ❌ Error logging ViewCart: $e');
    }
  }

  // ─── E-Commerce: Checkout ─────────────────────────────────────────────────

  static Future<void> logInitiateCheckout({
    required double value,
    required String currency,
    required int numItems,
    List<Map<String, dynamic>>? contents,
  }) async {
    debugPrint('[MetaPixelService] 💳 logInitiateCheckout: items=$numItems, value=$value');
    try {
      await _facebookAppEvents.logInitiatedCheckout(
        totalPrice: value,
        currency: currency,
        numItems: numItems,
        paymentInfoAvailable: false,
      );
      debugPrint('[MetaPixelService] ✅ InitiateCheckout logged');
    } catch (e) {
      debugPrint('[MetaPixelService] ❌ Error logging InitiateCheckout: $e');
    }
  }

  static Future<void> logAddShippingInfo({
    required String shippingTier,
  }) async {
    debugPrint('[MetaPixelService] 🚚 logAddShippingInfo: tier=$shippingTier');
    try {
      await _facebookAppEvents.logEvent(
        name: 'fb_mobile_add_shipping_info',
        parameters: {
          'shipping_tier': shippingTier,
        },
      );
      debugPrint('[MetaPixelService] ✅ AddShippingInfo logged');
    } catch (e) {
      debugPrint('[MetaPixelService] ❌ Error logging AddShippingInfo: $e');
    }
  }

  static Future<void> logAddPaymentInfo() async {
    debugPrint('[MetaPixelService] 💳 logAddPaymentInfo');
    try {
      await _facebookAppEvents.logEvent(
        name: 'fb_mobile_add_payment_info',
      );
      debugPrint('[MetaPixelService] ✅ AddPaymentInfo logged');
    } catch (e) {
      debugPrint('[MetaPixelService] ❌ Error logging AddPaymentInfo: $e');
    }
  }

  static Future<void> logPurchase({
    required String orderId,
    required double value,
    required String currency,
    required int numItems,
    List<Map<String, dynamic>>? contents,
  }) async {
    debugPrint('[MetaPixelService] 💰 logPurchase: order=$orderId, value=$value, currency=$currency');
    try {
      await _facebookAppEvents.logPurchase(
        amount: value,
        currency: currency,
        parameters: {
          'fb_order_id': orderId,
          'fb_num_items': numItems,
          if (contents != null) 'fb_content': json.encode(contents),
        },
      );
      debugPrint('[MetaPixelService] ✅ Purchase logged');
      // Flush to ensure event is sent immediately
      await _facebookAppEvents.flush();
    } catch (e) {
      debugPrint('[MetaPixelService] ❌ Error logging Purchase: $e');
    }
  }

  // ─── E-Commerce: Wishlist ─────────────────────────────────────────────────

  static Future<void> logAddToWishlist({
    required String contentId,
    required String contentName,
    String contentType = 'product',
    double? value,
    String? currency,
  }) async {
    debugPrint('[MetaPixelService] ❤️ logAddToWishlist: id=$contentId, value=$value');
    try {
      final content = {
        'id': contentId,
        'quantity': 1,
        'item_price': value,
      };
      
      await _facebookAppEvents.logAddToWishlist(
        id: contentId,
        type: contentType,
        currency: currency ?? 'USD',
        price: value ?? 0.0,
        content: content,
      );
      debugPrint('[MetaPixelService] ✅ AddToWishlist logged');
    } catch (e) {
      debugPrint('[MetaPixelService] ❌ Error logging AddToWishlist: $e');
    }
  }

  // ─── User Events ──────────────────────────────────────────────────────────

  static Future<void> logCompleteRegistration({
    String? method,
    bool success = true,
  }) async {
    debugPrint('[MetaPixelService] 📝 logCompleteRegistration: method=$method');
    try {
      await _facebookAppEvents.logCompletedRegistration(
        registrationMethod: method,
      );
      debugPrint('[MetaPixelService] ✅ CompleteRegistration logged');
    } catch (e) {
      debugPrint('[MetaPixelService] ❌ Error logging CompleteRegistration: $e');
    }
  }

  static Future<void> logSearch({
    required String searchString,
    bool success = true,
  }) async {
    debugPrint('[MetaPixelService] 🔍 logSearch: query=$searchString');
    try {
      await _facebookAppEvents.logEvent(
        name: 'fb_mobile_search',
        parameters: {
          'search_string': searchString,
          'success': success ? '1' : '0',
        },
      );
      debugPrint('[MetaPixelService] ✅ Search logged');
    } catch (e) {
      debugPrint('[MetaPixelService] ❌ Error logging Search: $e');
    }
  }

  static Future<void> logLogin({
    required String method,
  }) async {
    debugPrint('[MetaPixelService] 🔑 logLogin: method=$method');
    try {
      await _facebookAppEvents.logEvent(
        name: 'fb_mobile_login',
        parameters: {
          'method': method,
        },
      );
      debugPrint('[MetaPixelService] ✅ Login logged');
    } catch (e) {
      debugPrint('[MetaPixelService] ❌ Error logging Login: $e');
    }
  }

  // ─── Custom Events ────────────────────────────────────────────────────────

  static Future<void> logCustomEvent({
    required String eventName,
    Map<String, dynamic>? parameters,
    double? valueToSum,
  }) async {
    debugPrint('[MetaPixelService] 📊 logCustomEvent: $eventName');
    try {
      await _facebookAppEvents.logEvent(
        name: eventName,
        parameters: parameters,
        valueToSum: valueToSum,
      );
      debugPrint('[MetaPixelService] ✅ Custom event logged');
    } catch (e) {
      debugPrint('[MetaPixelService] ❌ Error logging custom event: $e');
    }
  }

  // ─── Helper Methods ───────────────────────────────────────────────────────

  static List<Map<String, dynamic>> buildContentsFromCart(CartData cartData) {
    final int minorUnit = _parseMinorUnit(cartData.totals?['currency_minor_unit']);
    final double divisor = minorUnit > 0 ? (10 * minorUnit).toDouble() : 1.0;
    
    return (cartData.items ?? []).map((item) {
      final double price = _parsePrice(item.prices?['price']) / divisor;
      return {
        'id': item.id?.toString() ?? item.key?.toString() ?? '',
        'quantity': item.quantity ?? 1,
        'item_price': price,
      };
    }).toList();
  }

  static List<Map<String, dynamic>> buildContentsFromOrder(OrderData orderData) {
    return (orderData.lineItems ?? []).map((item) {
      return {
        'id': item.productId?.toString() ?? item.variationId?.toString() ?? '',
        'quantity': item.quantity ?? 1,
        'item_price': item.price ?? 0.0,
      };
    }).toList();
  }

  static double _parsePrice(dynamic price) {
    if (price == null) return 0.0;
    return double.tryParse(price.toString()) ?? 0.0;
  }

  static int _parseMinorUnit(dynamic unit) {
    if (unit == null) return 0;
    return int.tryParse(unit.toString()) ?? 0;
  }
}
