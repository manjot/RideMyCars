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
  static const _referralCodeKey = 'referral_code';
  static const _referredByKey = 'referred_by';
  static const _avatarUrlKey = 'avatar_url';

  static const _countryCodeKey = 'selected_country_code';
  static const _countryNameKey = 'selected_country_name';
  static const _currencyCodeKey = 'selected_currency_code';
  static const _currencySymbolKey = 'selected_currency_symbol';
  static const _isCountryManualKey = 'is_country_manual';

  static Future<void> saveToken(String token) async {
    await _storage.write(key: _tokenKey, value: token);
  }

  static Future<String?> getToken() async {
    return await _storage.read(key: _tokenKey);
  }

  static Future<void> saveUserData({
    required String role,
    required String name,
    required String email,
    String? password,
    String? referralCode,
    String? referredBy,
    String? avatarUrl,
  }) async {
    await _storage.write(key: _roleKey, value: role);
    await _storage.write(key: _userNameKey, value: name);
    await _storage.write(key: _userEmailKey, value: email);
    if (password != null && password.isNotEmpty) {
      await _storage.write(key: _savedPasswordKey, value: password);
    }
    if (referralCode != null && referralCode.isNotEmpty) {
      await _storage.write(key: _referralCodeKey, value: referralCode);
    }
    if (referredBy != null && referredBy.isNotEmpty) {
      await _storage.write(key: _referredByKey, value: referredBy);
    }
    if (avatarUrl != null && avatarUrl.isNotEmpty) {
      await _storage.write(key: _avatarUrlKey, value: avatarUrl);
    }
  }

  static Future<void> saveCredentials(String email, String password) async {
    await _storage.write(key: _userEmailKey, value: email);
    await _storage.write(key: _savedPasswordKey, value: password);
  }

  static Future<void> saveCountryPreference({
    required String code,
    required String name,
    required String currencyCode,
    required String currencySymbol,
    bool isManual = false,
  }) async {
    await _storage.write(key: _countryCodeKey, value: code.toUpperCase());
    await _storage.write(key: _countryNameKey, value: name);
    await _storage.write(key: _currencyCodeKey, value: currencyCode.toUpperCase());
    await _storage.write(key: _currencySymbolKey, value: currencySymbol);
    await _storage.write(key: _isCountryManualKey, value: isManual ? 'true' : 'false');
  }

  static Future<String?> getSelectedCountryCode() async {
    return await _storage.read(key: _countryCodeKey);
  }

  static Future<String?> getSelectedCountryName() async {
    return await _storage.read(key: _countryNameKey);
  }

  static Future<String?> getCurrencyCode() async {
    return await _storage.read(key: _currencyCodeKey);
  }

  static Future<String?> getCurrencySymbol() async {
    return await _storage.read(key: _currencySymbolKey);
  }

  static Future<bool> isCountryManual() async {
    final val = await _storage.read(key: _isCountryManualKey);
    return val == 'true';
  }

  static Future<void> setCountryManual(bool isManual) async {
    await _storage.write(key: _isCountryManualKey, value: isManual ? 'true' : 'false');
  }

  static Future<String?> getRole() async {
    return await _storage.read(key: _roleKey);
  }

  static Future<String?> getUserName() async {
    return await _storage.read(key: _userNameKey);
  }

  static Future<String?> getUserEmail() async {
    return await _storage.read(key: _userEmailKey);
  }

  static Future<String?> getSavedPassword() async {
    return await _storage.read(key: _savedPasswordKey);
  }

  static Future<String?> getReferralCode() async {
    return await _storage.read(key: _referralCodeKey);
  }

  static Future<String?> getReferredBy() async {
    return await _storage.read(key: _referredByKey);
  }

  static Future<String?> getAvatarUrl() async {
    return await _storage.read(key: _avatarUrlKey);
  }

  static Future<void> clear() async {
    try {
      final email = await getUserEmail();
      final countryCode = await getSelectedCountryCode();
      final countryName = await getSelectedCountryName();
      final currCode = await getCurrencyCode();
      final currSym = await getCurrencySymbol();
      final isManual = await isCountryManual();

      // Completely clear auth token, saved password, roles, etc.
      await _storage.deleteAll();

      // Preserve only the remembered email for login pre-fill convenience (never the password!)
      if (email != null && email.isNotEmpty) {
        await _storage.write(key: _userEmailKey, value: email);
      }

      // Preserve detected/selected country preference across sessions
      if (countryCode != null && countryName != null && currCode != null && currSym != null) {
        await saveCountryPreference(
          code: countryCode,
          name: countryName,
          currencyCode: currCode,
          currencySymbol: currSym,
          isManual: isManual,
        );
      }
    } catch (_) {
      // Ignore secure storage deletion quirks on specific Android devices
    }
  }

  static Future<void> fullReset() async {
    try {
      await _storage.deleteAll();
    } catch (_) {}
  }
}
