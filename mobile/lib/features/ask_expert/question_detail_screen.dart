import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/utils/bangla.dart';
import '../../core/widgets/bullet_text.dart';
import '../../core/widgets/common.dart';
import '../../core/widgets/net_image.dart';
import '../../core/widgets/speak_button.dart';
import '../../l10n/app_localizations.dart';
import '../../models/question.dart';
import '../../state/settings_controller.dart';

class QuestionDetailScreen extends StatelessWidget {
  const QuestionDetailScreen({super.key, required this.question});
  final Question question;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;
    final q = question;

    return Scaffold(
      appBar: AppBar(title: Text(l10n.yourQuestion)),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(
                  [
                    if (q.cropName(lang).isNotEmpty) '${q.cropIcon ?? ''} ${q.cropName(lang)}',
                    if (q.createdAt != null) Bangla.date(q.createdAt!.toLocal(), lang),
                  ].join(' · '),
                  style: const TextStyle(fontWeight: FontWeight.w600),
                ),
                const SizedBox(height: 8),
                Text(q.body, style: Theme.of(context).textTheme.bodyLarge),
                if (q.imageUrl != null) ...[const SizedBox(height: 12), NetImage(q.imageUrl!, height: 240, fit: BoxFit.contain)],
              ]),
            ),
          ),
          if (q.answers.isEmpty) ...[
            const SizedBox(height: 16),
            InfoBanner(icon: '⏳', text: l10n.statusPending),
          ],
          for (final a in q.answers) ...[
            SectionHeader(l10n.expertAnswer, icon: '🧑‍🌾', trailing: SpeakButton(id: 'answer-${a.id}', text: a.body, compact: true)),
            Card(
              color: const Color(0xFFF1F8E9),
              child: Padding(
                padding: const EdgeInsets.all(14),
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  BulletText(a.body),
                  if (a.expertName != null) ...[
                    const SizedBox(height: 8),
                    Text('— ${a.expertName}', style: const TextStyle(fontStyle: FontStyle.italic)),
                  ],
                ]),
              ),
            ),
          ],
        ],
      ),
    );
  }
}
