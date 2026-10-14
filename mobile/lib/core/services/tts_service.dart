import 'package:flutter/foundation.dart';
import 'package:flutter_tts/flutter_tts.dart';

/// Reads advice aloud – important for users who find reading hard.
/// Uses the phone's text-to-speech engine (Google TTS has Bangla voices).
class TtsService extends ChangeNotifier {
  FlutterTts? _tts;
  String? speakingId;

  FlutterTts get _engine {
    if (_tts == null) {
      _tts = FlutterTts();
      _tts!.setCompletionHandler(_done);
      _tts!.setCancelHandler(_done);
      _tts!.setErrorHandler((_) => _done());
    }
    return _tts!;
  }

  void _done() {
    speakingId = null;
    notifyListeners();
  }

  /// Returns false when no voice for the language is installed.
  Future<bool> speak(String id, String text, String lang) async {
    try {
      await stop();
      final locale = lang == 'bn' ? 'bn-BD' : 'en-US';
      final available = await _engine.isLanguageAvailable(locale);
      if (available == false && lang == 'bn') {
        final india = await _engine.isLanguageAvailable('bn-IN');
        if (india == false) return false;
        await _engine.setLanguage('bn-IN');
      } else {
        await _engine.setLanguage(locale);
      }
      await _engine.setSpeechRate(kIsWeb ? 0.9 : 0.45);
      speakingId = id;
      notifyListeners();
      await _engine.speak(text);
      return true;
    } catch (_) {
      _done();
      return false;
    }
  }

  Future<void> stop() async {
    if (speakingId == null) return;
    try {
      await _engine.stop();
    } catch (_) {}
    _done();
  }
}
