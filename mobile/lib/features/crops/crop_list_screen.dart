import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/utils/nav.dart';
import '../../core/widgets/common.dart';
import '../../core/widgets/crop_avatar.dart';
import '../../l10n/app_localizations.dart';
import '../../models/content.dart';
import '../../state/content_controller.dart';
import '../../state/settings_controller.dart';
import 'crop_detail_screen.dart';
import 'crop_labels.dart';

/// Choose a crop by its picture. Gardeners see rooftop crops first.
class CropListScreen extends StatefulWidget {
  const CropListScreen({super.key, this.onPick, this.title, this.requireDoses = false, this.requireStages = false});

  /// When set, tapping a crop returns it instead of opening details.
  final ValueChanged<Crop>? onPick;
  final String? title;
  final bool requireDoses;
  final bool requireStages;

  @override
  State<CropListScreen> createState() => _CropListScreenState();
}

class _CropListScreenState extends State<CropListScreen> {
  String _query = '';
  late String _filter = context.read<SettingsController>().isGardener ? 'garden' : 'all';

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;
    final content = context.watch<ContentController>();

    if (!content.loaded) return Scaffold(appBar: AppBar(), body: const LoadingView());

    final q = _query.trim().toLowerCase();
    final crops = content.crops.where((c) {
      if (widget.requireDoses && c.doses.isEmpty) return false;
      if (widget.requireStages && c.stages.isEmpty) return false;
      if (_filter == 'garden' && !c.isGardenFriendly) return false;
      if (_filter != 'all' && _filter != 'garden' && c.category != _filter) return false;
      return q.isEmpty || c.nameBn.contains(q) || c.nameEn.toLowerCase().contains(q);
    }).toList();

    final filters = ['all', 'garden', ...CropLabels.categories.where((cat) => content.crops.any((c) => c.category == cat))];

    return Scaffold(
      appBar: AppBar(title: Text(widget.title ?? l10n.featureCropDoctor)),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
            child: TextField(
              decoration: InputDecoration(prefixIcon: const Icon(Icons.search), hintText: l10n.searchCrops),
              onChanged: (v) => setState(() => _query = v),
            ),
          ),
          SizedBox(
            height: 52,
            child: ListView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              children: [
                for (final f in filters)
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 4),
                    child: ChoiceChip(
                      label: Text(switch (f) {
                        'all' => l10n.filterAll,
                        'garden' => '🪴 ${l10n.filterGarden}',
                        _ => CropLabels.category(f, l10n),
                      }),
                      selected: _filter == f,
                      onSelected: (_) => setState(() => _filter = f),
                    ),
                  ),
              ],
            ),
          ),
          Expanded(
            child: crops.isEmpty
                ? EmptyView(message: l10n.nothingThisMonth, icon: '🔍')
                : GridView.builder(
                    padding: const EdgeInsets.all(16),
                    gridDelegate: const SliverGridDelegateWithMaxCrossAxisExtent(
                      maxCrossAxisExtent: 160,
                      mainAxisSpacing: 12,
                      crossAxisSpacing: 12,
                      childAspectRatio: 0.95,
                    ),
                    itemCount: crops.length,
                    itemBuilder: (context, i) {
                      final crop = crops[i];
                      return Card(
                        clipBehavior: Clip.antiAlias,
                        child: InkWell(
                          onTap: () {
                            if (widget.onPick != null) {
                              widget.onPick!(crop);
                            } else {
                              push(context, CropDetailScreen(crop: crop));
                            }
                          },
                          child: Padding(
                            padding: const EdgeInsets.all(10),
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                CropAvatar(crop, size: 64),
                                const SizedBox(height: 8),
                                Text(crop.name(lang),
                                    textAlign: TextAlign.center,
                                    maxLines: 2,
                                    style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w600, height: 1.2)),
                              ],
                            ),
                          ),
                        ),
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }
}
