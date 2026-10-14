import '../core/network/api_client.dart';
import '../core/storage/local_store.dart';
import '../models/weather.dart';
import 'cached_fetch.dart';

class WeatherRepository {
  WeatherRepository(this._api, this._store);

  final ApiClient _api;
  final LocalStore _store;

  CachedResult<Forecast>? peekForecast(int districtId) =>
      peekCache(_store, 'weather:$districtId', (j) => Forecast.fromJson(j as Map<String, dynamic>));

  Future<CachedResult<Forecast>> forecast(int districtId) => cachedFetch(
        store: _store,
        key: 'weather:$districtId',
        fetch: () async => (await _api.get('/weather', query: {'district_id': districtId})).data,
        parse: (j) => Forecast.fromJson(j as Map<String, dynamic>),
      );

  static List<FarmAlert> _parseAlerts(dynamic j) =>
      ((j as Map)['data'] as List).map((e) => FarmAlert.fromJson(e as Map<String, dynamic>)).toList();

  CachedResult<List<FarmAlert>>? peekAlerts(int? districtId) => peekCache(_store, 'alerts:$districtId', _parseAlerts);

  Future<CachedResult<List<FarmAlert>>> alerts(int? districtId) => cachedFetch(
        store: _store,
        key: 'alerts:$districtId',
        fetch: () async => (await _api.get('/alerts', query: {'district_id': districtId})).data,
        parse: _parseAlerts,
      );
}
