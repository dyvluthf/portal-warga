import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:portal_warga/app_router.dart';

void main() {
  runApp(const ProviderScope(child: PortalWargaApp()));
}

class PortalWargaApp extends StatelessWidget {
  const PortalWargaApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp.router(
      title: 'Portal Warga',
      debugShowCheckedModeBanner: false,
      routerConfig: appRouter,
      theme: ThemeData(
        colorSchemeSeed: Colors.green,
        useMaterial3: true,
        brightness: Brightness.light,
      ),
    );
  }
}
