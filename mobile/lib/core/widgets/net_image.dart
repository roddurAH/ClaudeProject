import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../l10n/app_localizations.dart';
import '../../state/settings_controller.dart';

/// Network image that is cached on the phone and, in data-saver mode, only
/// downloads after the user taps it.
class NetImage extends StatefulWidget {
  const NetImage(this.url, {super.key, this.height = 180, this.fit = BoxFit.cover});
  final String url;
  final double height;
  final BoxFit fit;

  @override
  State<NetImage> createState() => _NetImageState();
}

class _NetImageState extends State<NetImage> {
  bool _tapped = false;

  @override
  Widget build(BuildContext context) {
    final dataSaver = context.watch<SettingsController>().dataSaver;
    final radius = BorderRadius.circular(12);

    if (dataSaver && !_tapped) {
      return InkWell(
        onTap: () => setState(() => _tapped = true),
        borderRadius: radius,
        child: Container(
          height: widget.height,
          decoration: BoxDecoration(color: Colors.grey.shade200, borderRadius: radius),
          child: Center(
            child: Column(mainAxisSize: MainAxisSize.min, children: [
              const Icon(Icons.image_outlined, size: 40),
              Text(AppLocalizations.of(context).photoTapToLoad),
            ]),
          ),
        ),
      );
    }

    return ClipRRect(
      borderRadius: radius,
      child: CachedNetworkImage(
        imageUrl: widget.url,
        height: widget.height,
        width: double.infinity,
        fit: widget.fit,
        placeholder: (_, _) => Container(height: widget.height, color: Colors.grey.shade200),
        errorWidget: (_, _, _) => Container(
          height: widget.height,
          color: Colors.grey.shade200,
          child: const Center(child: Icon(Icons.broken_image_outlined, size: 40)),
        ),
      ),
    );
  }
}
