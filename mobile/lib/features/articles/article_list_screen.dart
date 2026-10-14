import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/utils/nav.dart';
import '../../core/widgets/bullet_text.dart';
import '../../core/widgets/common.dart';
import '../../core/widgets/net_image.dart';
import '../../core/widgets/speak_button.dart';
import '../../l10n/app_localizations.dart';
import '../../models/content.dart';
import '../../state/content_controller.dart';
import '../../state/settings_controller.dart';

/// Guides and tips; gardeners see gardening guides first, farmers farm guides.
class ArticleListScreen extends StatelessWidget {
  const ArticleListScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final settings = context.watch<SettingsController>();
    final lang = settings.language;
    final mine = settings.isGardener ? 'gardener' : 'farmer';
    final articles = [...context.watch<ContentController>().kb?.articles ?? <Article>[]]
      ..sort((a, b) => (a.audience == mine ? 0 : 1).compareTo(b.audience == mine ? 0 : 1));

    return Scaffold(
      appBar: AppBar(title: Text(l10n.featureArticles)),
      body: articles.isEmpty
          ? EmptyView(message: l10n.nothingThisMonth, icon: '📖')
          : ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: articles.length,
              separatorBuilder: (_, _) => const SizedBox(height: 12),
              itemBuilder: (context, i) {
                final a = articles[i];
                return Card(
                  clipBehavior: Clip.antiAlias,
                  child: ListTile(
                    contentPadding: const EdgeInsets.all(12),
                    leading: Text(a.icon ?? '📄', style: const TextStyle(fontSize: 34)),
                    title: Text(a.title(lang), style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 17)),
                    subtitle: Text(a.summary(lang), maxLines: 2, overflow: TextOverflow.ellipsis),
                    onTap: () => push(context, ArticleScreen(article: a)),
                  ),
                );
              },
            ),
    );
  }
}

class ArticleScreen extends StatelessWidget {
  const ArticleScreen({super.key, required this.article});
  final Article article;

  @override
  Widget build(BuildContext context) {
    final lang = context.watch<SettingsController>().language;
    return Scaffold(
      appBar: AppBar(title: Text(article.title(lang))),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          if (article.imageUrl != null) ...[NetImage(article.imageUrl!), const SizedBox(height: 12)],
          Text('${article.icon ?? ''} ${article.title(lang)}', style: Theme.of(context).textTheme.titleLarge),
          if (article.summary(lang).isNotEmpty) ...[
            const SizedBox(height: 6),
            Text(article.summary(lang), style: Theme.of(context).textTheme.bodyLarge?.copyWith(fontStyle: FontStyle.italic)),
          ],
          const SizedBox(height: 12),
          SpeakButton(id: 'article-${article.id}', text: '${article.title(lang)}\n${article.body(lang)}'),
          const SizedBox(height: 12),
          BulletText(article.body(lang)),
        ],
      ),
    );
  }
}
