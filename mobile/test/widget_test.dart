import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:krishi_sathi/app.dart';
import 'package:krishi_sathi/app_services.dart';
import 'package:krishi_sathi/core/storage/local_store.dart';
import 'package:krishi_sathi/features/calculator/fertilizer_calculator_screen.dart';
import 'package:krishi_sathi/features/my_crops/add_my_crop_screen.dart';

import 'helpers/fake_api.dart';

/// The whole app running with no internet – it must still be useful.
Future<AppServices> offlineApp(WidgetTester tester, {bool onboarded = true, String language = 'bn'}) async {
  final store = LocalStore.memory();
  if (onboarded) {
    await store.settings.put('onboarded', true);
    await store.settings.put('district_id', 9); // any district
    await store.settings.put('language', language);
  }
  final (api, _) = fakeApiClient(offline);
  final services = AppServices.create(store, api: api);
  await tester.runAsync(services.start);
  await tester.pumpWidget(KrishiSathiApp(services: services));
  await tester.pumpAndSettle();
  return services;
}

void main() {
  setUp(() {
    // Tall phone screen so long lists are fully built.
    TestWidgetsFlutterBinding.instance.platformDispatcher.views.first.physicalSize = const Size(1080, 2400);
  });

  testWidgets('onboarding: language, user type, district → home', (tester) async {
    await offlineApp(tester, onboarded: false);

    await tester.tap(find.text('বাংলা'));
    await tester.pumpAndSettle();
    expect(find.text('আপনি কে?'), findsOneWidget);

    await tester.tap(find.text('ছাদ বা বাড়ির বাগানি'));
    await tester.pumpAndSettle();
    expect(find.text('আপনার জেলা বেছে নিন'), findsOneWidget);

    await tester.enterText(find.byType(TextField), 'বগুড়া');
    await tester.pumpAndSettle();
    await tester.tap(find.widgetWithText(ListTile, 'বগুড়া'));
    await tester.pumpAndSettle();
    await tester.tap(find.text('শুরু করুন'));
    await tester.pumpAndSettle();

    expect(find.text('কৃষি সাথী'), findsOneWidget);
    expect(find.text('ফসলের সমস্যা'), findsOneWidget);
    await tester.scrollUntilVisible(find.textContaining('১৬১২৩'), 300, scrollable: find.byType(Scrollable).first);
    expect(find.textContaining('১৬১২৩'), findsOneWidget); // helpline button in Bangla digits
  });

  testWidgets('crop doctor works offline from the bundled knowledge base', (tester) async {
    await offlineApp(tester);

    await tester.tap(find.text('ফসলের সমস্যা'));
    await tester.pumpAndSettle();
    await tester.enterText(find.byType(TextField), 'বেগুন'); // search, like a user would
    await tester.pumpAndSettle();
    await tester.tap(find.descendant(of: find.byType(GridView), matching: find.text('বেগুন')));
    await tester.pumpAndSettle();

    expect(find.text('সমস্যা কোথায় দেখছেন?'), findsOneWidget);
    await tester.tap(find.text('ডগা ও ফল ছিদ্রকারী পোকা'));
    await tester.pumpAndSettle();

    expect(find.textContaining('প্রতিরোধ ও দমন'), findsOneWidget);
    await tester.scrollUntilVisible(find.textContaining('মাস্ক ও গ্লাভস'), 300, scrollable: find.byType(Scrollable).first);
    expect(find.textContaining('মাস্ক ও গ্লাভস'), findsOneWidget); // safety warning before chemicals
  });

  testWidgets('fertilizer calculator: 1 bigha of Boro rice', (tester) async {
    final services = await offlineApp(tester);
    final boro = services.content.kb!.cropBySlug('boro-rice')!;

    await tester.pumpWidget(KrishiSathiApp(services: services));
    final nav = tester.state<NavigatorState>(find.byType(Navigator));
    nav.push(MaterialPageRoute(builder: (_) => FertilizerCalculatorScreen(initialCrop: boro)));
    await tester.pumpAndSettle();

    // 260 kg urea per hectare × (33 / 247.105) ≈ 34.7 kg per bigha
    expect(find.text('৩৪.৭ কেজি'), findsOneWidget);
    await tester.scrollUntilVisible(find.textContaining('সরকারি দামে খরচ'), 300, scrollable: find.byType(Scrollable).first);
    expect(find.textContaining('সরকারি দামে খরচ'), findsOneWidget);
  });

  testWidgets('a question asked offline is kept in the outbox', (tester) async {
    final store = LocalStore.memory();
    await store.settings.put('onboarded', true);
    await store.settings.put('auth_token', 'test-token');
    await store.settings.put('auth_user', jsonEncode({'id': 1, 'name': 'রহিম', 'phone': '01711111111', 'role': 'farmer'}));
    final (api, _) = fakeApiClient(offline);
    final services = AppServices.create(store, api: api);
    await tester.runAsync(services.start);
    await tester.pumpWidget(KrishiSathiApp(services: services));
    await tester.pumpAndSettle();

    await tester.tap(find.text('বিশেষজ্ঞকে প্রশ্ন'));
    await tester.pumpAndSettle();
    await tester.tap(find.text('প্রশ্ন করুন'));
    await tester.pumpAndSettle();

    await tester.enterText(find.byType(TextField), 'টমেটোর পাতা কুঁকড়ে যাচ্ছে, কী করব?');
    await tester.tap(find.text('পাঠান'));
    await tester.pumpAndSettle();

    expect(find.textContaining('সংযোগ পেলে নিজে থেকেই পাঠানো হবে'), findsOneWidget);
    expect(services.questions.pending(), hasLength(1));
  });

  testWidgets('switching to English translates the home screen', (tester) async {
    await offlineApp(tester, language: 'en');

    expect(find.text('Krishi Sathi'), findsOneWidget);
    expect(find.text('Crop problems'), findsOneWidget);
    expect(find.text('Market prices'), findsOneWidget);
  });

  testWidgets('adding a crop creates a dated task list shown on Home', (tester) async {
    final services = await offlineApp(tester);
    final tomato = services.content.kb!.cropBySlug('tomato')!;

    final nav = tester.state<NavigatorState>(find.byType(Navigator));
    nav.push(MaterialPageRoute(builder: (_) => AddMyCropScreen(initialCrop: tomato)));
    await tester.pumpAndSettle();
    await tester.enterText(find.byType(TextField), 'ছাদ');
    await tester.tap(find.text('সংরক্ষণ করুন'));
    await tester.pumpAndSettle();

    expect(services.myCrops.items, hasLength(1));
    expect(services.myCrops.items.single.plotName, 'ছাদ');
    // Day 0 task ("transplanting") is due today and appears under "This week's tasks".
    expect(find.text('এই সপ্তাহের কাজ'), findsOneWidget);
    expect(find.text('চারা রোপণ'), findsOneWidget);

    // Ticking it off marks it done.
    await tester.tap(find.byType(Checkbox).first);
    await tester.pumpAndSettle();
    expect(services.myCrops.items.single.doneStageIds, isNotEmpty);
  });
}
