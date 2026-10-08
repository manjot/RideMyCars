import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import '../core/api/api_client.dart';
import '../core/constants/api_constants.dart';

class DeliveryService {
  static final Dio _dio = ApiClient().dio;

  static Future<List<Map<String, dynamic>>?> fetchCategories() async {
    try {
      final res = await _dio.get(ApiConstants.deliveryCategories);
      if (res.statusCode == 200 && res.data != null) {
        final data = res.data['data'] ?? res.data;
        if (data is List) {
          return data.map((e) => Map<String, dynamic>.from(e)).toList();
        }
      }
    } catch (e) {
      debugPrint('Error fetching delivery categories from API: $e');
    }
    return null;
  }

  static Future<Map<String, dynamic>?> calculatePrice({
    double? pickupLat,
    double? pickupLng,
    double? dropoffLat,
    double? dropoffLng,
    String? deliveryType,
    String? packageSize,
    double? packageWeightKg,
    String? packageCategory,
    String? country,
  }) async {
    try {
      final res = await _dio.post(
        ApiConstants.deliveryCalculate,
        data: {
          if (pickupLat != null) 'pickup_lat': pickupLat,
          if (pickupLng != null) 'pickup_lng': pickupLng,
          if (dropoffLat != null) 'dropoff_lat': dropoffLat,
          if (dropoffLng != null) 'dropoff_lng': dropoffLng,
          'delivery_type': deliveryType ?? 'Hyperlocal',
          'package_size': packageSize ?? 'Small',
          'package_weight_kg': packageWeightKg ?? 1.0,
          if (packageCategory != null) 'package_category': packageCategory,
          'country': country ?? 'USA',
        },
      );
      if (res.statusCode == 200 && res.data != null) {
        return Map<String, dynamic>.from(res.data);
      }
    } catch (e) {
      debugPrint('Error calculating delivery price via API: $e');
    }
    return null;
  }

  static Future<Map<String, dynamic>?> bookDelivery(Map<String, dynamic> payload) async {
    try {
      final res = await _dio.post(
        ApiConstants.deliveryBook,
        data: payload,
      );
      if (res.statusCode == 200 && res.data != null) {
        return Map<String, dynamic>.from(res.data);
      }
    } catch (e) {
      debugPrint('Error booking parcel delivery via API: $e');
      if (e is DioException && e.response?.data != null) {
        try {
          return Map<String, dynamic>.from(e.response!.data);
        } catch (_) {}
      }
    }
    return null;
  }

  static Future<Map<String, dynamic>?> getDeliveryStatus(int deliveryId) async {
    try {
      final res = await _dio.get(ApiConstants.deliveryStatus(deliveryId));
      if (res.statusCode == 200 && res.data != null) {
        return Map<String, dynamic>.from(res.data);
      }
    } catch (e) {
      debugPrint('Error fetching delivery status: $e');
    }
    return null;
  }

  static Future<Map<String, dynamic>?> uploadPrescription({
    required List<int> bytes,
    required String filename,
    String? tempToken,
  }) async {
    try {
      final formData = FormData.fromMap({
        'prescriptions[]': MultipartFile.fromBytes(bytes, filename: filename),
        if (tempToken != null && tempToken.isNotEmpty) 'temp_token': tempToken,
      });

      final res = await _dio.post(
        ApiConstants.deliveryPrescriptionUpload,
        data: formData,
      );

      if (res.statusCode == 200 && res.data != null) {
        return Map<String, dynamic>.from(res.data);
      }
    } catch (e) {
      debugPrint('Error uploading prescription via API: $e');
    }
    return null;
  }

  static Future<bool> deletePrescription(int id) async {
    try {
      final res = await _dio.delete(ApiConstants.deliveryPrescriptionDelete(id));
      return res.statusCode == 200;
    } catch (e) {
      debugPrint('Error deleting prescription via API: $e');
      return false;
    }
  }
}
