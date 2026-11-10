import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import '../core/api/api_client.dart';
import '../core/constants/api_constants.dart';

class WalletService {
  static final Dio _dio = ApiClient().dio;
  static double? cachedBalance;
  static String cachedCurrency = 'GH₵';

  /// Get wallet balance, currency, pending sums, and configuration
  static Future<Map<String, dynamic>?> getBalance() async {
    for (int attempt = 0; attempt < 2; attempt++) {
      try {
        final res = await _dio.get(ApiConstants.walletBalance);
        if (res.statusCode == 200 && res.data['success'] == true) {
          final data = Map<String, dynamic>.from(res.data['data'] ?? {});
          final parsedBal = double.tryParse((data['balance'] ?? data['wallet_balance'] ?? 0).toString());
          if (parsedBal != null) {
            cachedBalance = parsedBal;
          }
          final curr = (data['currency'] ?? '').toString();
          if (curr.isNotEmpty) {
            cachedCurrency = curr;
          }
          return data;
        }
      } catch (e) {
        debugPrint('Error getting wallet balance (attempt ${attempt + 1}): $e');
        if (attempt == 0) {
          await Future.delayed(const Duration(milliseconds: 350));
        }
      }
    }
    if (cachedBalance != null) {
      return {
        'balance': cachedBalance,
        'wallet_balance': cachedBalance,
        'currency': cachedCurrency,
      };
    }
    return null;
  }

  /// Top-up rider wallet
  static Future<Map<String, dynamic>> topupWallet({
    required double amount,
    required String paymentMethod,
    Map<String, dynamic>? paymentDetails,
    String? phoneNumber,
    String? network,
    bool initiatePrompt = false,
    bool authorized = false,
    String? transactionRef,
  }) async {
    try {
      final res = await _dio.post(
        ApiConstants.walletTopup,
        data: {
          'amount': amount.round(),
          'payment_method': paymentMethod,
          if (paymentDetails != null) 'payment_details': paymentDetails,
          if (phoneNumber != null && phoneNumber.isNotEmpty) 'phone_number': phoneNumber,
          if (network != null && network.isNotEmpty) 'network': network,
          if (initiatePrompt) 'initiate_prompt': true,
          if (authorized) 'authorized': true,
          if (transactionRef != null && transactionRef.isNotEmpty) 'transaction_ref': transactionRef,
        },
      );
      if (res.data != null) {
        return Map<String, dynamic>.from(res.data);
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data is Map) {
        return Map<String, dynamic>.from(e.response!.data);
      }
      return {'success': false, 'message': e.message ?? 'Network error topping up wallet.'};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
    return {'success': false, 'message': 'Unknown error occurred.'};
  }

  /// Submit a new withdrawal request
  static Future<Map<String, dynamic>> submitWithdrawal({
    required double amount,
    required String payoutMethod,
    required Map<String, dynamic> payoutDetails,
    bool savePayoutMethod = false,
  }) async {
    try {
      final normalizedMethod = payoutMethod == 'bank' ? 'bank_account' : payoutMethod;
      final res = await _dio.post(
        ApiConstants.walletWithdraw,
        data: {
          'amount': amount.round(),
          'payout_method': normalizedMethod,
          'payout_details': payoutDetails,
          'save_payout_method': savePayoutMethod,
        },
      );
      if (res.data != null) {
        return Map<String, dynamic>.from(res.data);
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data is Map) {
        return Map<String, dynamic>.from(e.response!.data);
      }
      return {'success': false, 'message': e.message ?? 'Network error submitting withdrawal.'};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
    return {'success': false, 'message': 'Unknown error occurred.'};
  }

  /// Get withdrawal history with optional status filter
  static Future<Map<String, dynamic>> getWithdrawalHistory({String? status, int page = 1}) async {
    try {
      final res = await _dio.get(
        ApiConstants.walletWithdrawals,
        queryParameters: {
          if (status != null && status.isNotEmpty && status != 'all') 'status': status,
          'page': page,
        },
      );
      if (res.statusCode == 200 && res.data['success'] == true) {
        return Map<String, dynamic>.from(res.data);
      }
    } catch (e) {
      debugPrint('Error fetching withdrawal history: $e');
    }
    return {'success': false, 'data': []};
  }

  /// Get user saved payout methods
  static Future<List<Map<String, dynamic>>> getPayoutMethods() async {
    try {
      final res = await _dio.get(ApiConstants.walletPayoutMethods);
      if (res.statusCode == 200 && res.data['success'] == true && res.data['data'] is List) {
        return List<Map<String, dynamic>>.from(res.data['data']);
      }
    } catch (e) {
      debugPrint('Error getting payout methods: $e');
    }
    return [];
  }

  /// Save new payout method
  static Future<bool> savePayoutMethod(Map<String, dynamic> data) async {
    try {
      final res = await _dio.post(ApiConstants.walletPayoutMethods, data: data);
      return res.statusCode == 201 || res.data['success'] == true;
    } catch (e) {
      debugPrint('Error saving payout method: $e');
      return false;
    }
  }

  /// Get recent wallet transactions
  static Future<List<Map<String, dynamic>>> getTransactions() async {
    try {
      final res = await _dio.get(ApiConstants.walletTransactions);
      if (res.statusCode == 200 && res.data['success'] == true && res.data['data'] is List) {
        return List<Map<String, dynamic>>.from(res.data['data']);
      }
    } catch (e) {
      debugPrint('Error getting transactions: $e');
    }
    return [];
  }
}
