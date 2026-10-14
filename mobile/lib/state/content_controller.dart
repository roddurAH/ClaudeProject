import 'package:flutter/foundation.dart';

import '../core/network/api_exception.dart';
import '../data/content_repository.dart';
import '../models/content.dart';

/// Holds the knowledge base for the whole app and keeps it fresh.
class ContentController extends ChangeNotifier {
  ContentController(this._repo);

  final ContentRepository _repo;

  KnowledgeBase? kb;

  /// When the current copy was downloaded; null = copy bundled in the app.
  DateTime? savedAt;
  bool syncing = false;
  bool loaded = false;
  ApiException? lastError;

  List<Crop> get crops => kb?.crops ?? const [];

  Future<void> init() async {
    final (local, time) = await _repo.loadLocal();
    kb = local;
    savedAt = time;
    loaded = true;
    notifyListeners();
    sync();
  }

  /// Returns true when new content was downloaded.
  Future<bool> sync() async {
    if (syncing) return false;
    syncing = true;
    lastError = null;
    notifyListeners();
    try {
      final fresh = await _repo.refresh(currentVersion: kb?.version);
      if (fresh != null) {
        kb = fresh;
      }
      savedAt = DateTime.now();
      return fresh != null;
    } on ApiException catch (e) {
      lastError = e;
      return false;
    } finally {
      syncing = false;
      notifyListeners();
    }
  }
}
