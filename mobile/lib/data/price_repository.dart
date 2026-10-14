import '../core/network/api_client.dart';
import '../core/storage/local_store.dart';
import '../models/market.dart';
import 'cached_fetch.dart';

class PriceRepository {
  PriceRepository(this._api, this._store);

  final ApiClient _api;
  final LocalStore _store;

  static List<Market> _parseMarkets(dynamic json) =>
      ((json as Map)['data'] as List).map((e) => Market.fromJson(e as Map<String, dynamic>)).toList();

  Future<CachedResult<List<Market>>> markets() => cachedFetch(
        store: _store,
        key: 'markets',
        fetch: () async => (await _api.get('/markets')).data,
        parse: _parseMarkets,
      );

  String _boardKey(int? marketId, int? districtId, String type) => 'prices:${marketId ?? 'd$districtId'}:$type';

  CachedResult<PriceBoard>? peekBoard({int? marketId, int? districtId, String type = 'wholesale'}) =>
      peekCache(_store, _boardKey(marketId, districtId, type), (j) => PriceBoard.fromJson(j as Map<String, dynamic>));

  /// Price board for a market, or for the market nearest to [districtId].
  Future<CachedResult<PriceBoard>> board({int? marketId, int? districtId, String type = 'wholesale'}) => cachedFetch(
        store: _store,
        key: _boardKey(marketId, districtId, type),
        fetch: () async => (await _api.get('/prices', query: {
          'market_id': marketId,
          'district_id': marketId == null ? districtId : null,
          'type': type,
        }))
            .data,
        parse: (j) => PriceBoard.fromJson(j as Map<String, dynamic>),
      );

  Future<CachedResult<List<PricePoint>>> history({required int commodityId, required int marketId, String type = 'wholesale', int days = 30}) =>
      cachedFetch(
        store: _store,
        key: 'history:$commodityId:$marketId:$type:$days',
        fetch: () async => (await _api.get('/prices/history', query: {
          'commodity_id': commodityId,
          'market_id': marketId,
          'type': type,
          'days': days,
        }))
            .data,
        parse: (j) => ((j as Map)['data'] as List).map((e) => PricePoint.fromJson(e as Map<String, dynamic>)).toList(),
      );
}
