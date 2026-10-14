import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/utils/bangla.dart';
import '../../core/utils/nav.dart';
import '../../core/widgets/common.dart';
import '../../l10n/app_localizations.dart';
import '../../models/my_crop.dart';
import '../../state/content_controller.dart';
import '../../state/my_crops_controller.dart';
import '../../state/settings_controller.dart';
import '../crops/crop_detail_screen.dart';
import '../home/task_tile.dart';

/// Day-by-day tasks for one planting, with tick boxes.
class MyCropDetailScreen extends StatelessWidget {
  const MyCropDetailScreen({super.key, required this.myCropId});
  final String myCropId;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;
    final controller = context.watch<MyCropsController>();
    final kb = context.watch<ContentController>().kb;
    final myCrop = controller.items.where((c) => c.id == myCropId).firstOrNull;
    final crop = myCrop == null ? null : kb?.cropBySlug(myCrop.cropSlug);

    if (myCrop == null || crop == null) return Scaffold(appBar: AppBar(), body: const LoadingView());

    final tasks = CropSchedule.tasksFor(myCrop, crop, DateTime.now());

    return Scaffold(
      appBar: AppBar(
        title: Text('${crop.emoji} ${crop.name(lang)}'),
        actions: [
          IconButton(
            icon: const Icon(Icons.menu_book),
            tooltip: crop.name(lang),
            onPressed: () => push(context, CropDetailScreen(crop: crop)),
          ),
          IconButton(
            icon: const Icon(Icons.delete_outline),
            tooltip: l10n.delete,
            onPressed: () async {
              final ok = await showDialog<bool>(
                context: context,
                builder: (ctx) => AlertDialog(
                  content: Text(l10n.deleteConfirm),
                  actions: [
                    TextButton(onPressed: () => Navigator.pop(ctx, false), child: Text(l10n.cancel)),
                    FilledButton(onPressed: () => Navigator.pop(ctx, true), child: Text(l10n.delete)),
                  ],
                ),
              );
              if (ok == true && context.mounted) {
                await context.read<MyCropsController>().remove(myCrop.id);
                if (context.mounted) Navigator.pop(context);
              }
            },
          ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text(
            '${myCrop.plotName ?? ''}${myCrop.plotName != null ? ' · ' : ''}${l10n.startDate}: ${Bangla.date(myCrop.startDate, lang)}',
            style: Theme.of(context).textTheme.titleSmall,
          ),
          const SizedBox(height: 12),
          Card(child: Column(children: [for (final t in tasks) TaskTile(task: t)])),
        ],
      ),
    );
  }
}
