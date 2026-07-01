import 'package:flutter/material.dart';
import 'package:portal_warga/screens/home_screen.dart';
import 'package:portal_warga/screens/berita_list_screen.dart';
import 'package:portal_warga/screens/profil_screen.dart';
import 'package:portal_warga/screens/iuran_screen.dart';
import 'package:portal_warga/screens/agenda_list_screen.dart';
import 'package:portal_warga/screens/galeri_grid_screen.dart';
import 'package:portal_warga/screens/notifikasi_list_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  int _selectedIndex = 0;

  final _pages = const [
    HomeScreen(),
    BeritaListScreen(),
    IuranScreen(),
    AgendaListScreen(),
    GaleriGridScreen(),
    NotifikasiListScreen(),
    ProfilScreen(),
  ];

  @override
  Widget build(BuildContext context) {
    final titles = ['Beranda', 'Berita', 'Iuran', 'Agenda', 'Galeri', 'Notifikasi', 'Profil'];
    return Scaffold(
      appBar: AppBar(title: Text(titles[_selectedIndex])),
      body: _pages[_selectedIndex],
      bottomNavigationBar: NavigationBar(
        selectedIndex: _selectedIndex,
        onDestinationSelected: (i) => setState(() => _selectedIndex = i),
        destinations: const [
          NavigationDestination(icon: Icon(Icons.home), label: 'Beranda'),
          NavigationDestination(icon: Icon(Icons.article), label: 'Berita'),
          NavigationDestination(icon: Icon(Icons.account_balance_wallet), label: 'Iuran'),
          NavigationDestination(icon: Icon(Icons.event), label: 'Agenda'),
          NavigationDestination(icon: Icon(Icons.photo_library), label: 'Galeri'),
          NavigationDestination(icon: Icon(Icons.notifications), label: 'Notif'),
          NavigationDestination(icon: Icon(Icons.person), label: 'Profil'),
        ],
      ),
    );
  }
}
