import 'localized.dart';

class Market {
  Market({required this.id, required this.nameBn, required this.nameEn, this.districtBn, this.districtEn});

  final int id;
  final String nameBn, nameEn;
  final String? districtBn, districtEn;

  String name(String lang) => pick(nameBn, nameEn, lang);
  String district(String lang) => pick(districtBn, districtEn, lang);

  factory Market.fromJson(Map<String, dynamic> j) => Market(
        id: toIntOrNull(j['id']) ?? 0,
        nameBn: j['name_bn']?.toString() ?? '',
        nameEn: j['name_en']?.toString() ?? '',
        districtBn: j['district_bn']?.toString(),
        districtEn: j['district_en']?.toString(),
      );
}

class Commodity {
  Commodity({required this.id, required this.nameBn, required this.nameEn, required this.unit, this.icon, this.cropId});

  final int id;
  final String nameBn, nameEn, unit;
  final String? icon;
  final int? cropId;

  String name(String lang) => pick(nameBn, nameEn, lang);

  factory Commodity.fromJson(Map<String, dynamic> j) => Commodity(
        id: toIntOrNull(j['id']) ?? 0,
        nameBn: j['name_bn']?.toString() ?? '',
        nameEn: j['name_en']?.toString() ?? '',
        unit: j['unit']?.toString() ?? 'kg',
        icon: j['icon']?.toString(),
        cropId: toIntOrNull(j['crop_id']),
      );
}

class PriceItem {
  PriceItem({required this.commodity, required this.min, required this.max, required this.avg, required this.trend, this.changePct, this.source, this.date});

  final Commodity commodity;
  final double min, max, avg;
  final double? changePct;
  final String trend; // up, down, stable
  final String? source;
  final String? date;

  bool get isDemo => (source ?? '').toLowerCase().contains('demo');

  factory PriceItem.fromJson(Map<String, dynamic> j) => PriceItem(
        commodity: Commodity.fromJson(j['commodity'] as Map<String, dynamic>),
        min: toDouble(j['min']),
        max: toDouble(j['max']),
        avg: toDouble(j['avg']),
        changePct: toDoubleOrNull(j['change_pct']),
        trend: j['trend']?.toString() ?? 'stable',
        source: j['source']?.toString(),
        date: j['date']?.toString(),
      );
}

class PriceBoard {
  PriceBoard({required this.market, required this.date, required this.type, required this.items});

  final Market? market;
  final DateTime? date;
  final String type;
  final List<PriceItem> items;

  factory PriceBoard.fromJson(Map<String, dynamic> j) => PriceBoard(
        market: j['market'] is Map ? Market.fromJson(j['market'] as Map<String, dynamic>) : null,
        date: DateTime.tryParse(j['date']?.toString() ?? ''),
        type: j['type']?.toString() ?? 'wholesale',
        items: (j['items'] as List? ?? []).map((e) => PriceItem.fromJson(e as Map<String, dynamic>)).toList(),
      );
}

class PricePoint {
  PricePoint({required this.date, required this.min, required this.max, required this.avg});

  final DateTime date;
  final double min, max, avg;

  factory PricePoint.fromJson(Map<String, dynamic> j) => PricePoint(
        date: DateTime.tryParse(j['date']?.toString() ?? '') ?? DateTime.now(),
        min: toDouble(j['min']),
        max: toDouble(j['max']),
        avg: toDouble(j['avg']),
      );
}
