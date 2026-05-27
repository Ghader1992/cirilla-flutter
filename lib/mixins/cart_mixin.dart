import 'package:cirilla/service/analytics_service.dart';
import 'package:cirilla/store/auth/auth_store.dart';
import 'package:cirilla/store/cart/cart_store.dart';
import 'package:cirilla/store/setting/setting_store.dart';
import 'package:cirilla/models/product/product.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

mixin CartMixin<T extends StatefulWidget> on State<T> {
  bool loading = false;

  late CartStore _cartStore;

  @override
  void didChangeDependencies() {
    _cartStore = Provider.of<AuthStore>(context).cartStore;
    super.didChangeDependencies();
  }

  Future<void> addToCart({int? productId, int? qty, List<dynamic>? variation, Product? product}) async {
    setState(() {
      loading = true;
    });
    try {
      final String currency =
          Provider.of<SettingStore>(context, listen: false).currency ?? 'USD';

      await _cartStore.addToCart({
        'id': productId,
        'quantity': qty,
        'variation': variation,
      });

      if (product != null) {
        await AnalyticsService.logAddToCart(
          product: product,
          qty: qty ?? 1,
          currency: currency,
        );
      }

      setState(() {
        loading = false;
      });
    } catch (e) {
      setState(() {
        loading = false;
      });
      rethrow;
    }
  }
}

