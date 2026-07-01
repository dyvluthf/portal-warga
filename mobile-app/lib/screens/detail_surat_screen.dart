import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:open_filex/open_filex.dart';
import 'package:portal_warga/providers/surat_provider.dart';

class DetailSuratScreen extends ConsumerStatefulWidget {
  final int id;
  const DetailSuratScreen({super.key, required this.id});

  @override
  ConsumerState<DetailSuratScreen> createState() => _DetailSuratScreenState();
}

class _DetailSuratScreenState extends ConsumerState<DetailSuratScreen> {
  @override
  Widget build(BuildContext context) {
    final allList = ref.watch(suratProvider).list;
    final data = allList.where((e) => e.id == widget.id).firstOrNull;

    if (data == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Detail Surat')),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    final theme = Theme.of(context);
    final statusColors = {
      'pending': Colors.blue,
      'disetujui': Colors.orange,
      'diterbitkan': Colors.green,
      'ditolak': Colors.red,
    };

    return Scaffold(
      appBar: AppBar(title: const Text('Detail Surat')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(child: Text(data.jenisSurat, style: theme.textTheme.titleLarge)),
                      Chip(
                        label: Text(data.status, style: const TextStyle(color: Colors.white, fontSize: 11)),
                        backgroundColor: statusColors[data.status] ?? Colors.grey,
                      ),
                    ],
                  ),
                  if (data.nomorSurat != null) ...[
                    const SizedBox(height: 8),
                    Text('No: ${data.nomorSurat}', style: const TextStyle(fontWeight: FontWeight.w500)),
                  ],
                  if (data.dataForm != null) ...[
                    const SizedBox(height: 12),
                    ...data.dataForm!.entries.map((e) => Padding(
                      padding: const EdgeInsets.symmetric(vertical: 2),
                      child: Row(
                        children: [
                          SizedBox(width: 120, child: Text(e.key.replaceAll('_', ' '), style: const TextStyle(color: Colors.grey))),
                          Expanded(child: Text('${e.value}')),
                        ],
                      ),
                    )),
                  ],
                  if (data.alasanPenolakan != null) ...[
                    const SizedBox(height: 12),
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(color: Colors.red[50], borderRadius: BorderRadius.circular(8)),
                      child: Text(data.alasanPenolakan!, style: const TextStyle(color: Colors.red)),
                    ),
                  ],
                  if (data.filePdfPath != null) ...[
                    const SizedBox(height: 16),
                    ElevatedButton.icon(
                      icon: const Icon(Icons.download),
                      label: const Text('Download PDF'),
                      onPressed: () async {
                        final url = 'http://10.0.2.2:8000/storage/${data.filePdfPath}';
                        await OpenFilex.open(url);
                      },
                    ),
                  ],
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
