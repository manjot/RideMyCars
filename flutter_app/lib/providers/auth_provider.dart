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
  String _role = 'customer'; // 'customer' or 'driver'
  String? _userName;
  String? _userEmail;
  int? _userId;
  String? _errorMessage;

  bool get isLoading => _isLoading;
  bool get isAuthenticated => _isAuthenticated;
  String? get token => _token;
  String get role => _role;
  String? get userName => _userName;
  String? get userEmail => _userEmail;
  int? get userId => _userId;
  String? get errorMessage => _errorMessage;

  bool get isDriver => _role == 'driver';

  Future<bool> loadSession() async {
    _token = await TokenStorage.getToken();
    if (_token != null && _token!.isNotEmpty) {
      _role = await TokenStorage.getRole() ?? 'customer';
      _userName = await TokenStorage.getUserName();
      _userEmail = await TokenStorage.getUserEmail();
      _isAuthenticated = true;

      // Validate session in background
      try {
        final res = await _dio.get(ApiConstants.me);
        if (res.statusCode == 200 && res.data['success'] == true) {
          final u = res.data['user'];
          _userId = u['id'];
          _userName = u['name'];
          _userEmail = u['email'];
          _role = res.data['role'] ?? _role;
          await TokenStorage.saveUserData(role: _role, name: _userName!, email: _userEmail!);
        }
      } catch (e) {
        // Offline or token expired
      }

      notifyListeners();
      return true;
    }
    _isAuthenticated = false;
    notifyListeners();
    return false;
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

      if (res.statusCode == 200 && res.data['success'] == true) {
        _token = res.data['token'];
        final u = res.data['user'];
        _userId = u['id'];
        _userName = u['name'];
        _userEmail = u['email'];
        _role = res.data['role'] ?? 'customer';
        _isAuthenticated = true;

        await TokenStorage.saveToken(_token!);
        await TokenStorage.saveUserData(role: _role, name: _userName!, email: _userEmail!);

        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = res.data['message'] ?? 'Login failed.';
      }
    } on DioException catch (e) {
      _errorMessage = e.response?.data['message'] ?? 'Unable to connect. Please check credentials.';
    } catch (e) {
      _errorMessage = 'An unexpected error occurred.';
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
    required String role,
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
        'role': role,
      });

      if ((res.statusCode == 200 || res.statusCode == 201) && res.data['success'] == true) {
        _token = res.data['token'];
        final u = res.data['user'];
        _userId = u['id'];
        _userName = u['name'];
        _userEmail = u['email'];
        _role = res.data['role'] ?? role;
        _isAuthenticated = true;

        await TokenStorage.saveToken(_token!);
        await TokenStorage.saveUserData(role: _role, name: _userName!, email: _userEmail!);

        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = res.data['message'] ?? 'Registration failed.';
      }
    } on DioException catch (e) {
      _errorMessage = e.response?.data['message'] ?? 'Registration failed. Please check inputs.';
    } catch (e) {
      _errorMessage = 'An unexpected error occurred.';
    }

    _isLoading = false;
    notifyListeners();
    return false;
  }

  void switchRole(String newRole) {
    _role = newRole;
    if (_userName != null && _userEmail != null) {
      TokenStorage.saveUserData(role: _role, name: _userName!, email: _userEmail!);
    }
    notifyListeners();
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
