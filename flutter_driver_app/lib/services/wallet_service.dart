import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import '../core/api/api_client.dart';
import '../core/constants/api_constants.dart';

class WalletService {
  static final Dio _dio = ApiClient().dio;

  /// Get driver wallet balance, currency, pending sums, and configuration
  static Future<Map<String, dynamic>?> getBalance() async {
    try {
      final res = await _dio.get(ApiConstants.walletBalance);
      if (res.statusCode == 200 && res.data['success'] == true) {
        return Map<String, dynamic>.from(res.data['data'] ?? {});
      }
    } catch (e) {
      debugPrint('Error getting driver wallet balance: $e');
    }
    return null;
  }

  /// Top-up driver wallet
  static Future<Map<String, dynamic>> topupWallet({
    required double amount,
    required String paymentMethod,
    Map<String, dynamic>? paymentDetails,
    String? phoneNumber,
    String? network,
  }) async {
    try {
      final res = await _dio.post(
        ApiConstants.walletTopup,
        data: {
          'amount': amount,
          'payment_method': paymentMethod,
          if (paymentDetails != null) 'payment_details': paymentDetails,
          if (phoneNumber != null && phoneNumber.isNotEmpty) 'phone_number': phoneNumber,
          if (network != null && network.isNotEmpty) 'network': network,
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
          'amount': amount,
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
      debugPrint('Error fetching driver withdrawal history: $e');
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
      debugPrint('Error getting driver payout methods: $e');
    }
    return [];
  }

  /// Save new payout method
  static Future<bool> savePayoutMethod(Map<String, dynamic> data) async {
    try {
      final res = await _dio.post(ApiConstants.walletPayoutMethods, data: data);
      return res.statusCode == 201 || res.data['success'] == true;
    } catch (e) {
      debugPrint('Error saving driver payout method: $e');
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
