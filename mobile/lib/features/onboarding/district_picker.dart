import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../l10n/app_localizations.dart';
import '../../models/reference.dart';
import '../../state/reference_controller.dart';
import '../../state/settings_controller.dart';

/// Searchable list of the 64 districts (works offline – bundled in the app).
/// Matches Bangla and English names.
class DistrictList extends StatefulWidget {
  const DistrictList({super.key, required this.selectedId, required this.onSelected});
  final int? selectedId;
  final ValueChanged<District> onSelected;

  @override
  State<DistrictList> createState() => _DistrictListState();
}

class _DistrictListState extends State<DistrictList> {
  String _query = '';

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;
    final all = context.watch<ReferenceController>().districts;
    final q = _query.trim().toLowerCase();
    final districts = all
        .where((d) => q.isEmpty || d.nameBn.contains(q) || d.nameEn.toLowerCase().contains(q))
        .toList()
      ..sort((a, b) => a.name(lang).compareTo(b.name(lang)));

    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 8, 16, 8),
          child: TextField(
            decoration: InputDecoration(prefixIcon: const Icon(Icons.search), hintText: l10n.searchDistrict),
            onChanged: (v) => setState(() => _query = v),
          ),
        ),
        Expanded(
          child: ListView.builder(
            itemCount: districts.length,
            itemBuilder: (context, i) {
              final d = districts[i];
              final selected = d.id == widget.selectedId;
              return ListTile(
                title: Text(d.name(lang), style: TextStyle(fontWeight: selected ? FontWeight.w600 : null, fontSize: 18)),
                subtitle: Text(d.division(lang)),
                trailing: selected ? Icon(Icons.check_circle, color: Theme.of(context).colorScheme.primary) : null,
                selected: selected,
                onTap: () => widget.onSelected(d),
              );
            },
          ),
        ),
      ],
    );
  }
}

/// Full-screen picker used from Settings.
class DistrictPickerScreen extends StatelessWidget {
  const DistrictPickerScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final settings = context.watch<SettingsController>();
    return Scaffold(
      appBar: AppBar(title: Text(AppLocalizations.of(context).chooseDistrict)),
      body: DistrictList(
        selectedId: settings.districtId,
        onSelected: (d) async {
          await settings.setDistrictId(d.id);
          if (context.mounted) Navigator.pop(context);
        },
      ),
    );
  }
}
