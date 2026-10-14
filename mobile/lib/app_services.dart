import 'core/network/api_client.dart';
import 'core/services/tts_service.dart';
import 'core/services/voice_input_service.dart';
import 'core/storage/local_store.dart';
import 'data/ai_repository.dart';
import 'data/auth_repository.dart';
import 'data/content_repository.dart';
import 'data/my_crops_repository.dart';
import 'data/price_repository.dart';
import 'data/question_repository.dart';
import 'data/reference_repository.dart';
import 'data/weather_repository.dart';
import 'state/auth_controller.dart';
import 'state/content_controller.dart';
import 'state/my_crops_controller.dart';
import 'state/reference_controller.dart';
import 'state/settings_controller.dart';

/// Creates and wires every service once. Tests can build their own with
/// [LocalStore.memory] and a fake Dio.
class AppServices {
  AppServices._({
    required this.store,
    required this.api,
    required this.settings,
    required this.reference,
    required this.content,
    required this.auth,
    required this.myCrops,
    required this.prices,
    required this.weather,
    required this.questions,
    required this.tts,
    required this.ai,
    required this.voice,
  });

  final LocalStore store;
  final ApiClient api;
  final SettingsController settings;
  final ReferenceController reference;
  final ContentController content;
  final AuthController auth;
  final MyCropsController myCrops;
  final PriceRepository prices;
  final WeatherRepository weather;
  final QuestionRepository questions;
  final TtsService tts;
  final AiRepository ai;
  final VoiceInput voice;

  factory AppServices.create(LocalStore store, {ApiClient? api, VoiceInput? voice}) {
    final settings = SettingsController(store);
    late final AuthRepository authRepo;
    final client = api ??
        ApiClient(
          baseUrl: () => settings.apiBaseUrl,
          token: () => authRepo.token,
          language: () => settings.language,
        );
    authRepo = AuthRepository(client, store);
    final auth = AuthController(authRepo);
    client.onUnauthorized = auth.sessionExpired;

    return AppServices._(
      store: store,
      api: client,
      settings: settings,
      reference: ReferenceController(ReferenceRepository(client, store)),
      content: ContentController(ContentRepository(client, store)),
      auth: auth,
      myCrops: MyCropsController(MyCropsRepository(store)),
      prices: PriceRepository(client, store),
      weather: WeatherRepository(client, store),
      questions: QuestionRepository(client, store),
      tts: TtsService(),
      ai: AiRepository(client),
      voice: voice ?? SpeechVoiceInput(),
    );
  }

  /// Loads local data immediately, then refreshes from the server in the background.
  Future<void> start() async {
    await Future.wait([reference.init(), content.init()]);
    if (auth.isLoggedIn) {
      questions.flushOutbox().ignore();
    }
  }
}
