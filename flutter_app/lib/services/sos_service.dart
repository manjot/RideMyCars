import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import '../core/api/api_client.dart';
import '../core/constants/api_constants.dart';

class SosService {
  static final Dio _dio = ApiClient().dio;

  /// Fetch user's saved emergency contacts
  static Future<List<Map<String, dynamic>>> getContacts() async {
    try {
      final res = await _dio.get(ApiConstants.emergencyContacts);
      if (res.statusCode == 200 && res.data['success'] == true && res.data['data'] is List) {
        return List<Map<String, dynamic>>.from(res.data['data']);
      }
    } catch (e) {
      debugPrint('Error fetching emergency contacts: $e');
    }
    return [];
  }

  /// Add a new emergency contact
  static Future<Map<String, dynamic>> addContact({
    required String name,
    required String phone,
    String relationship = 'Contact',
    bool isPrimary = false,
  }) async {
    try {
      final res = await _dio.post(
        ApiConstants.emergencyContacts,
        data: {
          'name': name,
          'phone': phone,
          'relationship': relationship,
          'is_primary': isPrimary,
        },
      );
      if (res.data != null) {
        return Map<String, dynamic>.from(res.data);
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data is Map) {
        return Map<String, dynamic>.from(e.response!.data);
      }
      return {'success': false, 'message': e.message ?? 'Failed to add emergency contact.'};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
    return {'success': false, 'message': 'Unknown error adding emergency contact.'};
  }

  /// Update an emergency contact
  static Future<Map<String, dynamic>> updateContact(
    int id, {
    String? name,
    String? phone,
    String? relationship,
    bool? isPrimary,
  }) async {
    try {
      final res = await _dio.put(
        '${ApiConstants.emergencyContacts}/$id',
        data: {
          if (name != null) 'name': name,
          if (phone != null) 'phone': phone,
          if (relationship != null) 'relationship': relationship,
          if (isPrimary != null) 'is_primary': isPrimary,
        },
      );
      if (res.data != null) {
        return Map<String, dynamic>.from(res.data);
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data is Map) {
        return Map<String, dynamic>.from(e.response!.data);
      }
      return {'success': false, 'message': e.message ?? 'Failed to update contact.'};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
    return {'success': false, 'message': 'Unknown error.'};
  }

  /// Delete an emergency contact
  static Future<bool> deleteContact(int id) async {
    try {
      final res = await _dio.delete('${ApiConstants.emergencyContacts}/$id');
      return res.statusCode == 200 || res.data['success'] == true;
    } catch (e) {
      debugPrint('Error deleting contact: $e');
      return false;
    }
  }

  /// Trigger emergency SOS alert
  static Future<Map<String, dynamic>> triggerSos({
    double? latitude,
    double? longitude,
    int? rideId,
    String? role,
    String? address,
    String? emergencyServiceDialed,
    String? notes,
  }) async {
    try {
      final res = await _dio.post(
        ApiConstants.sosTrigger,
        data: {
          if (latitude != null) 'latitude': latitude,
          if (longitude != null) 'longitude': longitude,
          if (rideId != null) 'ride_id': rideId,
          'role': role ?? 'rider',
          if (address != null) 'location_address': address,
          if (emergencyServiceDialed != null) 'emergency_service_dialed': emergencyServiceDialed,
          if (notes != null) 'notes': notes,
        },
      );
      if (res.data != null) {
        return Map<String, dynamic>.from(res.data);
      }
    } on DioException catch (e) {
      if (e.response?.data != null && e.response?.data is Map) {
        return Map<String, dynamic>.from(e.response!.data);
      }
      return {'success': false, 'message': e.message ?? 'Failed to trigger SOS alert.'};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
    return {'success': false, 'message': 'Unknown error triggering SOS.'};
  }
}
