import 'dart:math' as math;

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_theme.dart';
import '../../core/utils/bangla.dart';
import '../../core/widgets/common.dart';
import '../../data/cached_fetch.dart';
import '../../data/price_repository.dart';
import '../../l10n/app_localizations.dart';
import '../../models/market.dart';
import '../../state/settings_controller.dart';
import 'unit_label.dart';

/// 30-day price chart for one commodity – helps decide when and where to sell.
class PriceHistoryScreen extends StatefulWidget {
  const PriceHistoryScreen({super.key, required this.commodity, required this.market, required this.type});
  final Commodity commodity;
  final Market market;
  final String type;

  @override
  State<PriceHistoryScreen> createState() => _PriceHistoryScreenState();
}

class _PriceHistoryScreenState extends State<PriceHistoryScreen> {
  late Future<CachedResult<List<PricePoint>>> _future = _fetch();

  Future<CachedResult<List<PricePoint>>> _fetch() => context
      .read<PriceRepository>()
      .history(commodityId: widget.commodity.id, marketId: widget.market.id, type: widget.type);

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;
    final unit = unitLabel(widget.commodity.unit, l10n);

    return Scaffold(
      appBar: AppBar(title: Text('${widget.commodity.icon ?? ''} ${widget.commodity.name(lang)}')),
      body: FutureBuilder<CachedResult<List<PricePoint>>>(
        future: _future,
        builder: (context, snap) {
          if (snap.connectionState != ConnectionState.done) return const LoadingView();
          if (snap.hasError) return ErrorView(error: snap.error!, onRetry: () => setState(() => _future = _fetch()));
          final points = snap.data!.data;
          if (points.isEmpty) return EmptyView(message: l10n.noPrices, icon: '📉');

          final highest = points.map((p) => p.max).reduce(math.max);
          final lowest = points.map((p) => p.min).reduce(math.min);
          final average = points.map((p) => p.avg).reduce((a, b) => a + b) / points.length;
          String money(double v) => '৳${Bangla.number(v, lang)}';

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              if (snap.data!.fromCache) ...[OfflineNotice(savedAt: snap.data!.savedAt), const SizedBox(height: 8)],
              Text('${widget.market.name(lang)} · ${widget.type == 'retail' ? l10n.retail : l10n.wholesale} · ${l10n.perUnit(unit)}',
                  style: Theme.of(context).textTheme.titleSmall),
              const SizedBox(height: 12),
              Card(
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(12, 16, 12, 8),
                  child: Column(children: [
                    Text(l10n.last30Days, style: Theme.of(context).textTheme.titleMedium),
                    const SizedBox(height: 8),
                    SizedBox(height: 200, child: CustomPaint(painter: _PriceChartPainter(points), size: Size.infinite)),
                    Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                      Text(Bangla.shortDate(points.first.date, lang), style: const TextStyle(fontSize: 12)),
                      Text(Bangla.shortDate(points.last.date, lang), style: const TextStyle(fontSize: 12)),
                    ]),
                  ]),
                ),
              ),
              const SizedBox(height: 12),
              Row(children: [
                _Stat(label: l10n.highest, value: money(highest), color: AppTheme.green),
                const SizedBox(width: 8),
                _Stat(label: l10n.average, value: money(average), color: AppTheme.info),
                const SizedBox(width: 8),
                _Stat(label: l10n.lowest, value: money(lowest), color: AppTheme.danger),
              ]),
              const SizedBox(height: 16),
              Card(
                child: Column(children: [
                  for (final p in points.reversed.take(10))
                    ListTile(
                      dense: true,
                      title: Text(Bangla.date(p.date, lang)),
                      trailing: Text('${money(p.min)}–${Bangla.number(p.max, lang)}', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15)),
                    ),
                ]),
              ),
            ],
          );
        },
      ),
    );
  }
}

class _Stat extends StatelessWidget {
  const _Stat({required this.label, required this.value, required this.color});
  final String label, value;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Card(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
          child: Column(children: [
            Text(label, style: const TextStyle(fontSize: 13)),
            const SizedBox(height: 4),
            FittedBox(child: Text(value, style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600, color: color))),
          ]),
        ),
      ),
    );
  }
}

/// Min–max band with the average line. Hand-drawn to avoid a chart library.
class _PriceChartPainter extends CustomPainter {
  _PriceChartPainter(this.points);
  final List<PricePoint> points;

  @override
  void paint(Canvas canvas, Size size) {
    if (points.length < 2) return;
    final lo = points.map((p) => p.min).reduce(math.min);
    final hi = points.map((p) => p.max).reduce(math.max);
    final span = (hi - lo).abs() < 0.01 ? 1.0 : hi - lo;
    double x(int i) => i / (points.length - 1) * size.width;
    double y(double v) => size.height - ((v - lo) / span) * (size.height - 16) - 8;

    final grid = Paint()
      ..color = Colors.grey.shade300
      ..strokeWidth = 1;
    for (var i = 0; i <= 3; i++) {
      final gy = 8 + i * (size.height - 16) / 3;
      canvas.drawLine(Offset(0, gy), Offset(size.width, gy), grid);
    }

    final band = Path()..moveTo(x(0), y(points.first.max));
    for (var i = 1; i < points.length; i++) {
      band.lineTo(x(i), y(points[i].max));
    }
    for (var i = points.length - 1; i >= 0; i--) {
      band.lineTo(x(i), y(points[i].min));
    }
    band.close();
    canvas.drawPath(band, Paint()..color = AppTheme.green.withValues(alpha: 0.15));

    final line = Path()..moveTo(x(0), y(points.first.avg));
    for (var i = 1; i < points.length; i++) {
      line.lineTo(x(i), y(points[i].avg));
    }
    canvas.drawPath(
      line,
      Paint()
        ..color = AppTheme.green
        ..strokeWidth = 3
        ..style = PaintingStyle.stroke
        ..strokeJoin = StrokeJoin.round,
    );
    canvas.drawCircle(Offset(x(points.length - 1), y(points.last.avg)), 5, Paint()..color = AppTheme.darkGreen);
  }

  @override
  bool shouldRepaint(covariant _PriceChartPainter old) => old.points != points;
}
