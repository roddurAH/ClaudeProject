import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../l10n/app_localizations.dart';
import '../../state/settings_controller.dart';
import '../user_types.dart';
import 'district_picker.dart';

/// Three quick steps, big buttons, no typing required:
/// 1. language  2. who are you  3. district.
class OnboardingScreen extends StatefulWidget {
  const OnboardingScreen({super.key});

  @override
  State<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends State<OnboardingScreen> {
  int _step = 0;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final settings = context.watch<SettingsController>();

    final steps = [_languageStep(settings), _userTypeStep(l10n, settings), _districtStep(l10n, settings)];

    return Scaffold(
      body: SafeArea(
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
              child: Row(
                children: [
                  if (_step > 0)
                    IconButton(onPressed: () => setState(() => _step--), icon: const Icon(Icons.arrow_back), tooltip: l10n.back),
                  const Text('🌾', style: TextStyle(fontSize: 28)),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(l10n.appTitle, overflow: TextOverflow.ellipsis, style: Theme.of(context).textTheme.titleLarge),
                  ),
                  for (var i = 0; i < steps.length; i++)
                    Container(
                      margin: const EdgeInsets.only(left: 6),
                      width: i == _step ? 22 : 8,
                      height: 8,
                      decoration: BoxDecoration(
                        color: i <= _step ? Theme.of(context).colorScheme.primary : Colors.grey.shade300,
                        borderRadius: BorderRadius.circular(4),
                      ),
                    ),
                ],
              ),
            ),
            Expanded(child: steps[_step]),
          ],
        ),
      ),
    );
  }

  Widget _languageStep(SettingsController settings) {
    Widget option(String code, String label, String sample) => Padding(
          padding: const EdgeInsets.only(bottom: 16),
          child: SizedBox(
            width: double.infinity,
            height: 88,
            child: FilledButton.tonal(
              onPressed: () async {
                await settings.setLanguage(code);
                setState(() => _step = 1);
              },
              child: Column(mainAxisSize: MainAxisSize.min, children: [
                Text(label, style: const TextStyle(fontSize: 26, fontWeight: FontWeight.w600)),
                Text(sample, style: const TextStyle(fontSize: 15)),
              ]),
            ),
          ),
        );

    return Padding(
      padding: const EdgeInsets.all(24),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Text('🌾', style: TextStyle(fontSize: 72)),
          const SizedBox(height: 8),
          const Text('ভাষা বেছে নিন  ·  Choose language', textAlign: TextAlign.center, style: TextStyle(fontSize: 20)),
          const SizedBox(height: 32),
          option('bn', 'বাংলা', 'কৃষি সাথী'),
          option('en', 'English', 'Krishi Sathi'),
        ],
      ),
    );
  }

  Widget _userTypeStep(AppLocalizations l10n, SettingsController settings) {
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        Text(l10n.whoAreYou, style: Theme.of(context).textTheme.headlineSmall),
        const SizedBox(height: 4),
        Text(l10n.whoAreYouHint),
        const SizedBox(height: 20),
        for (final type in UserTypeOption.all)
          Padding(
            padding: const EdgeInsets.only(bottom: 12),
            child: Card(
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
                side: BorderSide(
                  color: settings.userType == type.key ? Theme.of(context).colorScheme.primary : Colors.transparent,
                  width: 2,
                ),
              ),
              child: ListTile(
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                leading: Text(type.emoji, style: const TextStyle(fontSize: 40)),
                title: Text(type.title(l10n), style: const TextStyle(fontSize: 19, fontWeight: FontWeight.w600)),
                subtitle: Text(type.description(l10n)),
                onTap: () async {
                  await settings.setUserType(type.key);
                  setState(() => _step = 2);
                },
              ),
            ),
          ),
      ],
    );
  }

  Widget _districtStep(AppLocalizations l10n, SettingsController settings) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(20, 20, 20, 0),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(l10n.chooseDistrict, style: Theme.of(context).textTheme.headlineSmall),
            const SizedBox(height: 4),
            Text(l10n.chooseDistrictHint),
          ]),
        ),
        Expanded(
          child: DistrictList(
            selectedId: settings.districtId,
            onSelected: (d) => settings.setDistrictId(d.id),
          ),
        ),
        SafeArea(
          top: false,
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: SizedBox(
              width: double.infinity,
              child: FilledButton(
                onPressed: settings.districtId == null ? null : settings.completeOnboarding,
                child: Text(l10n.start),
              ),
            ),
          ),
        ),
      ],
    );
  }
}
