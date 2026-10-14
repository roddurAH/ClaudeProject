import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_theme.dart';
import '../../core/utils/bangla.dart';
import '../../core/utils/nav.dart';
import '../../core/utils/weather_codes.dart';
import '../../core/widgets/common.dart';
import '../../core/widgets/speak_button.dart';
import '../../data/cached_fetch.dart';
import '../../data/weather_repository.dart';
import '../../l10n/app_localizations.dart';
import '../../models/weather.dart';
import '../../state/settings_controller.dart';
import '../onboarding/district_picker.dart';

/// 7-day forecast for the user's district plus farm advice derived from it.
class WeatherScreen extends StatefulWidget {
  const WeatherScreen({super.key});

  @override
  State<WeatherScreen> createState() => _WeatherScreenState();
}

class _WeatherScreenState extends State<WeatherScreen> {
  CachedResult<Forecast>? _result;
  Object? _error;
  bool _loading = false;

  @override
  void initState() {
    super.initState();
    final districtId = context.read<SettingsController>().districtId;
    if (districtId != null) {
      _result = context.read<WeatherRepository>().peekForecast(districtId);
      _load();
    }
  }

  Future<void> _load() async {
    final districtId = context.read<SettingsController>().districtId;
    if (districtId == null) return;
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final result = await context.read<WeatherRepository>().forecast(districtId);
      if (mounted) setState(() => _result = result);
    } catch (e) {
      if (mounted) setState(() => _error = e);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final settings = context.watch<SettingsController>();
    final lang = settings.language;
    final f = _result?.data;

    return Scaffold(
      appBar: AppBar(title: Text(l10n.featureWeather)),
      body: () {
        if (settings.districtId == null) {
          return EmptyView(
            message: l10n.chooseDistrictFirst,
            icon: '📍',
            action: FilledButton(
              onPressed: () async {
                await push(context, const DistrictPickerScreen());
                _load();
              },
              child: Text(l10n.chooseDistrict),
            ),
          );
        }
        if (f == null) {
          if (_error != null) return ErrorView(error: _error!, onRetry: _load);
          return const LoadingView();
        }
        return RefreshIndicator(
          onRefresh: _load,
          child: ListView(
            padding: const EdgeInsets.all(16),
            children: [
              if (_loading) const LinearProgressIndicator(),
              if (_result!.fromCache) ...[OfflineNotice(savedAt: _result!.savedAt), const SizedBox(height: 8)],
              if (f.stale) ...[InfoBanner(level: 'warning', text: l10n.staleWeather), const SizedBox(height: 8)],
              _CurrentCard(forecast: f),
              SectionHeader(l10n.farmAdvice, icon: '👨‍🌾', trailing: SpeakButton(
                id: 'weather-advice',
                compact: true,
                text: f.advisories.map((a) => '${a.title(lang)}। ${a.body(lang)}').join('\n'),
              )),
              for (final a in f.advisories)
                Padding(
                  padding: const EdgeInsets.only(bottom: 10),
                  child: InfoBanner(level: a.level, icon: a.emoji, title: a.title(lang), text: a.body(lang)),
                ),
              SectionHeader(l10n.next7Days, icon: '📅'),
              Card(
                child: Column(children: [
                  for (var i = 0; i < f.daily.length; i++) _DayRow(day: f.daily[i], index: i),
                ]),
              ),
              const SizedBox(height: 8),
              if (f.source != null) Text(l10n.forecastSource(f.source!), style: Theme.of(context).textTheme.bodySmall),
            ],
          ),
        );
      }(),
    );
  }
}

class _CurrentCard extends StatelessWidget {
  const _CurrentCard({required this.forecast});
  final Forecast forecast;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;
    final f = forecast;
    return Card(
      color: const Color(0xFFE8F5E9),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(children: [
          Text(WeatherCodes.emoji(f.code), style: const TextStyle(fontSize: 56)),
          const SizedBox(width: 16),
          Expanded(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(l10n.weatherIn(f.district(lang)), style: Theme.of(context).textTheme.titleMedium),
              if (f.temperature != null)
                Text('${Bangla.number(f.temperature!, lang)}°C', style: const TextStyle(fontSize: 34, fontWeight: FontWeight.w600)),
              Text(WeatherCodes.describe(f.code, l10n)),
              if (f.humidity != null) Text('${l10n.humidity}: ${Bangla.digits('${f.humidity}', lang)}%'),
            ]),
          ),
        ]),
      ),
    );
  }
}

class _DayRow extends StatelessWidget {
  const _DayRow({required this.day, required this.index});
  final DailyForecast day;
  final int index;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;
    final name = index == 0 ? l10n.today : (index == 1 ? l10n.tomorrow : Bangla.weekdayName(day.date, lang));
    final rainy = (day.rainMm ?? 0) >= 5;

    return ListTile(
      leading: Text(WeatherCodes.emoji(day.code), style: const TextStyle(fontSize: 30)),
      title: Text(name, style: const TextStyle(fontWeight: FontWeight.w600)),
      subtitle: Text(
        '${Bangla.shortDate(day.date, lang)} · ${WeatherCodes.describe(day.code, l10n)}'
        '${day.rainMm != null ? '\n💧 ${Bangla.number(day.rainMm!, lang, decimals: 1)} ${l10n.millimetre}' : ''}'
        '${day.rainProbability != null ? ' (${Bangla.digits('${day.rainProbability}', lang)}%)' : ''}',
        style: TextStyle(color: rainy ? AppTheme.info : null),
      ),
      isThreeLine: true,
      trailing: Text(
        '${Bangla.number(day.tMax ?? 0, lang)}° / ${Bangla.number(day.tMin ?? 0, lang)}°',
        style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w600),
      ),
    );
  }
}
