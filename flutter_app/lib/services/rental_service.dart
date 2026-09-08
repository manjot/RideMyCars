import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import '../core/api/api_client.dart';
import '../core/constants/api_constants.dart';
import '../models/vehicle_model.dart';

class RentalService {
  static final Dio _dio = ApiClient().dio;

  static Future<List<VehicleModel>> getAvailableVehicles({
    String? category,
    String? search,
    String? transmission,
    String? fuelType,
    String? country,
  }) async {
    try {
      final queryParams = <String, dynamic>{};
      if (category != null && category != 'All') queryParams['category'] = category;
      if (search != null && search.isNotEmpty) queryParams['search'] = search;
      if (transmission != null && transmission != 'All') queryParams['transmission'] = transmission.toLowerCase();
      if (fuelType != null && fuelType != 'All') queryParams['fuel_type'] = fuelType.toLowerCase();
      queryParams['driver_country'] = country ?? 'USA';

      final res = await _dio.get(ApiConstants.rentalVehicles, queryParameters: queryParams);
      if (res.statusCode == 200 && res.data != null) {
        final List list = res.data['data'] is List ? res.data['data'] : [];
        final defaultSymbol = res.data['currency_symbol']?.toString() ?? '\$';
        return list.map((item) {
          final map = Map<String, dynamic>.from(item);
          if (map['currency_symbol'] == null) {
            map['currency_symbol'] = defaultSymbol;
          }
          return VehicleModel.fromJson(map);
        }).toList();
      }
    } catch (e) {
      debugPrint('Error fetching rental vehicles: $e');
    }
    return [];
  }

  static Future<Map<String, dynamic>?> getCountryPricing(String country) async {
    try {
      final res = await _dio.get('/country-pricing', queryParameters: {
        'country': country,
      });
      if (res.statusCode == 200 && res.data != null && res.data['data'] != null) {
        return Map<String, dynamic>.from(res.data['data']);
      }
    } catch (e) {
      debugPrint('Error fetching country pricing for $country: $e');
    }
    return null;
  }

  static Future<Map<String, dynamic>?> getVehicleDetail(int vehicleId, {String? country}) async {
    try {
      final res = await _dio.get(
        '/rent/$vehicleId',
        queryParameters: {'driver_country': country ?? 'USA'},
      );
      if (res.statusCode == 200 && res.data != null && res.data['status'] == 'success') {
        return res.data['data'];
      }
    } catch (e) {
      debugPrint('Error fetching vehicle detail: $e');
    }
    return null;
  }

  static Future<Map<String, dynamic>?> bookRental(Map<String, dynamic> data, {int? vehicleId}) async {
    try {
      final url = vehicleId != null ? '/rent/$vehicleId/book' : '/rent/book';
      final res = await _dio.post(url, data: data);
      if (res.statusCode == 200 && res.data != null && res.data['status'] == 'success') {
        return res.data;
      }
    } catch (e) {
      debugPrint('Error booking rental: $e');
      if (e is DioException && e.response?.data != null) {
        return e.response?.data;
      }
    }
    return null;
  }
}
