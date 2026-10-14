import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/config/app_config.dart';
import '../../core/theme/app_theme.dart';
import '../../core/utils/bangla.dart';
import '../../core/utils/nav.dart';
import '../../core/widgets/common.dart';
import '../../data/question_repository.dart';
import '../../data/weather_repository.dart';
import '../../l10n/app_localizations.dart';
import '../../models/weather.dart';
import '../../state/auth_controller.dart';
import '../../state/content_controller.dart';
import '../../state/my_crops_controller.dart';
import '../../state/reference_controller.dart';
import '../../state/settings_controller.dart';
import '../ai_doctor/ai_doctor_screen.dart';
import '../articles/article_list_screen.dart';
import '../ask_expert/ask_expert_screen.dart';
import '../calculator/fertilizer_calculator_screen.dart';
import '../crops/crop_list_screen.dart';
import '../my_crops/my_crops_screen.dart';
import '../prices/prices_screen.dart';
import '../seasonal/sow_now_screen.dart';
import '../settings/settings_screen.dart';
import '../weather/weather_screen.dart';
import 'task_tile.dart';

/// Home: alerts, today's weather advice, this week's tasks and big tiles for
/// every feature. Works fully offline with saved data.
class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  List<FarmAlert> _alerts = [];
  Forecast? _forecast;
  int _pendingQuestions = 0;

  @override
  void initState() {
    super.initState();
    _loadLocal();
    WidgetsBinding.instance.addPostFrameCallback((_) => _refresh());
  }

  void _loadLocal() {
    final weather = context.read<WeatherRepository>();
    final districtId = context.read<SettingsController>().districtId;
    _alerts = weather.peekAlerts(districtId)?.data ?? [];
    _forecast = districtId == null ? null : weather.peekForecast(districtId)?.data;
    _pendingQuestions = context.read<QuestionRepository>().pending().length;
  }

  Future<void> _refresh() async {
    final weather = context.read<WeatherRepository>();
    final questions = context.read<QuestionRepository>();
    final content = context.read<ContentController>();
    final loggedIn = context.read<AuthController>().isLoggedIn;
    final districtId = context.read<SettingsController>().districtId;

    await Future.wait([
      weather.alerts(districtId).then((r) => _alerts = r.data).catchError((_) => _alerts),
      if (districtId != null)
        weather.forecast(districtId).then<Forecast?>((r) => _forecast = r.data).catchError((_) => _forecast),
      if (loggedIn) questions.flushOutbox().catchError((_) => 0),
      content.sync(),
    ]);
    if (!mounted) return;
    setState(() => _pendingQuestions = questions.pending().length);
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final settings = context.watch<SettingsController>();
    final lang = settings.language;
    final reference = context.watch<ReferenceController>();
    final content = context.watch<ContentController>();
    final tasks = context.watch<MyCropsController>().weekTasks(content.kb, DateTime.now());
    final now = DateTime.now();

    return Scaffold(
      appBar: AppBar(
        toolbarHeight: 68,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(l10n.appTitle),
            Text(
              '${Bangla.weekdayName(now, lang)}, ${Bangla.date(now, lang, withYear: false)} · ${Bangla.toBengali(now).format(lang)}',
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w400),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.settings),
            tooltip: l10n.settings,
            onPressed: () async {
              await push(context, const SettingsScreen());
              if (mounted) {
                setState(_loadLocal);
                _refresh();
              }
            },
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _refresh,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 32),
          children: [
            if (reference.updateRequired && !kIsWeb)
              Padding(
                padding: const EdgeInsets.only(bottom: 10),
                child: InfoBanner(
                  level: 'warning',
                  icon: '⬆️',
                  title: l10n.updateAppTitle,
                  text: l10n.updateAppText,
                  trailing: defaultTargetPlatform == TargetPlatform.android
                      ? TextButton(onPressed: () => openWebPage(Uri.parse(AppConfig.playStoreUrl)), child: Text(l10n.updateApp))
                      : null,
                ),
              ),
            for (final alert in _alerts)
              Padding(
                padding: const EdgeInsets.only(bottom: 10),
                child: InfoBanner(
                  level: alert.severity,
                  icon: alert.severity == 'danger' ? '🚨' : '📢',
                  title: alert.title(lang),
                  text: alert.body(lang),
                ),
              ),
            if (reference.meta?.aiEnabled ?? true) ...[
              const _AiDoctorCard(),
              const SizedBox(height: 10),
            ],
            _WeatherCard(forecast: _forecast, districtName: reference.districtById(settings.districtId)?.name(lang)),
            if (_pendingQuestions > 0) ...[
              const SizedBox(height: 10),
              InfoBanner(icon: '📤', text: l10n.pendingUploads(Bangla.digits('$_pendingQuestions', lang))),
            ],
            if (tasks.isNotEmpty) ...[
              SectionHeader(
                l10n.thisWeekTasks,
                icon: '🗓️',
                trailing: TextButton(onPressed: () => push(context, const MyCropsScreen()), child: Text(l10n.seeAll)),
              ),
              Card(
                child: Column(children: [for (final task in tasks.take(4)) TaskTile(task: task, showCrop: true)]),
              ),
            ],
            const SizedBox(height: 12),
            _FeatureGrid(gardenerFirst: settings.isGardener),
            const SizedBox(height: 16),
            _HelplineButton(number: reference.helpline),
          ],
        ),
      ),
    );
  }
}

/// The quickest way to help: photograph the sick plant (or just speak).
class _AiDoctorCard extends StatelessWidget {
  const _AiDoctorCard();

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Card(
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: () => push(context, const AiDoctorScreen()),
        child: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(colors: [Color(0xFF00897B), Color(0xFF004D40)], begin: Alignment.topLeft, end: Alignment.bottomRight),
          ),
          padding: const EdgeInsets.fromLTRB(16, 14, 12, 14),
          child: Row(children: [
            const Text('📸', style: TextStyle(fontSize: 36)),
            const SizedBox(width: 14),
            Expanded(
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(l10n.aiDoctorTitle, style: const TextStyle(color: Colors.white, fontSize: 19, fontWeight: FontWeight.w700)),
                const SizedBox(height: 2),
                Text(l10n.aiDoctorHomeDesc, style: const TextStyle(color: Colors.white, fontSize: 14)),
              ]),
            ),
            const CircleAvatar(backgroundColor: Colors.white24, child: Icon(Icons.photo_camera, color: Colors.white)),
          ]),
        ),
      ),
    );
  }
}

class _WeatherCard extends StatelessWidget {
  const _WeatherCard({required this.forecast, required this.districtName});
  final Forecast? forecast;
  final String? districtName;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;
    final f = forecast;
    final advisory = (f?.advisories.isNotEmpty ?? false) ? f!.advisories.first : null;
    final today = (f?.daily.isNotEmpty ?? false) ? f!.daily.first : null;

    return Card(
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: () => push(context, const WeatherScreen()),
        child: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(colors: [Color(0xFF43A047), Color(0xFF1B5E20)], begin: Alignment.topLeft, end: Alignment.bottomRight),
          ),
          padding: const EdgeInsets.all(16),
          child: DefaultTextStyle(
            style: const TextStyle(color: Colors.white, fontFamily: 'HindSiliguri', fontSize: 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Text(f == null ? '🌤️' : _emoji(f.code), style: const TextStyle(fontSize: 44)),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(districtName == null ? l10n.featureWeather : l10n.weatherIn(districtName!),
                              style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 17)),
                          if (f?.temperature != null)
                            Text(
                              '${Bangla.number(f!.temperature!, lang)}°C'
                              '${today?.rainProbability != null ? '  ·  ${l10n.rain} ${Bangla.digits('${today!.rainProbability}', lang)}%' : ''}',
                              style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w600),
                            )
                          else
                            Text(l10n.featureWeatherDesc),
                        ],
                      ),
                    ),
                    const Icon(Icons.chevron_right, color: Colors.white),
                  ],
                ),
                if (advisory != null) ...[
                  const Divider(color: Colors.white38, height: 20),
                  Text('${advisory.emoji}  ${advisory.title(lang)}', style: const TextStyle(fontWeight: FontWeight.w600)),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }

  static String _emoji(int? code) {
    if (code == null) return '🌤️';
    if (code == 0) return '☀️';
    if (code <= 3) return '⛅';
    if (code == 45 || code == 48) return '🌫️';
    if (code >= 95) return '⛈️';
    if (code >= 51) return '🌧️';
    return '🌤️';
  }
}

class _FeatureGrid extends StatelessWidget {
  const _FeatureGrid({required this.gardenerFirst});
  final bool gardenerFirst;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final cropDoctor = _Feature('🩺', l10n.featureCropDoctor, l10n.featureCropDoctorDesc, const Color(0xFFE8F5E9), const CropListScreen());
    final prices = _Feature('💰', l10n.featurePrices, l10n.featurePricesDesc, const Color(0xFFFFF8E1), const PricesScreen());
    final ask = _Feature('🧑‍🌾', l10n.featureAskExpert, l10n.featureAskExpertDesc, const Color(0xFFE3F2FD), const AskExpertScreen());
    final myCrops = _Feature('🗓️', l10n.featureMyCrops, l10n.featureMyCropsDesc, const Color(0xFFF3E5F5), const MyCropsScreen());
    final calc = _Feature('🧮', l10n.featureCalculator, l10n.featureCalculatorDesc, const Color(0xFFFFEBEE), const FertilizerCalculatorScreen());
    final sow = _Feature('🌱', l10n.featureSowNow, l10n.featureSowNowDesc, const Color(0xFFE0F2F1), const SowNowScreen());
    final tips = _Feature('📖', l10n.featureArticles, l10n.featureArticlesDesc, const Color(0xFFFFF3E0), const ArticleListScreen());

    // Home gardeners care less about wholesale prices and fertilizer per bigha.
    final features = gardenerFirst
        ? [cropDoctor, sow, ask, tips, myCrops, prices, calc]
        : [cropDoctor, prices, ask, myCrops, calc, sow, tips];

    // Rows of equal-height tiles that grow with the text, so nothing is cut
    // off on small phones or with the "extra large text" setting.
    return LayoutBuilder(builder: (context, constraints) {
      final columns = constraints.maxWidth > 700 ? 4 : (constraints.maxWidth > 460 ? 3 : 2);
      final rows = <Widget>[];
      for (var i = 0; i < features.length; i += columns) {
        final rowItems = features.skip(i).take(columns).toList();
        rows.add(Padding(
          padding: const EdgeInsets.only(bottom: 12),
          child: IntrinsicHeight(
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                for (var j = 0; j < columns; j++) ...[
                  if (j > 0) const SizedBox(width: 12),
                  Expanded(child: j < rowItems.length ? _FeatureTile(feature: rowItems[j]) : const SizedBox.shrink()),
                ],
              ],
            ),
          ),
        ));
      }
      return Column(children: rows);
    });
  }
}

class _Feature {
  const _Feature(this.emoji, this.title, this.subtitle, this.color, this.page);
  final String emoji, title, subtitle;
  final Color color;
  final Widget page;
}

class _FeatureTile extends StatelessWidget {
  const _FeatureTile({required this.feature});
  final _Feature feature;

  @override
  Widget build(BuildContext context) {
    return Card(
      color: feature.color,
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: () => push(context, feature.page),
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(feature.emoji, style: const TextStyle(fontSize: 38)),
              const SizedBox(height: 10),
              Text(feature.title, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w600, height: 1.2)),
              const SizedBox(height: 2),
              Text(feature.subtitle, style: const TextStyle(fontSize: 13.5, color: Colors.black87, height: 1.25)),
            ],
          ),
        ),
      ),
    );
  }
}

class _HelplineButton extends StatelessWidget {
  const _HelplineButton({required this.number});
  final String number;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;
    return SizedBox(
      width: double.infinity,
      child: FilledButton.icon(
        style: FilledButton.styleFrom(backgroundColor: AppTheme.soil, minimumSize: const Size.fromHeight(64)),
        onPressed: () => callNumber(number),
        icon: const Icon(Icons.call, size: 28),
        label: Column(children: [
          Text(l10n.callHelpline(Bangla.digits(number, lang))),
          Text(l10n.callHelplineDesc, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w400)),
        ]),
      ),
    );
  }
}
