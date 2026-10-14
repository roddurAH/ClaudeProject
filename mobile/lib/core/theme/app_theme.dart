import 'package:flutter/material.dart';

/// Calm green theme with large, high-contrast text and big touch targets –
/// designed for outdoor use on low-cost phones.
class AppTheme {
  AppTheme._();

  static const Color green = Color(0xFF2E7D32);
  static const Color darkGreen = Color(0xFF1B5E20);
  static const Color soil = Color(0xFF795548);
  static const Color sun = Color(0xFFF9A825);
  static const Color danger = Color(0xFFC62828);
  static const Color warning = Color(0xFFEF6C00);
  static const Color info = Color(0xFF1565C0);

  static ThemeData light() {
    final scheme = ColorScheme.fromSeed(seedColor: green, primary: green, surface: const Color(0xFFFBFDF8));
    final base = ThemeData(colorScheme: scheme, useMaterial3: true, fontFamily: 'HindSiliguri');

    return base.copyWith(
      scaffoldBackgroundColor: const Color(0xFFF3F6EF),
      textTheme: base.textTheme.copyWith(
        titleLarge: base.textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w600, fontSize: 22),
        titleMedium: base.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600, fontSize: 18),
        bodyLarge: base.textTheme.bodyLarge?.copyWith(fontSize: 17, height: 1.45),
        bodyMedium: base.textTheme.bodyMedium?.copyWith(fontSize: 16, height: 1.4),
      ),
      appBarTheme: const AppBarTheme(
        backgroundColor: green,
        foregroundColor: Colors.white,
        centerTitle: false,
        titleTextStyle: TextStyle(fontFamily: 'HindSiliguri', fontSize: 21, fontWeight: FontWeight.w600, color: Colors.white),
      ),
      cardTheme: CardThemeData(
        elevation: 0.5,
        margin: EdgeInsets.zero,
        color: Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          minimumSize: const Size(64, 52),
          textStyle: const TextStyle(fontFamily: 'HindSiliguri', fontSize: 17, fontWeight: FontWeight.w600),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          minimumSize: const Size(64, 50),
          textStyle: const TextStyle(fontFamily: 'HindSiliguri', fontSize: 16, fontWeight: FontWeight.w600),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      ),
      chipTheme: base.chipTheme.copyWith(
        labelStyle: const TextStyle(fontFamily: 'HindSiliguri', fontSize: 15),
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
      ),
      listTileTheme: const ListTileThemeData(minVerticalPadding: 12),
    );
  }

  static Color levelColor(String level) => switch (level) {
        'danger' => danger,
        'warning' => warning,
        _ => info,
      };
}
