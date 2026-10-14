import '../../l10n/app_localizations.dart';
import 'bangla.dart';

/// "just now", "5 min ago", "3 hours ago", "2 days ago" in the user's language.
String relativeTime(DateTime? time, AppLocalizations l10n, String lang) {
  if (time == null) return '';
  final diff = DateTime.now().difference(time);
  String n(int v) => Bangla.digits('$v', lang);
  if (diff.inMinutes < 1) return l10n.justNow;
  if (diff.inHours < 1) return l10n.minutesAgo(n(diff.inMinutes));
  if (diff.inDays < 1) return l10n.hoursAgo(n(diff.inHours));
  return l10n.daysAgo(n(diff.inDays));
}
