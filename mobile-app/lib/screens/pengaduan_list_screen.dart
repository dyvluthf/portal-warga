import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:portal_warga/providers/pengaduan_provider.dart';
import 'package:portal_warga/screens/detail_pengaduan_screen.dart';
import 'package:portal_warga/screens/buat_pengaduan_screen.dart';

class PengaduanListScreen extends ConsumerStatefulWidget {
  const PengaduanListScreen({super.key});

  @override
  ConsumerState<PengaduanListScreen> createState() => _PengaduanListScreenState();
}

class _PengaduanListScreenState extends ConsumerState<PengaduanListScreen> {
  final _statuses = ['', 'baru', 'diproses', 'selesai', 'ditolak'];
  final _labels = ['Semua', 'Baru', 'Diproses', 'Selesai', 'Ditolak'];
  int _selectedFilter = 0;

  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(pengaduanProvider.notifier).fetchList());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(pengaduanProvider);
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(title: const Text('Pengaduan')),
      floatingActionButton: FloatingActionButton(
        onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const BuatPengaduanScreen())),
        child: const Icon(Icons.add),
      ),
      body: Column(
        children: [
          SizedBox(
            height: 48,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              itemCount: _statuses.length,
              itemBuilder: (context, index) {
                final selected = _selectedFilter == index;
                return Padding(
                  padding: const EdgeInsets.only(right: 8),
                  child: FilterChip(
                    label: Text(_labels[index]),
                    selected: selected,
                    onSelected: (_) {
                      setState(() => _selectedFilter = index);
                      ref.read(pengaduanProvider.notifier).fetchList(status: _statuses[index], page: 1);
                    },
                  ),
                );
              },
            ),
          ),
          Expanded(
            child: state.isLoading
                ? const Center(child: CircularProgressIndicator())
                : state.list.isEmpty
                    ? const Center(child: Text('Belum ada pengaduan'))
                    : RefreshIndicator(
                        onRefresh: () => ref.read(pengaduanProvider.notifier).fetchList(),
                        child: ListView.builder(
                          itemCount: state.list.length,
                          itemBuilder: (context, index) {
                            final item = state.list[index];
                            return Card(
                              margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                              child: ListTile(
                                title: Text(item.kategori, style: const TextStyle(fontWeight: FontWeight.w600)),
                                subtitle: Text(item.deskripsi, maxLines: 2, overflow: TextOverflow.ellipsis),
                                trailing: Chip(
                                  label: Text(item.status, style: const TextStyle(fontSize: 11)),
                                  visualDensity: VisualDensity.compact,
                                ),
                                onTap: () => Navigator.push(
                                  context,
                                  MaterialPageRoute(builder: (_) => DetailPengaduanScreen(id: item.id)),
                                ),
                              ),
                            );
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }
}
