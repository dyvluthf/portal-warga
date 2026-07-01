import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:portal_warga/providers/pengaduan_provider.dart';

class DetailPengaduanScreen extends ConsumerStatefulWidget {
  final int id;
  const DetailPengaduanScreen({super.key, required this.id});

  @override
  ConsumerState<DetailPengaduanScreen> createState() => _DetailPengaduanScreenState();
}

class _DetailPengaduanScreenState extends ConsumerState<DetailPengaduanScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => _refresh());
  }

  Future<void> _refresh() async {
    final notifier = ref.read(pengaduanProvider.notifier);
    final data = await notifier.getDetail(widget.id);
    if (data != null && mounted) setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    final allList = ref.watch(pengaduanProvider).list;
    final data = allList.where((e) => e.id == widget.id).firstOrNull;

    if (data == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Detail Pengaduan')),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    final theme = Theme.of(context);
    final statusColors = {
      'baru': Colors.blue,
      'diproses': Colors.orange,
      'selesai': Colors.green,
      'ditolak': Colors.red,
    };

    return Scaffold(
      appBar: AppBar(title: const Text('Detail Pengaduan')),
      body: RefreshIndicator(
        onRefresh: _refresh,
        child: ListView(
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
                        Expanded(child: Text(data.kategori, style: theme.textTheme.titleLarge)),
                        Chip(
                          label: Text(data.status, style: const TextStyle(color: Colors.white, fontSize: 11)),
                          backgroundColor: statusColors[data.status] ?? Colors.grey,
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Text(data.deskripsi),
                    if (data.lokasiManual != null) ...[
                      const SizedBox(height: 8),
                      Row(children: [const Icon(Icons.location_on, size: 16), Text(' ${data.lokasiManual}')]),
                    ],
                    if (data.foto != null && data.foto!.isNotEmpty) ...[
                      const SizedBox(height: 12),
                      SizedBox(
                        height: 120,
                        child: ListView.builder(
                          scrollDirection: Axis.horizontal,
                          itemCount: data.foto!.length,
                          itemBuilder: (context, index) => Padding(
                            padding: const EdgeInsets.only(right: 8),
                            child: ClipRRect(
                              borderRadius: BorderRadius.circular(8),
                              child: Image.network(
                                'http://10.0.2.2:8000/storage/${data.foto![index]}',
                                width: 120, height: 120, fit: BoxFit.cover,
                                errorBuilder: (_, __, ___) => Container(width: 120, height: 120, color: Colors.grey[200]),
                              ),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ),
            if (data.timeline != null && data.timeline!.isNotEmpty) ...[
              const SizedBox(height: 16),
              Text('Timeline', style: theme.textTheme.titleMedium),
              const SizedBox(height: 8),
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Stepper(
                    currentStep: -1,
                    controlsBuilder: (_, __) => const SizedBox(),
                    steps: data.timeline!.map((t) => Step(
                      title: Text(t.status, style: const TextStyle(fontWeight: FontWeight.w600)),
                      subtitle: t.keterangan != null ? Text(t.keterangan!) : null,
                      content: Text(t.createdAt, style: const TextStyle(fontSize: 12, color: Colors.grey)),
                    )).toList(),
                  ),
                ),
              ),
            ],
            if (data.status == 'selesai') ...[
              const SizedBox(height: 16),
              Text('Beri Rating', style: theme.textTheme.titleMedium),
              const SizedBox(height: 8),
              _RatingWidget(pengaduanId: data.id),
            ],
          ],
        ),
      ),
    );
  }
}

class _RatingWidget extends ConsumerStatefulWidget {
  final int pengaduanId;
  const _RatingWidget({required this.pengaduanId});

  @override
  ConsumerState<_RatingWidget> createState() => _RatingWidgetState();
}

class _RatingWidgetState extends ConsumerState<_RatingWidget> {
  int _rating = 0;
  final _ulasanCtrl = TextEditingController();
  bool _loading = false;

  @override
  void dispose() {
    _ulasanCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: List.generate(5, (index) {
                return IconButton(
                  icon: Icon(index < _rating ? Icons.star : Icons.star_border, color: Colors.amber),
                  onPressed: () => setState(() => _rating = index + 1),
                );
              }),
            ),
            TextField(
              controller: _ulasanCtrl,
              decoration: const InputDecoration(labelText: 'Ulasan (opsional)', border: OutlineInputBorder()),
              maxLines: 3,
            ),
            const SizedBox(height: 12),
            ElevatedButton(
              onPressed: _loading || _rating == 0 ? null : _submit,
              child: _loading ? const CircularProgressIndicator() : const Text('Kirim Rating'),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _submit() async {
    setState(() => _loading = true);
    await ref.read(pengaduanProvider.notifier).beriRating(
      widget.pengaduanId,
      _rating,
      ulasan: _ulasanCtrl.text.isNotEmpty ? _ulasanCtrl.text : null,
    );
    if (mounted) {
      setState(() => _loading = false);
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Rating terkirim')));
    }
  }
}
