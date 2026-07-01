import 'package:go_router/go_router.dart';
import 'package:portal_warga/screens/splash_screen.dart';
import 'package:portal_warga/screens/login_screen.dart';
import 'package:portal_warga/screens/register_screen.dart';
import 'package:portal_warga/screens/dashboard_screen.dart';
import 'package:portal_warga/screens/berita_detail_screen.dart';
import 'package:portal_warga/screens/edit_profil_screen.dart';
import 'package:portal_warga/screens/pengaduan_list_screen.dart';
import 'package:portal_warga/screens/buat_pengaduan_screen.dart';
import 'package:portal_warga/screens/detail_pengaduan_screen.dart';
import 'package:portal_warga/screens/surat_list_screen.dart';
import 'package:portal_warga/screens/ajukan_surat_screen.dart';
import 'package:portal_warga/screens/detail_surat_screen.dart';
import 'package:portal_warga/screens/notifikasi_list_screen.dart';
import 'package:portal_warga/screens/iuran_screen.dart';
import 'package:portal_warga/screens/bayar_iuran_screen.dart';
import 'package:portal_warga/screens/agenda_list_screen.dart';
import 'package:portal_warga/screens/detail_agenda_screen.dart';
import 'package:portal_warga/screens/galeri_grid_screen.dart';
import 'package:portal_warga/screens/detail_album_screen.dart';

final appRouter = GoRouter(
  initialLocation: '/',
  routes: [
    GoRoute(path: '/', builder: (context, state) => const SplashScreen()),
    GoRoute(path: '/login', builder: (context, state) => const LoginScreen()),
    GoRoute(path: '/register', builder: (context, state) => const RegisterScreen()),
    GoRoute(path: '/dashboard', builder: (context, state) => const DashboardScreen()),
    GoRoute(
      path: '/berita/:id',
      builder: (context, state) {
        final id = int.parse(state.pathParameters['id']!);
        return BeritaDetailScreen(id: id);
      },
    ),
    GoRoute(path: '/edit-profil', builder: (context, state) => const EditProfilScreen()),
    GoRoute(path: '/pengaduan', builder: (context, state) => const PengaduanListScreen()),
    GoRoute(path: '/pengaduan/buat', builder: (context, state) => const BuatPengaduanScreen()),
    GoRoute(
      path: '/pengaduan/:id',
      builder: (context, state) {
        final id = int.parse(state.pathParameters['id']!);
        return DetailPengaduanScreen(id: id);
      },
    ),
    GoRoute(path: '/surat', builder: (context, state) => const SuratListScreen()),
    GoRoute(path: '/surat/ajukan', builder: (context, state) => const AjukanSuratScreen()),
    GoRoute(
      path: '/surat/:id',
      builder: (context, state) {
        final id = int.parse(state.pathParameters['id']!);
        return DetailSuratScreen(id: id);
      },
    ),
    GoRoute(path: '/notifikasi', builder: (context, state) => const NotifikasiListScreen()),
    GoRoute(path: '/iuran', builder: (context, state) => const IuranScreen()),
    GoRoute(path: '/iuran/bayar', builder: (context, state) => const BayarIuranScreen()),
    GoRoute(path: '/agenda', builder: (context, state) => const AgendaListScreen()),
    GoRoute(
      path: '/agenda/:id',
      builder: (context, state) {
        final id = int.parse(state.pathParameters['id']!);
        return DetailAgendaScreen(id: id, nama: '', tanggal: '', jam: '', lokasi: '');
      },
    ),
    GoRoute(path: '/galeri', builder: (context, state) => const GaleriGridScreen()),
    GoRoute(
      path: '/galeri/:id',
      builder: (context, state) {
        final id = int.parse(state.pathParameters['id']!);
        return DetailAlbumScreen(albumId: id, albumNama: '');
      },
    ),
  ],
);
