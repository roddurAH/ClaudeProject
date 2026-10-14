import 'package:flutter/material.dart';

import '../../models/content.dart';

/// Round crop icon (emoji) – recognisable without reading.
class CropAvatar extends StatelessWidget {
  const CropAvatar(this.crop, {super.key, this.size = 48});
  final Crop crop;
  final double size;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(color: const Color(0xFFE8F5E9), borderRadius: BorderRadius.circular(size / 2)),
      alignment: Alignment.center,
      child: Text(crop.emoji, style: TextStyle(fontSize: size * 0.55)),
    );
  }
}
