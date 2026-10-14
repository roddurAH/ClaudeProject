import 'dart:convert';

import '../core/storage/local_store.dart';
import '../models/my_crop.dart';

/// The user's plantings live only on the phone – no account or internet needed.
class MyCropsRepository {
  MyCropsRepository(this._store);

  final LocalStore _store;

  List<MyCrop> all() => _store.myCrops.keys
      .map((k) => _store.myCrops.get(k))
      .whereType<String>()
      .map((raw) => MyCrop.fromJson(jsonDecode(raw) as Map<String, dynamic>))
      .toList()
    ..sort((a, b) => b.startDate.compareTo(a.startDate));

  Future<void> save(MyCrop crop) => _store.myCrops.put(crop.id, jsonEncode(crop.toJson()));

  Future<void> delete(String id) => _store.myCrops.delete(id);
}
