import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:portal_warga/providers/iuran_provider.dart';
import 'package:portal_warga/screens/bayar_iuran_screen.dart';

class IuranScreen extends ConsumerStatefulWidget {
  const IuranScreen({super.key});

  @override
  ConsumerState<IuranScreen> createState() => _IuranScreenState();
}

class _IuranScreenState extends ConsumerState<IuranScreen> {
  @override
  void initState() {
    super.initState();
    final now = DateTime.now();
    final bulan = '${now.year}-${now.month.toString().padLeft(2, '0')}';
    Future.microtask(() => ref.read(iuranProvider.notifier).fetchList(bulan: bulan));
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(iuranProvider);
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(title: const Text('Iuran')),
      floatingActionButton: FloatingActionButton(
        onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const BayarIuranScreen())),
        child: const Icon(Icons.payment),
      ),
      body: state.isLoading
          ? const Center(child: CircularProgressIndicator())
          : state.list.isEmpty
              ? const Center(child: Text('Belum ada riwayat iuran'))
              : RefreshIndicator(
                  onRefresh: () => ref.read(iuranProvider.notifier).fetchList(),
                  child: ListView.builder(
                    itemCount: state.list.length,
                    itemBuilder: (context, index) {
                      final item = state.list[index];
                      final statusColors = {
                        'lunas': Colors.green,
                        'menunggu_verifikasi': Colors.orange,
                        'belum_bayar': Colors.red,
                      };
                      return Card(
                        margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                        child: ListTile(
                          title: Text('Iuran ${item.bulan}', style: const TextStyle(fontWeight: FontWeight.w600)),
                          subtitle: Text('Rp ${item.nominal.toStringAsFixed(0)}'),
                          trailing: Chip(
                            label: Text(item.status.replaceAll('_', ' '), style: const TextStyle(color: Colors.white, fontSize: 11)),
                            backgroundColor: statusColors[item.status] ?? Colors.grey,
                          ),
                        ),
                      );
                    },
                  ),
                ),
    );
  }
}
