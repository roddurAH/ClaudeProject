import 'dart:convert';

import '../core/network/api_client.dart';
import '../core/storage/local_store.dart';
import '../models/question.dart';

/// Phone + PIN login. The token and profile are kept in the settings box.
class AuthRepository {
  AuthRepository(this._api, this._store);

  final ApiClient _api;
  final LocalStore _store;

  static const _tokenKey = 'auth_token';
  static const _userKey = 'auth_user';

  String? get token => _store.settings.get(_tokenKey) as String?;

  AppUser? get savedUser {
    final raw = _store.settings.get(_userKey);
    if (raw is! String) return null;
    try {
      return AppUser.fromJson(jsonDecode(raw) as Map<String, dynamic>);
    } catch (_) {
      return null;
    }
  }

  Future<AppUser> login({required String phone, required String pin}) async {
    final response = await _api.post('/auth/login', data: {'phone': phone, 'pin': pin, 'device_name': 'krishi-sathi-app'});
    return _saveSession(response.data as Map<String, dynamic>);
  }

  Future<AppUser> register({
    required String name,
    required String phone,
    required String pin,
    int? districtId,
    String? userType,
    String language = 'bn',
  }) async {
    final response = await _api.post('/auth/register', data: {
      'name': name,
      'phone': phone,
      'pin': pin,
      'district_id': districtId,
      'user_type': userType,
      'preferred_language': language,
      'device_name': 'krishi-sathi-app',
    });
    return _saveSession(response.data as Map<String, dynamic>);
  }

  Future<void> logout() async {
    try {
      await _api.post('/auth/logout');
    } catch (_) {
      // Logging out must work offline too; the token expires on the server anyway.
    }
    await clearSession();
    await _forgetUserData();
  }

  /// Permanently deletes the account on the server (Google Play requirement).
  /// Throws [ApiException] (e.g. wrong PIN, offline) and then keeps the session.
  Future<void> deleteAccount(String pin) async {
    await _api.delete('/me', data: {'pin': pin});
    await clearSession();
    await _forgetUserData();
  }

  Future<void> clearSession() async {
    await _store.settings.delete(_tokenKey);
    await _store.settings.delete(_userKey);
  }

  /// The next person to sign in on this phone must not see (or send) the
  /// previous user's questions. "My crops" is not tied to an account and stays.
  Future<void> _forgetUserData() async {
    await _store.cache.delete('questions:mine');
    for (final key in _store.outbox.keys.toList()) {
      await _store.outbox.delete(key);
    }
  }

  Future<AppUser> _saveSession(Map<String, dynamic> json) async {
    final user = AppUser.fromJson(json['user'] as Map<String, dynamic>);
    await _store.settings.put(_tokenKey, json['token'] as String);
    await _store.settings.put(_userKey, jsonEncode(user.toJson()));
    return user;
  }
}
