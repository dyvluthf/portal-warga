import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:portal_warga/providers/agenda_provider.dart';
import 'package:portal_warga/screens/detail_agenda_screen.dart';

class AgendaListScreen extends ConsumerStatefulWidget {
  const AgendaListScreen({super.key});

  @override
  ConsumerState<AgendaListScreen> createState() => _AgendaListScreenState();
}

class _AgendaListScreenState extends ConsumerState<AgendaListScreen> {
  String _periode = '';

  final _periodeLabels = {
    '': 'Semua',
    'hari_ini': 'Hari Ini',
    'minggu_ini': 'Minggu Ini',
    'bulan_ini': 'Bulan Ini',
  };

  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(agendaProvider.notifier).fetchList());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(agendaProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Agenda')),
      body: Column(
        children: [
          SizedBox(
            height: 48,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              itemCount: _periodeLabels.length,
              itemBuilder: (context, index) {
                final entry = _periodeLabels.entries.elementAt(index);
                final selected = _periode == entry.key;
                return Padding(
                  padding: const EdgeInsets.only(right: 8),
                  child: FilterChip(
                    label: Text(entry.value),
                    selected: selected,
                    onSelected: (_) {
                      setState(() => _periode = entry.key);
                      ref.read(agendaProvider.notifier).fetchList(periode: entry.key.isEmpty ? null : entry.key);
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
                    ? const Center(child: Text('Belum ada agenda'))
                    : RefreshIndicator(
                        onRefresh: () => ref.read(agendaProvider.notifier).fetchList(),
                        child: ListView.builder(
                          itemCount: state.list.length,
                          itemBuilder: (context, index) {
                            final item = state.list[index];
                            return Card(
                              margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                              child: ListTile(
                                leading: CircleAvatar(
                                  backgroundColor: Theme.of(context).colorScheme.primary,
                                  child: const Icon(Icons.event, color: Colors.white),
                                ),
                                title: Text(item.nama, style: const TextStyle(fontWeight: FontWeight.w600)),
                                subtitle: Text('${item.tanggal} ${item.jam}\n📍 ${item.lokasi}'),
                                isThreeLine: true,
                                onTap: () => Navigator.push(
                                  context,
                                  MaterialPageRoute(builder: (_) => DetailAgendaScreen(id: item.id, nama: item.nama, tanggal: item.tanggal, jam: item.jam, lokasi: item.lokasi, deskripsi: item.deskripsi)),
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
