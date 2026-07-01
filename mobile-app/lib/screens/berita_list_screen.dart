import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:portal_warga/providers/berita_provider.dart';

class BeritaListScreen extends ConsumerStatefulWidget {
  const BeritaListScreen({super.key});

  @override
  ConsumerState<BeritaListScreen> createState() => _BeritaListScreenState();
}

class _BeritaListScreenState extends ConsumerState<BeritaListScreen> {
  final _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(beritaProvider.notifier).fetchBerita(refresh: true));
    _scrollController.addListener(_onScroll);
  }

  void _onScroll() {
    if (_scrollController.position.pixels >= _scrollController.position.maxScrollExtent - 200) {
      ref.read(beritaProvider.notifier).fetchBerita();
    }
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(beritaProvider);
    final theme = Theme.of(context);

    return Column(
      children: [
        SizedBox(
          height: 40,
          child: ListView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
            children: ['Semua', 'Informasi', 'Kegiatan', 'Pengumuman'].map((label) {
              final selected = (label == 'Semua' && state.kategori == null) ||
                  state.kategori == label;
              return Padding(
                padding: const EdgeInsets.only(right: 8),
                child: FilterChip(
                  label: Text(label),
                  selected: selected,
                  onSelected: (_) {
                    ref.read(beritaProvider.notifier).setKategori(label == 'Semua' ? null : label);
                  },
                ),
              );
            }).toList(),
          ),
        ),
        Expanded(
          child: state.isLoading && state.list.isEmpty
              ? const Center(child: CircularProgressIndicator())
              : state.list.isEmpty
                  ? const Center(child: Text('Belum ada berita'))
                  : ListView.builder(
                      controller: _scrollController,
                      padding: const EdgeInsets.all(16),
                      itemCount: state.list.length + (state.isLoading ? 1 : 0),
                      itemBuilder: (context, index) {
                        if (index == state.list.length) {
                          return const Center(child: Padding(
                            padding: EdgeInsets.all(16),
                            child: CircularProgressIndicator(),
                          ));
                        }
                        final item = state.list[index];
                        return Card(
                          margin: const EdgeInsets.only(bottom: 12),
                          clipBehavior: Clip.antiAlias,
                          child: InkWell(
                            onTap: () => context.go('/berita/${item.id}'),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                if (item.gambar != null)
                                  Image.network(
                                    'http://10.0.2.2:8000/storage/${item.gambar}',
                                    height: 160,
                                    width: double.infinity,
                                    fit: BoxFit.cover,
                                    errorBuilder: (_, _, _) => Container(height: 160, color: Colors.grey.shade200),
                                  ),
                                Padding(
                                  padding: const EdgeInsets.all(12),
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Row(
                                        children: [
                                          if (item.kategori != null)
                                            Container(
                                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                              decoration: BoxDecoration(
                                                color: theme.colorScheme.primaryContainer,
                                                borderRadius: BorderRadius.circular(4),
                                              ),
                                              child: Text(item.kategori!, style: const TextStyle(fontSize: 10)),
                                            ),
                                          const Spacer(),
                                          if (item.tanggalPublish != null)
                                            Text(
                                              item.tanggalPublish!.substring(0, 10),
                                              style: const TextStyle(fontSize: 10, color: Colors.grey),
                                            ),
                                        ],
                                      ),
                                      const SizedBox(height: 6),
                                      Text(item.judul, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
        ),
      ],
    );
  }
}
