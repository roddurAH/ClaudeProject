import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../l10n/app_localizations.dart';
import '../../state/settings_controller.dart';
import '../services/tts_service.dart';

/// "Listen" button that reads [text] aloud in the user's language.
class SpeakButton extends StatelessWidget {
  const SpeakButton({super.key, required this.id, required this.text, this.compact = false});
  final String id;
  final String text;
  final bool compact;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final tts = context.watch<TtsService>();
    final speaking = tts.speakingId == id;

    Future<void> onPressed() async {
      if (speaking) {
        await tts.stop();
        return;
      }
      final ok = await tts.speak(id, text, context.read<SettingsController>().language);
      if (!ok && context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(l10n.ttsUnavailable)));
      }
    }

    final icon = Icon(speaking ? Icons.stop_circle_outlined : Icons.volume_up_rounded);
    if (compact) {
      return IconButton(onPressed: onPressed, icon: icon, tooltip: speaking ? l10n.stopListening : l10n.listen);
    }
    return OutlinedButton.icon(onPressed: onPressed, icon: icon, label: Text(speaking ? l10n.stopListening : l10n.listen));
  }
}
