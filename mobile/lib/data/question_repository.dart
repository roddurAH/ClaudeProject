import 'dart:convert';

import '../core/network/api_client.dart';
import '../core/network/api_exception.dart';
import '../core/storage/local_store.dart';
import '../models/question.dart';
import 'cached_fetch.dart';

enum AskResult { sent, queued }

/// Ask-an-expert. Questions written without internet go to an outbox on the
/// phone and are sent automatically later (see [flushOutbox]).
class QuestionRepository {
  QuestionRepository(this._api, this._store);

  final ApiClient _api;
  final LocalStore _store;

  static List<Question> _parseList(dynamic j) =>
      ((j as Map)['data'] as List).map((e) => Question.fromJson(e as Map<String, dynamic>)).toList();

  CachedResult<List<Question>>? peekMine() => peekCache(_store, 'questions:mine', _parseList);

  Future<CachedResult<List<Question>>> mine() => cachedFetch(
        store: _store,
        key: 'questions:mine',
        fetch: () async => (await _api.get('/questions')).data,
        parse: _parseList,
      );

  Future<CachedResult<List<Question>>> community({int? cropId}) => cachedFetch(
        store: _store,
        key: 'questions:community:$cropId',
        fetch: () async => (await _api.get('/community/questions', query: {'crop_id': cropId})).data,
        parse: _parseList,
      );

  Future<Question> detail(int id) async =>
      Question.fromJson(((await _api.get('/questions/$id')).data as Map)['data'] as Map<String, dynamic>);

  /// Sends the question, or saves it to the outbox when there is no internet.
  Future<AskResult> ask({required String body, int? cropId, List<int>? imageBytes}) async {
    try {
      await _send(body: body, cropId: cropId, imageBytes: imageBytes);
      return AskResult.sent;
    } on ApiException catch (e) {
      if (!e.isNetwork) rethrow;
      final pending = PendingQuestion(
        id: DateTime.now().microsecondsSinceEpoch.toString(),
        body: body,
        cropId: cropId,
        imageBase64: imageBytes == null ? null : base64Encode(imageBytes),
        createdAt: DateTime.now(),
      );
      await _store.outbox.put(pending.id, jsonEncode(pending.toJson()));
      return AskResult.queued;
    }
  }

  List<PendingQuestion> pending() => _store.outbox.keys
      .map((k) => _store.outbox.get(k))
      .whereType<String>()
      .map((raw) => PendingQuestion.fromJson(jsonDecode(raw) as Map<String, dynamic>))
      .toList()
    ..sort((a, b) => a.createdAt.compareTo(b.createdAt));

  /// Tries to send everything in the outbox. Returns how many were sent.
  /// Stops at the first network error (still offline).
  Future<int> flushOutbox() async {
    var sent = 0;
    for (final q in pending()) {
      try {
        await _send(body: q.body, cropId: q.cropId, imageBytes: q.imageBase64 == null ? null : base64Decode(q.imageBase64!));
        await _store.outbox.delete(q.id);
        sent++;
      } on ApiException catch (e) {
        if (e.isNetwork || e.type == ApiErrorType.unauthorized || e.type == ApiErrorType.rateLimited) break;
        await _store.outbox.delete(q.id); // permanently rejected (e.g. validation) – drop it
      }
    }
    return sent;
  }

  Future<void> _send({required String body, int? cropId, List<int>? imageBytes}) async {
    await _api.postMultipart('/questions', {'body': body, 'crop_id': cropId?.toString()}, fileBytes: imageBytes);
  }
}
