import 'package:flutter/foundation.dart';

/// Build-time and default configuration.
///
/// The API address can be set three ways (first match wins):
///  1. In the app: Settings → Server address (stored on the phone).
///  2. At build time: `flutter run --dart-define=API_BASE_URL=http://192.168.0.10:8000/api/v1`
///  3. Defaults below, which suit local development with `php artisan serve`.
class AppConfig {
  AppConfig._();

  /// Keep in step with `version:` in pubspec.yaml (see docs/ANDROID_RELEASE.md).
  static const String appVersion = '1.0.0';

  /// Google Play listing, opened when the server requires a newer app version.
  static const String playStoreUrl = 'https://play.google.com/store/apps/details?id=com.krishisathi.app';

  static const String _definedApiBaseUrl = String.fromEnvironment('API_BASE_URL');

  /// Free government Agriculture Call Centre. The server can override it
  /// through /meta, this is only the offline fallback.
  static const String defaultHelpline = '16123';

  /// Size of one bigha in decimals. Differs by region; user can change it.
  static const double defaultBighaDecimal = 33;

  /// Empty means "no server configured": the app runs offline until an
  /// address is entered in Settings or built in with --dart-define.
  static String defaultApiBaseUrl() {
    if (_definedApiBaseUrl.isNotEmpty) return _definedApiBaseUrl;
    // Published phone apps must not point at a developer laptop.
    if (kReleaseMode && !kIsWeb) return '';
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

  /// True when [version] is lower than [minimum] ("1.2.10" vs "1.10.0" compares numerically).
  static bool isOlderVersion(String version, String minimum) {
    List<int> parts(String v) => v.split(RegExp(r'[.+]')).take(3).map((p) => int.tryParse(p) ?? 0).toList();
    final a = parts(version), b = parts(minimum);
    for (var i = 0; i < 3; i++) {
      final x = i < a.length ? a[i] : 0, y = i < b.length ? b[i] : 0;
      if (x != y) return x < y;
    }
    return false;
  }

  /// Public web page on the same server as the API, e.g. the privacy policy:
  /// `https://example.com/api/v1` + `/privacy` → `https://example.com/privacy`.
  /// Null when no server is configured.
  static Uri? webPageUri(String apiBaseUrl, String path, {String language = 'bn'}) {
    final base = apiBaseUrl.trim().replaceAll(RegExp(r'/+$'), '').replaceFirst(RegExp(r'/api(/v\d+)?$'), '');
    if (base.isEmpty) return null;
    final uri = Uri.tryParse('$base$path');
    if (uri == null || !uri.hasScheme) return null;
    return language == 'en' ? uri.replace(queryParameters: {'lang': 'en'}) : uri;
  }
}
