import 'package:flutter/foundation.dart';

import '../data/my_crops_repository.dart';
import '../models/content.dart';
import '../models/my_crop.dart';

class MyCropsController extends ChangeNotifier {
  MyCropsController(this._repo) : items = _repo.all();

  final MyCropsRepository _repo;
  List<MyCrop> items;

  Future<void> add({required String cropSlug, required DateTime startDate, String? plotName}) async {
    final crop = MyCrop(
      id: DateTime.now().microsecondsSinceEpoch.toString(),
      cropSlug: cropSlug,
      startDate: startDate,
      plotName: (plotName?.trim().isEmpty ?? true) ? null : plotName!.trim(),
    );
    await _repo.save(crop);
    items = _repo.all();
    notifyListeners();
  }

  Future<void> remove(String id) async {
    await _repo.delete(id);
    items = _repo.all();
    notifyListeners();
  }

  Future<void> toggleStage(MyCrop myCrop, int stageId) async {
    if (!myCrop.doneStageIds.remove(stageId)) {
      myCrop.doneStageIds.add(stageId);
    }
    await _repo.save(myCrop);
    notifyListeners();
  }

  /// This week's tasks across all plantings, soonest first.
  List<CropTask> weekTasks(KnowledgeBase? kb, DateTime today) {
    if (kb == null) return const [];
    final tasks = <CropTask>[];
    for (final myCrop in items) {
      final crop = kb.cropBySlug(myCrop.cropSlug);
      if (crop == null) continue;
      tasks.addAll(CropSchedule.thisWeek(CropSchedule.tasksFor(myCrop, crop, today), today));
    }
    tasks.sort((a, b) => a.date.compareTo(b.date));
    return tasks;
  }
}
