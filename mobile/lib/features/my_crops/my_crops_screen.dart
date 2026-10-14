import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/utils/bangla.dart';
import '../../core/utils/nav.dart';
import '../../core/widgets/common.dart';
import '../../core/widgets/crop_avatar.dart';
import '../../l10n/app_localizations.dart';
import '../../models/my_crop.dart';
import '../../state/content_controller.dart';
import '../../state/my_crops_controller.dart';
import '../../state/settings_controller.dart';
import 'add_my_crop_screen.dart';
import 'my_crop_detail_screen.dart';

class MyCropsScreen extends StatelessWidget {
  const MyCropsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;
    final kb = context.watch<ContentController>().kb;
    final items = context.watch<MyCropsController>().items;
    final today = DateTime.now();

    return Scaffold(
      appBar: AppBar(title: Text(l10n.featureMyCrops)),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => push(context, const AddMyCropScreen()),
        icon: const Icon(Icons.add),
        label: Text(l10n.addCrop, style: const TextStyle(fontSize: 17)),
      ),
      body: items.isEmpty
          ? EmptyView(message: l10n.myCropsEmpty, icon: '🗓️')
          : ListView(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 96),
              children: [
                Text('📱 ${l10n.savedOnPhone}', style: Theme.of(context).textTheme.bodySmall),
                const SizedBox(height: 8),
                for (final myCrop in items)
                  if (kb?.cropBySlug(myCrop.cropSlug) case final crop?)
                    Builder(builder: (context) {
                      final tasks = CropSchedule.tasksFor(myCrop, crop, today);
                      final next = CropSchedule.next(tasks);
                      final done = tasks.where((t) => t.status == TaskStatus.done).length;
                      final day = myCrop.dayNumber(today);
                      return Padding(
                        padding: const EdgeInsets.only(bottom: 12),
                        child: Card(
                          clipBehavior: Clip.antiAlias,
                          child: InkWell(
                            onTap: () => push(context, MyCropDetailScreen(myCropId: myCrop.id)),
                            child: Padding(
                              padding: const EdgeInsets.all(14),
                              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                                Row(children: [
                                  CropAvatar(crop, size: 52),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                                      Text('${crop.name(lang)}${myCrop.plotName != null ? ' · ${myCrop.plotName}' : ''}',
                                          style: Theme.of(context).textTheme.titleMedium),
                                      Text(day >= 0
                                          ? l10n.cropDay(Bangla.digits('$day', lang))
                                          : l10n.taskInDays(Bangla.digits('${-day}', lang))),
                                    ]),
                                  ),
                                  const Icon(Icons.chevron_right),
                                ]),
                                const SizedBox(height: 10),
                                LinearProgressIndicator(
                                  value: tasks.isEmpty ? 0 : done / tasks.length,
                                  minHeight: 8,
                                  borderRadius: BorderRadius.circular(4),
                                ),
                                const SizedBox(height: 8),
                                Text(
                                  next == null ? '✅ ${l10n.allTasksDone}' : '${next.stage.emoji} ${l10n.nextTask(next.stage.title(lang))} · ${Bangla.shortDate(next.date, lang)}',
                                  style: const TextStyle(fontWeight: FontWeight.w600),
                                ),
                              ]),
                            ),
                          ),
                        ),
                      );
                    }),
              ],
            ),
    );
  }
}
