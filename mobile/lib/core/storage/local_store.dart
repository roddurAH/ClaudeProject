import 'dart:convert';

import 'package:hive_ce_flutter/hive_flutter.dart';

/// Minimal key-value box so the app can use Hive on devices and a plain
/// in-memory map in tests.
abstract class KvBox {
  dynamic get(String key);
  Future<void> put(String key, dynamic value);
  Future<void> delete(String key);
  Iterable<String> get keys;
}

class HiveKvBox implements KvBox {
  HiveKvBox(this._box);
  final Box<dynamic> _box;

  @override
  dynamic get(String key) => _box.get(key);
  @override
  Future<void> put(String key, dynamic value) => _box.put(key, value);
  @override
  Future<void> delete(String key) => _box.delete(key);
  @override
  Iterable<String> get keys => _box.keys.cast<String>();
}

class MemoryKvBox implements KvBox {
  final Map<String, dynamic> _data = {};

  @override
  dynamic get(String key) => _data[key];
  @override
  Future<void> put(String key, dynamic value) async => _data[key] = value;
  @override
  Future<void> delete(String key) async => _data.remove(key);
  @override
  Iterable<String> get keys => _data.keys;
}

/// Everything the app keeps on the phone.
///
/// * settings – language, district, login token, preferences
/// * cache    – last good API responses (JSON) for offline use
/// * myCrops  – the user's plantings (local only)
/// * outbox   – questions written while offline, sent later
class LocalStore {
  LocalStore({required this.settings, required this.cache, required this.myCrops, required this.outbox});

  final KvBox settings;
  final KvBox cache;
  final KvBox myCrops;
  final KvBox outbox;

  static Future<LocalStore> open() async {
    await Hive.initFlutter('krishi_sathi');
    return LocalStore(
      settings: HiveKvBox(await Hive.openBox<dynamic>('settings')),
      cache: HiveKvBox(await Hive.openBox<dynamic>('cache')),
      myCrops: HiveKvBox(await Hive.openBox<dynamic>('my_crops')),
      outbox: HiveKvBox(await Hive.openBox<dynamic>('outbox')),
    );
  }

  factory LocalStore.memory() =>
      LocalStore(settings: MemoryKvBox(), cache: MemoryKvBox(), myCrops: MemoryKvBox(), outbox: MemoryKvBox());

  /// Stores [data] (anything JSON-encodable) with a timestamp and optional ETag.
  Future<void> writeCache(String key, Object? data, {String? etag}) => cache.put(
        key,
        jsonEncode({'savedAt': DateTime.now().toIso8601String(), 'etag': etag, 'data': data}),
      );

  CachedEntry? readCache(String key) {
    final raw = cache.get(key);
    if (raw is! String) return null;
    try {
      final map = jsonDecode(raw) as Map<String, dynamic>;
      return CachedEntry(
        data: map['data'],
        savedAt: DateTime.tryParse(map['savedAt'] as String? ?? ''),
        etag: map['etag'] as String?,
      );
    } catch (_) {
      return null; // corrupted entry – behave as if nothing cached
    }
  }
}

class CachedEntry {
  CachedEntry({required this.data, this.savedAt, this.etag});
  final dynamic data;
  final DateTime? savedAt;
  final String? etag;
}
