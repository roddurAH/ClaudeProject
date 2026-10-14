import '../../l10n/app_localizations.dart';

/// Translated labels for the fixed vocabularies used by crops and problems.
class CropLabels {
  CropLabels._();

  static const categories = ['cereal', 'vegetable', 'fruit', 'pulse', 'oilseed', 'fibre', 'spice'];
  static const parts = ['leaf', 'stem', 'fruit', 'grain', 'root', 'whole'];

  static String category(String key, AppLocalizations l10n) => switch (key) {
        'cereal' => l10n.categoryCereal,
        'vegetable' => l10n.categoryVegetable,
        'fruit' => l10n.categoryFruit,
        'pulse' => l10n.categoryPulse,
        'oilseed' => l10n.categoryOilseed,
        'fibre' => l10n.categoryFibre,
        'spice' => l10n.categorySpice,
        _ => key,
      };

  static String season(String? key, AppLocalizations l10n) => switch (key) {
        'rabi' => l10n.seasonRabi,
        'kharif1' => l10n.seasonKharif1,
        'kharif2' => l10n.seasonKharif2,
        'year_round' => l10n.seasonYearRound,
        _ => '',
      };

  static String part(String key, AppLocalizations l10n) => switch (key) {
        'leaf' => l10n.partLeaf,
        'stem' => l10n.partStem,
        'fruit' => l10n.partFruit,
        'grain' => l10n.partGrain,
        'root' => l10n.partRoot,
        'whole' => l10n.partWhole,
        _ => l10n.partAll,
      };

  static String partEmoji(String key) => switch (key) {
        'leaf' => '🍃',
        'stem' => '🌿',
        'fruit' => '🍆',
        'grain' => '🌾',
        'root' => '🥔',
        'whole' => '🪴',
        _ => '🔎',
      };

  static String problemType(String key, AppLocalizations l10n) => switch (key) {
        'pest' => l10n.typePest,
        'disease' => l10n.typeDisease,
        'nutrient' => l10n.typeNutrient,
        'weed' => l10n.typeWeed,
        _ => l10n.typePhysiological,
      };
}
