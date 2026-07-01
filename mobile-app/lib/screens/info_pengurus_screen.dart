import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:portal_warga/providers/warga_provider.dart';

class InfoPengurusScreen extends ConsumerStatefulWidget {
  const InfoPengurusScreen({super.key});

  @override
  ConsumerState<InfoPengurusScreen> createState() => _InfoPengurusScreenState();
}

class _InfoPengurusScreenState extends ConsumerState<InfoPengurusScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(profileProvider.notifier).fetchInfoRtRw());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(profileProvider);
    final theme = Theme.of(context);
    final data = state.data;

    return Scaffold(
      appBar: AppBar(title: const Text('Pengurus RT/RW')),
      body: state.isLoading
          ? const Center(child: CircularProgressIndicator())
          : data == null
              ? const Center(child: Text('Belum ada data pengurus'))
              : Padding(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Card(
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'RT ${data['nomor_rt'] ?? '-'} / RW ${data['nomor_rw'] ?? '-'}',
                                style: theme.textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                '${data['nama_kelurahan'] ?? '-'}, ${data['kecamatan'] ?? '-'}',
                                style: const TextStyle(color: Colors.grey),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      const Text('Struktur Pengurus', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                      const SizedBox(height: 12),
                      _pengurusCard(theme, 'Ketua RT', data['ketua_rt']),
                      _pengurusCard(theme, 'Sekretaris', data['sekretaris']),
                      _pengurusCard(theme, 'Bendahara', data['bendahara']),
                    ],
                  ),
                ),
    );
  }

  Widget _pengurusCard(ThemeData theme, String jabatan, dynamic warga) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: theme.colorScheme.primary,
          child: Text(
            (warga?['nama']?[0]?.toUpperCase() ?? '?'),
            style: const TextStyle(color: Colors.white),
          ),
        ),
        title: Text(warga?['nama'] ?? '-'),
        subtitle: Text(jabatan),
      ),
    );
  }
}
