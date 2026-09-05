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
    final email = await getUserEmail();
    final pass = await getSavedPassword();
    await _storage.deleteAll();
    // Keep saved credentials so user never has to retype
    if (email != null && pass != null) {
      await saveCredentials(email, pass);
    }
  }

  static Future<void> fullReset() async {
    await _storage.deleteAll();
  }
}
