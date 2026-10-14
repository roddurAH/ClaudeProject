import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:provider/provider.dart';

import 'app_services.dart';
import 'core/services/voice_input_service.dart';
import 'core/theme/app_theme.dart';
import 'features/home/home_screen.dart';
import 'features/onboarding/onboarding_screen.dart';
import 'l10n/app_localizations.dart';
import 'state/settings_controller.dart';

class KrishiSathiApp extends StatelessWidget {
  const KrishiSathiApp({super.key, required this.services});
  final AppServices services;

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        Provider.value(value: services),
        Provider.value(value: services.prices),
        Provider.value(value: services.weather),
        Provider.value(value: services.questions),
        Provider.value(value: services.ai),
        ChangeNotifierProvider.value(value: services.settings),
        ChangeNotifierProvider.value(value: services.reference),
        ChangeNotifierProvider.value(value: services.content),
        ChangeNotifierProvider.value(value: services.auth),
        ChangeNotifierProvider.value(value: services.myCrops),
        ChangeNotifierProvider.value(value: services.tts),
        ChangeNotifierProvider<VoiceInput>.value(value: services.voice),
      ],
      child: Consumer<SettingsController>(
        builder: (context, settings, _) => MaterialApp(
          title: 'Krishi Sathi',
          debugShowCheckedModeBanner: false,
          theme: AppTheme.light(),
          locale: Locale(settings.language),
          supportedLocales: AppLocalizations.supportedLocales,
          localizationsDelegates: const [
            AppLocalizations.delegate,
            GlobalMaterialLocalizations.delegate,
            GlobalWidgetsLocalizations.delegate,
            GlobalCupertinoLocalizations.delegate,
          ],
          // User-chosen text size on top of the phone's own setting.
          builder: (context, child) {
            final media = MediaQuery.of(context);
            return MediaQuery(
              data: media.copyWith(textScaler: TextScaler.linear(media.textScaler.scale(1) * settings.textScale)),
              child: child!,
            );
          },
          home: settings.onboarded ? const HomeScreen() : const OnboardingScreen(),
        ),
      ),
    );
  }
}
