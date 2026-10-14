import 'package:flutter/foundation.dart';

import '../data/auth_repository.dart';
import '../models/question.dart';

class AuthController extends ChangeNotifier {
  AuthController(this._repo) : user = _repo.savedUser;

  final AuthRepository _repo;
  AppUser? user;

  bool get isLoggedIn => user != null && _repo.token != null;

  Future<void> login(String phone, String pin) async {
    user = await _repo.login(phone: phone, pin: pin);
    notifyListeners();
  }

  Future<void> register({required String name, required String phone, required String pin, int? districtId, String? userType, String language = 'bn'}) async {
    user = await _repo.register(name: name, phone: phone, pin: pin, districtId: districtId, userType: userType, language: language);
    notifyListeners();
  }

  Future<void> logout() async {
    await _repo.logout();
    user = null;
    notifyListeners();
  }

  Future<void> deleteAccount(String pin) async {
    await _repo.deleteAccount(pin);
    user = null;
    notifyListeners();
  }

  /// Called when the server rejects our token.
  Future<void> sessionExpired() async {
    if (user == null) return;
    await _repo.clearSession();
    user = null;
    notifyListeners();
  }
}
