import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';

import '../../core/network/api_exception.dart';
import '../../core/services/tts_service.dart';
import '../../core/services/voice_input_service.dart';
import '../../core/utils/bangla.dart';
import '../../core/utils/nav.dart';
import '../../core/widgets/common.dart';
import '../../core/widgets/crop_avatar.dart';
import '../../data/ai_repository.dart';
import '../../data/question_repository.dart';
import '../../l10n/app_localizations.dart';
import '../../models/ai.dart';
import '../../state/auth_controller.dart';
import '../../state/content_controller.dart';
import '../../state/settings_controller.dart';
import '../ask_expert/ask_expert_screen.dart';
import '../auth/login_screen.dart';
import 'ai_answer_card.dart';

/// AI crop doctor: photo (camera or gallery) and/or a question typed or
/// spoken in Bangla → an answer in seconds, read aloud when asked by voice,
/// with a one-tap hand-over to a human expert.
class AiDoctorScreen extends StatefulWidget {
  const AiDoctorScreen({super.key, this.cropId});
  final int? cropId;

  @override
  State<AiDoctorScreen> createState() => _AiDoctorScreenState();
}

sealed class _Turn {}

class _UserTurn extends _Turn {
  _UserTurn(this.text, this.photo);
  final String text;
  final Uint8List? photo;
}

class _ThinkingTurn extends _Turn {}

class _AnswerTurn extends _Turn {
  _AnswerTurn(this.message);
  final AiMessage message;
}

class _ErrorTurn extends _Turn {
  _ErrorTurn(this.message, {required this.offerExpert, required this.text, required this.photo});
  final String message;
  final bool offerExpert;
  final String text;
  final Uint8List? photo;
}

class _AiDoctorScreenState extends State<AiDoctorScreen> {
  final _text = TextEditingController();
  final _scroll = ScrollController();
  final List<_Turn> _turns = [];
  late int? _cropId = widget.cropId;
  Uint8List? _photo;
  int? _conversationId;
  int? _remaining;
  bool _sending = false;
  bool _usedVoice = false;
  bool? _rating;
  bool _escalated = false;
  bool _escalating = false;
  late final VoiceInput _voice;

  @override
  void initState() {
    super.initState();
    _voice = context.read<VoiceInput>();
  }

  @override
  void dispose() {
    _voice.stop();
    _text.dispose();
    _scroll.dispose();
    super.dispose();
  }

  void _scrollToEnd() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scroll.hasClients) {
        _scroll.animateTo(_scroll.position.maxScrollExtent, duration: const Duration(milliseconds: 300), curve: Curves.easeOut);
      }
    });
  }

  Future<void> _pick(ImageSource source) async {
    try {
      final file = await ImagePicker().pickImage(source: source, maxWidth: 1280, maxHeight: 1280, imageQuality: 75);
      if (file == null) return;
      final bytes = await file.readAsBytes();
      setState(() => _photo = bytes);
    } catch (_) {
      // Camera not available (e.g. desktop) – ignore.
    }
  }

  Future<void> _toggleVoice() async {
    final l10n = AppLocalizations.of(context);
    if (_voice.listening) {
      await _voice.stop();
      return;
    }
    final before = _text.text.trim();
    final ok = await _voice.start(
      language: context.read<SettingsController>().language,
      onText: (words, done) {
        if (!mounted) return;
        _text.text = [before, words].where((s) => s.isNotEmpty).join(' ');
        _text.selection = TextSelection.collapsed(offset: _text.text.length);
        _usedVoice = true;
      },
    );
    if (!ok && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(l10n.aiVoiceUnavailable)));
    }
  }

  Future<void> _send({String? retryText, Uint8List? retryPhoto}) async {
    final l10n = AppLocalizations.of(context);
    final text = retryText ?? _text.text.trim();
    final photo = retryText != null ? retryPhoto : _photo;
    if (text.isEmpty && photo == null) return;

    if (!context.read<AuthController>().isLoggedIn) {
      final ok = await push<bool>(context, const LoginScreen());
      if (ok != true || !mounted) return;
    }
    if (!mounted) return;
    await _voice.stop();
    if (!mounted) return;

    final speakAnswer = _usedVoice;
    final repo = context.read<AiRepository>();
    final tts = context.read<TtsService>();
    final lang = context.read<SettingsController>().language;
    setState(() {
      _turns.removeWhere((t) => t is _ErrorTurn);
      _turns
        ..add(_UserTurn(text, photo))
        ..add(_ThinkingTurn());
      _text.clear();
      _photo = null;
      _sending = true;
      _usedVoice = false;
      _rating = null;
    });
    _scrollToEnd();

    _Turn outcome;
    try {
      final response = await repo.ask(conversationId: _conversationId, text: text.isEmpty ? null : text, imageBytes: photo, cropId: _cropId);
      _conversationId = response.conversationId;
      _remaining = response.remainingToday;
      outcome = _AnswerTurn(response.message);
      final result = response.message.result;
      if (speakAnswer && result != null) {
        tts.speak('ai-${response.message.id}', result.speakText, lang).ignore();
      }
    } on ApiException catch (e) {
      final message = switch (e.type) {
        ApiErrorType.network => l10n.aiOffline,
        // 429 daily limit and 503 busy / switched off carry a clear server message.
        ApiErrorType.rateLimited || ApiErrorType.server => e.message ?? friendlyError(e, l10n),
        _ => friendlyError(e, l10n),
      };
      final offerExpert = e.type == ApiErrorType.network || e.type == ApiErrorType.server || e.type == ApiErrorType.rateLimited;
      outcome = _ErrorTurn(message, offerExpert: offerExpert, text: text, photo: photo);
    }
    if (!mounted) return;
    setState(() {
      _turns.removeWhere((t) => t is _ThinkingTurn);
      if (outcome is _ErrorTurn) _turns.removeLast(); // the user's turn goes back into the error card
      _turns.add(outcome);
      _sending = false;
    });
    _scrollToEnd();
  }

  /// No AI available: keep the question for a human expert (queued offline).
  Future<void> _saveForExpert(_ErrorTurn turn) async {
    final l10n = AppLocalizations.of(context);
    try {
      final result = await context.read<QuestionRepository>().ask(
            body: turn.text.length >= 5 ? turn.text : '${turn.text} ${l10n.aiPhotoOnlyQuestion}'.trim(),
            cropId: _cropId,
            imageBytes: turn.photo,
          );
      if (!mounted) return;
      setState(() => _turns.remove(turn));
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(result == AskResult.sent ? l10n.questionSent : l10n.questionQueued),
        duration: const Duration(seconds: 5),
      ));
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(friendlyError(e, l10n))));
    }
  }

  Future<void> _escalate() async {
    final id = _conversationId;
    if (id == null) return;
    final l10n = AppLocalizations.of(context);
    setState(() => _escalating = true);
    try {
      await context.read<AiRepository>().escalate(id);
      if (!mounted) return;
      setState(() => _escalated = true);
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(l10n.aiSentToExpert),
        duration: const Duration(seconds: 6),
        action: SnackBarAction(label: l10n.featureAskExpert, onPressed: () => push(context, const AskExpertScreen())),
      ));
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(friendlyError(e, l10n))));
    } finally {
      if (mounted) setState(() => _escalating = false);
    }
  }

  Future<void> _rate(bool helpful) async {
    final id = _conversationId;
    if (id == null) return;
    setState(() => _rating = helpful);
    try {
      await context.read<AiRepository>().rate(id, helpful: helpful);
    } catch (_) {
      // Feedback is best-effort.
    }
  }

  void _newProblem() {
    context.read<TtsService>().stop();
    setState(() {
      _turns.clear();
      _conversationId = null;
      _rating = null;
      _escalated = false;
      _photo = null;
      _text.clear();
    });
  }

  Future<void> _chooseCrop() async {
    final l10n = AppLocalizations.of(context);
    final lang = context.read<SettingsController>().language;
    final crops = context.read<ContentController>().crops;
    final picked = await showModalBottomSheet<int>(
      context: context,
      isScrollControlled: true,
      builder: (ctx) => SafeArea(
        child: ConstrainedBox(
          constraints: BoxConstraints(maxHeight: MediaQuery.of(ctx).size.height * 0.7),
          child: ListView(shrinkWrap: true, children: [
            ListTile(leading: const Text('💬', style: TextStyle(fontSize: 24)), title: Text(l10n.aiCropOptional), onTap: () => Navigator.pop(ctx, 0)),
            for (final c in crops)
              ListTile(leading: CropAvatar(c, size: 36), title: Text(c.name(lang)), selected: c.id == _cropId, onTap: () => Navigator.pop(ctx, c.id)),
          ]),
        ),
      ),
    );
    if (picked != null) setState(() => _cropId = picked == 0 ? null : picked);
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lastAnswer = _turns.lastWhere((t) => t is _AnswerTurn, orElse: () => _ThinkingTurn());

    return Scaffold(
      appBar: AppBar(
        title: Text(l10n.aiDoctorTitle),
        actions: [
          if (_turns.isNotEmpty && !_sending)
            IconButton(onPressed: _newProblem, icon: const Icon(Icons.add_a_photo_outlined), tooltip: l10n.aiNewQuestion),
        ],
      ),
      body: Column(
        children: [
          Expanded(
            child: _turns.isEmpty
                ? _Intro(cropId: _cropId, onChooseCrop: _chooseCrop)
                : ListView(
                    controller: _scroll,
                    padding: const EdgeInsets.fromLTRB(12, 12, 12, 24),
                    children: [
                      for (final turn in _turns)
                        Padding(
                          padding: const EdgeInsets.only(bottom: 12),
                          child: switch (turn) {
                            _UserTurn() => _UserBubble(turn: turn),
                            _ThinkingTurn() => const _Thinking(),
                            _AnswerTurn() => AiAnswerCard(
                                message: turn.message,
                                isLatest: identical(turn, lastAnswer),
                                onEscalate: _escalate,
                                onRate: _rate,
                                rating: _rating,
                                escalated: _escalated,
                                escalating: _escalating,
                              ),
                            _ErrorTurn() => _ErrorCard(
                                turn: turn,
                                onRetry: () => _send(retryText: turn.text, retryPhoto: turn.photo),
                                onSaveForExpert: () => _saveForExpert(turn),
                              ),
                          },
                        ),
                      if (_remaining != null && !_sending)
                        Text(l10n.aiRemaining(Bangla.digits('$_remaining', context.watch<SettingsController>().language)),
                            textAlign: TextAlign.center, style: Theme.of(context).textTheme.bodySmall),
                    ],
                  ),
          ),
          _Composer(
            controller: _text,
            photo: _photo,
            sending: _sending,
            followUp: _turns.isNotEmpty,
            onCamera: () => _pick(ImageSource.camera),
            onGallery: () => _pick(ImageSource.gallery),
            onRemovePhoto: () => setState(() => _photo = null),
            onVoice: _toggleVoice,
            onSend: _send,
          ),
        ],
      ),
    );
  }
}

class _Intro extends StatelessWidget {
  const _Intro({required this.cropId, required this.onChooseCrop});
  final int? cropId;
  final VoidCallback onChooseCrop;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;
    final crop = context.watch<ContentController>().kb?.cropById(cropId);
    final theme = Theme.of(context);

    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        const Center(child: Text('🩺🌿', style: TextStyle(fontSize: 56))),
        const SizedBox(height: 12),
        Text(l10n.aiIntroTitle, textAlign: TextAlign.center, style: theme.textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.w700)),
        const SizedBox(height: 8),
        Text(l10n.aiIntroText, textAlign: TextAlign.center, style: theme.textTheme.bodyLarge),
        const SizedBox(height: 16),
        Center(
          child: ActionChip(
            avatar: crop == null ? const Text('🌱') : CropAvatar(crop, size: 24),
            label: Text(crop == null ? l10n.aiCropOptional : crop.name(lang)),
            onPressed: onChooseCrop,
          ),
        ),
        const SizedBox(height: 20),
        InfoBanner(icon: 'ℹ️', text: l10n.aiDisclaimer),
        if (!context.watch<AuthController>().isLoggedIn) ...[
          const SizedBox(height: 10),
          InfoBanner(icon: '🔐', text: l10n.aiLoginNeeded),
        ],
      ],
    );
  }
}

class _UserBubble extends StatelessWidget {
  const _UserBubble({required this.turn});
  final _UserTurn turn;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Align(
      alignment: Alignment.centerRight,
      child: ConstrainedBox(
        constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width * 0.8),
        child: Container(
          padding: const EdgeInsets.all(10),
          decoration: BoxDecoration(color: theme.colorScheme.primaryContainer, borderRadius: BorderRadius.circular(16)),
          child: Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
            if (turn.photo != null)
              ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: Image.memory(turn.photo!, height: 180, fit: BoxFit.cover),
              ),
            if (turn.photo != null && turn.text.isNotEmpty) const SizedBox(height: 8),
            if (turn.text.isNotEmpty) Text(turn.text, style: theme.textTheme.bodyLarge),
          ]),
        ),
      ),
    );
  }
}

class _Thinking extends StatelessWidget {
  const _Thinking();

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(children: [
          const SizedBox(width: 28, height: 28, child: CircularProgressIndicator(strokeWidth: 3)),
          const SizedBox(width: 14),
          Expanded(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('🔍 ${l10n.aiThinking}', style: Theme.of(context).textTheme.titleMedium),
              Text(l10n.aiThinkingHint, style: Theme.of(context).textTheme.bodySmall),
            ]),
          ),
        ]),
      ),
    );
  }
}

class _ErrorCard extends StatelessWidget {
  const _ErrorCard({required this.turn, required this.onRetry, required this.onSaveForExpert});
  final _ErrorTurn turn;
  final VoidCallback onRetry;
  final VoidCallback onSaveForExpert;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
      _UserBubble(turn: _UserTurn(turn.text, turn.photo)),
      const SizedBox(height: 8),
      InfoBanner(level: 'danger', text: turn.message),
      const SizedBox(height: 8),
      Wrap(alignment: WrapAlignment.end, spacing: 8, runSpacing: 8, children: [
        OutlinedButton.icon(onPressed: onRetry, icon: const Icon(Icons.refresh), label: Text(l10n.retry)),
        if (turn.offerExpert) FilledButton.icon(onPressed: onSaveForExpert, icon: const Icon(Icons.support_agent), label: Text(l10n.aiSaveForExpert)),
      ]),
    ]);
  }
}

class _Composer extends StatelessWidget {
  const _Composer({
    required this.controller,
    required this.photo,
    required this.sending,
    required this.followUp,
    required this.onCamera,
    required this.onGallery,
    required this.onRemovePhoto,
    required this.onVoice,
    required this.onSend,
  });

  final TextEditingController controller;
  final Uint8List? photo;
  final bool sending;
  final bool followUp;
  final VoidCallback onCamera;
  final VoidCallback onGallery;
  final VoidCallback onRemovePhoto;
  final VoidCallback onVoice;
  final VoidCallback onSend;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final voice = context.watch<VoiceInput>();
    final theme = Theme.of(context);
    final big = ButtonStyle(minimumSize: WidgetStateProperty.all(const Size(0, 52)), padding: WidgetStateProperty.all(const EdgeInsets.symmetric(horizontal: 6)));

    return Material(
      elevation: 8,
      color: theme.colorScheme.surface,
      child: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(12, 10, 12, 10),
          child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.stretch, children: [
            if (photo != null)
              Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: Row(children: [
                  ClipRRect(borderRadius: BorderRadius.circular(8), child: Image.memory(photo!, width: 56, height: 56, fit: BoxFit.cover)),
                  const SizedBox(width: 10),
                  Expanded(child: Text('📷 ${l10n.aiPhotoAdded}')),
                  IconButton(onPressed: sending ? null : onRemovePhoto, icon: const Icon(Icons.close), tooltip: l10n.removePhoto),
                ]),
              ),
            ListenableBuilder(
              listenable: controller,
              builder: (context, _) => TextField(
                controller: controller,
                enabled: !sending,
                minLines: 1,
                maxLines: 4,
                maxLength: 1000,
                textInputAction: TextInputAction.newline,
                decoration: InputDecoration(
                  hintText: voice.listening ? l10n.aiListening : (followUp ? l10n.aiFollowUpHint : l10n.aiInputHint),
                  counterText: '',
                  isDense: true,
                  suffixIcon: controller.text.isEmpty ? null : IconButton(icon: const Icon(Icons.clear), onPressed: sending ? null : controller.clear),
                ),
              ),
            ),
            const SizedBox(height: 8),
            Row(children: [
              Expanded(child: OutlinedButton.icon(style: big, onPressed: sending ? null : onCamera, icon: const Icon(Icons.photo_camera), label: Text(l10n.camera))),
              const SizedBox(width: 6),
              Expanded(child: OutlinedButton.icon(style: big, onPressed: sending ? null : onGallery, icon: const Icon(Icons.photo_library), label: Text(l10n.gallery))),
              if (voice.supported) ...[
                const SizedBox(width: 6),
                Expanded(
                  child: voice.listening
                      ? FilledButton.icon(
                          style: big.copyWith(backgroundColor: WidgetStateProperty.all(theme.colorScheme.error)),
                          onPressed: onVoice,
                          icon: const Icon(Icons.stop),
                          label: Text(l10n.aiStopListening))
                      : OutlinedButton.icon(style: big, onPressed: sending ? null : onVoice, icon: const Icon(Icons.mic), label: Text(l10n.aiSpeak)),
                ),
              ],
            ]),
            const SizedBox(height: 8),
            ListenableBuilder(
              listenable: controller,
              builder: (context, _) => FilledButton.icon(
                style: big,
                onPressed: sending || (controller.text.trim().isEmpty && photo == null) ? null : onSend,
                icon: sending ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2)) : const Icon(Icons.send),
                label: Text(l10n.send),
              ),
            ),
          ]),
        ),
      ),
    );
  }
}
