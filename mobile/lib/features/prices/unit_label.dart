import '../../l10n/app_localizations.dart';

String unitLabel(String unit, AppLocalizations l10n) => switch (unit) {
      'maund' => l10n.unitMaund,
      'piece' => l10n.unitPiece,
      'hali' => l10n.unitHali,
      'dozen' => l10n.unitDozen,
      'litre' => l10n.unitLitre,
      _ => l10n.unitKg,
    };
