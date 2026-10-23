import 'package:flutter/foundation.dart';
import '../core/api/api_client.dart';

class PaymentGatewayConfig {
  final bool isStripeActive;
  final bool isAuthorizeNetActive;
  final bool isMomoActive;
  final bool isCashActive;
  final String? authorizenetMode;
  final String? authorizenetClientKey;
  final String? authorizenetApiLoginId;

  const PaymentGatewayConfig({
    this.isStripeActive = true,
    this.isAuthorizeNetActive = true,
    this.isMomoActive = true,
    this.isCashActive = true,
    this.authorizenetMode,
    this.authorizenetClientKey,
    this.authorizenetApiLoginId,
  });

  bool isMethodActive(String method) {
    switch (method.toLowerCase().trim()) {
      case 'authorizenet':
      case 'authorize.net':
      case 'anet':
        return isAuthorizeNetActive;
      case 'stripe':
      case 'card':
      case 'credit_card':
      case 'apple_pay':
        return isStripeActive;
      case 'momo':
      case 'mobile_money':
      case 'mtn_momo':
        return isMomoActive;
      case 'cash':
        return isCashActive;
      case 'wallet':
        return true;
      default:
        return false;
    }
  }

  String get firstActiveMethod {
    if (isAuthorizeNetActive) return 'authorizenet';
    if (isStripeActive) return 'stripe';
    if (isMomoActive) return 'momo';
    if (isCashActive) return 'cash';
    return 'authorizenet';
  }
}

class PaymentConfigService {
  static PaymentGatewayConfig _cachedConfig = const PaymentGatewayConfig();
  static bool _hasLoaded = false;
  static DateTime? _lastFetch;

  static PaymentGatewayConfig get currentConfig => _cachedConfig;

  /// Fetch active gateway configurations from the server
  static Future<PaymentGatewayConfig> getPaymentGateways({bool forceRefresh = false}) async {
    final now = DateTime.now();
    if (!forceRefresh && _hasLoaded && _lastFetch != null && now.difference(_lastFetch!).inMinutes < 5) {
      return _cachedConfig;
    }

    try {
      final res = await ApiClient().dio.get('/api/payment/gateways');
      if (res.statusCode == 200 && res.data != null) {
        final data = res.data;
        final rawActive = data['active_gateways'];
        final Map<String, dynamic> active = {};
        if (rawActive is Map<String, dynamic>) {
          active.addAll(rawActive);
        } else if (rawActive is List) {
          for (var item in rawActive) {
            active[item.toString()] = true;
          }
        }
        final rawData = data['data'] as Map<String, dynamic>? ?? {};
        final anet = (data['authorizenet'] ?? rawData['authorizenet']) as Map<String, dynamic>? ?? {};

        // A gateway is active if explicitly set to true in active_gateways,
        // or if active_gateways is empty and the key exists in data['data']
        final stripeActive = active.containsKey('stripe')
            ? (active['stripe'] == true)
            : rawData.containsKey('stripe');
        final anetActive = active.containsKey('authorizenet')
            ? (active['authorizenet'] == true)
            : rawData.containsKey('authorizenet');
        final momoActive = active.containsKey('momo')
            ? (active['momo'] == true)
            : rawData.containsKey('momo');
        final cashActive = active.containsKey('cash')
            ? (active['cash'] == true)
            : rawData.containsKey('cash');

        _cachedConfig = PaymentGatewayConfig(
          isStripeActive: stripeActive,
          isAuthorizeNetActive: anetActive,
          isMomoActive: momoActive,
          isCashActive: cashActive,
          authorizenetMode: anet['mode']?.toString(),
          authorizenetClientKey: anet['client_key']?.toString(),
          authorizenetApiLoginId: anet['api_login_id']?.toString(),
        );
        _hasLoaded = true;
        _lastFetch = now;
      }
    } catch (e) {
      debugPrint('Notice: Error fetching payment gateways config: $e');
    }

    return _cachedConfig;
  }
}
