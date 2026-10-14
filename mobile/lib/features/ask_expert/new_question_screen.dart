import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';

import '../../core/utils/nav.dart';
import '../../core/widgets/common.dart';
import '../../data/question_repository.dart';
import '../../l10n/app_localizations.dart';
import '../../state/auth_controller.dart';
import '../../state/content_controller.dart';
import '../../state/settings_controller.dart';
import '../auth/login_screen.dart';

/// Write the problem, optionally attach a photo. Photos are shrunk on the
/// phone (max 1280 px, JPEG 70%) so they upload quickly on slow networks.
class NewQuestionScreen extends StatefulWidget {
  const NewQuestionScreen({super.key, this.initialCropId});
  final int? initialCropId;

  @override
  State<NewQuestionScreen> createState() => _NewQuestionScreenState();
}

class _NewQuestionScreenState extends State<NewQuestionScreen> {
  final _body = TextEditingController();
  late int? _cropId = widget.initialCropId;
  Uint8List? _photo;
  bool _busy = false;
  String? _error;

  Future<void> _pick(ImageSource source) async {
    try {
      final file = await ImagePicker().pickImage(source: source, maxWidth: 1280, maxHeight: 1280, imageQuality: 70);
      if (file == null) return;
      final bytes = await file.readAsBytes();
      setState(() => _photo = bytes);
    } catch (_) {
      // Camera not available (e.g. desktop) – ignore.
    }
  }

  Future<void> _send() async {
    final l10n = AppLocalizations.of(context);
    if (_body.text.trim().length < 5) {
      setState(() => _error = l10n.questionTooShort);
      return;
    }
    if (!context.read<AuthController>().isLoggedIn) {
      final ok = await push<bool>(context, const LoginScreen());
      if (ok != true || !mounted) return;
    }
    setState(() {
      _busy = true;
      _error = null;
    });
    try {
      final result = await context.read<QuestionRepository>().ask(body: _body.text.trim(), cropId: _cropId, imageBytes: _photo);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(result == AskResult.sent ? l10n.questionSent : l10n.questionQueued),
        duration: const Duration(seconds: 5),
      ));
      Navigator.pop(context, true);
    } catch (e) {
      setState(() => _error = friendlyError(e, l10n));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  void dispose() {
    _body.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;
    final crops = context.watch<ContentController>().crops;

    return Scaffold(
      appBar: AppBar(title: Text(l10n.askQuestion)),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          DropdownButtonFormField<int?>(
            initialValue: _cropId,
            isExpanded: true,
            decoration: InputDecoration(labelText: l10n.cropOptional),
            items: [
              DropdownMenuItem<int?>(value: null, child: Text('💬 ${l10n.noCropSelected}')),
              for (final c in crops) DropdownMenuItem<int?>(value: c.id, child: Text('${c.emoji} ${c.name(lang)}')),
            ],
            onChanged: (v) => setState(() => _cropId = v),
          ),
          const SizedBox(height: 14),
          TextField(
            controller: _body,
            minLines: 5,
            maxLines: 10,
            maxLength: 2000,
            decoration: InputDecoration(hintText: l10n.questionHint, alignLabelWithHint: true),
          ),
          const SizedBox(height: 8),
          Text('📷 ${l10n.addPhoto}', style: Theme.of(context).textTheme.titleSmall),
          const SizedBox(height: 8),
          if (_photo != null)
            Stack(children: [
              ClipRRect(borderRadius: BorderRadius.circular(12), child: Image.memory(_photo!, height: 220, width: double.infinity, fit: BoxFit.cover)),
              Positioned(
                right: 8,
                top: 8,
                child: IconButton.filled(
                  onPressed: () => setState(() => _photo = null),
                  icon: const Icon(Icons.close),
                  tooltip: l10n.removePhoto,
                ),
              ),
            ])
          else
            Row(children: [
              Expanded(child: OutlinedButton.icon(onPressed: () => _pick(ImageSource.camera), icon: const Icon(Icons.photo_camera), label: Text(l10n.camera))),
              const SizedBox(width: 12),
              Expanded(child: OutlinedButton.icon(onPressed: () => _pick(ImageSource.gallery), icon: const Icon(Icons.photo_library), label: Text(l10n.gallery))),
            ]),
          if (_error != null) ...[const SizedBox(height: 14), InfoBanner(level: 'danger', text: _error!)],
          const SizedBox(height: 20),
          FilledButton.icon(
            onPressed: _busy ? null : _send,
            icon: _busy ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2)) : const Icon(Icons.send),
            label: Text(l10n.send),
          ),
        ],
      ),
    );
  }
}
