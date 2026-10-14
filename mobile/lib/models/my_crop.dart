import 'content.dart';
import 'localized.dart';

/// A crop the user is growing, stored only on the phone.
class MyCrop {
  MyCrop({required this.id, required this.cropSlug, required this.startDate, this.plotName, Set<int>? doneStageIds})
      : doneStageIds = doneStageIds ?? {};

  final String id;
  final String cropSlug;
  final DateTime startDate;
  final String? plotName;
  final Set<int> doneStageIds;

  int dayNumber(DateTime today) => _dateOnly(today).difference(_dateOnly(startDate)).inDays;

  Map<String, dynamic> toJson() => {
        'id': id,
        'crop_slug': cropSlug,
        'start_date': startDate.toIso8601String(),
        'plot_name': plotName,
        'done': doneStageIds.toList(),
      };

  factory MyCrop.fromJson(Map<String, dynamic> j) => MyCrop(
        id: j['id'].toString(),
        cropSlug: j['crop_slug']?.toString() ?? '',
        startDate: DateTime.tryParse(j['start_date']?.toString() ?? '') ?? DateTime.now(),
        plotName: j['plot_name']?.toString(),
        doneStageIds: toIntList(j['done']).toSet(),
      );
}

enum TaskStatus { done, overdue, today, upcoming }

/// One calendar step of a planting with its real date.
class CropTask {
  CropTask({required this.myCrop, required this.crop, required this.stage, required this.date, required this.status});

  final MyCrop myCrop;
  final Crop crop;
  final CropStage stage;
  final DateTime date;
  final TaskStatus status;

  /// Days from today (negative = in the past).
  int daysFrom(DateTime today) => _dateOnly(date).difference(_dateOnly(today)).inDays;
}

/// Turns a crop's calendar template into dated tasks for one planting.
class CropSchedule {
  CropSchedule._();

  /// A task is "overdue" only within this many days; older undone steps are
  /// shown as overdue in the planting's own list but not nagged on Home.
  static const int overdueWindowDays = 7;

  static List<CropTask> tasksFor(MyCrop myCrop, Crop crop, DateTime today) {
    final t = _dateOnly(today);
    return crop.stages.map((stage) {
      final date = _dateOnly(myCrop.startDate).add(Duration(days: stage.dayOffset));
      final TaskStatus status;
      if (myCrop.doneStageIds.contains(stage.id)) {
        status = TaskStatus.done;
      } else if (date.isBefore(t)) {
        status = TaskStatus.overdue;
      } else if (date == t) {
        status = TaskStatus.today;
      } else {
        status = TaskStatus.upcoming;
      }
      return CropTask(myCrop: myCrop, crop: crop, stage: stage, date: date, status: status);
    }).toList();
  }

  /// Tasks that need attention this week: recently overdue, today, and the next 7 days.
  static List<CropTask> thisWeek(List<CropTask> tasks, DateTime today) {
    return tasks.where((task) {
      if (task.status == TaskStatus.done) return false;
      final days = task.daysFrom(today);
      return days >= -overdueWindowDays && days <= 7;
    }).toList()
      ..sort((a, b) => a.date.compareTo(b.date));
  }

  static CropTask? next(List<CropTask> tasks) {
    for (final task in tasks) {
      if (task.status != TaskStatus.done) return task;
    }
    return null;
  }
}

DateTime _dateOnly(DateTime d) => DateTime(d.year, d.month, d.day);
