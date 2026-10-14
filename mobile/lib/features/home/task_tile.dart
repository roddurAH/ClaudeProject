import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_theme.dart';
import '../../core/utils/bangla.dart';
import '../../l10n/app_localizations.dart';
import '../../models/my_crop.dart';
import '../../state/my_crops_controller.dart';
import '../../state/settings_controller.dart';

/// One calendar task with a tick box. Used on Home and in My crops.
class TaskTile extends StatelessWidget {
  const TaskTile({super.key, required this.task, this.showCrop = false});
  final CropTask task;
  final bool showCrop;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;
    final days = task.daysFrom(DateTime.now());
    final done = task.status == TaskStatus.done;

    final (String whenText, Color color) = switch (task.status) {
      TaskStatus.done => (l10n.allTasksDone, Colors.grey),
      TaskStatus.today => (l10n.taskToday, AppTheme.green),
      TaskStatus.overdue => ('${l10n.taskOverdue} · ${l10n.taskDaysAgo(Bangla.digits('${-days}', lang))}', AppTheme.danger),
      TaskStatus.upcoming => (l10n.taskInDays(Bangla.digits('$days', lang)), AppTheme.info),
    };

    final cropLabel = showCrop
        ? '${task.crop.emoji} ${task.crop.name(lang)}${task.myCrop.plotName != null ? ' (${task.myCrop.plotName})' : ''} · '
        : '';

    return ListTile(
      leading: Text(task.stage.emoji, style: const TextStyle(fontSize: 26)),
      title: Text(task.stage.title(lang),
          style: TextStyle(fontWeight: FontWeight.w600, decoration: done ? TextDecoration.lineThrough : null)),
      subtitle: Text('$cropLabel${Bangla.shortDate(task.date, lang)} · ${done ? '✓' : whenText}',
          style: TextStyle(color: done ? Colors.grey : color)),
      trailing: Checkbox(
        value: done,
        onChanged: (_) => context.read<MyCropsController>().toggleStage(task.myCrop, task.stage.id),
      ),
    );
  }
}
