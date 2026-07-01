import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:portal_warga/providers/surat_provider.dart';
import 'package:portal_warga/screens/ajukan_surat_screen.dart';
import 'package:portal_warga/screens/detail_surat_screen.dart';

class SuratListScreen extends ConsumerStatefulWidget {
  const SuratListScreen({super.key});

  @override
  ConsumerState<SuratListScreen> createState() => _SuratListScreenState();
}

class _SuratListScreenState extends ConsumerState<SuratListScreen> {
  final _statuses = ['', 'pending', 'disetujui', 'diterbitkan', 'ditolak'];
  final _labels = ['Semua', 'Pending', 'Disetujui', 'Terbit', 'Ditolak'];
  int _selectedFilter = 0;

  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(suratProvider.notifier).fetchList());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(suratProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Surat')),
      floatingActionButton: FloatingActionButton(
        onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const AjukanSuratScreen())),
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
                      ref.read(suratProvider.notifier).fetchList(status: _statuses[index], page: 1);
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
                    ? const Center(child: Text('Belum ada pengajuan surat'))
                    : RefreshIndicator(
                        onRefresh: () => ref.read(suratProvider.notifier).fetchList(),
                        child: ListView.builder(
                          itemCount: state.list.length,
                          itemBuilder: (context, index) {
                            final item = state.list[index];
                            return Card(
                              margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                              child: ListTile(
                                title: Text(item.jenisSurat, style: const TextStyle(fontWeight: FontWeight.w600)),
                                subtitle: item.nomorSurat != null
                                    ? Text(item.nomorSurat!, style: const TextStyle(fontSize: 12))
                                    : null,
                                trailing: Chip(
                                  label: Text(item.status, style: const TextStyle(fontSize: 11)),
                                  visualDensity: VisualDensity.compact,
                                ),
                                onTap: () => Navigator.push(
                                  context,
                                  MaterialPageRoute(builder: (_) => DetailSuratScreen(id: item.id)),
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
