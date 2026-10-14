import 'dart:convert';

import 'package:flutter/services.dart' show rootBundle;

import '../core/network/api_client.dart';
import '../core/storage/local_store.dart';
import '../models/content.dart';

/// The offline knowledge base: crops, calendars, problems, fertilizers, articles.
///
/// Load order: saved copy on the phone → copy bundled in the app. Refreshing
/// asks the server with the current version as ETag, so an unchanged
/// knowledge base costs only a tiny 304 response.
class ContentRepository {
  ContentRepository(this._api, this._store, {AssetBundleLoader? assetLoader})
      : _assetLoader = assetLoader ?? rootBundle.loadString;

  static const cacheKey = 'content';
  static const seedAsset = 'assets/seed/content.json';

  final ApiClient _api;
  final LocalStore _store;
  final AssetBundleLoader _assetLoader;

  /// Returns (knowledge base, saved time). Saved time is null for the bundled copy.
  Future<(KnowledgeBase?, DateTime?)> loadLocal() async {
    final cached = _store.readCache(cacheKey);
    if (cached?.data is Map<String, dynamic>) {
      try {
        return (KnowledgeBase.fromJson(cached!.data as Map<String, dynamic>), cached.savedAt);
      } catch (_) {
        // fall through to the bundled copy
      }
    }
    try {
      final raw = await _assetLoader(seedAsset);
      return (KnowledgeBase.fromJson(jsonDecode(raw) as Map<String, dynamic>), null);
    } catch (_) {
      return (null, null);
    }
  }

  /// Downloads the knowledge base if it changed. Returns null when unchanged.
  Future<KnowledgeBase?> refresh({String? currentVersion}) async {
    final response = await _api.get('/content', headers: {
      if (currentVersion != null && currentVersion.isNotEmpty) 'If-None-Match': '"$currentVersion"',
    });
    if (response.notModified || response.data is! Map) return null;

    final json = response.data as Map<String, dynamic>;
    await _store.writeCache(cacheKey, json);
    return KnowledgeBase.fromJson(json);
  }
}

typedef AssetBundleLoader = Future<String> Function(String key);
