import 'package:flutter/foundation.dart';

import '../core/config/app_config.dart';
import '../core/storage/local_store.dart';

/// User preferences, saved on the phone.
class SettingsController extends ChangeNotifier {
  SettingsController(this._store);

  final LocalStore _store;

  T? _get<T>(String key) {
    final v = _store.settings.get(key);
    return v is T ? v : null;
  }

  Future<void> _set(String key, Object? value) async {
    if (value == null) {
      await _store.settings.delete(key);
    } else {
      await _store.settings.put(key, value);
    }
    notifyListeners();
  }

  /// 'bn' or 'en'. Bangla is the default.
  String get language => _get<String>('language') ?? 'bn';
  Future<void> setLanguage(String value) => _set('language', value);

  bool get onboarded => _get<bool>('onboarded') ?? false;
  Future<void> completeOnboarding() => _set('onboarded', true);

  int? get districtId => _get<int>('district_id');
  Future<void> setDistrictId(int? value) => _set('district_id', value);

  /// farmer, gardener, learner, extension_worker
  String get userType => _get<String>('user_type') ?? 'farmer';
  Future<void> setUserType(String value) => _set('user_type', value);
  bool get isGardener => userType == 'gardener';

  /// 1.0 normal, 1.15 large, 1.3 extra large
  double get textScale => _get<double>('text_scale') ?? 1.0;
  Future<void> setTextScale(double value) => _set('text_scale', value);

  /// When on, photos load only after a tap (saves mobile data).
  bool get dataSaver => _get<bool>('data_saver') ?? false;
  Future<void> setDataSaver(bool value) => _set('data_saver', value);

  double get bighaDecimal => _get<double>('bigha_decimal') ?? AppConfig.defaultBighaDecimal;
  Future<void> setBighaDecimal(double value) => _set('bigha_decimal', value);

  String? get apiBaseUrlOverride => _get<String>('api_base_url');
  String get apiBaseUrl => (apiBaseUrlOverride?.isNotEmpty ?? false) ? apiBaseUrlOverride! : AppConfig.defaultApiBaseUrl();
  Future<void> setApiBaseUrl(String? value) => _set('api_base_url', (value == null || value.trim().isEmpty) ? null : value.trim());
}
