import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';
import 'package:portal_warga/providers/iuran_provider.dart';

class BayarIuranScreen extends ConsumerStatefulWidget {
  const BayarIuranScreen({super.key});

  @override
  ConsumerState<BayarIuranScreen> createState() => _BayarIuranScreenState();
}

class _BayarIuranScreenState extends ConsumerState<BayarIuranScreen> {
  final _picker = ImagePicker();
  final Set<String> _selectedBulan = {};
  String _metode = 'manual';
  String? _buktiPath;
  bool _loading = false;
  double nominalPerBulan = 50000;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(title: const Text('Bayar Iuran')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text('Pilih Bulan', style: theme.textTheme.titleMedium),
          const SizedBox(height: 8),
          Wrap(
            spacing: 8,
            runSpacing: 4,
            children: List.generate(12, (i) {
              final now = DateTime.now();
              final bulan = DateTime(now.year, i + 1);
              final key = '${bulan.year}-${bulan.month.toString().padLeft(2, '0')}';
              final selected = _selectedBulan.contains(key);
              return FilterChip(
                label: Text(['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'][i]),
                selected: selected,
                onSelected: (v) {
                  setState(() {
                    if (v) { _selectedBulan.add(key); } else { _selectedBulan.remove(key); }
                  });
                },
              );
            }),
          ),
          const SizedBox(height: 16),
          Text('Total: Rp ${(nominalPerBulan * _selectedBulan.length).toStringAsFixed(0)}', style: theme.textTheme.titleLarge),
          const SizedBox(height: 16),
          SegmentedButton(
            segments: const [
              ButtonSegment(value: 'manual', label: Text('Transfer Manual')),
              ButtonSegment(value: 'qris', label: Text('QRIS')),
            ],
            selected: {_metode},
            onSelectionChanged: (v) => setState(() => _metode = v.first),
          ),
          const SizedBox(height: 16),
          if (_metode == 'manual') ...[
            ElevatedButton.icon(
              icon: const Icon(Icons.upload),
              label: Text(_buktiPath != null ? 'Ganti Bukti' : 'Upload Bukti Transfer'),
              onPressed: () async {
                final file = await _picker.pickImage(source: ImageSource.gallery);
                if (file != null) setState(() => _buktiPath = file.path);
              },
            ),
            if (_buktiPath != null) ...[
              const SizedBox(height: 8),
              Text('File: ${_buktiPath!.split('\\').last.split('/').last}', style: const TextStyle(fontSize: 12)),
            ],
          ],
          const SizedBox(height: 24),
          ElevatedButton(
            onPressed: _loading || _selectedBulan.isEmpty ? null : _submit,
            child: _loading ? const CircularProgressIndicator() : const Text('Bayar'),
          ),
        ],
      ),
    );
  }

  Future<void> _submit() async {
    setState(() => _loading = true);
    final notifier = ref.read(iuranProvider.notifier);
    final total = nominalPerBulan * _selectedBulan.length;

    String? msg;
    if (_metode == 'manual') {
      if (_buktiPath == null) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Upload bukti transfer')));
        setState(() => _loading = false);
        return;
      }
      msg = await notifier.bayarManual(
        bulanList: _selectedBulan.toList(),
        nominal: total,
        buktiPath: _buktiPath!,
      );
    } else {
      final result = await notifier.bayarQris(
        bulanList: _selectedBulan.toList(),
        nominal: total,
      );
      if (result != null && result['qr_string'] != null) {
        if (mounted) {
          showDialog(
            context: context,
            builder: (ctx) => AlertDialog(
              title: const Text('Scan QRIS'),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Text('Scan QR code berikut untuk membayar:'),
                  const SizedBox(height: 16),
                  Image.network(result['qr_string'], height: 200, errorBuilder: (_, __, ___) => const Icon(Icons.qr_code, size: 200)),
                ],
              ),
              actions: [TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Tutup'))],
            ),
          );
        }
        setState(() => _loading = false);
        return;
      }
      msg = 'Gagal membuat QRIS';
    }

    if (!mounted) return;
    setState(() => _loading = false);
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg ?? 'Gagal')));
    if (msg == 'Pembayaran diajukan') Navigator.pop(context);
  }
}
