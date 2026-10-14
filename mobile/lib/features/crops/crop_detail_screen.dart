import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/utils/bangla.dart';
import '../../core/utils/nav.dart';
import '../../core/widgets/bullet_text.dart';
import '../../core/widgets/common.dart';
import '../../core/widgets/crop_avatar.dart';
import '../../core/widgets/net_image.dart';
import '../../core/widgets/speak_button.dart';
import '../../l10n/app_localizations.dart';
import '../../models/content.dart';
import '../../state/content_controller.dart';
import '../../state/settings_controller.dart';
import '../ai_doctor/ai_doctor_screen.dart';
import '../calculator/fertilizer_calculator_screen.dart';
import '../my_crops/add_my_crop_screen.dart';
import 'crop_labels.dart';
import 'problem_detail_screen.dart';

class CropDetailScreen extends StatelessWidget {
  const CropDetailScreen({super.key, required this.crop});
  final Crop crop;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;

    return DefaultTabController(
      length: 4,
      child: Scaffold(
        appBar: AppBar(
          title: Row(children: [CropAvatar(crop, size: 36), const SizedBox(width: 10), Flexible(child: Text(crop.name(lang)))]),
          bottom: TabBar(
            isScrollable: true,
            tabAlignment: TabAlignment.start,
            labelColor: Colors.white,
            unselectedLabelColor: Colors.white70,
            indicatorColor: Colors.white,
            labelStyle: const TextStyle(fontFamily: 'HindSiliguri', fontSize: 16, fontWeight: FontWeight.w600),
            tabs: [
              Tab(text: '🩺 ${l10n.tabProblems}'),
              Tab(text: '📘 ${l10n.tabGuide}'),
              Tab(text: '🗓️ ${l10n.tabCalendar}'),
              Tab(text: '🧪 ${l10n.tabFertilizer}'),
            ],
          ),
        ),
        body: TabBarView(children: [
          _ProblemsTab(crop: crop),
          _GuideTab(crop: crop),
          _CalendarTab(crop: crop),
          _FertilizerTab(crop: crop),
        ]),
      ),
    );
  }
}

/// Find the problem by tapping the part of the plant where you see it.
class _ProblemsTab extends StatefulWidget {
  const _ProblemsTab({required this.crop});
  final Crop crop;

  @override
  State<_ProblemsTab> createState() => _ProblemsTabState();
}

class _ProblemsTabState extends State<_ProblemsTab> {
  String _part = 'all';

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;
    final parts = CropLabels.parts.where((p) => widget.crop.problems.any((pr) => pr.affectedPart == p)).toList();
    final problems = widget.crop.problems.where((p) => _part == 'all' || p.affectedPart == _part).toList();

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        FilledButton.icon(
          onPressed: () => push(context, AiDoctorScreen(cropId: widget.crop.id)),
          icon: const Icon(Icons.photo_camera),
          label: Text(l10n.aiShowPhoto),
        ),
        const SizedBox(height: 16),
        Text(l10n.whereIsProblem, style: Theme.of(context).textTheme.titleMedium),
        const SizedBox(height: 10),
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: [
            for (final p in ['all', ...parts])
              ChoiceChip(
                avatar: Text(CropLabels.partEmoji(p)),
                label: Text(p == 'all' ? l10n.partAll : CropLabels.part(p, l10n)),
                selected: _part == p,
                onSelected: (_) => setState(() => _part = p),
              ),
          ],
        ),
        const SizedBox(height: 16),
        if (problems.isEmpty) EmptyView(message: l10n.noProblemsForPart, icon: '👍'),
        for (final problem in problems)
          Padding(
            padding: const EdgeInsets.only(bottom: 12),
            child: Card(
              clipBehavior: Clip.antiAlias,
              child: InkWell(
                onTap: () => push(context, ProblemDetailScreen(problem: problem, crop: widget.crop)),
                child: Padding(
                  padding: const EdgeInsets.all(14),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      if (problem.imageUrl != null) ...[NetImage(problem.imageUrl!, height: 150), const SizedBox(height: 10)],
                      Row(
                        children: [
                          Text(problem.emoji, style: const TextStyle(fontSize: 28)),
                          const SizedBox(width: 10),
                          Expanded(child: Text(problem.name(lang), style: Theme.of(context).textTheme.titleMedium)),
                          const Icon(Icons.chevron_right),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Wrap(spacing: 6, children: [
                        Pill(CropLabels.problemType(problem.type, l10n), color: Colors.brown),
                        Pill('${CropLabels.partEmoji(problem.affectedPart)} ${CropLabels.part(problem.affectedPart, l10n)}', color: Colors.green.shade800),
                        if (problem.severity == 'high') Pill(l10n.severityHigh, color: Colors.red.shade700),
                      ]),
                      const SizedBox(height: 8),
                      Text(problem.symptoms(lang).split('\n').first, maxLines: 3, overflow: TextOverflow.ellipsis),
                    ],
                  ),
                ),
              ),
            ),
          ),
      ],
    );
  }
}

class _GuideTab extends StatelessWidget {
  const _GuideTab({required this.crop});
  final Crop crop;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;
    String months(List<int> m) => m.map((x) => Bangla.monthName(x, lang)).join(', ');

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        if (crop.imageUrl != null) ...[NetImage(crop.imageUrl!), const SizedBox(height: 12)],
        if (crop.summary(lang).isNotEmpty) Text(crop.summary(lang), style: Theme.of(context).textTheme.bodyLarge),
        const SizedBox(height: 12),
        Card(
          child: Column(children: [
            if (crop.season != null) _fact('🌦️', l10n.season, CropLabels.season(crop.season, l10n)),
            if (crop.sowingMonths.isNotEmpty) _fact('🌱', l10n.sowingTime, months(crop.sowingMonths)),
            if (crop.harvestMonths.isNotEmpty) _fact('🧺', l10n.harvestTime, months(crop.harvestMonths)),
            if (crop.durationDays != null) _fact('⏱️', l10n.duration, l10n.daysCount(Bangla.digits('${crop.durationDays}', lang))),
            if (crop.isGardenFriendly) _fact('🪴', l10n.gardenFriendly, '✓'),
          ]),
        ),
        const SizedBox(height: 16),
        Row(children: [
          Expanded(child: Text(l10n.tabGuide, style: Theme.of(context).textTheme.titleMedium)),
          SpeakButton(id: 'guide-${crop.id}', text: crop.cultivation(lang), compact: true),
        ]),
        const SizedBox(height: 8),
        BulletText(crop.cultivation(lang)),
      ],
    );
  }

  Widget _fact(String emoji, String label, String value) => ListTile(
        dense: true,
        leading: Text(emoji, style: const TextStyle(fontSize: 22)),
        title: Text(label, style: const TextStyle(fontSize: 14, color: Colors.black54)),
        subtitle: Text(value, style: const TextStyle(fontSize: 16, color: Colors.black87, fontWeight: FontWeight.w600)),
      );
}

class _CalendarTab extends StatelessWidget {
  const _CalendarTab({required this.crop});
  final Crop crop;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;

    if (crop.stages.isEmpty) return EmptyView(message: l10n.noCalendar, icon: '🗓️');

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        InfoBanner(text: l10n.calendarExplain),
        const SizedBox(height: 12),
        Card(
          child: Column(children: [
            for (final stage in crop.stages)
              ListTile(
                leading: Text(stage.emoji, style: const TextStyle(fontSize: 26)),
                title: Text(stage.title(lang), style: const TextStyle(fontWeight: FontWeight.w600)),
                subtitle: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text(
                    stage.dayOffset < 0
                        ? l10n.stageDaysBefore(Bangla.digits('${-stage.dayOffset}', lang))
                        : l10n.stageDay(Bangla.digits('${stage.dayOffset}', lang)),
                    style: TextStyle(color: Theme.of(context).colorScheme.primary, fontWeight: FontWeight.w600),
                  ),
                  if (stage.details(lang).isNotEmpty) Text(stage.details(lang)),
                ]),
              ),
          ]),
        ),
        const SizedBox(height: 16),
        FilledButton.icon(
          onPressed: () => push(context, AddMyCropScreen(initialCrop: crop)),
          icon: const Icon(Icons.add),
          label: Text(l10n.addToMyCrops),
        ),
      ],
    );
  }
}

class _FertilizerTab extends StatelessWidget {
  const _FertilizerTab({required this.crop});
  final Crop crop;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final settings = context.watch<SettingsController>();
    final lang = settings.language;
    final kb = context.watch<ContentController>().kb;

    if (crop.doses.isEmpty || kb == null) return EmptyView(message: l10n.noFertilizerData, icon: '🧪');

    final perBigha = settings.bighaDecimal / 247.105; // hectares in one bigha

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Card(
          child: Column(children: [
            for (final dose in crop.doses)
              if (kb.fertilizerById(dose.fertilizerId) case final fertilizer?)
                ListTile(
                  title: Text(fertilizer.name(lang), style: const TextStyle(fontWeight: FontWeight.w600)),
                  subtitle: Text(dose.application(lang)),
                  trailing: Text(
                    '${l10n.kg(Bangla.number(dose.kgPerHectare * perBigha, lang, decimals: 1))}\n${l10n.perBighaShort}',
                    textAlign: TextAlign.end,
                    style: const TextStyle(fontWeight: FontWeight.w600),
                  ),
                ),
          ]),
        ),
        const SizedBox(height: 12),
        InfoBanner(text: l10n.calcDisclaimer),
        const SizedBox(height: 16),
        FilledButton.icon(
          onPressed: () => push(context, FertilizerCalculatorScreen(initialCrop: crop)),
          icon: const Icon(Icons.calculate),
          label: Text(l10n.calculateForMyLand),
        ),
      ],
    );
  }
}
