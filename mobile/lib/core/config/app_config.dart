import 'package:flutter/foundation.dart';

/// Build-time and default configuration.
///
/// The API address can be set three ways (first match wins):
///  1. In the app: Settings → Server address (stored on the phone).
///  2. At build time: `flutter run --dart-define=API_BASE_URL=http://192.168.0.10:8000/api/v1`
///  3. Defaults below, which suit local development with `php artisan serve`.
class AppConfig {
  AppConfig._();

  static const String appVersion = '1.0.0';

  static const String _definedApiBaseUrl = String.fromEnvironment('API_BASE_URL');

  /// Free government Agriculture Call Centre. The server can override it
  /// through /meta, this is only the offline fallback.
  static const String defaultHelpline = '16123';

  /// Size of one bigha in decimals. Differs by region; user can change it.
  static const double defaultBighaDecimal = 33;

  static String defaultApiBaseUrl() {
    if (_definedApiBaseUrl.isNotEmpty) return _definedApiBaseUrl;
    if (kIsWeb) {
      // Served by `flutter run -d chrome` or any web server on the same host.
      final host = Uri.base.host.isEmpty ? 'localhost' : Uri.base.host;
      return 'http://$host:8000/api/v1';
    }
    if (defaultTargetPlatform == TargetPlatform.android) {
      // The Android emulator reaches the host computer at 10.0.2.2.
      return 'http://10.0.2.2:8000/api/v1';
    }
    return 'http://localhost:8000/api/v1';
  }
}
