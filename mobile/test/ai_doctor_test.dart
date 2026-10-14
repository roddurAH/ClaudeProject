import 'dart:convert';

import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:krishi_sathi/app.dart';
import 'package:krishi_sathi/app_services.dart';
import 'package:krishi_sathi/core/network/api_exception.dart';
import 'package:krishi_sathi/core/services/voice_input_service.dart';
import 'package:krishi_sathi/core/storage/local_store.dart';
import 'package:krishi_sathi/data/ai_repository.dart';
import 'package:krishi_sathi/features/ai_doctor/ai_doctor_screen.dart';

import 'helpers/fake_api.dart';

/// Speech input that is not available (e.g. no recogniser installed).
class NoVoice extends VoiceInput {
  @override
  bool get supported => false;
  @override
  bool get listening => false;
  @override
  Future<bool> start({required String language, required void Function(String text, bool done) onText}) async => false;
  @override
  Future<void> stop() async {}
}

Map<String, dynamic> aiAnswer({int? problemId, String name = 'পাতা কোঁকড়ানো ভাইরাস'}) => {
      'conversation_id': 7,
      'remaining_today': 9,
      'message': {
        'id': 42,
        'role': 'assistant',
        'text': 'এটি সম্ভবত পাতা কোঁকড়ানো ভাইরাস।',
        'result': {
          'is_plant_photo': true,
          'answer': 'এটি সম্ভবত পাতা কোঁকড়ানো ভাইরাস।',
          'likely_problems': [
            {'problem_id': problemId, 'name': name, 'confidence': 'high'},
          ],
          'steps': ['আক্রান্ত গাছ তুলে ফেলুন।', 'হলুদ আঠালো ফাঁদ দিন।'],
          'safety_note': null,
          'needs_expert': false,
          'follow_up_question': 'পাতার নিচে সাদা মাছি আছে?',
        },
      },
    };

Future<(AppServices, FakeAdapter)> signedInApp(WidgetTester tester, Handler handler) async {
  final store = LocalStore.memory();
  await store.settings.put('onboarded', true);
  await store.settings.put('district_id', 9);
  await store.settings.put('language', 'bn');
  await store.settings.put('auth_token', 'test-token');
  await store.settings.put('auth_user', jsonEncode({'id': 1, 'name': 'রহিম', 'phone': '01711111111', 'role': 'farmer'}));
  final (api, adapter) = fakeApiClient(handler, token: () => 'test-token');
  final services = AppServices.create(store, api: api, voice: NoVoice());
  await tester.runAsync(services.start);
  await tester.pumpWidget(KrishiSathiApp(services: services));
  await tester.pumpAndSettle();
  return (services, adapter);
}

void main() {
  setUp(() {
    TestWidgetsFlutterBinding.instance.platformDispatcher.views.first.physicalSize = const Size(1080, 2400);
  });

  test('ask sends a multipart request and waits long enough for the AI', () async {
    final (api, adapter) = fakeApiClient((o) async => json(aiAnswer(problemId: 3)));
    final response = await AiRepository(api).ask(text: 'পাতা কুঁকড়ে যাচ্ছে', cropId: 10, imageBytes: [1, 2, 3]);

    final request = adapter.requests.single;
    expect(request.path, endsWith('/ai/ask'));
    expect(request.receiveTimeout, AiRepository.answerTimeout);
    final form = request.data as FormData;
    expect(Map.fromEntries(form.fields), {'text': 'পাতা কুঁকড়ে যাচ্ছে', 'crop_id': '10'});
    expect(form.files.single.key, 'image');
    expect(response.conversationId, 7);
    expect(response.message.result!.likelyProblems.single.problemId, 3);
    expect(response.message.result!.followUpQuestion, 'পাতার নিচে সাদা মাছি আছে?');
  });

  test('the server explains when the AI is busy', () async {
    final (api, _) = fakeApiClient((o) async => json({'message': 'AI ডাক্তার এখন ব্যস্ত।'}, status: 503));
    await expectLater(
      AiRepository(api).ask(text: 'হলুদ পাতা'),
      throwsA(isA<ApiException>().having((e) => e.type, 'type', ApiErrorType.server).having((e) => e.message, 'message', 'AI ডাক্তার এখন ব্যস্ত।')),
    );
  });

  testWidgets('ask the AI doctor from Home and open the matching guide', (tester) async {
    late int problemId;
    late String problemName;
    final (services, adapter) = await signedInApp(tester, (o) async {
      if (o.path.endsWith('/ai/ask')) return json(aiAnswer(problemId: problemId, name: problemName));
      return offline(o);
    });
    final tomato = services.content.kb!.cropBySlug('tomato')!;
    problemId = tomato.problems.first.id;
    problemName = tomato.problems.first.nameBn;

    await tester.tap(find.text('কৃষি ডাক্তার (AI)'));
    await tester.pumpAndSettle();
    expect(find.text('গাছের সমস্যা দেখান'), findsOneWidget);
    expect(find.text('বলুন'), findsNothing); // no speech recogniser → no mic button

    await tester.enterText(find.byType(TextField), 'টমেটো পাতা কুঁকড়ে যাচ্ছে');
    await tester.pump(); // the send button enables on the next frame
    await tester.tap(find.text('পাঠান'));
    await tester.pumpAndSettle();

    expect(adapter.requests.where((r) => r.path.endsWith('/ai/ask')), hasLength(1));
    expect(find.text(problemName), findsWidgets);
    expect(find.text('প্রায় নিশ্চিত'), findsOneWidget);
    expect(find.textContaining('আক্রান্ত গাছ তুলে ফেলুন'), findsOneWidget);
    expect(find.text('❓ পাতার নিচে সাদা মাছি আছে?'), findsOneWidget);
    expect(find.text('আজ আরও ৯টি প্রশ্ন করতে পারবেন।'), findsOneWidget);

    await tester.tap(find.text('পুরো নির্দেশিকা: $problemName'));
    await tester.pumpAndSettle();
    expect(find.textContaining('প্রতিরোধ ও দমন'), findsOneWidget); // the expert-checked problem page
  });

  testWidgets('offline: the question can be saved for an expert instead', (tester) async {
    final (services, _) = await signedInApp(tester, offline);
    await tester.pumpWidget(KrishiSathiApp(services: services));
    await tester.tap(find.text('কৃষি ডাক্তার (AI)'));
    await tester.pumpAndSettle();

    await tester.enterText(find.byType(TextField), 'মরিচের পাতা কুঁকড়ে যাচ্ছে');
    await tester.pump();
    await tester.tap(find.text('পাঠান'));
    await tester.pumpAndSettle();
    expect(find.textContaining('AI ডাক্তারের জন্য ইন্টারনেট লাগবে'), findsOneWidget);

    await tester.tap(find.text('বিশেষজ্ঞের জন্য রেখে দিন'));
    await tester.pumpAndSettle();
    expect(services.questions.pending().single.body, 'মরিচের পাতা কুঁকড়ে যাচ্ছে');
    expect(find.byType(AiDoctorScreen), findsOneWidget);
    await tester.pump(const Duration(seconds: 6)); // let the snack bar go
  });
}
