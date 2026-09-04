import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../core/api/api_client.dart';
import '../core/constants/api_constants.dart';
import '../core/storage/token_storage.dart';

class AuthProvider extends ChangeNotifier {
  final Dio _dio = ApiClient().dio;

  bool _isLoading = false;
  bool _isAuthenticated = false;
  String? _token;
  String? _userName;
  String? _userEmail;
  int? _userId;
  String? _errorMessage;

  bool get isLoading => _isLoading;
  bool get isAuthenticated => _isAuthenticated;
  String? get token => _token;
  String? get userName => _userName;
  String? get userEmail => _userEmail;
  int? get userId => _userId;
  String? get errorMessage => _errorMessage;

  Future<bool> loadSession() async {
    _token = await TokenStorage.getToken();
    _userName = await TokenStorage.getUserName();
    _userEmail = await TokenStorage.getUserEmail();
    final savedPassword = await TokenStorage.getSavedPassword();

    if (_token != null && _token!.isNotEmpty) {
      _isAuthenticated = true;
      notifyListeners();

      // Silent background validation
      try {
        final res = await _dio.get(ApiConstants.me);
        if (res.statusCode == 200 && res.data['success'] == true) {
          final u = res.data['user'];
          _userId = u['id'];
          _userName = u['name'];
          await TokenStorage.saveUserData(
            role: 'driver',
            name: _userName!,
            email: _userEmail!,
            password: savedPassword,
          );
        }
      } catch (_) {
        if (_userEmail != null && savedPassword != null) {
          await login(_userEmail!, savedPassword);
        }
      }

      notifyListeners();
      return true;
    }

    // Auto-login with saved credentials if token is missing
    if (_userEmail != null && savedPassword != null) {
      final loggedIn = await login(_userEmail!, savedPassword);
      if (loggedIn) return true;
    }

    _isAuthenticated = false;
    notifyListeners();
    return false;
  }

  String _extractErrorMessage(dynamic data, String fallback) {
    if (data is Map) {
      if (data['message'] != null && data['message'].toString().isNotEmpty) {
        return data['message'].toString();
      }
      if (data['error'] != null && data['error'].toString().isNotEmpty) {
        return data['error'].toString();
      }
      if (data['errors'] != null && data['errors'] is Map) {
        final errors = data['errors'] as Map;
        if (errors.isNotEmpty) {
          final firstVal = errors.values.first;
          if (firstVal is List && firstVal.isNotEmpty) {
            return firstVal.first.toString();
          }
          return firstVal.toString();
        }
      }
    } else if (data is String && data.isNotEmpty && !data.contains('<!DOCTYPE html>')) {
      return data;
    }
    return fallback;
  }

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final res = await _dio.post(ApiConstants.login, data: {
        'email': email.trim(),
        'password': password,
      });

      if (res.statusCode == 200 && res.data is Map && res.data['success'] == true) {
        final role = res.data['role'] ?? 'driver';
        _token = res.data['token'];
        final u = res.data['user'];
        _userId = u['id'];
        _userName = u['name'];
        _userEmail = u['email'];
        _isAuthenticated = true;

        if (_token != null) {
          await TokenStorage.saveToken(_token!);
        }
        await TokenStorage.saveUserData(
          role: role,
          name: _userName ?? 'Driver Partner',
          email: _userEmail ?? email,
          password: password,
        );

        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = _extractErrorMessage(res.data, 'Login failed. Please verify your driver credentials.');
      }
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) {
        _errorMessage = 'Invalid email or password. Please verify your driver account credentials.';
      } else if (e.response?.data != null) {
        _errorMessage = _extractErrorMessage(e.response!.data, 'Login failed. Please check credentials.');
      } else if (e.type == DioExceptionType.connectionTimeout || e.type == DioExceptionType.receiveTimeout) {
        _errorMessage = 'Connection timed out. Please check your internet connection.';
      } else {
        _errorMessage = 'Unable to connect to driver service. Please try again.';
      }
    } catch (e) {
      _errorMessage = 'An unexpected error occurred. Please try again.';
    }

    _isLoading = false;
    notifyListeners();
    return false;
  }

  Future<bool> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final res = await _dio.post(ApiConstants.register, data: {
        'name': name.trim(),
        'email': email.trim(),
        'password': password,
        'password_confirmation': passwordConfirmation,
        'role': 'driver',
      });

      if ((res.statusCode == 200 || res.statusCode == 201) && res.data is Map && res.data['success'] == true) {
        _token = res.data['token'];
        final u = res.data['user'];
        _userId = u['id'];
        _userName = u['name'];
        _userEmail = u['email'];
        _isAuthenticated = true;

        if (_token != null) {
          await TokenStorage.saveToken(_token!);
        }
        await TokenStorage.saveUserData(role: 'driver', name: _userName ?? name, email: _userEmail ?? email);

        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = _extractErrorMessage(res.data, 'Registration failed.');
      }
    } on DioException catch (e) {
      if (e.response?.data != null) {
        _errorMessage = _extractErrorMessage(e.response!.data, 'Driver registration failed. Please check inputs.');
      } else {
        _errorMessage = 'Unable to connect to server. Please try again.';
      }
    } catch (e) {
      _errorMessage = 'An unexpected error occurred.';
    }

    _isLoading = false;
    notifyListeners();
    return false;
  }

  Future<Map<String, dynamic>> sendPhoneOtp({
    required String phone,
    required String action, // 'login' or 'register'
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final res = await _dio.post(ApiConstants.sendOtp, data: {
        'phone': phone.trim(),
        'action': action,
      });

      _isLoading = false;
      notifyListeners();

      if (res.data is Map) {
        return Map<String, dynamic>.from(res.data);
      }
      return {'success': true, 'message': 'OTP sent successfully'};
    } on DioException catch (e) {
      _isLoading = false;
      notifyListeners();

      if (e.response?.data is Map) {
        final data = Map<String, dynamic>.from(e.response!.data);
        _errorMessage = _extractErrorMessage(data, 'Failed to send OTP.');
        return data;
      }
      _errorMessage = 'Unable to contact server. Please try again.';
      return {'success': false, 'error': _errorMessage};
    } catch (e) {
      _isLoading = false;
      notifyListeners();
      _errorMessage = 'An error occurred while sending OTP.';
      return {'success': false, 'error': _errorMessage};
    }
  }

  Future<bool> verifyPhoneOtp({
    required String phone,
    required String otp,
    String? name,
    String? email,
    String? password,
    String role = 'driver',
    Map<String, dynamic>? driverDetails,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final payload = <String, dynamic>{
        'phone': phone.trim(),
        'otp': otp.trim(),
        'role': role,
      };
      if (name != null && name.isNotEmpty) payload['name'] = name.trim();
      if (email != null && email.isNotEmpty) payload['email'] = email.trim();
      if (password != null && password.isNotEmpty) payload['password'] = password;
      if (driverDetails != null) payload.addAll(driverDetails);

      final res = await _dio.post(ApiConstants.verifyOtp, data: payload);

      if (res.statusCode == 200 && res.data is Map && res.data['success'] == true) {
        _token = res.data['token'];
        final u = res.data['user'];
        if (u != null) {
          _userId = u['id'];
          _userName = u['name'];
          _userEmail = u['email'];
        }
        _isAuthenticated = true;

        if (_token != null) {
          await TokenStorage.saveToken(_token!);
        }
        await TokenStorage.saveUserData(
          role: 'driver',
          name: _userName ?? (name ?? 'Driver'),
          email: _userEmail ?? (email ?? phone),
          password: password ?? '',
        );

        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = _extractErrorMessage(res.data, 'Verification failed. Please check the code.');
      }
    } on DioException catch (e) {
      if (e.response?.data != null) {
        _errorMessage = _extractErrorMessage(e.response!.data, 'Invalid OTP code.');
      } else {
        _errorMessage = 'Unable to connect to server. Please try again.';
      }
    } catch (e) {
      _errorMessage = 'An unexpected error occurred during verification.';
    }

    _isLoading = false;
    notifyListeners();
    return false;
  }

  Future<void> logout() async {
    try {
      await _dio.post(ApiConstants.logout);
    } catch (_) {}

    await TokenStorage.clear();
    _token = null;
    _userId = null;
    _userName = null;
    _userEmail = null;
    _isAuthenticated = false;
    notifyListeners();
  }
}
