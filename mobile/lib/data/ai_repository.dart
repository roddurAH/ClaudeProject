import '../core/network/api_client.dart';
import '../models/ai.dart';
import '../models/question.dart';

/// AI crop doctor (needs internet and a signed-in user).
class AiRepository {
  AiRepository(this._api);

  final ApiClient _api;

  /// The model looks at the photo for 10–30 s, so wait longer than usual.
  static const answerTimeout = Duration(seconds: 150);

  Future<AiAskResponse> ask({int? conversationId, String? text, List<int>? imageBytes, int? cropId}) async {
    final response = await _api.postMultipart(
      '/ai/ask',
      {'conversation_id': conversationId, 'text': text, 'crop_id': cropId},
      fileBytes: imageBytes,
      receiveTimeout: answerTimeout,
    );
    return AiAskResponse.fromJson(response.data as Map<String, dynamic>);
  }

  Future<void> rate(int conversationId, {required bool helpful}) =>
      _api.post('/ai/conversations/$conversationId/rate', data: {'rating': helpful ? 'up' : 'down'});

  /// Sends the case (photo + AI answer) to the human experts.
  Future<Question> escalate(int conversationId) async {
    final response = await _api.post('/ai/conversations/$conversationId/escalate');
    return Question.fromJson((response.data as Map)['data'] as Map<String, dynamic>);
  }
}
