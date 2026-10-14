import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/network/api_exception.dart';
import '../../core/utils/bangla.dart';
import '../../core/widgets/common.dart';
import '../../l10n/app_localizations.dart';
import '../../state/auth_controller.dart';
import '../../state/settings_controller.dart';

/// Phone number + PIN. No email, no password rules, no SMS cost.
class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _form = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _phone = TextEditingController();
  final _pin = TextEditingController();
  final _pin2 = TextEditingController();
  bool _register = false;
  bool _busy = false;
  String? _error;

  static final _phoneRegex = RegExp(r'^01[3-9]\d{8}$');

  String _cleanPhone(String v) {
    var digits = Bangla.toAscii(v).replaceAll(RegExp(r'\D'), '');
    if (digits.startsWith('880')) digits = digits.substring(2);
    return digits;
  }

  Future<void> _submit() async {
    if (!_form.currentState!.validate()) return;
    final auth = context.read<AuthController>();
    final settings = context.read<SettingsController>();
    final l10n = AppLocalizations.of(context);
    setState(() {
      _busy = true;
      _error = null;
    });
    try {
      final phone = _cleanPhone(_phone.text);
      final pin = Bangla.toAscii(_pin.text.trim());
      if (_register) {
        await auth.register(
          name: _name.text.trim(),
          phone: phone,
          pin: pin,
          districtId: settings.districtId,
          userType: settings.userType,
          language: settings.language,
        );
      } else {
        await auth.login(phone, pin);
      }
      if (mounted) Navigator.pop(context, true);
    } on ApiException catch (e) {
      setState(() => _error = friendlyError(e, l10n));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  void dispose() {
    _name.dispose();
    _phone.dispose();
    _pin.dispose();
    _pin2.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);

    String? required(String? v) => (v == null || v.trim().isEmpty) ? l10n.requiredField : null;

    return Scaffold(
      appBar: AppBar(title: Text(_register ? l10n.register : l10n.login)),
      body: Form(
        key: _form,
        child: ListView(
          padding: const EdgeInsets.all(20),
          children: [
            const Center(child: Text('🔐', style: TextStyle(fontSize: 56))),
            const SizedBox(height: 8),
            Text(l10n.loginNeeded, textAlign: TextAlign.center, style: Theme.of(context).textTheme.bodyLarge),
            const SizedBox(height: 24),
            if (_register) ...[
              TextFormField(
                controller: _name,
                textCapitalization: TextCapitalization.words,
                decoration: InputDecoration(labelText: l10n.yourName, prefixIcon: const Icon(Icons.person)),
                validator: required,
              ),
              const SizedBox(height: 14),
            ],
            TextFormField(
              controller: _phone,
              keyboardType: TextInputType.phone,
              autofillHints: const [AutofillHints.telephoneNumber],
              decoration: InputDecoration(labelText: l10n.mobileNumber, prefixIcon: const Icon(Icons.phone_android), hintText: '01XXXXXXXXX'),
              validator: (v) => required(v) ?? (_phoneRegex.hasMatch(_cleanPhone(v!)) ? null : l10n.invalidPhone),
            ),
            const SizedBox(height: 14),
            TextFormField(
              controller: _pin,
              keyboardType: TextInputType.number,
              obscureText: true,
              maxLength: 6,
              decoration: InputDecoration(labelText: l10n.pin, prefixIcon: const Icon(Icons.pin), counterText: ''),
              validator: (v) => required(v) ?? (RegExp(r'^\d{4,6}$').hasMatch(Bangla.toAscii(v!.trim())) ? null : l10n.invalidPin),
            ),
            if (_register) ...[
              const SizedBox(height: 14),
              TextFormField(
                controller: _pin2,
                keyboardType: TextInputType.number,
                obscureText: true,
                maxLength: 6,
                decoration: InputDecoration(labelText: l10n.confirmPin, prefixIcon: const Icon(Icons.pin), counterText: ''),
                validator: (v) => Bangla.toAscii((v ?? '').trim()) == Bangla.toAscii(_pin.text.trim()) ? null : l10n.pinMismatch,
              ),
              const SizedBox(height: 6),
              Text('🔒 ${l10n.pinHint}', style: Theme.of(context).textTheme.bodySmall),
            ],
            if (_error != null) ...[const SizedBox(height: 14), InfoBanner(level: 'danger', text: _error!)],
            const SizedBox(height: 20),
            FilledButton(
              onPressed: _busy ? null : _submit,
              child: _busy
                  ? const SizedBox(width: 24, height: 24, child: CircularProgressIndicator(strokeWidth: 3))
                  : Text(_register ? l10n.register : l10n.login),
            ),
            const SizedBox(height: 8),
            TextButton(
              onPressed: () => setState(() {
                _register = !_register;
                _error = null;
              }),
              child: Text(_register ? l10n.haveAccount : l10n.noAccount),
            ),
          ],
        ),
      ),
    );
  }
}
