/// Picks the Bangla or English version of a text, falling back to the other
/// language when one is missing.
String pick(String? bn, String? en, String lang) {
  final primary = lang == 'bn' ? bn : en;
  final fallback = lang == 'bn' ? en : bn;
  if (primary != null && primary.trim().isNotEmpty) return primary;
  return fallback ?? '';
}

double toDouble(dynamic v) => v is num ? v.toDouble() : double.tryParse('$v') ?? 0;
double? toDoubleOrNull(dynamic v) => v == null ? null : (v is num ? v.toDouble() : double.tryParse('$v'));
int? toIntOrNull(dynamic v) => v == null ? null : (v is num ? v.toInt() : int.tryParse('$v'));
List<int> toIntList(dynamic v) => v is List ? v.map((e) => toIntOrNull(e)).whereType<int>().toList() : const [];
