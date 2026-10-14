import 'localized.dart';

class AppUser {
  AppUser({required this.id, required this.name, required this.phone, required this.role, this.userType, this.districtId});

  final int id;
  final String name, phone, role;
  final String? userType;
  final int? districtId;

  factory AppUser.fromJson(Map<String, dynamic> j) => AppUser(
        id: toIntOrNull(j['id']) ?? 0,
        name: j['name']?.toString() ?? '',
        phone: j['phone']?.toString() ?? '',
        role: j['role']?.toString() ?? 'farmer',
        userType: j['user_type']?.toString(),
        districtId: toIntOrNull(j['district_id']),
      );

  Map<String, dynamic> toJson() =>
      {'id': id, 'name': name, 'phone': phone, 'role': role, 'user_type': userType, 'district_id': districtId};
}

class Answer {
  Answer({required this.id, required this.body, this.expertName, this.createdAt});

  final int id;
  final String body;
  final String? expertName;
  final DateTime? createdAt;

  factory Answer.fromJson(Map<String, dynamic> j) => Answer(
        id: toIntOrNull(j['id']) ?? 0,
        body: j['body']?.toString() ?? '',
        expertName: j['expert_name']?.toString(),
        createdAt: DateTime.tryParse(j['created_at']?.toString() ?? ''),
      );
}

class Question {
  Question({
    required this.id,
    required this.body,
    required this.status,
    this.imageUrl,
    this.cropNameBn,
    this.cropNameEn,
    this.cropIcon,
    this.districtBn,
    this.districtEn,
    this.answers = const [],
    this.createdAt,
  });

  final int id;
  final String body, status;
  final String? imageUrl, cropNameBn, cropNameEn, cropIcon, districtBn, districtEn;
  final List<Answer> answers;
  final DateTime? createdAt;

  String cropName(String lang) => pick(cropNameBn, cropNameEn, lang);
  String district(String lang) => pick(districtBn, districtEn, lang);
  bool get isAnswered => status == 'answered';

  factory Question.fromJson(Map<String, dynamic> j) {
    final crop = j['crop'] as Map?;
    final district = j['district'] as Map?;
    return Question(
      id: toIntOrNull(j['id']) ?? 0,
      body: j['body']?.toString() ?? '',
      status: j['status']?.toString() ?? 'pending',
      imageUrl: j['image_url']?.toString(),
      cropNameBn: crop?['name_bn']?.toString(),
      cropNameEn: crop?['name_en']?.toString(),
      cropIcon: crop?['icon']?.toString(),
      districtBn: district?['name_bn']?.toString(),
      districtEn: district?['name_en']?.toString(),
      answers: (j['answers'] as List? ?? []).map((e) => Answer.fromJson(e as Map<String, dynamic>)).toList(),
      createdAt: DateTime.tryParse(j['created_at']?.toString() ?? ''),
    );
  }
}

/// A question written while offline, waiting in the outbox.
class PendingQuestion {
  PendingQuestion({required this.id, required this.body, this.cropId, this.imageBase64, required this.createdAt});

  final String id;
  final String body;
  final int? cropId;
  final String? imageBase64;
  final DateTime createdAt;

  Map<String, dynamic> toJson() =>
      {'id': id, 'body': body, 'crop_id': cropId, 'image': imageBase64, 'created_at': createdAt.toIso8601String()};

  factory PendingQuestion.fromJson(Map<String, dynamic> j) => PendingQuestion(
        id: j['id'].toString(),
        body: j['body']?.toString() ?? '',
        cropId: toIntOrNull(j['crop_id']),
        imageBase64: j['image']?.toString(),
        createdAt: DateTime.tryParse(j['created_at']?.toString() ?? '') ?? DateTime.now(),
      );
}
