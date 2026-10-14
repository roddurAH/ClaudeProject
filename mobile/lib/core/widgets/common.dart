import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../l10n/app_localizations.dart';
import '../../state/settings_controller.dart';
import '../network/api_exception.dart';
import '../theme/app_theme.dart';
import '../utils/relative_time.dart';

/// Turns any error into a short message the user understands.
String friendlyError(Object error, AppLocalizations l10n) {
  if (error is ApiException) {
    return switch (error.type) {
      ApiErrorType.network => l10n.errorNetwork,
      ApiErrorType.server => l10n.errorServer,
      ApiErrorType.rateLimited => error.firstMessage ?? l10n.errorTooMany,
      ApiErrorType.unauthorized || ApiErrorType.forbidden => l10n.errorSessionExpired,
      ApiErrorType.validation => error.firstMessage ?? l10n.errorGeneric,
      _ => l10n.errorGeneric,
    };
  }
  return l10n.errorGeneric;
}

class SectionHeader extends StatelessWidget {
  const SectionHeader(this.title, {super.key, this.trailing, this.icon});
  final String title;
  final Widget? trailing;
  final String? icon;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(4, 16, 4, 8),
      child: Row(
        children: [
          if (icon != null) Padding(padding: const EdgeInsets.only(right: 8), child: Text(icon!, style: const TextStyle(fontSize: 20))),
          Expanded(child: Text(title, style: Theme.of(context).textTheme.titleMedium)),
          ?trailing,
        ],
      ),
    );
  }
}

/// Coloured box for warnings, tips and alerts.
class InfoBanner extends StatelessWidget {
  const InfoBanner({super.key, required this.text, this.title, this.level = 'info', this.icon, this.trailing});
  final String text;
  final String? title;
  final String level;
  final String? icon;
  final Widget? trailing;

  @override
  Widget build(BuildContext context) {
    final color = AppTheme.levelColor(level);
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.08),
        border: Border.all(color: color.withValues(alpha: 0.4)),
        borderRadius: BorderRadius.circular(14),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(icon ?? (level == 'info' ? 'ℹ️' : '⚠️'), style: const TextStyle(fontSize: 22)),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (title != null)
                  Text(title!, style: Theme.of(context).textTheme.titleSmall?.copyWith(color: color, fontWeight: FontWeight.w600, fontSize: 16)),
                if (title != null) const SizedBox(height: 2),
                Text(text, style: Theme.of(context).textTheme.bodyMedium),
              ],
            ),
          ),
          ?trailing,
        ],
      ),
    );
  }
}

/// "No internet – showing saved information (2 hours ago)".
class OfflineNotice extends StatelessWidget {
  const OfflineNotice({super.key, required this.savedAt});
  final DateTime? savedAt;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;
    return Container(
      width: double.infinity,
      color: Colors.amber.shade100,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Row(
        children: [
          const Icon(Icons.wifi_off, size: 18),
          const SizedBox(width: 8),
          Expanded(child: Text(l10n.offlineNotice(relativeTime(savedAt, l10n, lang)), style: const TextStyle(fontSize: 14))),
        ],
      ),
    );
  }
}

class LoadingView extends StatelessWidget {
  const LoadingView({super.key});

  @override
  Widget build(BuildContext context) => const Center(child: Padding(padding: EdgeInsets.all(32), child: CircularProgressIndicator()));
}

class EmptyView extends StatelessWidget {
  const EmptyView({super.key, required this.message, this.icon = '🌱', this.action});
  final String message;
  final String icon;
  final Widget? action;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(icon, style: const TextStyle(fontSize: 56)),
            const SizedBox(height: 12),
            Text(message, textAlign: TextAlign.center, style: Theme.of(context).textTheme.bodyLarge),
            if (action != null) ...[const SizedBox(height: 16), action!],
          ],
        ),
      ),
    );
  }
}

class ErrorView extends StatelessWidget {
  const ErrorView({super.key, required this.error, required this.onRetry});
  final Object error;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final isNetwork = error is ApiException && (error as ApiException).isNetwork;
    return EmptyView(
      icon: isNetwork ? '📶' : '⚠️',
      message: friendlyError(error, l10n),
      action: FilledButton.icon(onPressed: onRetry, icon: const Icon(Icons.refresh), label: Text(l10n.retry)),
    );
  }
}

/// Coloured pill, e.g. "Answered", "Rising".
class Pill extends StatelessWidget {
  const Pill(this.text, {super.key, required this.color, this.icon});
  final String text;
  final Color color;
  final IconData? icon;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
      decoration: BoxDecoration(color: color.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(20)),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (icon != null) Icon(icon, size: 14, color: color),
          if (icon != null) const SizedBox(width: 4),
          Text(text, style: TextStyle(color: color, fontWeight: FontWeight.w600, fontSize: 13)),
        ],
      ),
    );
  }
}
