import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:portal_warga/providers/warga_provider.dart';

class ProfilScreen extends ConsumerStatefulWidget {
  const ProfilScreen({super.key});

  @override
  ConsumerState<ProfilScreen> createState() => _ProfilScreenState();
}

class _ProfilScreenState extends ConsumerState<ProfilScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(profileProvider.notifier).fetchProfile());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(profileProvider);
    final theme = Theme.of(context);
    final data = state.data;

    return Scaffold(
      appBar: AppBar(title: const Text('Profil Saya'), actions: [
        IconButton(
          icon: const Icon(Icons.edit),
          onPressed: () => context.go('/edit-profil'),
        ),
      ]),
      body: state.isLoading
          ? const Center(child: CircularProgressIndicator())
          : data == null
              ? const Center(child: Text('Gagal memuat profil'))
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    children: [
                      CircleAvatar(
                        radius: 48,
                        backgroundColor: theme.colorScheme.primary,
                        backgroundImage: data['foto_profil'] != null
                            ? NetworkImage('http://10.0.2.2:8000/storage/${data['foto_profil']}')
                            : null,
                        child: data['foto_profil'] == null
                            ? Text(data['nama']?[0]?.toUpperCase() ?? '?', style: const TextStyle(fontSize: 32, color: Colors.white))
                            : null,
                      ),
                      const SizedBox(height: 16),
                      Text(data['nama'] ?? '', style: theme.textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold)),
                      const SizedBox(height: 24),
                      _infoTile(Icons.badge, 'NIK', data['nik'] ?? '-'),
                      _infoTile(Icons.location_on, 'Alamat', data['alamat'] ?? '-'),
                      if (data['rt_rw'] != null) ...[
                        _infoTile(Icons.home, 'RT/RW', 'RT ${data['rt_rw']['nomor_rt']} / RW ${data['rt_rw']['nomor_rw']}'),
                      ],
                      _infoTile(Icons.phone, 'No. Telepon', data['no_telp'] ?? '-'),
                      _infoTile(Icons.verified, 'Status', data['status_verifikasi'] ?? '-'),
                    ],
                  ),
                ),
    );
  }

  Widget _infoTile(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        children: [
          Icon(icon, size: 20, color: Colors.grey),
          const SizedBox(width: 12),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(label, style: const TextStyle(fontSize: 11, color: Colors.grey)),
              Text(value, style: const TextStyle(fontSize: 14)),
            ],
          ),
        ],
      ),
    );
  }
}
