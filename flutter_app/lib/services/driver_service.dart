import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import '../core/api/api_client.dart';
import '../core/constants/api_constants.dart';
import '../models/driver_model.dart';

class DriverService {
  static final Dio _dio = ApiClient().dio;

  static Future<List<DriverModel>> getDrivers({
    String? country,
    bool? available,
    double? minRating,
  }) async {
    try {
      final queryParams = <String, dynamic>{};
      if (country != null && country.isNotEmpty && country != 'All') {
        queryParams['country'] = country;
      }
      if (available != null) queryParams['available'] = available;
      if (minRating != null) queryParams['min_rating'] = minRating;

      final res = await _dio.get(ApiConstants.drivers, queryParameters: queryParams);
      if (res.statusCode == 200 && res.data != null) {
        final List list = res.data['data'] is List ? res.data['data'] : [];
        return list.map((item) => DriverModel.fromJson(Map<String, dynamic>.from(item))).toList();
      }
    } catch (e) {
      debugPrint('Error fetching drivers: $e');
    }
    return [];
  }

  static Future<DriverModel?> getDriverDetail(int driverId) async {
    try {
      final res = await _dio.get('${ApiConstants.drivers}/$driverId');
      if (res.statusCode == 200 && res.data != null && res.data['data'] != null) {
        return DriverModel.fromJson(Map<String, dynamic>.from(res.data['data']));
      }
    } catch (e) {
      debugPrint('Error fetching driver detail: $e');
    }
    return null;
  }

  static Future<Map<String, dynamic>?> calculatePrice({
    required int driverProfileId,
    required String durationType,
    required int durationCount,
    required String country,
  }) async {
    try {
      final res = await _dio.post('/drivers/calculate-price', data: {
        'driver_profile_id': driverProfileId,
        'duration_type': durationType,
        'duration_count': durationCount,
        'country': country,
      });
      if (res.statusCode == 200 && res.data != null && res.data['data'] != null) {
        return Map<String, dynamic>.from(res.data['data']);
      }
    } catch (e) {
      debugPrint('Error calculating driver fare: $e');
    }
    return null;
  }

  static Future<Map<String, dynamic>?> bookDriver(Map<String, dynamic> payload) async {
    try {
      final res = await _dio.post('/drivers/book', data: payload);
      if (res.statusCode == 200 && res.data != null) {
        return Map<String, dynamic>.from(res.data);
      }
    } catch (e) {
      debugPrint('Error submitting driver booking: $e');
      if (e is DioException && e.response?.data != null) {
        return Map<String, dynamic>.from(e.response!.data);
      }
    }
    return null;
  }
}
