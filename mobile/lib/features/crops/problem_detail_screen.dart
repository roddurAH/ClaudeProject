import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/utils/nav.dart';
import '../../core/widgets/bullet_text.dart';
import '../../core/widgets/common.dart';
import '../../core/widgets/net_image.dart';
import '../../core/widgets/speak_button.dart';
import '../../l10n/app_localizations.dart';
import '../../models/content.dart';
import '../../state/settings_controller.dart';
import '../ask_expert/new_question_screen.dart';
import 'crop_labels.dart';

/// Symptoms → prevention (IPM first) → organic → chemical (last resort, with
/// a safety warning). Ordered on purpose to reduce pesticide overuse.
class ProblemDetailScreen extends StatelessWidget {
  const ProblemDetailScreen({super.key, required this.problem, required this.crop});
  final Problem problem;
  final Crop crop;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;

    final sections = <(String, String, String)>[
      ('🔎', l10n.symptoms, problem.symptoms(lang)),
      ('🛡️', l10n.prevention, problem.prevention(lang)),
      ('🌿', l10n.organicControl, problem.organic(lang)),
    ].where((s) => s.$3.trim().isNotEmpty).toList();

    final chemical = problem.chemical(lang);
    final speech = [problem.name(lang), for (final s in sections) '${s.$2}। ${s.$3}', if (chemical.isNotEmpty) '${l10n.chemicalControl}। $chemical'].join('\n');

    return Scaffold(
      appBar: AppBar(title: Text(problem.name(lang))),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          if (problem.imageUrl != null) ...[NetImage(problem.imageUrl!, height: 220), const SizedBox(height: 12)],
          Row(
            children: [
              Text(problem.emoji, style: const TextStyle(fontSize: 36)),
              const SizedBox(width: 10),
              Expanded(
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text(problem.name(lang), style: Theme.of(context).textTheme.titleLarge),
                  Text('${crop.emoji} ${crop.name(lang)} · ${CropLabels.problemType(problem.type, l10n)}'),
                ]),
              ),
            ],
          ),
          const SizedBox(height: 12),
          SpeakButton(id: 'problem-${problem.id}', text: speech),
          for (final (emoji, title, text) in sections) ...[
            SectionHeader(title, icon: emoji),
            Card(child: Padding(padding: const EdgeInsets.all(14), child: BulletText(text))),
          ],
          if (chemical.isNotEmpty) ...[
            SectionHeader(l10n.chemicalControl, icon: '⚗️'),
            InfoBanner(level: 'warning', icon: '🧤', text: l10n.chemicalWarning),
            const SizedBox(height: 8),
            Card(child: Padding(padding: const EdgeInsets.all(14), child: BulletText(chemical))),
          ],
          const SizedBox(height: 20),
          FilledButton.icon(
            onPressed: () => push(context, NewQuestionScreen(initialCropId: crop.id)),
            icon: const Icon(Icons.support_agent),
            label: Text(l10n.notSureAskExpert),
          ),
        ],
      ),
    );
  }
}
