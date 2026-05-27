import 'package:cirilla/models/models.dart';
import 'package:cirilla/service/meta_pixel_service.dart';
import 'package:firebase_analytics/firebase_analytics.dart';

/// Centralized Analytics Service for Cirilla App.
/// All Firebase Analytics calls should go through this class.
class AnalyticsService {
  static final FirebaseAnalytics _analytics = FirebaseAnalytics.instance;

  // ─── Screen Tracking ─────────────────────────────────────────────────────

  /// Log when a screen becomes visible to the user.
  static Future<void> logScreen(String screenName) async {
    try {
      await _analytics.logScreenView(screenName: screenName);
      // Meta Pixel
      await MetaPixelService.logViewContent(
        contentType: 'screen',
        contentId: screenName,
      );
    } catch (e) {
      _log('logScreenView [$screenName]', e);
    }
  }

  // ─── Auth ─────────────────────────────────────────────────────────────────

  /// Log when a user logs in successfully.
  static Future<void> logLogin(String method) async {
    try {
      await _analytics.logLogin(loginMethod: method);
      // Meta Pixel
      await MetaPixelService.logLogin(method: method);
    } catch (e) {
      _log('logLogin', e);
    }
  }

  /// Log when a user registers a new account.
  static Future<void> logSignUp(String method) async {
    try {
      await _analytics.logSignUp(signUpMethod: method);
      // Meta Pixel
      await MetaPixelService.logCompleteRegistration(method: method);
    } catch (e) {
      _log('logSignUp', e);
    }
  }

  // ─── Search ───────────────────────────────────────────────────────────────

  /// Log when a user performs a search.
  static Future<void> logSearch(String term) async {
    try {
      await _analytics.logSearch(searchTerm: term);
      // Meta Pixel
      await MetaPixelService.logSearch(searchString: term);
    } catch (e) {
      _log('logSearch', e);
    }
  }

  // ─── E-Commerce: Product Browsing ─────────────────────────────────────────

  /// Log when a product list / category page is viewed.
  static Future<void> logViewItemList({
    required List<Product> products,
    required String currency,
    String listName = 'Product List',
  }) async {
    try {
      final items = products
          .take(20) // Firebase allows max 200 items, 20 is a safe practical limit
          .map((p) => AnalyticsEventItem(
                itemId: p.id.toString(),
                itemName: p.name ?? '',
                itemCategory: p.category,
                price: _parsePrice(p.price),
                itemListName: listName,
              ))
          .toList();
      await _analytics.logViewItemList(
        itemListId: listName.toLowerCase().replaceAll(' ', '_'),
        itemListName: listName,
        items: items,
      );
    } catch (e) {
      _log('logViewItemList', e);
    }
  }

  /// Log when a user taps on a product from a list.
  static Future<void> logSelectItem({
    required Product product,
    required String currency,
    String listName = 'Product List',
  }) async {
    try {
      await _analytics.logSelectItem(
        itemListId: listName.toLowerCase().replaceAll(' ', '_'),
        itemListName: listName,
        items: [
          AnalyticsEventItem(
            itemId: product.id.toString(),
            itemName: product.name ?? '',
            itemCategory: product.category,
            price: _parsePrice(product.price),
            itemListName: listName,
          ),
        ],
      );
    } catch (e) {
      _log('logSelectItem', e);
    }
  }

  /// Log when a product detail page is viewed.
  static Future<void> logViewItem({
    required Product product,
    required String currency,
  }) async {
    try {
      final price = _parsePrice(product.price);
      await _analytics.logViewItem(
        currency: currency,
        value: price,
        items: [
          AnalyticsEventItem(
            itemId: product.id.toString(),
            itemName: product.name ?? '',
            itemCategory: product.category,
            price: price,
          ),
        ],
      );
      // Meta Pixel
      await MetaPixelService.logViewContent(
        contentType: 'product',
        contentId: product.id.toString(),
        contentName: product.name,
        value: price,
        currency: currency,
      );
    } catch (e) {
      _log('logViewItem', e);
    }
  }

  // ─── E-Commerce: Cart ─────────────────────────────────────────────────────

  /// Log when a product is added to cart.
  static Future<void> logAddToCart({
    required Product product,
    required int qty,
    required String currency,
  }) async {
    try {
      final price = _parsePrice(product.price);
      await _analytics.logAddToCart(
        currency: currency,
        value: price * qty,
        items: [
          AnalyticsEventItem(
            itemId: product.id.toString(),
            itemName: product.name ?? '',
            itemCategory: product.category,
            price: price,
            quantity: qty,
          ),
        ],
      );
      // Meta Pixel
      await MetaPixelService.logAddToCart(
        contentId: product.id.toString(),
        contentName: product.name ?? '',
        value: price * qty,
        currency: currency,
        quantity: qty,
      );
    } catch (e) {
      _log('logAddToCart', e);
    }
  }

  /// Log when a product is removed from cart.
  static Future<void> logRemoveFromCart({
    required CartItem item,
  }) async {
    try {
      final int unit = _parseMinorUnit(item.prices?['currency_minor_unit']);
      final double divisor = unit > 0 ? (10 * unit).toDouble() : 1.0;
      final double price =
          _parsePrice(item.prices?['price']) / divisor;
      final String currency = item.prices?['currency_code']?.toString() ?? 'USD';

      await _analytics.logRemoveFromCart(
        currency: currency,
        value: price * (item.quantity ?? 1),
        items: [
          AnalyticsEventItem(
            itemId: item.id?.toString() ?? '',
            itemName: item.name ?? '',
            price: price,
            quantity: item.quantity ?? 1,
          ),
        ],
      );
      // Meta Pixel
      await MetaPixelService.logRemoveFromCart(
        contentId: item.id?.toString() ?? '',
        contentName: item.name,
      );
    } catch (e) {
      _log('logRemoveFromCart', e);
    }
  }

  /// Log when the cart tab/page is viewed.
  static Future<void> logViewCart({CartData? cartData}) async {
    try {
      if (cartData == null) return;
      final totals = _parseCartTotals(cartData);
      final items = _buildAnalyticsItems(cartData);
      await _analytics.logViewCart(
        currency: totals['currency'],
        value: totals['total'],
        items: items,
      );
      // Meta Pixel
      await MetaPixelService.logViewCart(
        value: totals['total'],
        currency: totals['currency'],
        numItems: cartData.items?.length ?? 0,
      );
    } catch (e) {
      _log('logViewCart', e);
    }
  }

  // ─── E-Commerce: Wishlist ─────────────────────────────────────────────────

  /// Log when a product is added to the wishlist.
  static Future<void> logWishlistAdd({
    required Product product,
    required String currency,
  }) async {
    try {
      final price = _parsePrice(product.price);
      await _analytics.logEvent(
        name: 'add_to_wishlist',
        parameters: {
          'currency': currency,
          'value': price,
          'items': [
            {
              'item_id': product.id.toString(),
              'item_name': product.name ?? '',
              'item_category': product.category,
              'price': price,
              'quantity': 1,
            }
          ],
        },
      );
      // Meta Pixel
      await MetaPixelService.logAddToWishlist(
        contentId: product.id.toString(),
        contentName: product.name ?? '',
        value: price,
        currency: currency,
      );
    } catch (e) {
      _log('logAddToWishlist', e);
    }
  }

  // ─── E-Commerce: Checkout Funnel ──────────────────────────────────────────

  /// Log when the user begins the checkout process.
  static Future<void> logBeginCheckout({required CartData cartData}) async {
    try {
      final totals = _parseCartTotals(cartData);
      final items = _buildAnalyticsItems(cartData);
      await _analytics.logBeginCheckout(
        currency: totals['currency'],
        value: totals['total'],
        items: items,
      );
      // Meta Pixel
      final contents = MetaPixelService.buildContentsFromCart(cartData);
      await MetaPixelService.logInitiateCheckout(
        value: totals['total'],
        currency: totals['currency'],
        numItems: cartData.items?.length ?? 0,
        contents: contents,
      );
    } catch (e) {
      _log('logBeginCheckout', e);
    }
  }

  /// Log when the user selects a shipping method.
  static Future<void> logAddShippingInfo({
    required CartData cartData,
    required String shippingTier,
  }) async {
    try {
      final totals = _parseCartTotals(cartData);
      final items = _buildAnalyticsItems(cartData);
      await _analytics.logAddShippingInfo(
        currency: totals['currency'],
        value: totals['total'],
        shippingTier: shippingTier,
        items: items,
      );
      // Meta Pixel
      await MetaPixelService.logAddShippingInfo(shippingTier: shippingTier);
    } catch (e) {
      _log('logAddShippingInfo', e);
    }
  }

  /// Log when the user selects a payment method.
  static Future<void> logAddPaymentInfo({
    required CartData cartData,
    required String paymentType,
  }) async {
    try {
      final totals = _parseCartTotals(cartData);
      final items = _buildAnalyticsItems(cartData);
      await _analytics.logAddPaymentInfo(
        currency: totals['currency'],
        value: totals['total'],
        paymentType: paymentType,
        items: items,
      );
      // Meta Pixel
      await MetaPixelService.logAddPaymentInfo();
    } catch (e) {
      _log('logAddPaymentInfo', e);
    }
  }

  /// Log when a purchase is completed.
  static Future<void> logPurchase({
    required CartData cartData,
    required int orderId,
  }) async {
    try {
      final totals = _parseCartTotals(cartData);
      final items = _buildAnalyticsItems(cartData);
      await _analytics.logPurchase(
        transactionId: orderId.toString(),
        currency: totals['currency'],
        value: totals['total'],
        items: items,
      );
      // Meta Pixel
      final contents = MetaPixelService.buildContentsFromCart(cartData);
      await MetaPixelService.logPurchase(
        orderId: orderId.toString(),
        value: totals['total'],
        currency: totals['currency'],
        numItems: cartData.items?.length ?? 0,
        contents: contents,
      );
    } catch (e) {
      _log('logPurchase', e);
    }
  }

  /// Log when a purchase is completed (using OrderData from server to ensure 100% accuracy).
  static Future<void> logPurchaseOrder(OrderData orderData) async {
    try {
      final double total = _parsePrice(orderData.total);
      final double tax = _parsePrice(orderData.totalTax);
      final double shipping = _parsePrice(orderData.shippingTotal);
      String currency = orderData.currency?.toString() ?? 'USD';
      if (currency.trim().isEmpty) currency = 'USD';

      final items = (orderData.lineItems ?? []).map((item) {
        return AnalyticsEventItem(
          itemId: item.productId?.toString() ?? item.variationId?.toString() ?? 'unknown_item',
          itemName: item.name ?? '',
          price: item.price ?? 0.0,
          quantity: item.quantity ?? 1,
        );
      }).toList();

      if (items.isEmpty) return; // Prevent logging empty purchases

      await _analytics.logPurchase(
        transactionId: orderData.id?.toString() ?? orderData.orderKey ?? '0',
        currency: currency,
        value: total,
        tax: tax > 0 ? tax : null,
        shipping: shipping > 0 ? shipping : null,
        items: items,
      );
      // Meta Pixel
      final contents = MetaPixelService.buildContentsFromOrder(orderData);
      await MetaPixelService.logPurchase(
        orderId: orderData.id?.toString() ?? '',
        value: total,
        currency: currency,
        numItems: orderData.lineItems?.length ?? 0,
        contents: contents,
      );
    } catch (e) {
      _log('logPurchaseOrder', e);
    }
  }

  // ─── E-Commerce: Promotions ───────────────────────────────────────────────

  /// Log when a promo code / coupon is applied.
  static Future<void> logSelectPromotion(String couponCode) async {
    try {
      await _analytics.logSelectPromotion(promotionName: couponCode);
      // Meta Pixel - log as custom event
      await MetaPixelService.logCustomEvent(
        eventName: 'fb_mobile_select_promotion',
        parameters: {
          'promotion_name': couponCode,
        },
      );
    } catch (e) {
      _log('logSelectPromotion', e);
    }
  }

  // ─── Content ──────────────────────────────────────────────────────────────

  /// Log when content is shared.
  static Future<void> logShare({
    required String contentType,
    required String itemId,
    String method = 'native',
  }) async {
    try {
      await _analytics.logShare(
        contentType: contentType,
        itemId: itemId,
        method: method,
      );
    } catch (e) {
      _log('logShare', e);
    }
  }

  // ─── Private Helpers ──────────────────────────────────────────────────────

  static double _parsePrice(dynamic price) {
    if (price == null) return 0.0;
    return double.tryParse(price.toString()) ?? 0.0;
  }

  static int _parseMinorUnit(dynamic unit) {
    if (unit == null) return 0;
    return int.tryParse(unit.toString()) ?? 0;
  }

  static Map<String, dynamic> _parseCartTotals(CartData cartData) {
    final int minorUnit = _parseMinorUnit(cartData.totals?['currency_minor_unit']);
    final double divisor = minorUnit > 0 ? (10 * minorUnit).toDouble() : 1.0;
    final double total = _parsePrice(cartData.totals?['total_price']) / divisor;
    String currency = cartData.totals?['currency_code']?.toString() ?? 'USD';
    if (currency.trim().isEmpty) currency = 'USD';
    return {'total': total, 'currency': currency, 'divisor': divisor};
  }

  static List<AnalyticsEventItem> _buildAnalyticsItems(CartData cartData) {
    final int minorUnit = _parseMinorUnit(cartData.totals?['currency_minor_unit']);
    final double divisor = minorUnit > 0 ? (10 * minorUnit).toDouble() : 1.0;
    return (cartData.items ?? []).map((item) {
      final double price = _parsePrice(item.prices?['price']) / divisor;
      return AnalyticsEventItem(
        itemId: item.id?.toString() ?? item.key?.toString() ?? 'unknown_item',
        itemName: item.name ?? '',
        price: price,
        quantity: item.quantity ?? 1,
      );
    }).toList();
  }

  static void _log(String event, dynamic error) {
    // ignore: avoid_print
    print('[AnalyticsService] ERROR in $event: $error');
  }
}
