import '../../l10n/app_localizations.dart';

/// WMO weather codes (used by Open-Meteo) → emoji + short description.
class WeatherCodes {
  WeatherCodes._();

  static String emoji(int? code) {
    if (code == null) return '🌡️';
    if (code == 0) return '☀️';
    if (code <= 2) return '⛅';
    if (code == 3) return '☁️';
    if (code == 45 || code == 48) return '🌫️';
    if (code >= 51 && code <= 57) return '🌦️';
    if (code >= 61 && code <= 67) return '🌧️';
    if (code >= 80 && code <= 82) return '🌦️';
    if (code >= 95) return '⛈️';
    return '🌥️';
  }

  static String describe(int? code, AppLocalizations l10n) {
    if (code == null) return '';
    if (code == 0) return l10n.wClear;
    if (code <= 2) return l10n.wPartlyCloudy;
    if (code == 3) return l10n.wCloudy;
    if (code == 45 || code == 48) return l10n.wFog;
    if (code >= 51 && code <= 57) return l10n.wDrizzle;
    if (code >= 61 && code <= 67) return l10n.wRain;
    if (code >= 80 && code <= 82) return l10n.wShowers;
    if (code >= 95) return l10n.wThunder;
    return l10n.wCloudy;
  }
}
