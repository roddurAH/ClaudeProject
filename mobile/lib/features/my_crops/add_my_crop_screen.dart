import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/utils/bangla.dart';
import '../../core/utils/nav.dart';
import '../../core/widgets/crop_avatar.dart';
import '../../l10n/app_localizations.dart';
import '../../models/content.dart';
import '../../state/my_crops_controller.dart';
import '../../state/settings_controller.dart';
import '../crops/crop_list_screen.dart';

class AddMyCropScreen extends StatefulWidget {
  const AddMyCropScreen({super.key, this.initialCrop});
  final Crop? initialCrop;

  @override
  State<AddMyCropScreen> createState() => _AddMyCropScreenState();
}

class _AddMyCropScreenState extends State<AddMyCropScreen> {
  late Crop? _crop = widget.initialCrop;
  DateTime _date = DateTime.now();
  final _plot = TextEditingController();

  @override
  void dispose() {
    _plot.dispose();
    super.dispose();
  }

  Future<void> _chooseCrop() async {
    final l10n = AppLocalizations.of(context);
    await push(
      context,
      CropListScreen(
        title: l10n.chooseCrop,
        requireStages: true,
        onPick: (crop) {
          setState(() => _crop = crop);
          Navigator.pop(context);
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final lang = context.watch<SettingsController>().language;

    return Scaffold(
      appBar: AppBar(title: Text(l10n.addCrop)),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            child: ListTile(
              contentPadding: const EdgeInsets.all(12),
              leading: _crop == null ? const Text('🌱', style: TextStyle(fontSize: 36)) : CropAvatar(_crop!, size: 52),
              title: Text(_crop?.name(lang) ?? l10n.chooseCrop, style: Theme.of(context).textTheme.titleMedium),
              trailing: const Icon(Icons.edit),
              onTap: _chooseCrop,
            ),
          ),
          const SizedBox(height: 12),
          Card(
            child: ListTile(
              contentPadding: const EdgeInsets.all(12),
              leading: const Icon(Icons.event, size: 32),
              title: Text(l10n.startDate),
              subtitle: Text(Bangla.date(_date, lang), style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w600)),
              trailing: const Icon(Icons.edit_calendar),
              onTap: () async {
                final picked = await showDatePicker(
                  context: context,
                  initialDate: _date,
                  firstDate: DateTime.now().subtract(const Duration(days: 365)),
                  lastDate: DateTime.now().add(const Duration(days: 180)),
                );
                if (picked != null) setState(() => _date = picked);
              },
            ),
          ),
          const SizedBox(height: 12),
          TextField(controller: _plot, decoration: InputDecoration(labelText: l10n.plotName, hintText: l10n.plotNameHint)),
          const SizedBox(height: 24),
          FilledButton.icon(
            onPressed: _crop == null
                ? null
                : () async {
                    await context.read<MyCropsController>().add(cropSlug: _crop!.slug, startDate: _date, plotName: _plot.text);
                    if (context.mounted) Navigator.pop(context);
                  },
            icon: const Icon(Icons.save),
            label: Text(l10n.save),
          ),
        ],
      ),
    );
  }
}
