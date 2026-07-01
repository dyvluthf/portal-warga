import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:portal_warga/providers/notifikasi_provider.dart';

class NotifikasiListScreen extends ConsumerStatefulWidget {
  const NotifikasiListScreen({super.key});

  @override
  ConsumerState<NotifikasiListScreen> createState() => _NotifikasiListScreenState();
}

class _NotifikasiListScreenState extends ConsumerState<NotifikasiListScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      ref.read(notifikasiProvider.notifier).fetchList();
      ref.read(notifikasiProvider.notifier).fetchUnreadCount();
    });
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(notifikasiProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifikasi'),
        actions: [
          if (state.unreadCount > 0)
            TextButton(
              onPressed: () => ref.read(notifikasiProvider.notifier).markAllRead(),
              child: const Text('Baca Semua'),
            ),
        ],
      ),
      body: state.isLoading
          ? const Center(child: CircularProgressIndicator())
          : state.list.isEmpty
              ? const Center(child: Text('Belum ada notifikasi'))
              : RefreshIndicator(
                  onRefresh: () => ref.read(notifikasiProvider.notifier).fetchList(),
                  child: ListView.builder(
                    itemCount: state.list.length,
                    itemBuilder: (context, index) {
                      final item = state.list[index];
                      return ListTile(
                        leading: CircleAvatar(
                          backgroundColor: item.isRead ? Colors.grey[200] : Theme.of(context).colorScheme.primary,
                          child: Icon(
                            item.tipe == 'pengaduan' ? Icons.report_problem :
                            item.tipe == 'surat' ? Icons.description : Icons.notifications,
                            color: item.isRead ? Colors.grey : Colors.white,
                          ),
                        ),
                        title: Text(item.judul, style: TextStyle(fontWeight: item.isRead ? FontWeight.normal : FontWeight.bold)),
                        subtitle: Text(item.pesan, maxLines: 2, overflow: TextOverflow.ellipsis),
                        trailing: Text(
                          _formatDate(item.createdAt),
                          style: const TextStyle(fontSize: 11, color: Colors.grey),
                        ),
                        onTap: () => ref.read(notifikasiProvider.notifier).markRead(item.id),
                      );
                    },
                  ),
                ),
    );
  }

  String _formatDate(String iso) {
    try {
      final dt = DateTime.parse(iso);
      return '${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
    } catch (_) {
      return '';
    }
  }
}
