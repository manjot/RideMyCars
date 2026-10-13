import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:image_picker/image_picker.dart';
import '../core/api/api_client.dart';
import '../core/constants/api_constants.dart';

class VerificationService {
  static final Dio _dio = ApiClient().dio;

  /// Fetch full verification status including vehicle certs, ID card, driver license, and guarantor (if chauffeur)
  static Future<Map<String, dynamic>?> getVerificationStatus() async {
    try {
      final res = await _dio.get(ApiConstants.driverVerificationStatus);
      if (res.statusCode == 200 && res.data != null) {
        if (res.data is Map<String, dynamic>) {
          return res.data;
        }
      }
      return null;
    } catch (e) {
      debugPrint('Error getting verification status: $e');
      return null;
    }
  }

  /// Helper to convert XFile or local path to MultipartFile or base64
  static Future<dynamic> _prepareUploadField(dynamic fileOrPath, String defaultName) async {
    if (fileOrPath == null) return null;

    if (fileOrPath is XFile) {
      if (kIsWeb) {
        final bytes = await fileOrPath.readAsBytes();
        return MultipartFile.fromBytes(bytes, filename: fileOrPath.name.isNotEmpty ? fileOrPath.name : defaultName);
      } else {
        return await MultipartFile.fromFile(
          fileOrPath.path,
          filename: fileOrPath.name.isNotEmpty ? fileOrPath.name : defaultName,
        );
      }
    } else if (fileOrPath is String) {
      // If it's already a base64 or URL or path
      if (fileOrPath.startsWith('http://') || fileOrPath.startsWith('https://') || fileOrPath.startsWith('data:image/')) {
        return fileOrPath;
      }
      return await MultipartFile.fromFile(fileOrPath, filename: defaultName);
    }
    return fileOrPath;
  }

  /// 1. Upload Vehicle Certificates (Insurance & Roadworthy/Fitness)
  static Future<Map<String, dynamic>> uploadVehicleCertificates({
    String? insurancePolicyNumber,
    String? insuranceExpiry,
    dynamic insuranceScan,
    String? roadworthyNumber,
    String? roadworthyExpiry,
    dynamic roadworthyScan,
  }) async {
    try {
      final Map<String, dynamic> data = {};

      if (insurancePolicyNumber != null && insurancePolicyNumber.isNotEmpty) {
        data['insurance_policy_number'] = insurancePolicyNumber;
      }
      if (insuranceExpiry != null && insuranceExpiry.isNotEmpty) {
        data['insurance_expiry'] = insuranceExpiry;
      }
      if (roadworthyNumber != null && roadworthyNumber.isNotEmpty) {
        data['roadworthy_number'] = roadworthyNumber;
      }
      if (roadworthyExpiry != null && roadworthyExpiry.isNotEmpty) {
        data['roadworthy_expiry'] = roadworthyExpiry;
      }

      if (insuranceScan != null) {
        data['insurance_certificate'] = await _prepareUploadField(insuranceScan, 'insurance_certificate.jpg');
      }
      if (roadworthyScan != null) {
        data['roadworthy_certificate'] = await _prepareUploadField(roadworthyScan, 'roadworthy_certificate.jpg');
      }

      final formData = FormData.fromMap(data);
      final res = await _dio.post(ApiConstants.driverVehicleCertificates, data: formData);

      return {
        'success': res.statusCode == 200 && (res.data['status'] == 'success' || res.data['success'] == true),
        'message': res.data['message'] ?? 'Vehicle certificates submitted successfully!',
        'data': res.data,
      };
    } on DioException catch (de) {
      final msg = de.response?.data?['message'] ?? de.message ?? 'Failed to submit vehicle certificates.';
      return {'success': false, 'message': msg};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// 2. Upload National ID Card Verification
  static Future<Map<String, dynamic>> uploadIdCard({
    required String idType,
    required String idNumber,
    required String expiryDate,
    dynamic frontImage,
    dynamic backImage,
  }) async {
    try {
      final Map<String, dynamic> data = {
        'id_card_type': idType,
        'id_card_number': idNumber,
        'id_card_expiry': expiryDate,
      };

      if (frontImage != null) {
        data['id_card_front_image'] = await _prepareUploadField(frontImage, 'id_card_front.jpg');
      }
      if (backImage != null) {
        data['id_card_back_image'] = await _prepareUploadField(backImage, 'id_card_back.jpg');
      }

      final formData = FormData.fromMap(data);
      final res = await _dio.post(ApiConstants.driverVerifyIdCard, data: formData);

      return {
        'success': res.statusCode == 200 && (res.data['status'] == 'success' || res.data['success'] == true),
        'message': res.data['message'] ?? 'ID Card submitted for verification!',
        'data': res.data,
      };
    } on DioException catch (de) {
      final msg = de.response?.data?['message'] ?? de.message ?? 'Failed to upload ID Card.';
      return {'success': false, 'message': msg};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// 3. Upload Driver License Verification
  static Future<Map<String, dynamic>> uploadDriverLicense({
    required String licenseNumber,
    required String issuingCountry,
    required String expiryDate,
    dynamic frontImage,
    dynamic backImage,
  }) async {
    try {
      final Map<String, dynamic> data = {
        'license_number': licenseNumber,
        'issuing_country': issuingCountry,
        'license_expiry': expiryDate,
      };

      if (frontImage != null) {
        data['license_front_image'] = await _prepareUploadField(frontImage, 'license_front.jpg');
      }
      if (backImage != null) {
        data['license_back_image'] = await _prepareUploadField(backImage, 'license_back.jpg');
      }

      final formData = FormData.fromMap(data);
      final res = await _dio.post(ApiConstants.driverVerifyLicense, data: formData);

      return {
        'success': res.statusCode == 200 && (res.data['status'] == 'success' || res.data['success'] == true),
        'message': res.data['message'] ?? 'Driver License submitted for verification!',
        'data': res.data,
      };
    } on DioException catch (de) {
      final msg = de.response?.data?['message'] ?? de.message ?? 'Failed to upload Driver License.';
      return {'success': false, 'message': msg};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// 4. Submit Guarantor Verification (Strictly Chauffeur Only)
  static Future<Map<String, dynamic>> submitGuarantor({
    required String name,
    required String phone,
    required String relationship,
    required String address,
    String? workplace,
    dynamic idCardImage,
  }) async {
    try {
      final Map<String, dynamic> data = {
        'name': name,
        'phone': phone,
        'relationship': relationship,
        'address': address,
        if (workplace != null && workplace.isNotEmpty) 'workplace': workplace,
      };

      if (idCardImage != null) {
        data['id_card_image'] = await _prepareUploadField(idCardImage, 'guarantor_id.jpg');
      }

      final formData = FormData.fromMap(data);
      final res = await _dio.post(ApiConstants.driverSubmitGuarantor, data: formData);

      return {
        'success': res.statusCode == 200 && (res.data['status'] == 'success' || res.data['success'] == true),
        'message': res.data['message'] ?? 'Guarantor details submitted successfully!',
        'data': res.data,
      };
    } on DioException catch (de) {
      final msg = de.response?.data?['message'] ?? de.message ?? 'Failed to submit guarantor details.';
      return {'success': false, 'message': msg};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  /// 5. Toggle or Upgrade to Chauffeur Role
  static Future<Map<String, dynamic>> toggleChauffeurRole(bool isChauffeur) async {
    try {
      final res = await _dio.post(
        ApiConstants.driverToggleChauffeur,
        data: {'is_chauffeur': isChauffeur},
      );

      return {
        'success': res.statusCode == 200 && (res.data['status'] == 'success' || res.data['success'] == true),
        'message': res.data['message'] ?? 'Chauffeur status updated successfully!',
        'is_chauffeur': res.data['is_chauffeur'] ?? isChauffeur,
      };
    } on DioException catch (de) {
      final msg = de.response?.data?['message'] ?? de.message ?? 'Failed to update chauffeur status.';
      return {'success': false, 'message': msg};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }
}
