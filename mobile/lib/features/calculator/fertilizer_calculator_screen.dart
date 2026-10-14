import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/utils/bangla.dart';
import '../../core/utils/fertilizer_calc.dart';
import '../../core/utils/land_units.dart';
import '../../core/utils/nav.dart';
import '../../core/widgets/common.dart';
import '../../core/widgets/crop_avatar.dart';
import '../../l10n/app_localizations.dart';
import '../../models/content.dart';
import '../../state/content_controller.dart';
import '../../state/settings_controller.dart';
import '../crops/crop_list_screen.dart';

/// How much fertilizer for MY land, in the units farmers use, and what it
/// should cost at the government price (helps spot overcharging).
class FertilizerCalculatorScreen extends StatefulWidget {
  const FertilizerCalculatorScreen({super.key, this.initialCrop});
  final Crop? initialCrop;

  @override
  State<FertilizerCalculatorScreen> createState() => _FertilizerCalculatorScreenState();
}

class _FertilizerCalculatorScreenState extends State<FertilizerCalculatorScreen> {
  late Crop? _crop = widget.initialCrop;
  late final _area = TextEditingController(text: Bangla.digits('1', context.read<SettingsController>().language));
  LandUnit _unit = LandUnit.bigha;

  @override
  void dispose() {
    _area.dispose();
    super.dispose();
  }

  String _unitLabel(LandUnit u, AppLocalizations l10n) => switch (u) {
        LandUnit.decimal => l10n.unitDecimal,
        LandUnit.katha => l10n.unitKatha,
        LandUnit.bigha => l10n.unitBigha,
        LandUnit.acre => l10n.unitAcre,
        LandUnit.hectare => l10n.unitHectare,
      };

  String _amount(double kg, AppLocalizations l10n, String lang) => kg < 1
      ? l10n.gram(Bangla.number(kg * 1000, lang))
      : l10n.kg(Bangla.number(kg, lang, decimals: kg < 10 ? 2 : 1));

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final settings = context.watch<SettingsController>();
    final lang = settings.language;
    final kb = context.watch<ContentController>().kb;

    final area = double.tryParse(Bangla.toAscii(_area.text).replaceAll(',', '.')) ?? 0;
    final hectares = LandUnits.toHectares(area, _unit, bighaDecimal: settings.bighaDecimal);
    final amounts = (_crop == null || kb == null) ? <FertilizerAmount>[] : FertilizerCalculator.calculate(_crop!, kb, hectares);
    final total = FertilizerCalculator.totalCost(amounts);

    return Scaffold(
      appBar: AppBar(title: Text(l10n.featureCalculator)),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            child: ListTile(
              contentPadding: const EdgeInsets.all(12),
              leading: _crop == null ? const Text('🌱', style: TextStyle(fontSize: 36)) : CropAvatar(_crop!, size: 52),
              title: Text(_crop?.name(lang) ?? l10n.chooseCrop, style: Theme.of(context).textTheme.titleMedium),
              trailing: const Icon(Icons.edit),
              onTap: () => push(
                context,
                CropListScreen(
                  title: l10n.chooseCrop,
                  requireDoses: true,
                  onPick: (crop) {
                    setState(() => _crop = crop);
                    Navigator.pop(context);
                  },
                ),
              ),
            ),
          ),
          const SizedBox(height: 16),
          Text(l10n.landSize, style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 8),
          Row(children: [
            SizedBox(
              width: 120,
              child: TextField(
                controller: _area,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w600),
                textAlign: TextAlign.center,
                onChanged: (_) => setState(() {}),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Wrap(spacing: 6, runSpacing: 6, children: [
                for (final u in LandUnit.values)
                  ChoiceChip(label: Text(_unitLabel(u, l10n)), selected: _unit == u, onSelected: (_) => setState(() => _unit = u)),
              ]),
            ),
          ]),
          if (_unit == LandUnit.bigha || _unit == LandUnit.katha) ...[
            const SizedBox(height: 6),
            Text(l10n.bighaNote(Bangla.number(settings.bighaDecimal, lang)), style: Theme.of(context).textTheme.bodySmall),
          ],
          const SizedBox(height: 16),
          if (_crop != null && area <= 0) InfoBanner(text: l10n.enterLandSize),
          if (_crop != null && area > 0 && amounts.isEmpty) InfoBanner(text: l10n.noFertilizerData),
          if (amounts.isNotEmpty) ...[
            SectionHeader(l10n.fertilizerNeeded, icon: '🧪'),
            Card(
              child: Column(children: [
                for (final a in amounts)
                  ExpansionTile(
                    title: Text(a.fertilizer.name(lang), style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 17)),
                    subtitle: a.cost == null ? null : Text('৳${Bangla.number(a.cost!, lang)}'),
                    trailing: Text(_amount(a.kg, l10n, lang), style: const TextStyle(fontSize: 19, fontWeight: FontWeight.w600)),
                    childrenPadding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
                    expandedCrossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('${l10n.whenToApply}: ${a.dose.application(lang)}'),
                      if (a.fertilizer.notes(lang).isNotEmpty) Text(a.fertilizer.notes(lang), style: Theme.of(context).textTheme.bodySmall),
                    ],
                  ),
              ]),
            ),
            if (total > 0) ...[
              const SizedBox(height: 12),
              InfoBanner(icon: '💰', title: l10n.estimatedCost(Bangla.number(total, lang)), text: l10n.officialPriceNote),
            ],
            const SizedBox(height: 12),
            InfoBanner(level: 'warning', text: l10n.calcDisclaimer),
          ],
          if (kb != null) ...[
            SectionHeader(l10n.officialPrices, icon: '🏛️'),
            Card(
              child: Column(children: [
                for (final f in kb.fertilizers.where((f) => f.officialPricePerKg != null))
                  ListTile(
                    dense: true,
                    title: Text(f.name(lang), style: const TextStyle(fontSize: 16)),
                    trailing: Text(l10n.perKg(Bangla.number(f.officialPricePerKg!, lang)), style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
                  ),
              ]),
            ),
          ],
        ],
      ),
    );
  }
}
