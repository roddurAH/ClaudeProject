import 'localized.dart';

/// One likely problem named by the AI doctor. [problemId] points into the
/// offline knowledge base when the AI matched one of our expert-checked guides.
class AiProblem {
  AiProblem({required this.name, required this.confidence, this.problemId});

  final int? problemId;
  final String name;
  final String confidence; // high, medium, low

  factory AiProblem.fromJson(Map<String, dynamic> j) => AiProblem(
        problemId: toIntOrNull(j['problem_id']),
        name: j['name']?.toString() ?? '',
        confidence: j['confidence']?.toString() ?? 'low',
      );
}

/// The structured answer (see backend app/Ai/DiagnosisSchema.php).
class AiResult {
  AiResult({
    required this.isPlantPhoto,
    required this.answer,
    required this.likelyProblems,
    required this.steps,
    required this.needsExpert,
    this.safetyNote,
    this.followUpQuestion,
  });

  final bool isPlantPhoto;
  final String answer;
  final List<AiProblem> likelyProblems;
  final List<String> steps;
  final String? safetyNote;
  final bool needsExpert;
  final String? followUpQuestion;

  /// What the read-aloud button says.
  String get speakText => [answer, ...steps, ?safetyNote, ?followUpQuestion].join('\n');

  factory AiResult.fromJson(Map<String, dynamic> j) => AiResult(
        isPlantPhoto: j['is_plant_photo'] != false,
        answer: j['answer']?.toString() ?? '',
        likelyProblems: (j['likely_problems'] as List? ?? []).map((e) => AiProblem.fromJson(e as Map<String, dynamic>)).toList(),
        steps: (j['steps'] as List? ?? []).map((e) => e.toString()).toList(),
        safetyNote: j['safety_note']?.toString(),
        needsExpert: j['needs_expert'] == true,
        followUpQuestion: j['follow_up_question']?.toString(),
      );
}

class AiMessage {
  AiMessage({required this.id, required this.role, this.text, this.imageUrl, this.result});

  final int id;
  final String role; // user, assistant
  final String? text;
  final String? imageUrl;

  /// Null for the user's turns and when the AI declined to answer.
  final AiResult? result;

  factory AiMessage.fromJson(Map<String, dynamic> j) => AiMessage(
        id: toIntOrNull(j['id']) ?? 0,
        role: j['role']?.toString() ?? 'assistant',
        text: j['text']?.toString(),
        imageUrl: j['image_url']?.toString(),
        result: j['result'] is Map ? AiResult.fromJson((j['result'] as Map).cast<String, dynamic>()) : null,
      );
}

class AiAskResponse {
  AiAskResponse({required this.conversationId, required this.message, this.remainingToday});

  final int conversationId;
  final AiMessage message;
  final int? remainingToday;

  factory AiAskResponse.fromJson(Map<String, dynamic> j) => AiAskResponse(
        conversationId: toIntOrNull(j['conversation_id']) ?? 0,
        message: AiMessage.fromJson(j['message'] as Map<String, dynamic>),
        remainingToday: toIntOrNull(j['remaining_today']),
      );
}
