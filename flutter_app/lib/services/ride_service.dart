import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import '../core/api/api_client.dart';
import '../core/constants/api_constants.dart';
import '../models/ride_category_model.dart';

class RideService {
  static final Dio _dio = ApiClient().dio;

  /// Fetch all canonical vehicle categories & initial pricing for a country
  static Future<List<RideCategoryModel>> getCategories({String? country}) async {
    try {
      final res = await _dio.get(
        ApiConstants.rideCategories,
        queryParameters: {
          if (country != null && country.isNotEmpty) 'country': country,
        },
      );

      if (res.statusCode == 200 && res.data != null && res.data['categories'] is List) {
        final symbol = res.data['currency_symbol']?.toString() ?? '\$';
        final List list = res.data['categories'];
        return list.map((item) {
          final map = Map<String, dynamic>.from(item);
          return RideCategoryModel.fromJson(map, defaultSymbol: symbol);
        }).toList();
      }
    } catch (e) {
      debugPrint('Error fetching ride categories: $e');
    }
    return [];
  }

  /// Calculate real-time dynamic fares for all categories based on route distance and coordinates
  static Future<Map<String, dynamic>?> calculatePrice({
    double? pickupLat,
    double? pickupLng,
    double? dropoffLat,
    double? dropoffLng,
    double? distanceKm,
    int? durationMinutes,
    int stopsCount = 0,
    String? country,
  }) async {
    try {
      final res = await _dio.post(
        ApiConstants.rideCalculatePrice,
        data: {
          if (pickupLat != null) 'pickup_lat': pickupLat,
          if (pickupLng != null) 'pickup_lng': pickupLng,
          if (dropoffLat != null) 'dropoff_lat': dropoffLat,
          if (dropoffLng != null) 'dropoff_lng': dropoffLng,
          if (distanceKm != null) 'distance_km': distanceKm,
          if (durationMinutes != null) 'duration_minutes': durationMinutes,
          'stops_count': stopsCount,
          if (country != null) 'country': country,
        },
      );

      if (res.statusCode == 200 && res.data != null && res.data['success'] == true) {
        final symbol = res.data['currency_symbol']?.toString() ?? '\$';
        final List catList = res.data['categories'] is List ? res.data['categories'] : [];
        final parsedCategories = catList.map((item) {
          final map = Map<String, dynamic>.from(item);
          return RideCategoryModel.fromJson(map, defaultSymbol: symbol);
        }).toList();

        return {
          'categories': parsedCategories,
          'distance_km': (res.data['distance_km'] as num?)?.toDouble() ?? distanceKm ?? 10.0,
          'duration_minutes': (res.data['duration_minutes'] as num?)?.toInt() ?? durationMinutes ?? 15,
          'currency_symbol': symbol,
          'currency_code': res.data['currency_code']?.toString() ?? 'USD',
          'country_code': res.data['country_code']?.toString(),
          'surge_info': res.data['surge_info'],
        };
      }
    } catch (e) {
      debugPrint('Error calculating ride fares: $e');
    }
    return null;
  }
}
