import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:share_plus/share_plus.dart';
import 'package:portal_warga/providers/berita_provider.dart';

class BeritaDetailScreen extends ConsumerWidget {
  final int id;
  const BeritaDetailScreen({super.key, required this.id});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final async = ref.watch(beritaDetailProvider(id));
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Berita'),
        actions: [
          IconButton(
            icon: const Icon(Icons.share),
            onPressed: () async {
              final data = async.valueOrNull;
              if (data != null) {
                await Share.share('${data.judul}\n\n${data.konten}');
              }
            },
          ),
        ],
      ),
      body: async.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (_, _) => const Center(child: Text('Gagal memuat berita')),
        data: (berita) => SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (berita.gambar != null)
                ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: Image.network(
                    'http://10.0.2.2:8000/storage/${berita.gambar}',
                    width: double.infinity,
                    height: 200,
                    fit: BoxFit.cover,
                    errorBuilder: (_, _, _) => Container(height: 200, color: Colors.grey.shade200),
                  ),
                ),
              if (berita.kategori != null) ...[
                const SizedBox(height: 12),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: theme.colorScheme.primaryContainer,
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(berita.kategori!, style: const TextStyle(fontSize: 11)),
                ),
              ],
              const SizedBox(height: 12),
              Text(berita.judul, style: theme.textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold)),
              if (berita.tanggalPublish != null) ...[
                const SizedBox(height: 4),
                Text(berita.tanggalPublish!.substring(0, 10), style: const TextStyle(color: Colors.grey, fontSize: 12)),
              ],
              const Divider(height: 24),
              Text(berita.konten, style: const TextStyle(height: 1.6, fontSize: 14)),
            ],
          ),
        ),
      ),
    );
  }
}
