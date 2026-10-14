import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

Future<T?> push<T>(BuildContext context, Widget page) =>
    Navigator.of(context).push<T>(MaterialPageRoute(builder: (_) => page));

/// Opens the phone dialer (the Agriculture Call Centre 16123 is free).
Future<void> callNumber(String number) async {
  await launchUrl(Uri(scheme: 'tel', path: number));
}

/// Opens a web page (e.g. the privacy policy) in the phone's browser.
Future<void> openWebPage(Uri uri) async {
  await launchUrl(uri, mode: LaunchMode.externalApplication);
}
