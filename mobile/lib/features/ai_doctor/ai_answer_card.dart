import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/utils/bangla.dart';
import '../../core/utils/nav.dart';
import '../../core/widgets/bullet_text.dart';
import '../../core/widgets/common.dart';
import '../../core/widgets/speak_button.dart';
import '../../l10n/app_localizations.dart';
import '../../models/ai.dart';
import '../../state/content_controller.dart';
import '../../state/settings_controller.dart';
import '../crops/problem_detail_screen.dart';

/// One AI doctor answer: likely problem, what to do, safety, a link to our
/// expert-checked guide when it matched, and "send to an expert".
class AiAnswerCard extends StatelessWidget {
  const AiAnswerCard({
    super.key,
    required this.message,
    required this.isLatest,
    this.onEscalate,
    this.onRate,
    this.rating,
    this.escalated = false,
    this.escalating = false,
  });

  final AiMessage message;

  /// Feedback and "send to an expert" are shown on the newest answer only.
  final bool isLatest;
  final VoidCallback? onEscalate;
  final void Function(bool helpful)? onRate;
  final bool? rating;
  final bool escalated;
  final bool escalating;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;
    final kb = context.watch<ContentController>().kb;
    final result = message.result;
    final theme = Theme.of(context);

    if (result == null) {
      return Card(
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
            InfoBanner(level: 'warning', icon: '🤔', text: l10n.aiDeclined),
            if (isLatest) ...[const SizedBox(height: 12), _escalateButton(l10n, prominent: true)],
          ]),
        ),
      );
    }

    final top = result.likelyProblems.isEmpty ? null : result.likelyProblems.first;
    final others = result.likelyProblems.skip(1).toList();
    final guides = [
      for (final p in result.likelyProblems)
        if (kb?.problemById(p.problemId) case final match?) (p, match),
    ];

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('🩺', style: TextStyle(fontSize: 26)),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Text(top?.name ?? l10n.aiAnswerTitle, style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w700)),
                    if (top != null) ...[const SizedBox(height: 4), _ConfidencePill(top.confidence)],
                  ]),
                ),
                SpeakButton(id: 'ai-${message.id}', text: result.speakText, compact: true),
              ],
            ),
            const SizedBox(height: 10),
            Text(result.answer, style: theme.textTheme.bodyLarge),
            if (others.isNotEmpty) ...[
              const SizedBox(height: 10),
              Text(l10n.aiAlsoPossible, style: theme.textTheme.labelLarge),
              const SizedBox(height: 4),
              Wrap(spacing: 6, runSpacing: 6, children: [
                for (final p in others) Chip(label: Text(p.name), visualDensity: VisualDensity.compact),
              ]),
            ],
            if (result.steps.isNotEmpty) ...[
              const SizedBox(height: 14),
              Text('✅ ${l10n.aiWhatToDo}', style: theme.textTheme.titleSmall?.copyWith(fontWeight: FontWeight.w700)),
              const SizedBox(height: 6),
              BulletText([
                for (var i = 0; i < result.steps.length; i++) '${Bangla.digits('${i + 1}', lang)}. ${result.steps[i]}',
              ].join('\n')),
            ],
            if (result.safetyNote != null) ...[
              const SizedBox(height: 8),
              InfoBanner(level: 'warning', icon: '🧤', text: result.safetyNote!),
            ],
            if (result.followUpQuestion != null) ...[
              const SizedBox(height: 10),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(color: theme.colorScheme.primaryContainer.withValues(alpha: 0.5), borderRadius: BorderRadius.circular(12)),
                child: Text('❓ ${result.followUpQuestion!}', style: theme.textTheme.bodyLarge?.copyWith(fontWeight: FontWeight.w600)),
              ),
            ],
            for (final (problem, (kbProblem, crop)) in guides) ...[
              const SizedBox(height: 8),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  onPressed: () => push(context, ProblemDetailScreen(problem: kbProblem, crop: crop)),
                  icon: const Icon(Icons.menu_book),
                  label: Text(l10n.aiFullGuide(problem.name), textAlign: TextAlign.center),
                ),
              ),
            ],
            if (isLatest) ...[
              if (result.needsExpert) ...[
                const SizedBox(height: 10),
                InfoBanner(level: 'warning', icon: '🧑‍🌾', text: l10n.aiNeedsExpert),
              ],
              const SizedBox(height: 10),
              _escalateButton(l10n, prominent: result.needsExpert),
              const Divider(height: 24),
              if (rating == null)
                Row(children: [
                  Expanded(child: Text(l10n.aiWasHelpful)),
                  IconButton(onPressed: () => onRate?.call(true), icon: const Icon(Icons.thumb_up_alt_outlined), tooltip: '👍'),
                  IconButton(onPressed: () => onRate?.call(false), icon: const Icon(Icons.thumb_down_alt_outlined), tooltip: '👎'),
                ])
              else
                Text(l10n.aiThanks, style: theme.textTheme.bodyMedium?.copyWith(color: theme.colorScheme.primary)),
            ],
          ],
        ),
      ),
    );
  }

  Widget _escalateButton(AppLocalizations l10n, {required bool prominent}) {
    if (escalated) return Text(l10n.aiSentToExpertShort, style: const TextStyle(fontWeight: FontWeight.w600));
    final child = escalating
        ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2))
        : Text(l10n.aiSendToExpert);
    final onPressed = escalating ? null : onEscalate;
    return SizedBox(
      width: double.infinity,
      child: prominent
          ? FilledButton.icon(onPressed: onPressed, icon: const Icon(Icons.support_agent), label: child)
          : OutlinedButton.icon(onPressed: onPressed, icon: const Icon(Icons.support_agent), label: child),
    );
  }
}

class _ConfidencePill extends StatelessWidget {
  const _ConfidencePill(this.confidence);
  final String confidence;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final (label, color) = switch (confidence) {
      'high' => (l10n.aiConfHigh, Colors.green.shade700),
      'medium' => (l10n.aiConfMedium, Colors.orange.shade800),
      _ => (l10n.aiConfLow, Colors.grey.shade700),
    };
    return Pill(label, color: color);
  }
}
