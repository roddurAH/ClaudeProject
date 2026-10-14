import '../l10n/app_localizations.dart';

/// The four kinds of users, with their icon and labels.
class UserTypeOption {
  const UserTypeOption(this.key, this.emoji);
  final String key;
  final String emoji;

  static const all = [
    UserTypeOption('farmer', '👨‍🌾'),
    UserTypeOption('gardener', '🪴'),
    UserTypeOption('learner', '📚'),
    UserTypeOption('extension_worker', '🧑‍💼'),
  ];

  String title(AppLocalizations l10n) => switch (key) {
        'gardener' => l10n.userTypeGardener,
        'learner' => l10n.userTypeLearner,
        'extension_worker' => l10n.userTypeExtension,
        _ => l10n.userTypeFarmer,
      };

  String description(AppLocalizations l10n) => switch (key) {
        'gardener' => l10n.userTypeGardenerDesc,
        'learner' => l10n.userTypeLearnerDesc,
        'extension_worker' => l10n.userTypeExtensionDesc,
        _ => l10n.userTypeFarmerDesc,
      };
}
