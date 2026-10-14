import '../core/network/api_exception.dart';
import '../core/storage/local_store.dart';

/// Result of a network call that may have been answered from the phone's cache.
class CachedResult<T> {
  CachedResult(this.data, {this.savedAt, this.fromCache = false});

  final T data;
  final DateTime? savedAt;

  /// True when the network failed and saved data is shown instead.
  final bool fromCache;
}

/// Fetch from the API and remember the answer. If the phone is offline (or
/// the server is down) return the last saved answer instead of an error.
Future<CachedResult<T>> cachedFetch<T>({
  required LocalStore store,
  required String key,
  required Future<dynamic> Function() fetch,
  required T Function(dynamic json) parse,
}) async {
  try {
    final json = await fetch();
    await store.writeCache(key, json);
    return CachedResult(parse(json), savedAt: DateTime.now());
  } on ApiException catch (e) {
    final cached = store.readCache(key);
    if (cached != null && (e.isNetwork || e.type == ApiErrorType.server)) {
      return CachedResult(parse(cached.data), savedAt: cached.savedAt, fromCache: true);
    }
    rethrow;
  }
}

/// Saved answer for [key] without touching the network (for instant display).
CachedResult<T>? peekCache<T>(LocalStore store, String key, T Function(dynamic json) parse) {
  final cached = store.readCache(key);
  if (cached == null) return null;
  try {
    return CachedResult(parse(cached.data), savedAt: cached.savedAt, fromCache: true);
  } catch (_) {
    return null;
  }
}
