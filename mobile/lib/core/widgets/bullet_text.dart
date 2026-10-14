import 'package:flutter/material.dart';

/// Shows multi-line advice as a readable list. Lines that already start with
/// a number ("1." or "১.") keep it; other lines get a bullet.
class BulletText extends StatelessWidget {
  const BulletText(this.text, {super.key, this.style});
  final String text;
  final TextStyle? style;

  static final _numbered = RegExp(r'^\s*([0-9০-৯]+)[.)।]\s*');

  @override
  Widget build(BuildContext context) {
    final lines = text.split('\n').map((l) => l.trim()).where((l) => l.isNotEmpty).toList();
    final textStyle = style ?? Theme.of(context).textTheme.bodyLarge;

    if (lines.length == 1) return Text(lines.first, style: textStyle);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        for (final line in lines)
          Padding(
            padding: const EdgeInsets.only(bottom: 6),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                SizedBox(
                  width: 28,
                  child: Text(_numbered.firstMatch(line)?.group(1) ?? '•',
                      style: textStyle?.copyWith(fontWeight: FontWeight.w600, color: Theme.of(context).colorScheme.primary)),
                ),
                Expanded(child: Text(line.replaceFirst(_numbered, ''), style: textStyle)),
              ],
            ),
          ),
      ],
    );
  }
}
