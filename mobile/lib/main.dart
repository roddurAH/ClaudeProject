import 'package:flutter/material.dart';

import 'app.dart';
import 'app_services.dart';
import 'core/storage/local_store.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final store = await LocalStore.open();
  final services = AppServices.create(store);
  services.start(); // loads local data, then refreshes in the background
  runApp(KrishiSathiApp(services: services));
}
