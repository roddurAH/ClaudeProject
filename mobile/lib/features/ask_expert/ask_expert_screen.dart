import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/utils/bangla.dart';
import '../../core/utils/nav.dart';
import '../../core/widgets/common.dart';
import '../../data/cached_fetch.dart';
import '../../data/question_repository.dart';
import '../../l10n/app_localizations.dart';
import '../../models/question.dart';
import '../../state/auth_controller.dart';
import '../../state/reference_controller.dart';
import '../../state/settings_controller.dart';
import '../auth/login_screen.dart';
import 'new_question_screen.dart';
import 'question_detail_screen.dart';

/// "My questions" (needs login) and public expert answers (no login).
class AskExpertScreen extends StatefulWidget {
  const AskExpertScreen({super.key});

  @override
  State<AskExpertScreen> createState() => _AskExpertScreenState();
}

class _AskExpertScreenState extends State<AskExpertScreen> {
  int _reload = 0;

  Future<void> _ask() async {
    final auth = context.read<AuthController>();
    if (!auth.isLoggedIn) {
      final ok = await push<bool>(context, const LoginScreen());
      if (ok != true || !mounted) return;
    }
    await push(context, const NewQuestionScreen());
    if (mounted) setState(() => _reload++);
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;
    final helpline = context.watch<ReferenceController>().helpline;

    return DefaultTabController(
      length: 2,
      child: Scaffold(
        appBar: AppBar(
          title: Text(l10n.featureAskExpert),
          bottom: TabBar(
            labelColor: Colors.white,
            unselectedLabelColor: Colors.white70,
            indicatorColor: Colors.white,
            labelStyle: const TextStyle(fontFamily: 'HindSiliguri', fontSize: 16, fontWeight: FontWeight.w600),
            tabs: [Tab(text: l10n.myQuestions), Tab(text: l10n.communityAnswers)],
          ),
        ),
        floatingActionButton: FloatingActionButton.extended(
          onPressed: _ask,
          icon: const Icon(Icons.add_comment),
          label: Text(l10n.askQuestion, style: const TextStyle(fontSize: 17)),
        ),
        body: Column(
          children: [
            Material(
              color: Colors.brown.shade50,
              child: ListTile(
                leading: const Icon(Icons.call, color: Colors.brown),
                title: Text(l10n.urgentCall(Bangla.digits(helpline, lang))),
                onTap: () => callNumber(helpline),
              ),
            ),
            Expanded(
              child: TabBarView(children: [
                _MyQuestions(key: ValueKey('mine$_reload'), onAsk: _ask),
                const _Community(),
              ]),
            ),
          ],
        ),
      ),
    );
  }
}

class _MyQuestions extends StatefulWidget {
  const _MyQuestions({super.key, required this.onAsk});
  final VoidCallback onAsk;

  @override
  State<_MyQuestions> createState() => _MyQuestionsState();
}

class _MyQuestionsState extends State<_MyQuestions> {
  CachedResult<List<Question>>? _result;
  Object? _error;
  bool _loading = false;

  @override
  void initState() {
    super.initState();
    if (context.read<AuthController>().isLoggedIn) {
      _result = context.read<QuestionRepository>().peekMine();
      _load();
    }
  }

  Future<void> _load() async {
    final repo = context.read<QuestionRepository>();
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      await repo.flushOutbox();
      final result = await repo.mine();
      if (mounted) setState(() => _result = result);
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
    final auth = context.watch<AuthController>();
    final pending = context.read<QuestionRepository>().pending();

    if (!auth.isLoggedIn) {
      return EmptyView(message: l10n.loginNeeded, icon: '🔐', action: FilledButton(onPressed: widget.onAsk, child: Text(l10n.login)));
    }
    final questions = _result?.data;
    if (questions == null) {
      if (_error != null) return ErrorView(error: _error!, onRetry: _load);
      return const LoadingView();
    }

    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 96),
        children: [
          if (_loading) const LinearProgressIndicator(),
          if (_result!.fromCache) OfflineNotice(savedAt: _result!.savedAt),
          if (pending.isNotEmpty)
            Padding(
              padding: const EdgeInsets.only(bottom: 10),
              child: InfoBanner(
                icon: '📤',
                text: l10n.pendingUploads(Bangla.digits('${pending.length}', lang)),
                trailing: TextButton(onPressed: _load, child: Text(l10n.sendNow)),
              ),
            ),
          if (questions.isEmpty && pending.isEmpty) EmptyView(message: l10n.noQuestionsYet, icon: '💬'),
          for (final q in questions) QuestionCard(question: q),
        ],
      ),
    );
  }
}

class _Community extends StatefulWidget {
  const _Community();

  @override
  State<_Community> createState() => _CommunityState();
}

class _CommunityState extends State<_Community> {
  late Future<CachedResult<List<Question>>> _future = context.read<QuestionRepository>().community();

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return FutureBuilder<CachedResult<List<Question>>>(
      future: _future,
      builder: (context, snap) {
        if (snap.connectionState != ConnectionState.done) return const LoadingView();
        if (snap.hasError) {
          return ErrorView(error: snap.error!, onRetry: () => setState(() => _future = context.read<QuestionRepository>().community()));
        }
        final questions = snap.data!.data;
        if (questions.isEmpty) return EmptyView(message: l10n.noCommunityAnswers, icon: '💬');
        return RefreshIndicator(
          onRefresh: () async => setState(() => _future = context.read<QuestionRepository>().community()),
          child: ListView(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 96),
            children: [
              if (snap.data!.fromCache) OfflineNotice(savedAt: snap.data!.savedAt),
              for (final q in questions) QuestionCard(question: q),
            ],
          ),
        );
      },
    );
  }
}

class QuestionCard extends StatelessWidget {
  const QuestionCard({super.key, required this.question});
  final Question question;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;
    final q = question;
    final (String status, Color color) = switch (q.status) {
      'answered' => (l10n.statusAnswered, Colors.green.shade700),
      'closed' => (l10n.statusClosed, Colors.grey),
      _ => (l10n.statusPending, Colors.orange.shade800),
    };

    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Card(
        clipBehavior: Clip.antiAlias,
        child: InkWell(
          onTap: () => push(context, QuestionDetailScreen(question: q)),
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(children: [
                  Text(q.cropIcon ?? '💬', style: const TextStyle(fontSize: 22)),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      [if (q.cropName(lang).isNotEmpty) q.cropName(lang), if (q.district(lang).isNotEmpty) q.district(lang)].join(' · '),
                      style: const TextStyle(fontWeight: FontWeight.w600),
                    ),
                  ),
                  Pill(status, color: color),
                ]),
                const SizedBox(height: 8),
                Text(q.body, maxLines: 3, overflow: TextOverflow.ellipsis),
                if (q.answers.isNotEmpty) ...[
                  const Divider(height: 20),
                  Text('✅ ${q.answers.first.body}', maxLines: 3, overflow: TextOverflow.ellipsis, style: TextStyle(color: Colors.green.shade900)),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}
