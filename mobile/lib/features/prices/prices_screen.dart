import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_theme.dart';
import '../../core/utils/bangla.dart';
import '../../core/utils/nav.dart';
import '../../core/widgets/common.dart';
import '../../data/cached_fetch.dart';
import '../../data/price_repository.dart';
import '../../l10n/app_localizations.dart';
import '../../models/market.dart';
import '../../state/settings_controller.dart';
import 'price_history_screen.dart';
import 'unit_label.dart';

/// Today's prices at the nearest (or chosen) market, with trend arrows.
class PricesScreen extends StatefulWidget {
  const PricesScreen({super.key});

  @override
  State<PricesScreen> createState() => _PricesScreenState();
}

class _PricesScreenState extends State<PricesScreen> {
  List<Market> _markets = [];
  int? _marketId; // null = nearest to my district
  String _type = 'wholesale';
  CachedResult<PriceBoard>? _board;
  Object? _error;
  bool _loading = true;

  PriceRepository get _repo => context.read<PriceRepository>();
  int? get _districtId => context.read<SettingsController>().districtId;

  @override
  void initState() {
    super.initState();
    _board = _repo.peekBoard(marketId: _marketId, districtId: _districtId, type: _type);
    _load();
    _repo.markets().then((r) {
      if (mounted) setState(() => _markets = r.data);
    }).catchError((_) {});
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final board = await _repo.board(marketId: _marketId, districtId: _districtId, type: _type);
      if (mounted) setState(() => _board = board);
    } catch (e) {
      if (mounted) setState(() => _error = e);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;
    final board = _board?.data;

    return Scaffold(
      appBar: AppBar(title: Text(l10n.featurePrices)),
      body: Column(
        children: [
          if (_board?.fromCache == true && !_loading) OfflineNotice(savedAt: _board!.savedAt),
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
            child: Column(
              children: [
                DropdownButtonFormField<int?>(
                  initialValue: _marketId,
                  isExpanded: true,
                  decoration: InputDecoration(labelText: l10n.market, prefixIcon: const Icon(Icons.storefront)),
                  items: [
                    DropdownMenuItem<int?>(
                      value: null,
                      child: Text('📍 ${board?.market == null ? l10n.market : l10n.nearestMarket(board!.market!.name(lang))}'),
                    ),
                    for (final m in _markets)
                      DropdownMenuItem<int?>(value: m.id, child: Text('${m.name(lang)} – ${m.district(lang)}', overflow: TextOverflow.ellipsis)),
                  ],
                  onChanged: (v) {
                    setState(() {
                      _marketId = v;
                      _board = _repo.peekBoard(marketId: v, districtId: _districtId, type: _type);
                    });
                    _load();
                  },
                ),
                const SizedBox(height: 10),
                SizedBox(
                  width: double.infinity,
                  child: SegmentedButton<String>(
                    segments: [
                      ButtonSegment(value: 'wholesale', label: Text(l10n.wholesale), icon: const Icon(Icons.local_shipping_outlined)),
                      ButtonSegment(value: 'retail', label: Text(l10n.retail), icon: const Icon(Icons.shopping_basket_outlined)),
                    ],
                    selected: {_type},
                    onSelectionChanged: (s) {
                      setState(() {
                        _type = s.first;
                        _board = _repo.peekBoard(marketId: _marketId, districtId: _districtId, type: _type);
                      });
                      _load();
                    },
                  ),
                ),
              ],
            ),
          ),
          Expanded(child: _body(l10n, lang, board)),
        ],
      ),
    );
  }

  Widget _body(AppLocalizations l10n, String lang, PriceBoard? board) {
    if (board == null) {
      if (_loading) return const LoadingView();
      if (_error != null) return ErrorView(error: _error!, onRetry: _load);
    }
    if (board == null || board.items.isEmpty) {
      return RefreshIndicator(
        onRefresh: _load,
        child: ListView(children: [EmptyView(message: l10n.noPrices, icon: '🧺')]),
      );
    }

    final isDemo = board.items.any((i) => i.isDemo);
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 4, 16, 24),
        children: [
          if (_loading) const LinearProgressIndicator(),
          if (board.date != null)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 8),
              child: Text('${board.market?.name(lang) ?? ''} · ${l10n.pricesOfDate(Bangla.date(board.date!, lang))}',
                  style: Theme.of(context).textTheme.titleSmall),
            ),
          if (isDemo) ...[InfoBanner(level: 'warning', text: l10n.demoDataWarning), const SizedBox(height: 8)],
          Card(
            child: Column(
              children: [
                for (final item in board.items) ...[
                  _PriceTile(item: item, onTap: () {
                    if (board.market != null) {
                      push(context, PriceHistoryScreen(commodity: item.commodity, market: board.market!, type: _type));
                    }
                  }),
                  if (item != board.items.last) const Divider(height: 1, indent: 72),
                ],
              ],
            ),
          ),
          const SizedBox(height: 12),
          InfoBanner(icon: '💡', text: l10n.priceTip),
          if (!isDemo && board.items.first.source != null) ...[
            const SizedBox(height: 8),
            Text(l10n.source(board.items.first.source!), style: Theme.of(context).textTheme.bodySmall),
          ],
        ],
      ),
    );
  }
}

class _PriceTile extends StatelessWidget {
  const _PriceTile({required this.item, required this.onTap});
  final PriceItem item;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;
    final (IconData icon, Color color, String label) = switch (item.trend) {
      'up' => (Icons.trending_up, AppTheme.green, l10n.trendUp),
      'down' => (Icons.trending_down, AppTheme.danger, l10n.trendDown),
      _ => (Icons.trending_flat, Colors.grey.shade700, l10n.trendStable),
    };
    final range = item.min == item.max
        ? '৳${Bangla.number(item.min, lang)}'
        : '৳${Bangla.number(item.min, lang)}–${Bangla.number(item.max, lang)}';
    final pct = item.changePct == null ? '' : ' ${item.changePct! > 0 ? '+' : ''}${Bangla.number(item.changePct!, lang, decimals: 1)}%';

    return ListTile(
      onTap: onTap,
      leading: CircleAvatar(
        radius: 24,
        backgroundColor: const Color(0xFFF1F8E9),
        child: Text(item.commodity.icon ?? '🧺', style: const TextStyle(fontSize: 24)),
      ),
      title: Text(item.commodity.name(lang), style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 17)),
      subtitle: Text(l10n.perUnit(unitLabel(item.commodity.unit, l10n))),
      trailing: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          Text(range, style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w600)),
          Pill('$label$pct', color: color, icon: icon),
        ],
      ),
    );
  }
}
