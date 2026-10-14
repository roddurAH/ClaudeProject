import 'package:flutter/foundation.dart';

import '../core/config/app_config.dart';
import '../data/reference_repository.dart';
import '../models/reference.dart';

/// Districts and server settings (helpline number …).
class ReferenceController extends ChangeNotifier {
  ReferenceController(this._repo);

  final ReferenceRepository _repo;

  List<District> districts = [];
  AppMeta? meta;

  String get helpline => meta?.helpline ?? AppConfig.defaultHelpline;

  District? districtById(int? id) {
    for (final d in districts) {
      if (d.id == id) return d;
    }
    return null;
  }

  Future<void> init() async {
    districts = await _repo.localDistricts();
    meta = _repo.localMeta();
    notifyListeners();
    refresh();
  }

  Future<void> refresh() async {
    try {
      meta = await _repo.refreshMeta();
      final fresh = await _repo.refreshDistricts();
      if (fresh.isNotEmpty) districts = fresh;
      notifyListeners();
    } catch (_) {
      // Offline: keep using local data.
    }
  }
}
