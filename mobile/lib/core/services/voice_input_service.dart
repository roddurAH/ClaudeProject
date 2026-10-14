import 'package:flutter/foundation.dart';
import 'package:speech_to_text/speech_to_text.dart';

/// Speech → text with the phone's own recogniser (Google's on most Android
/// phones: free, and offline when the Bangla voice pack is installed).
/// Nothing is recorded or uploaded by the app – only the text is used.
abstract class VoiceInput extends ChangeNotifier {
  /// The platform may support speech input (the mic button is shown).
  bool get supported;

  bool get listening;

  /// Starts listening. Returns false when speech input is not available
  /// (no recogniser, or the microphone permission was refused).
  Future<bool> start({required String language, required void Function(String text, bool done) onText});

  Future<void> stop();
}

class SpeechVoiceInput extends VoiceInput {
  final SpeechToText _speech = SpeechToText();
  bool? _ready;
  bool _listening = false;
  final Map<String, String> _localeIds = {};

  @override
  bool get supported =>
      kIsWeb ||
      const [TargetPlatform.android, TargetPlatform.iOS, TargetPlatform.windows].contains(defaultTargetPlatform);

  @override
  bool get listening => _listening;

  void _setListening(bool value) {
    if (_listening == value) return;
    _listening = value;
    notifyListeners();
  }

  /// Asks for the microphone permission the first time it is needed, not at start-up.
  Future<bool> _init() async {
    if (_ready != null) return _ready!;
    try {
      _ready = await _speech.initialize(
        onStatus: (status) {
          if (status == 'done' || status == 'notListening') _setListening(false);
        },
        onError: (_) => _setListening(false),
      );
    } catch (_) {
      _ready = false;
    }
    return _ready!;
  }

  Future<String?> _localeFor(String language) async {
    if (_localeIds.containsKey(language)) return _localeIds[language];
    String? id;
    try {
      final locales = await _speech.locales();
      final prefix = language == 'bn' ? 'bn' : 'en';
      final preferred = language == 'bn' ? 'bn_BD' : 'en_US';
      final matches = locales.map((l) => l.localeId).where((id) => id.toLowerCase().startsWith(prefix)).toList();
      id = matches.firstWhere((m) => m.replaceAll('-', '_') == preferred, orElse: () => matches.isEmpty ? preferred : matches.first);
    } catch (_) {
      id = language == 'bn' ? 'bn_BD' : 'en_US';
    }
    return _localeIds[language] = id;
  }

  @override
  Future<bool> start({required String language, required void Function(String text, bool done) onText}) async {
    if (!await _init()) return false;
    final localeId = await _localeFor(language);
    _setListening(true);
    try {
      await _speech.listen(
        onResult: (result) => onText(result.recognizedWords, result.finalResult),
        listenOptions: SpeechListenOptions(
          localeId: localeId,
          partialResults: true,
          cancelOnError: true,
          listenMode: ListenMode.dictation,
          listenFor: const Duration(seconds: 60),
          pauseFor: const Duration(seconds: 5),
        ),
      );
      return true;
    } catch (_) {
      _setListening(false);
      return false;
    }
  }

  @override
  Future<void> stop() async {
    if (!_listening) return;
    await _speech.stop();
    _setListening(false);
  }
}
