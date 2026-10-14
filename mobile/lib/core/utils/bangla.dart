/// Helpers for showing numbers and dates the way Bangladeshi users read them.
class Bangla {
  Bangla._();

  static const _bnDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

  /// "123.5" → "১২৩.৫" when [lang] is 'bn'; unchanged otherwise.
  static String digits(String input, String lang) {
    if (lang != 'bn') return input;
    final buffer = StringBuffer();
    for (final rune in input.runes) {
      final ch = String.fromCharCode(rune);
      final d = int.tryParse(ch);
      buffer.write(d != null && ch.codeUnitAt(0) < 128 ? _bnDigits[d] : ch);
    }
    return buffer.toString();
  }

  /// Bangla digits typed on a Bangla keyboard → ASCII digits.
  static String toAscii(String input) {
    var out = input;
    for (var i = 0; i < 10; i++) {
      out = out.replaceAll(_bnDigits[i], '$i');
    }
    return out;
  }

  /// Formats a number with up to [decimals] decimals (trailing zeros removed)
  /// and thousands separators, in the user's digits.
  static String number(num value, String lang, {int decimals = 0}) {
    var text = value.toStringAsFixed(decimals);
    if (text.contains('.')) {
      text = text.replaceFirst(RegExp(r'0+$'), '').replaceFirst(RegExp(r'\.$'), '');
    }
    final negative = text.startsWith('-');
    if (negative) text = text.substring(1);
    final parts = text.split('.');
    final intPart = parts[0].replaceAllMapped(RegExp(r'(\d)(?=(\d{3})+$)'), (m) => '${m[1]},');
    final result = (negative ? '-' : '') + intPart + (parts.length > 1 ? '.${parts[1]}' : '');
    return digits(result, lang);
  }

  static const monthsBn = [
    'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন',
    'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর',
  ];
  static const monthsEn = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December',
  ];
  static const _weekdaysBn = ['সোমবার', 'মঙ্গলবার', 'বুধবার', 'বৃহস্পতিবার', 'শুক্রবার', 'শনিবার', 'রবিবার'];
  static const _weekdaysEn = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

  /// Bengali calendar months, starting with Boishakh.
  static const bengaliMonths = [
    'বৈশাখ', 'জ্যৈষ্ঠ', 'আষাঢ়', 'শ্রাবণ', 'ভাদ্র', 'আশ্বিন',
    'কার্তিক', 'অগ্রহায়ণ', 'পৌষ', 'মাঘ', 'ফাল্গুন', 'চৈত্র',
  ];
  static const bengaliMonthsEn = [
    'Boishakh', 'Jyoishtho', 'Asharh', 'Srabon', 'Bhadro', 'Ashwin',
    'Kartik', 'Agrahayan', 'Poush', 'Magh', 'Falgun', 'Chaitra',
  ];

  /// month: 1–12
  static String monthName(int month, String lang) => (lang == 'bn' ? monthsBn : monthsEn)[month - 1];

  static String weekdayName(DateTime date, String lang) =>
      (lang == 'bn' ? _weekdaysBn : _weekdaysEn)[date.weekday - 1];

  /// "14 October 2026" / "১৪ অক্টোবর ২০২৬"
  static String date(DateTime d, String lang, {bool withYear = true}) =>
      digits('${d.day} ${monthName(d.month, lang)}${withYear ? ' ${d.year}' : ''}', lang);

  /// Short date: "14 Oct" / "১৪ অক্টো".
  static String shortDate(DateTime d, String lang) {
    final month = monthName(d.month, lang);
    return digits('${d.day} ${lang == 'bn' ? month : month.substring(0, 3)}', lang);
  }

  /// Converts a Gregorian date to the revised Bengali calendar used in
  /// Bangladesh (2019 revision): 1 Boishakh = 14 April; Boishakh–Ashwin have
  /// 31 days, Falgun 29 (30 in leap years), all others 30.
  static BengaliDate toBengali(DateTime date) {
    final d = DateTime(date.year, date.month, date.day);
    final startYear = (d.month > 4 || (d.month == 4 && d.day >= 14)) ? d.year : d.year - 1;
    final newYear = DateTime(startYear, 4, 14);
    var dayIndex = d.difference(newYear).inDays;

    final falgunHasLeapDay = _isLeap(startYear + 1);
    final lengths = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, falgunHasLeapDay ? 30 : 29, 30];
    var month = 0;
    while (month < 11 && dayIndex >= lengths[month]) {
      dayIndex -= lengths[month];
      month++;
    }
    return BengaliDate(day: dayIndex + 1, month: month + 1, year: startYear - 593);
  }

  static bool _isLeap(int y) => (y % 4 == 0 && y % 100 != 0) || y % 400 == 0;
}

class BengaliDate {
  const BengaliDate({required this.day, required this.month, required this.year});
  final int day;
  final int month; // 1 = Boishakh
  final int year;

  String format(String lang) => lang == 'bn'
      ? Bangla.digits('$day ${Bangla.bengaliMonths[month - 1]} $year', 'bn')
      : '$day ${Bangla.bengaliMonthsEn[month - 1]} $year';
}
