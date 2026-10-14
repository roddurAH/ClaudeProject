import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/config/app_config.dart';
import '../../core/network/api_exception.dart';
import '../../core/utils/bangla.dart';
import '../../core/utils/nav.dart';
import '../../core/utils/relative_time.dart';
import '../../core/widgets/common.dart';
import '../../l10n/app_localizations.dart';
import '../../state/auth_controller.dart';
import '../../state/content_controller.dart';
import '../../state/reference_controller.dart';
import '../../state/settings_controller.dart';
import '../auth/login_screen.dart';
import '../onboarding/district_picker.dart';
import '../user_types.dart';

class SettingsScreen extends StatelessWidget {
  const SettingsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final settings = context.watch<SettingsController>();
    final lang = settings.language;
    final content = context.watch<ContentController>();
    final auth = context.watch<AuthController>();
    final district = context.watch<ReferenceController>().districtById(settings.districtId);
    final userType = UserTypeOption.all.firstWhere((t) => t.key == settings.userType, orElse: () => UserTypeOption.all.first);
    final privacyUri = AppConfig.webPageUri(settings.apiBaseUrl, '/privacy', language: lang);

    return Scaffold(
      appBar: AppBar(title: Text(l10n.settings)),
      body: ListView(
        padding: const EdgeInsets.symmetric(vertical: 8),
        children: [
          ListTile(
            leading: const Icon(Icons.translate),
            title: Text(l10n.language),
            trailing: SegmentedButton<String>(
              segments: const [ButtonSegment(value: 'bn', label: Text('বাংলা')), ButtonSegment(value: 'en', label: Text('English'))],
              selected: {lang},
              onSelectionChanged: (s) => settings.setLanguage(s.first),
            ),
          ),
          ListTile(
            leading: const Icon(Icons.place),
            title: Text(l10n.district),
            subtitle: Text(district?.name(lang) ?? '—'),
            trailing: const Icon(Icons.chevron_right),
            onTap: () => push(context, const DistrictPickerScreen()),
          ),
          ListTile(
            leading: Text(userType.emoji, style: const TextStyle(fontSize: 24)),
            title: Text(l10n.iAmA),
            subtitle: Text(userType.title(l10n)),
            trailing: const Icon(Icons.chevron_right),
            onTap: () => showModalBottomSheet<void>(
              context: context,
              builder: (ctx) => SafeArea(
                child: Column(mainAxisSize: MainAxisSize.min, children: [
                  for (final t in UserTypeOption.all)
                    ListTile(
                      leading: Text(t.emoji, style: const TextStyle(fontSize: 28)),
                      title: Text(t.title(l10n)),
                      selected: t.key == settings.userType,
                      onTap: () {
                        settings.setUserType(t.key);
                        Navigator.pop(ctx);
                      },
                    ),
                ]),
              ),
            ),
          ),
          ListTile(
            leading: const Icon(Icons.format_size),
            title: Text(l10n.textSize),
            subtitle: Padding(
              padding: const EdgeInsets.only(top: 8),
              child: SegmentedButton<double>(
                segments: [
                  ButtonSegment(value: 1.0, label: Text(l10n.textNormal)),
                  ButtonSegment(value: 1.15, label: Text(l10n.textLarge)),
                  ButtonSegment(value: 1.3, label: Text(l10n.textExtraLarge)),
                ],
                selected: {settings.textScale},
                onSelectionChanged: (s) => settings.setTextScale(s.first),
              ),
            ),
          ),
          SwitchListTile(
            secondary: const Icon(Icons.data_saver_on),
            title: Text(l10n.dataSaver),
            subtitle: Text(l10n.dataSaverDesc),
            value: settings.dataSaver,
            onChanged: settings.setDataSaver,
          ),
          ListTile(
            leading: const Icon(Icons.square_foot),
            title: Text(l10n.bighaSize),
            subtitle: Text('${Bangla.number(settings.bighaDecimal, lang)} ${l10n.unitDecimal}'),
            trailing: const Icon(Icons.edit),
            onTap: () => _editNumber(context, l10n.bighaSize, settings.bighaDecimal, settings.setBighaDecimal),
          ),
          const Divider(),
          ListTile(
            leading: content.syncing
                ? const SizedBox(width: 24, height: 24, child: CircularProgressIndicator(strokeWidth: 2))
                : const Icon(Icons.sync),
            title: Text(l10n.updateNow),
            subtitle: Text(content.savedAt == null ? l10n.contentBuiltIn : l10n.contentUpdated(relativeTime(content.savedAt, l10n, lang))),
            onTap: content.syncing
                ? null
                : () async {
                    await content.sync();
                    if (!context.mounted) return;
                    final error = content.lastError;
                    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(error == null ? l10n.upToDate : friendlyError(error, l10n))));
                  },
          ),
          const Divider(),
          ListTile(
            leading: const Icon(Icons.account_circle),
            title: Text(l10n.account),
            subtitle: Text(auth.isLoggedIn ? l10n.loggedInAs('${auth.user!.name} (${Bangla.digits(auth.user!.phone, lang)})') : l10n.loginNeeded),
            trailing: auth.isLoggedIn
                ? TextButton(onPressed: auth.logout, child: Text(l10n.logout))
                : TextButton(onPressed: () => push(context, const LoginScreen()), child: Text(l10n.login)),
          ),
          if (auth.isLoggedIn)
            ListTile(
              leading: Icon(Icons.delete_forever_outlined, color: Theme.of(context).colorScheme.error),
              title: Text(l10n.deleteAccount, style: TextStyle(color: Theme.of(context).colorScheme.error)),
              onTap: () => _deleteAccount(context),
            ),
          const Divider(),
          ExpansionTile(
            leading: const Icon(Icons.dns_outlined),
            title: Text(l10n.serverAddress),
            subtitle: Text(settings.apiBaseUrl.isEmpty ? '—' : settings.apiBaseUrl, style: const TextStyle(fontSize: 13)),
            childrenPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
            children: [_ServerAddressEditor(settings: settings)],
          ),
          if (privacyUri != null)
            ListTile(
              leading: const Icon(Icons.privacy_tip_outlined),
              title: Text(l10n.privacyPolicy),
              trailing: const Icon(Icons.open_in_new, size: 20),
              onTap: () => openWebPage(privacyUri),
            ),
          ListTile(
            leading: const Icon(Icons.info_outline),
            title: Text(l10n.about),
            subtitle: Text('${l10n.aboutText}\n\n${l10n.version(AppConfig.appVersion)}'),
            isThreeLine: true,
          ),
        ],
      ),
    );
  }

  Future<void> _deleteAccount(BuildContext context) async {
    final deleted = await showDialog<bool>(context: context, builder: (_) => const _DeleteAccountDialog());
    if (deleted == true && context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(AppLocalizations.of(context).accountDeleted)));
    }
  }

  Future<void> _editNumber(BuildContext context, String title, double current, Future<void> Function(double) save) async {
    final l10n = AppLocalizations.of(context);
    final controller = TextEditingController(text: current.toStringAsFixed(current % 1 == 0 ? 0 : 2));
    final value = await showDialog<double>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(title),
        content: TextField(controller: controller, keyboardType: const TextInputType.numberWithOptions(decimal: true), autofocus: true),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: Text(l10n.cancel)),
          FilledButton(onPressed: () => Navigator.pop(ctx, double.tryParse(Bangla.toAscii(controller.text))), child: Text(l10n.save)),
        ],
      ),
    );
    if (value != null && value > 0 && value < 1000) await save(value);
  }
}

/// Asks for the PIN again, then deletes the account on the server.
class _DeleteAccountDialog extends StatefulWidget {
  const _DeleteAccountDialog();

  @override
  State<_DeleteAccountDialog> createState() => _DeleteAccountDialogState();
}

class _DeleteAccountDialogState extends State<_DeleteAccountDialog> {
  final _pin = TextEditingController();
  bool _busy = false;
  String? _error;

  @override
  void dispose() {
    _pin.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    final l10n = AppLocalizations.of(context);
    final pin = Bangla.toAscii(_pin.text.trim());
    if (pin.isEmpty) return;
    setState(() {
      _busy = true;
      _error = null;
    });
    try {
      await context.read<AuthController>().deleteAccount(pin);
      if (mounted) Navigator.pop(context, true);
    } on ApiException catch (e) {
      if (mounted) setState(() => _error = friendlyError(e, l10n));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final error = Theme.of(context).colorScheme.error;
    return AlertDialog(
      icon: Icon(Icons.warning_amber_rounded, color: error, size: 36),
      title: Text(l10n.deleteAccount),
      content: SingleChildScrollView(
        child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(l10n.deleteAccountWarning),
          const SizedBox(height: 16),
          TextField(
            controller: _pin,
            enabled: !_busy,
            obscureText: true,
            keyboardType: TextInputType.number,
            maxLength: 6,
            decoration: InputDecoration(labelText: l10n.deleteAccountPin, errorText: _error, errorMaxLines: 3),
            onSubmitted: (_) => _submit(),
          ),
        ]),
      ),
      actions: [
        TextButton(onPressed: _busy ? null : () => Navigator.pop(context, false), child: Text(l10n.cancel)),
        FilledButton(
          style: FilledButton.styleFrom(backgroundColor: error, foregroundColor: Theme.of(context).colorScheme.onError),
          onPressed: _busy ? null : _submit,
          child: _busy
              ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2))
              : Text(l10n.deleteAccountConfirm),
        ),
      ],
    );
  }
}

class _ServerAddressEditor extends StatefulWidget {
  const _ServerAddressEditor({required this.settings});
  final SettingsController settings;

  @override
  State<_ServerAddressEditor> createState() => _ServerAddressEditorState();
}

class _ServerAddressEditorState extends State<_ServerAddressEditor> {
  late final _controller = TextEditingController(text: widget.settings.apiBaseUrl);
  bool _checking = false;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Text(l10n.serverAddressHint, style: Theme.of(context).textTheme.bodySmall),
      const SizedBox(height: 8),
      TextField(controller: _controller, keyboardType: TextInputType.url),
      const SizedBox(height: 8),
      Row(children: [
        TextButton(
          onPressed: () async {
            await widget.settings.setApiBaseUrl(null);
            _controller.text = widget.settings.apiBaseUrl;
          },
          child: Text(l10n.resetDefault),
        ),
        const Spacer(),
        FilledButton(
          onPressed: _checking
              ? null
              : () async {
                  await widget.settings.setApiBaseUrl(_controller.text);
                  _controller.text = widget.settings.apiBaseUrl; // show the cleaned-up address
                  if (!context.mounted) return;
                  setState(() => _checking = true);
                  final reference = context.read<ReferenceController>();
                  final content = context.read<ContentController>();
                  final problem = await reference.checkServer();
                  if (!context.mounted) return;
                  setState(() => _checking = false);
                  if (problem == null) {
                    content.sync();
                    reference.refresh();
                  }
                  ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                    content: Text(problem == null ? l10n.serverConnected : friendlyError(problem, l10n)),
                    duration: const Duration(seconds: 6),
                  ));
                },
          child: _checking
              ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2))
              : Text(l10n.save),
        ),
      ]),
    ]);
  }
}
