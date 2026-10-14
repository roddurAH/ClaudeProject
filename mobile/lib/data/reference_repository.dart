import 'dart:convert';

import 'package:flutter/services.dart' show rootBundle;

import '../core/network/api_client.dart';
import '../core/network/api_exception.dart';
import '../core/storage/local_store.dart';
import '../models/reference.dart';
import 'cached_fetch.dart';

/// Districts and server settings.
class ReferenceRepository {
  ReferenceRepository(this._api, this._store);

  final ApiClient _api;
  final LocalStore _store;

  static List<District> _parseDistricts(dynamic json) =>
      ((json as Map)['data'] as List).map((e) => District.fromJson(e as Map<String, dynamic>)).toList();

  /// Saved or bundled list – available instantly and offline.
  Future<List<District>> localDistricts() async {
    final cached = peekCache(_store, 'districts', _parseDistricts);
    if (cached != null && cached.data.isNotEmpty) return cached.data;
    try {
      return _parseDistricts(jsonDecode(await rootBundle.loadString('assets/seed/districts.json')));
    } catch (_) {
      return [];
    }
  }

  Future<List<District>> refreshDistricts() async {
    final result = await cachedFetch(
      store: _store,
      key: 'districts',
      fetch: () async => (await _api.get('/districts')).data,
      parse: _parseDistricts,
    );
    return result.data;
  }

  AppMeta? localMeta() => peekCache(_store, 'meta', (j) => AppMeta.fromJson(j as Map<String, dynamic>))?.data;

  /// Asks the server directly (no cached fallback), to check a new address.
  Future<AppMeta> checkServer() async {
    final json = (await _api.get('/meta')).data;
    if (json is! Map<String, dynamic> || !json.containsKey('content_version')) {
      throw ApiException(ApiErrorType.wrongServer, message: 'Not a Krishi Sathi server');
    }
    await _store.writeCache('meta', json);
    return AppMeta.fromJson(json);
  }

  Future<AppMeta> refreshMeta() async {
    final result = await cachedFetch(
      store: _store,
      key: 'meta',
      fetch: () async => (await _api.get('/meta')).data,
      parse: (j) => AppMeta.fromJson(j as Map<String, dynamic>),
    );
    return result.data;
  }
}
