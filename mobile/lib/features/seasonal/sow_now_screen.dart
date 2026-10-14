import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/utils/bangla.dart';
import '../../core/utils/nav.dart';
import '../../core/widgets/common.dart';
import '../../core/widgets/crop_avatar.dart';
import '../../l10n/app_localizations.dart';
import '../../models/content.dart';
import '../../state/content_controller.dart';
import '../../state/settings_controller.dart';
import '../crops/crop_detail_screen.dart';

/// What can be sown / planted – and harvested – this month (or another month).
class SowNowScreen extends StatefulWidget {
  const SowNowScreen({super.key});

  @override
  State<SowNowScreen> createState() => _SowNowScreenState();
}

class _SowNowScreenState extends State<SowNowScreen> {
  int _month = DateTime.now().month;
  late bool _gardenOnly = context.read<SettingsController>().isGardener;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;
    final crops = context.watch<ContentController>().crops.where((c) => !_gardenOnly || c.isGardenFriendly).toList();
    final sow = crops.where((c) => c.sowingMonths.contains(_month)).toList();
    final harvest = crops.where((c) => c.harvestMonths.contains(_month)).toList();
    final monthName = Bangla.monthName(_month, lang);

    return Scaffold(
      appBar: AppBar(title: Text(l10n.featureSowNow)),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Row(children: [
            IconButton.filledTonal(onPressed: () => setState(() => _month = _month == 1 ? 12 : _month - 1), icon: const Icon(Icons.chevron_left)),
            Expanded(child: Text(monthName, textAlign: TextAlign.center, style: Theme.of(context).textTheme.headlineSmall)),
            IconButton.filledTonal(onPressed: () => setState(() => _month = _month == 12 ? 1 : _month + 1), icon: const Icon(Icons.chevron_right)),
          ]),
          SwitchListTile(
            value: _gardenOnly,
            onChanged: (v) => setState(() => _gardenOnly = v),
            title: Text('🪴 ${l10n.rooftopOnly}'),
          ),
          SectionHeader(l10n.sowInMonth(monthName), icon: '🌱'),
          _CropWrap(crops: sow),
          SectionHeader(l10n.harvestInMonth(monthName), icon: '🧺'),
          _CropWrap(crops: harvest),
        ],
      ),
    );
  }
}

class _CropWrap extends StatelessWidget {
  const _CropWrap({required this.crops});
  final List<Crop> crops;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;
    if (crops.isEmpty) return Padding(padding: const EdgeInsets.all(8), child: Text(l10n.nothingThisMonth));
    return Card(
      child: Column(children: [
        for (final c in crops)
          ListTile(
            leading: CropAvatar(c),
            title: Text(c.name(lang), style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 17)),
            subtitle: Text(c.summary(lang), maxLines: 2, overflow: TextOverflow.ellipsis),
            trailing: c.isGardenFriendly ? const Text('🪴') : null,
            onTap: () => push(context, CropDetailScreen(crop: c)),
          ),
      ]),
    );
  }
}
