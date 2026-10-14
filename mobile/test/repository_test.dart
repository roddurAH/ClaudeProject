import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:krishi_sathi/core/network/api_client.dart';
import 'package:krishi_sathi/core/network/api_exception.dart';
import 'package:krishi_sathi/core/storage/local_store.dart';
import 'package:krishi_sathi/data/auth_repository.dart';
import 'package:krishi_sathi/data/content_repository.dart';
import 'package:krishi_sathi/data/price_repository.dart';
import 'package:krishi_sathi/data/question_repository.dart';

import 'helpers/fake_api.dart';

Map<String, dynamic> board(double potato) => {
      'market': {'id': 1, 'name_bn': 'কারওয়ান বাজার', 'name_en': 'Kawran Bazar'},
      'date': '2026-10-14',
      'type': 'wholesale',
      'items': [
        {
          'commodity': {'id': 6, 'name_bn': 'আলু', 'name_en': 'Potato', 'unit': 'kg', 'icon': '🥔'},
          'min': potato, 'max': potato + 2, 'avg': potato + 1, 'trend': 'up', 'change_pct': 5.0, 'source': 'DAM',
        },
      ],
    };

void main() {
  test('price board falls back to the saved copy when offline', () async {
    final store = LocalStore.memory();
    final (api, adapter) = fakeApiClient((o) async => json(board(20)));
    final repo = PriceRepository(api, store);

    final online = await repo.board(districtId: 5);
    expect(online.fromCache, isFalse);
    expect(online.data.items.single.min, 20);

    adapter.handler = offline;
    final saved = await repo.board(districtId: 5);
    expect(saved.fromCache, isTrue);
    expect(saved.data.items.single.commodity.nameBn, 'আলু');
    expect(saved.savedAt, isNotNull);
  });

  test('offline with nothing saved throws a network error', () async {
    final (api, _) = fakeApiClient(offline);
    final repo = PriceRepository(api, LocalStore.memory());

    expect(() => repo.board(districtId: 1), throwsA(isA<ApiException>().having((e) => e.isNetwork, 'isNetwork', true)));
  });

  test('validation errors are parsed into field messages', () async {
    final (api, _) = fakeApiClient((o) async => json({
          'message': 'The given data was invalid.',
          'errors': {'phone': ['This mobile number is already registered.']},
        }, status: 422));

    try {
      await api.post('/auth/register', data: {});
      fail('should throw');
    } on ApiException catch (e) {
      expect(e.type, ApiErrorType.validation);
      expect(e.firstMessage, 'This mobile number is already registered.');
    }
  });

  group('content', () {
    final content = {
      'version': 'v2',
      'crops': [{'id': 1, 'slug': 'tomato', 'name_bn': 'টমেটো', 'name_en': 'Tomato', 'category': 'vegetable'}],
      'fertilizers': [],
      'articles': [],
    };

    test('uses bundled copy first, then saves the downloaded one', () async {
      final store = LocalStore.memory();
      final (api, adapter) = fakeApiClient((o) async => json(content));
      final repo = ContentRepository(api, store, assetLoader: (_) async => '{"version":"v1","crops":[],"fertilizers":[],"articles":[]}');

      final (bundled, savedAt) = await repo.loadLocal();
      expect(bundled!.version, 'v1');
      expect(savedAt, isNull);

      final fresh = await repo.refresh(currentVersion: 'v1');
      expect(fresh!.crops.single.slug, 'tomato');
      expect(adapter.requests.single.headers['If-None-Match'], '"v1"');

      final (local, time) = await repo.loadLocal();
      expect(local!.version, 'v2');
      expect(time, isNotNull);
    });

    test('304 Not Modified downloads nothing', () async {
      final (api, _) = fakeApiClient((o) async => json('', status: 304));
      final repo = ContentRepository(api, LocalStore.memory(), assetLoader: (_) async => '{}');

      expect(await repo.refresh(currentVersion: 'v2'), isNull);
    });
  });

  group('question outbox', () {
    test('queues while offline and sends later', () async {
      final store = LocalStore.memory();
      final (api, adapter) = fakeApiClient(offline);
      final repo = QuestionRepository(api, store);

      final result = await repo.ask(body: 'বেগুনের ডগা মরে যাচ্ছে', cropId: 3, imageBytes: [1, 2, 3]);
      expect(result, AskResult.queued);
      expect(repo.pending().single.cropId, 3);

      // Still offline: nothing sent, nothing lost.
      expect(await repo.flushOutbox(), 0);
      expect(repo.pending(), hasLength(1));

      adapter.handler = (o) async => json({'data': {'id': 1, 'body': 'x', 'status': 'pending'}}, status: 201);
      expect(await repo.flushOutbox(), 1);
      expect(repo.pending(), isEmpty);
      expect(adapter.requests.last.path, endsWith('/questions'));
    });

    test('server rejection is not queued', () async {
      final (api, _) = fakeApiClient((o) async => json({'message': 'Too many'}, status: 429));
      final repo = QuestionRepository(api, LocalStore.memory());

      expect(() => repo.ask(body: 'question text'), throwsA(isA<ApiException>()));
      expect(repo.pending(), isEmpty);
    });
  });

  test('no server address configured behaves like offline, without any request', () async {
    final adapter = FakeAdapter((o) async => json({}));
    final dio = Dio()..httpClientAdapter = adapter;
    final api = ApiClient(baseUrl: () => '', token: () => null, language: () => 'bn', dio: dio);

    expect(() => api.get('/meta'), throwsA(isA<ApiException>().having((e) => e.isNetwork, 'isNetwork', true)));
    expect(adapter.requests, isEmpty);
  });

  test('an address that is not the API base gives a clear "wrong server" error', () async {
    // Laravel answers a missing route with 404 "The route … could not be found."
    final (api, _) = fakeApiClient((o) async => json({'message': 'The route auth/login could not be found.'}, status: 404));
    await expectLater(api.post('/auth/login'), throwsA(isA<ApiException>().having((e) => e.type, 'type', ApiErrorType.wrongServer)));

    // Another web server answering with an HTML page.
    final (html, _) = fakeApiClient((o) async => ResponseBody.fromString('<html>It works!</html>', 200, headers: {
          Headers.contentTypeHeader: ['text/html'],
        }));
    await expectLater(html.get('/meta'), throwsA(isA<ApiException>().having((e) => e.type, 'type', ApiErrorType.wrongServer)));

    // A real "not found" from the API stays notFound.
    final (missing, _) = fakeApiClient((o) async => json({'message': 'Not found.'}, status: 404));
    await expectLater(missing.get('/questions/9'), throwsA(isA<ApiException>().having((e) => e.type, 'type', ApiErrorType.notFound)));
  });

  group('delete account', () {
    Future<LocalStore> signedInStore() async {
      final store = LocalStore.memory();
      await store.settings.put('auth_token', 'secret');
      await store.settings.put('auth_user', '{"id":1,"name":"করিম","phone":"01712345678","role":"farmer"}');
      await store.writeCache('questions:mine', {'data': []});
      await store.outbox.put('q1', '{}');
      return store;
    }

    test('sends the PIN, then forgets the session and this user\'s questions', () async {
      final store = await signedInStore();
      final (api, adapter) = fakeApiClient((o) async => json({'message': 'deleted'}), token: () => 'secret');
      final repo = AuthRepository(api, store);

      await repo.deleteAccount('12345');

      expect(adapter.requests.single.method, 'DELETE');
      expect(adapter.requests.single.path, endsWith('/me'));
      expect(adapter.requests.single.data, {'pin': '12345'});
      expect(repo.token, isNull);
      expect(store.readCache('questions:mine'), isNull);
      expect(store.outbox.keys, isEmpty);
    });

    test('a wrong PIN keeps the user signed in', () async {
      final store = await signedInStore();
      final (api, _) = fakeApiClient((o) async => json({'message': 'PIN is incorrect.', 'errors': {'pin': ['PIN is incorrect.']}}, status: 422));
      final repo = AuthRepository(api, store);

      await expectLater(repo.deleteAccount('99999'), throwsA(isA<ApiException>().having((e) => e.firstMessage, 'message', 'PIN is incorrect.')));
      expect(repo.token, 'secret');
      expect(store.outbox.keys, hasLength(1));
    });
  });
}
