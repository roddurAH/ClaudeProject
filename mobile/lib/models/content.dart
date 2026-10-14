import 'localized.dart';

/// The offline knowledge base downloaded from GET /content (or bundled).
class KnowledgeBase {
  KnowledgeBase({required this.version, required this.crops, required this.fertilizers, required this.articles, this.generatedAt});

  final String version;
  final DateTime? generatedAt;
  final List<Crop> crops;
  final List<Fertilizer> fertilizers;
  final List<Article> articles;

  factory KnowledgeBase.fromJson(Map<String, dynamic> json) => KnowledgeBase(
        version: json['version']?.toString() ?? '',
        generatedAt: DateTime.tryParse(json['generated_at']?.toString() ?? ''),
        crops: (json['crops'] as List? ?? []).map((e) => Crop.fromJson(e as Map<String, dynamic>)).toList(),
        fertilizers: (json['fertilizers'] as List? ?? []).map((e) => Fertilizer.fromJson(e as Map<String, dynamic>)).toList(),
        articles: (json['articles'] as List? ?? []).map((e) => Article.fromJson(e as Map<String, dynamic>)).toList(),
      );

  Crop? cropBySlug(String slug) {
    for (final c in crops) {
      if (c.slug == slug) return c;
    }
    return null;
  }

  Crop? cropById(int? id) {
    for (final c in crops) {
      if (c.id == id) return c;
    }
    return null;
  }

  Fertilizer? fertilizerById(int id) {
    for (final f in fertilizers) {
      if (f.id == id) return f;
    }
    return null;
  }
}

class Crop {
  Crop({
    required this.id,
    required this.slug,
    required this.nameBn,
    required this.nameEn,
    required this.category,
    this.icon,
    this.imageUrl,
    this.season,
    this.sowingMonths = const [],
    this.harvestMonths = const [],
    this.durationDays,
    this.isGardenFriendly = false,
    this.summaryBn,
    this.summaryEn,
    this.cultivationBn,
    this.cultivationEn,
    this.stages = const [],
    this.problems = const [],
    this.doses = const [],
  });

  final int id;
  final String slug;
  final String nameBn;
  final String nameEn;
  final String category;
  final String? icon;
  final String? imageUrl;
  final String? season;
  final List<int> sowingMonths;
  final List<int> harvestMonths;
  final int? durationDays;
  final bool isGardenFriendly;
  final String? summaryBn;
  final String? summaryEn;
  final String? cultivationBn;
  final String? cultivationEn;
  final List<CropStage> stages;
  final List<Problem> problems;
  final List<FertilizerDose> doses;

  String name(String lang) => pick(nameBn, nameEn, lang);
  String summary(String lang) => pick(summaryBn, summaryEn, lang);
  String cultivation(String lang) => pick(cultivationBn, cultivationEn, lang);
  String get emoji => (icon == null || icon!.isEmpty) ? '🌱' : icon!;

  factory Crop.fromJson(Map<String, dynamic> j) => Crop(
        id: toIntOrNull(j['id']) ?? 0,
        slug: j['slug']?.toString() ?? '',
        nameBn: j['name_bn']?.toString() ?? '',
        nameEn: j['name_en']?.toString() ?? '',
        category: j['category']?.toString() ?? '',
        icon: j['icon']?.toString(),
        imageUrl: j['image_url']?.toString(),
        season: j['season']?.toString(),
        sowingMonths: toIntList(j['sowing_months']),
        harvestMonths: toIntList(j['harvest_months']),
        durationDays: toIntOrNull(j['duration_days']),
        isGardenFriendly: j['is_garden_friendly'] == true,
        summaryBn: j['summary_bn']?.toString(),
        summaryEn: j['summary_en']?.toString(),
        cultivationBn: j['cultivation_bn']?.toString(),
        cultivationEn: j['cultivation_en']?.toString(),
        stages: (j['stages'] as List? ?? []).map((e) => CropStage.fromJson(e as Map<String, dynamic>)).toList(),
        problems: (j['problems'] as List? ?? []).map((e) => Problem.fromJson(e as Map<String, dynamic>)).toList(),
        doses: (j['fertilizer_doses'] as List? ?? []).map((e) => FertilizerDose.fromJson(e as Map<String, dynamic>)).toList(),
      );
}

class CropStage {
  CropStage({required this.id, required this.dayOffset, required this.stageType, required this.titleBn, required this.titleEn, this.detailsBn, this.detailsEn});

  final int id;
  final int dayOffset;
  final String stageType;
  final String titleBn;
  final String titleEn;
  final String? detailsBn;
  final String? detailsEn;

  String title(String lang) => pick(titleBn, titleEn, lang);
  String details(String lang) => pick(detailsBn, detailsEn, lang);

  String get emoji => switch (stageType) {
        'land_prep' => '🚜',
        'sowing' => '🌱',
        'fertilizer' => '🧪',
        'irrigation' => '💧',
        'weeding' => '🌿',
        'pest_watch' => '🔍',
        'harvest' => '🧺',
        _ => '📌',
      };

  factory CropStage.fromJson(Map<String, dynamic> j) => CropStage(
        id: toIntOrNull(j['id']) ?? 0,
        dayOffset: toIntOrNull(j['day_offset']) ?? 0,
        stageType: j['stage_type']?.toString() ?? 'other',
        titleBn: j['title_bn']?.toString() ?? '',
        titleEn: j['title_en']?.toString() ?? '',
        detailsBn: j['details_bn']?.toString(),
        detailsEn: j['details_en']?.toString(),
      );
}

class Problem {
  Problem({
    required this.id,
    required this.cropId,
    required this.type,
    required this.affectedPart,
    required this.severity,
    required this.nameBn,
    required this.nameEn,
    this.imageUrl,
    this.symptomsBn,
    this.symptomsEn,
    this.preventionBn,
    this.preventionEn,
    this.organicBn,
    this.organicEn,
    this.chemicalBn,
    this.chemicalEn,
  });

  final int id;
  final int cropId;
  final String type;
  final String affectedPart;
  final String severity;
  final String nameBn;
  final String nameEn;
  final String? imageUrl;
  final String? symptomsBn, symptomsEn, preventionBn, preventionEn, organicBn, organicEn, chemicalBn, chemicalEn;

  String name(String lang) => pick(nameBn, nameEn, lang);
  String symptoms(String lang) => pick(symptomsBn, symptomsEn, lang);
  String prevention(String lang) => pick(preventionBn, preventionEn, lang);
  String organic(String lang) => pick(organicBn, organicEn, lang);
  String chemical(String lang) => pick(chemicalBn, chemicalEn, lang);

  String get emoji => switch (type) {
        'pest' => '🐛',
        'disease' => '🦠',
        'nutrient' => '🧪',
        'weed' => '🌿',
        _ => '⚠️',
      };

  factory Problem.fromJson(Map<String, dynamic> j) => Problem(
        id: toIntOrNull(j['id']) ?? 0,
        cropId: toIntOrNull(j['crop_id']) ?? 0,
        type: j['type']?.toString() ?? '',
        affectedPart: j['affected_part']?.toString() ?? 'whole',
        severity: j['severity']?.toString() ?? 'medium',
        nameBn: j['name_bn']?.toString() ?? '',
        nameEn: j['name_en']?.toString() ?? '',
        imageUrl: j['image_url']?.toString(),
        symptomsBn: j['symptoms_bn']?.toString(),
        symptomsEn: j['symptoms_en']?.toString(),
        preventionBn: j['prevention_bn']?.toString(),
        preventionEn: j['prevention_en']?.toString(),
        organicBn: j['organic_control_bn']?.toString(),
        organicEn: j['organic_control_en']?.toString(),
        chemicalBn: j['chemical_control_bn']?.toString(),
        chemicalEn: j['chemical_control_en']?.toString(),
      );
}

class Fertilizer {
  Fertilizer({required this.id, required this.key, required this.nameBn, required this.nameEn, this.officialPricePerKg, this.notesBn, this.notesEn});

  final int id;
  final String key;
  final String nameBn;
  final String nameEn;
  final double? officialPricePerKg;
  final String? notesBn;
  final String? notesEn;

  String name(String lang) => pick(nameBn, nameEn, lang);
  String notes(String lang) => pick(notesBn, notesEn, lang);

  factory Fertilizer.fromJson(Map<String, dynamic> j) => Fertilizer(
        id: toIntOrNull(j['id']) ?? 0,
        key: j['key']?.toString() ?? '',
        nameBn: j['name_bn']?.toString() ?? '',
        nameEn: j['name_en']?.toString() ?? '',
        officialPricePerKg: toDoubleOrNull(j['official_price_per_kg']),
        notesBn: j['notes_bn']?.toString(),
        notesEn: j['notes_en']?.toString(),
      );
}

class FertilizerDose {
  FertilizerDose({required this.fertilizerId, required this.kgPerHectare, this.applicationBn, this.applicationEn});

  final int fertilizerId;
  final double kgPerHectare;
  final String? applicationBn;
  final String? applicationEn;

  String application(String lang) => pick(applicationBn, applicationEn, lang);

  factory FertilizerDose.fromJson(Map<String, dynamic> j) => FertilizerDose(
        fertilizerId: toIntOrNull(j['fertilizer_id']) ?? 0,
        kgPerHectare: toDouble(j['kg_per_hectare']),
        applicationBn: j['application_bn']?.toString(),
        applicationEn: j['application_en']?.toString(),
      );
}

class Article {
  Article({
    required this.id,
    required this.slug,
    required this.category,
    required this.audience,
    required this.titleBn,
    required this.titleEn,
    this.icon,
    this.imageUrl,
    this.summaryBn,
    this.summaryEn,
    this.bodyBn,
    this.bodyEn,
  });

  final int id;
  final String slug;
  final String category;
  final String audience;
  final String? icon;
  final String? imageUrl;
  final String titleBn, titleEn;
  final String? summaryBn, summaryEn, bodyBn, bodyEn;

  String title(String lang) => pick(titleBn, titleEn, lang);
  String summary(String lang) => pick(summaryBn, summaryEn, lang);
  String body(String lang) => pick(bodyBn, bodyEn, lang);

  factory Article.fromJson(Map<String, dynamic> j) => Article(
        id: toIntOrNull(j['id']) ?? 0,
        slug: j['slug']?.toString() ?? '',
        category: j['category']?.toString() ?? '',
        audience: j['audience']?.toString() ?? 'all',
        icon: j['icon']?.toString(),
        imageUrl: j['image_url']?.toString(),
        titleBn: j['title_bn']?.toString() ?? '',
        titleEn: j['title_en']?.toString() ?? '',
        summaryBn: j['summary_bn']?.toString(),
        summaryEn: j['summary_en']?.toString(),
        bodyBn: j['body_bn']?.toString(),
        bodyEn: j['body_en']?.toString(),
      );
}
