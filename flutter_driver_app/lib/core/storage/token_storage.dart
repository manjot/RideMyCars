import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class TokenStorage {
  static const _storage = FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
  );
  static const _tokenKey = 'auth_token';
  static const _roleKey = 'user_role';
  static const _userNameKey = 'user_name';
  static const _userEmailKey = 'user_email';
  static const _savedPasswordKey = 'saved_password';
  static const _avatarUrlKey = 'avatar_url';
  static const _countryCodeKey = 'selected_country_code';
  static const _countryNameKey = 'selected_country_name';
  static const _currencyCodeKey = 'selected_currency_code';
  static const _currencySymbolKey = 'selected_currency_symbol';
  static const _isCountryManualKey = 'is_country_manual';

  // In-memory cache for instant (0ms) access and immunity against Android Keystore freezes
  static String? _cachedToken;
  static String? _cachedRole;
  static String? _cachedUserName;
  static String? _cachedUserEmail;
  static String? _cachedSavedPassword;
  static String? _cachedAvatarUrl;
  static String? _cachedCountryCode;
  static String? _cachedCountryName;
  static String? _cachedCurrencyCode;
  static String? _cachedCurrencySymbol;
  static bool? _cachedIsCountryManual;

  static Future<void> saveCountryPreference({
    required String code,
    required String name,
    required String currencyCode,
    required String currencySymbol,
    bool isManual = true,
  }) async {
    _cachedCountryCode = code.toUpperCase();
    _cachedCountryName = name;
    _cachedCurrencyCode = currencyCode.toUpperCase();
    _cachedCurrencySymbol = currencySymbol;
    _cachedIsCountryManual = isManual;

    try {
      await _storage.write(key: _countryCodeKey, value: _cachedCountryCode!).timeout(const Duration(seconds: 2));
      await _storage.write(key: _countryNameKey, value: name).timeout(const Duration(seconds: 2));
      await _storage.write(key: _currencyCodeKey, value: _cachedCurrencyCode!).timeout(const Duration(seconds: 2));
      await _storage.write(key: _currencySymbolKey, value: currencySymbol).timeout(const Duration(seconds: 2));
      await _storage.write(key: _isCountryManualKey, value: isManual ? 'true' : 'false').timeout(const Duration(seconds: 2));
    } catch (_) {}
  }

  static Future<String?> getSelectedCountryCode() async {
    if (_cachedCountryCode != null) return _cachedCountryCode;
    try {
      _cachedCountryCode = await _storage.read(key: _countryCodeKey).timeout(const Duration(seconds: 2));
    } catch (_) {}
    return _cachedCountryCode;
  }

  static Future<String?> getSelectedCountryName() async {
    if (_cachedCountryName != null) return _cachedCountryName;
    try {
      _cachedCountryName = await _storage.read(key: _countryNameKey).timeout(const Duration(seconds: 2));
    } catch (_) {}
    return _cachedCountryName;
  }

  static Future<String?> getCurrencyCode() async {
    if (_cachedCurrencyCode != null) return _cachedCurrencyCode;
    try {
      _cachedCurrencyCode = await _storage.read(key: _currencyCodeKey).timeout(const Duration(seconds: 2));
    } catch (_) {}
    return _cachedCurrencyCode;
  }

  static Future<String?> getCurrencySymbol() async {
    if (_cachedCurrencySymbol != null) return _cachedCurrencySymbol;
    try {
      _cachedCurrencySymbol = await _storage.read(key: _currencySymbolKey).timeout(const Duration(seconds: 2));
    } catch (_) {}
    return _cachedCurrencySymbol;
  }

  static Future<bool> isCountryManual() async {
    if (_cachedIsCountryManual != null) return _cachedIsCountryManual!;
    try {
      final val = await _storage.read(key: _isCountryManualKey).timeout(const Duration(seconds: 2));
      _cachedIsCountryManual = val == 'true';
    } catch (_) {
      _cachedIsCountryManual = false;
    }
    return _cachedIsCountryManual!;
  }

  static Future<void> setCountryManual(bool isManual) async {
    _cachedIsCountryManual = isManual;
    try {
      await _storage.write(key: _isCountryManualKey, value: isManual ? 'true' : 'false').timeout(const Duration(seconds: 2));
    } catch (_) {}
  }

  static Future<void> saveToken(String token) async {
    _cachedToken = token;
    try {
      await _storage.write(key: _tokenKey, value: token).timeout(const Duration(seconds: 2));
    } catch (_) {}
  }

  static Future<String?> getToken() async {
    if (_cachedToken != null) return _cachedToken;
    try {
      _cachedToken = await _storage.read(key: _tokenKey).timeout(const Duration(seconds: 2));
    } catch (_) {}
    return _cachedToken;
  }

  static Future<void> saveUserData({
    required String role,
    required String name,
    required String email,
    String? password,
    String? avatarUrl,
  }) async {
    _cachedRole = role;
    _cachedUserName = name;
    _cachedUserEmail = email;
    if (password != null && password.isNotEmpty) {
      _cachedSavedPassword = password;
    }
    if (avatarUrl != null && avatarUrl.isNotEmpty) {
      _cachedAvatarUrl = avatarUrl;
    }

    try {
      await _storage.write(key: _roleKey, value: role).timeout(const Duration(seconds: 2));
      await _storage.write(key: _userNameKey, value: name).timeout(const Duration(seconds: 2));
      await _storage.write(key: _userEmailKey, value: email).timeout(const Duration(seconds: 2));
      if (password != null && password.isNotEmpty) {
        await _storage.write(key: _savedPasswordKey, value: password).timeout(const Duration(seconds: 2));
      }
      if (avatarUrl != null && avatarUrl.isNotEmpty) {
        await _storage.write(key: _avatarUrlKey, value: avatarUrl).timeout(const Duration(seconds: 2));
      }
    } catch (_) {}
  }

  static Future<void> saveAvatarUrl(String? avatarUrl) async {
    _cachedAvatarUrl = avatarUrl;
    try {
      if (avatarUrl != null && avatarUrl.isNotEmpty) {
        await _storage.write(key: _avatarUrlKey, value: avatarUrl).timeout(const Duration(seconds: 2));
      } else {
        await _storage.delete(key: _avatarUrlKey).timeout(const Duration(seconds: 2));
      }
    } catch (_) {}
  }

  static Future<String?> getAvatarUrl() async {
    if (_cachedAvatarUrl != null) return _cachedAvatarUrl;
    try {
      _cachedAvatarUrl = await _storage.read(key: _avatarUrlKey).timeout(const Duration(seconds: 2));
    } catch (_) {}
    return _cachedAvatarUrl;
  }

  static Future<void> saveCredentials(String email, String password) async {
    _cachedUserEmail = email;
    _cachedSavedPassword = password;
    try {
      await _storage.write(key: _userEmailKey, value: email).timeout(const Duration(seconds: 2));
      await _storage.write(key: _savedPasswordKey, value: password).timeout(const Duration(seconds: 2));
    } catch (_) {}
  }

  static Future<String?> getRole() async {
    if (_cachedRole != null) return _cachedRole;
    try {
      _cachedRole = await _storage.read(key: _roleKey).timeout(const Duration(seconds: 2));
    } catch (_) {}
    return _cachedRole;
  }

  static Future<String?> getUserName() async {
    if (_cachedUserName != null) return _cachedUserName;
    try {
      _cachedUserName = await _storage.read(key: _userNameKey).timeout(const Duration(seconds: 2));
    } catch (_) {}
    return _cachedUserName;
  }

  static Future<String?> getUserEmail() async {
    if (_cachedUserEmail != null) return _cachedUserEmail;
    try {
      _cachedUserEmail = await _storage.read(key: _userEmailKey).timeout(const Duration(seconds: 2));
    } catch (_) {}
    return _cachedUserEmail;
  }

  static Future<String?> getSavedPassword() async {
    if (_cachedSavedPassword != null) return _cachedSavedPassword;
    try {
      _cachedSavedPassword = await _storage.read(key: _savedPasswordKey).timeout(const Duration(seconds: 2));
    } catch (_) {}
    return _cachedSavedPassword;
  }

  static Future<void> clear() async {
    final email = _cachedUserEmail;
    final countryCode = _cachedCountryCode;
    final countryName = _cachedCountryName;
    final currCode = _cachedCurrencyCode;
    final currSym = _cachedCurrencySymbol;
    final isManual = _cachedIsCountryManual ?? true;

    // Immediately clear in-memory credentials so state is 100% logged out
    _cachedToken = null;
    _cachedRole = null;
    _cachedUserName = null;
    _cachedSavedPassword = null;
    _cachedAvatarUrl = null;

    try {
      // Completely clear auth token, saved password, roles, etc. from storage
      await _storage.deleteAll().timeout(const Duration(seconds: 2));
      // Keep remembered email for login convenience
      if (email != null && email.isNotEmpty) {
        await _storage.write(key: _userEmailKey, value: email).timeout(const Duration(seconds: 2));
      }
      if (countryCode != null) {
        await saveCountryPreference(
          code: countryCode,
          name: countryName ?? 'United States',
          currencyCode: currCode ?? 'USD',
          currencySymbol: currSym ?? '\$',
          isManual: isManual,
        );
      }
    } catch (_) {}
  }

  static Future<void> fullReset() async {
    _cachedToken = null;
    _cachedRole = null;
    _cachedUserName = null;
    _cachedUserEmail = null;
    _cachedSavedPassword = null;
    _cachedAvatarUrl = null;
    try {
      await _storage.deleteAll().timeout(const Duration(seconds: 2));
    } catch (_) {}
  }
}
