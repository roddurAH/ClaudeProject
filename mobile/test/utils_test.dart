import 'package:flutter_test/flutter_test.dart';
import 'package:krishi_sathi/core/config/app_config.dart';
import 'package:krishi_sathi/core/utils/bangla.dart';
import 'package:krishi_sathi/core/utils/fertilizer_calc.dart';
import 'package:krishi_sathi/core/utils/land_units.dart';
import 'package:krishi_sathi/models/content.dart';
import 'package:krishi_sathi/models/my_crop.dart';

void main() {
  test('app version comparison for forced updates', () {
    expect(AppConfig.isOlderVersion('1.0.0', '1.0.1'), isTrue);
    expect(AppConfig.isOlderVersion('1.2.10', '1.10.0'), isTrue);
    expect(AppConfig.isOlderVersion('1.0.0', '1.0.0'), isFalse);
    expect(AppConfig.isOlderVersion('2.0', '1.9.9'), isFalse);
    expect(AppConfig.isOlderVersion('1.0.0', 'garbage'), isFalse);
  });

  test('server address is forgiving about what people type', () {
    const api = 'http://192.168.0.10:8000/api/v1';
    for (final typed in [
      '192.168.0.10:8000',
      'http://192.168.0.10:8000',
      ' http://192.168.0.10:8000/ ',
      'http://192.168.0.10:8000/api',
      'http://192.168.0.10:8000/api/v1/',
      'http://192.168.0.10:8000/api/v1/meta',
      '১৯২.১৬৮.০.১০:৮০০০',
    ]) {
      expect(AppConfig.normalizeApiBaseUrl(typed), api, reason: typed);
    }
    expect(AppConfig.normalizeApiBaseUrl('https://krishi.example.com/api/v1'), 'https://krishi.example.com/api/v1');
    expect(AppConfig.normalizeApiBaseUrl('https://example.com/krishi/public'), 'https://example.com/krishi/public/api/v1');
    expect(AppConfig.normalizeApiBaseUrl('  '), '');
  });

  test('public web pages live next to the API', () {
    expect(AppConfig.webPageUri('https://krishi.example.com/api/v1/', '/privacy').toString(), 'https://krishi.example.com/privacy');
    expect(AppConfig.webPageUri('https://example.com/krishi/api/v1', '/privacy', language: 'en').toString(),
        'https://example.com/krishi/privacy?lang=en');
    expect(AppConfig.webPageUri('', '/privacy'), isNull);
  });

  group('Bangla', () {
    test('converts digits both ways', () {
      expect(Bangla.digits('2026', 'bn'), '২০২৬');
      expect(Bangla.digits('2026', 'en'), '2026');
      expect(Bangla.toAscii('০১৭১২'), '01712');
    });

    test('formats numbers with separators and trimmed decimals', () {
      expect(Bangla.number(1234567, 'en'), '1,234,567');
      expect(Bangla.number(34.70, 'en', decimals: 2), '34.7');
      expect(Bangla.number(12.0, 'bn', decimals: 1), '১২');
    });

    test('Bengali calendar (2019 revision)', () {
      final today = Bangla.toBengali(DateTime(2026, 10, 14));
      expect((today.day, today.month, today.year), (29, 6, 1433)); // 29 Ashwin 1433
      expect(Bangla.toBengali(DateTime(2026, 4, 14)).format('en'), '1 Boishakh 1433');
      expect(Bangla.toBengali(DateTime(2026, 4, 13)).format('en'), '30 Chaitra 1432');
      expect(Bangla.toBengali(DateTime(2026, 8, 21)).format('bn'), '৬ ভাদ্র ১৪৩৩');
      expect(Bangla.toBengali(DateTime(2026, 10, 17)).format('en'), '1 Kartik 1433');
      expect(Bangla.toBengali(DateTime(2027, 2, 14)).format('en'), '1 Falgun 1433');
      expect(Bangla.toBengali(DateTime(2027, 3, 15)).format('en'), '1 Chaitra 1433');
      // Leap year: Falgun has 30 days, Chaitra still starts 15 March.
      expect(Bangla.toBengali(DateTime(2028, 3, 14)).format('en'), '30 Falgun 1434');
      expect(Bangla.toBengali(DateTime(2028, 3, 15)).format('en'), '1 Chaitra 1434');
    });
  });

  group('Land units', () {
    test('converts to decimals and hectares', () {
      expect(LandUnits.toDecimals(1, LandUnit.bigha), 33);
      expect(LandUnits.toDecimals(20, LandUnit.katha), closeTo(33, 0.001));
      expect(LandUnits.toDecimals(1, LandUnit.acre), 100);
      expect(LandUnits.toHectares(247.105, LandUnit.decimal), closeTo(1, 0.0001));
      expect(LandUnits.toDecimals(1, LandUnit.bigha, bighaDecimal: 52), 52);
    });
  });

  Crop crop({List<CropStage> stages = const [], List<FertilizerDose> doses = const []}) => Crop(
        id: 1, slug: 'boro-rice', nameBn: 'বোরো ধান', nameEn: 'Boro rice', category: 'cereal', stages: stages, doses: doses);

  test('fertilizer calculator scales per-hectare dose and prices it', () {
    final kb = KnowledgeBase(version: 'v', crops: [], articles: [], fertilizers: [
      Fertilizer(id: 1, key: 'urea', nameBn: 'ইউরিয়া', nameEn: 'Urea', officialPricePerKg: 27),
      Fertilizer(id: 2, key: 'gypsum', nameBn: 'জিপসাম', nameEn: 'Gypsum'),
    ]);
    final c = crop(doses: [FertilizerDose(fertilizerId: 1, kgPerHectare: 260), FertilizerDose(fertilizerId: 2, kgPerHectare: 110)]);

    final oneBigha = LandUnits.toHectares(1, LandUnit.bigha);
    final amounts = FertilizerCalculator.calculate(c, kb, oneBigha);

    expect(amounts.first.kg, closeTo(34.72, 0.01)); // 260 kg/ha × 33/247.105
    expect(amounts.first.cost, closeTo(937.4, 0.5));
    expect(amounts.last.cost, isNull); // no official gypsum price
    expect(FertilizerCalculator.totalCost(amounts), closeTo(937.4, 0.5));
  });

  group('Crop schedule', () {
    final stages = [
      CropStage(id: 1, dayOffset: -5, stageType: 'sowing', titleBn: 'বীজতলা', titleEn: 'Seedbed'),
      CropStage(id: 2, dayOffset: 0, stageType: 'sowing', titleBn: 'রোপণ', titleEn: 'Transplant'),
      CropStage(id: 3, dayOffset: 3, stageType: 'fertilizer', titleBn: 'সার', titleEn: 'Urea'),
      CropStage(id: 4, dayOffset: 30, stageType: 'harvest', titleBn: 'কাটা', titleEn: 'Harvest'),
    ];
    final today = DateTime(2026, 10, 14);

    test('dates and statuses', () {
      final myCrop = MyCrop(id: 'a', cropSlug: 'boro-rice', startDate: today, doneStageIds: {1});
      final tasks = CropSchedule.tasksFor(myCrop, crop(stages: stages), today);

      expect(tasks.map((t) => t.status), [TaskStatus.done, TaskStatus.today, TaskStatus.upcoming, TaskStatus.upcoming]);
      expect(tasks[2].date, DateTime(2026, 10, 17));
      expect(CropSchedule.next(tasks)!.stage.id, 2);
    });

    test('this week excludes done, far-future and old overdue tasks', () {
      final myCrop = MyCrop(id: 'a', cropSlug: 'boro-rice', startDate: today.subtract(const Duration(days: 20)));
      final tasks = CropSchedule.tasksFor(myCrop, crop(stages: stages), today);
      final week = CropSchedule.thisWeek(tasks, today);

      // Day 30 is 10 days ahead → excluded; steps from 17–25 days ago are too old.
      expect(week, isEmpty);

      final recent = MyCrop(id: 'b', cropSlug: 'boro-rice', startDate: today.subtract(const Duration(days: 2)));
      final ids = CropSchedule.thisWeek(CropSchedule.tasksFor(recent, crop(stages: stages), today), today).map((t) => t.stage.id);
      expect(ids, [1, 2, 3]); // overdue (7 & 2 days ago) and tomorrow
    });
  });
}
