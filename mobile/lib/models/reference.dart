import 'localized.dart';

class District {
  District({required this.id, required this.nameBn, required this.nameEn, this.divisionBn, this.divisionEn});

  final int id;
  final String nameBn;
  final String nameEn;
  final String? divisionBn;
  final String? divisionEn;

  String name(String lang) => pick(nameBn, nameEn, lang);
  String division(String lang) => pick(divisionBn, divisionEn, lang);

  factory District.fromJson(Map<String, dynamic> j) => District(
        id: toIntOrNull(j['id']) ?? 0,
        nameBn: j['name_bn']?.toString() ?? '',
        nameEn: j['name_en']?.toString() ?? '',
        divisionBn: j['division_bn']?.toString(),
        divisionEn: j['division_en']?.toString(),
      );
}

/// Server-side settings from GET /meta.
class AppMeta {
  AppMeta({required this.helpline, required this.contentVersion, required this.minAppVersion, required this.bighaDecimal, this.aiEnabled = true});

  final String helpline;
  final String contentVersion;
  final String minAppVersion;
  final double bighaDecimal;

  /// The server's AI doctor is switched on (and has an API key).
  final bool aiEnabled;

  factory AppMeta.fromJson(Map<String, dynamic> j) => AppMeta(
        helpline: j['helpline_number']?.toString() ?? '16123',
        contentVersion: j['content_version']?.toString() ?? '',
        minAppVersion: j['min_app_version']?.toString() ?? '1.0.0',
        bighaDecimal: toDoubleOrNull((j['units'] as Map?)?['decimal_per_bigha']) ?? 33,
        aiEnabled: (j['ai'] as Map?)?['enabled'] != false,
      );
}
