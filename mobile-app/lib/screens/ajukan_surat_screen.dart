import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:file_picker/file_picker.dart';
import 'package:portal_warga/providers/surat_provider.dart';

class AjukanSuratScreen extends ConsumerStatefulWidget {
  const AjukanSuratScreen({super.key});

  @override
  ConsumerState<AjukanSuratScreen> createState() => _AjukanSuratScreenState();
}

class _AjukanSuratScreenState extends ConsumerState<AjukanSuratScreen> {
  final _formKey = GlobalKey<FormState>();
  final _keteranganCtrl = TextEditingController();
  final _tempatLahirCtrl = TextEditingController();
  final _tglLahirCtrl = TextEditingController();
  final _jkCtrl = TextEditingController();
  String _jenisSurat = 'SKTM';
  final List<String> _dokumenPaths = [];
  bool _loading = false;

  final _jenisList = ['SKTM', 'Keterangan Domisili', 'Keterangan Usaha', 'Izin Keramaian', 'Lainnya'];

  @override
  void dispose() {
    _keteranganCtrl.dispose();
    _tempatLahirCtrl.dispose();
    _tglLahirCtrl.dispose();
    _jkCtrl.dispose();
    super.dispose();
  }

  Future<void> _pickFile() async {
    final result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: ['jpg', 'jpeg', 'png', 'pdf'],
    );
    if (result != null && result.files.single.path != null) {
      setState(() => _dokumenPaths.add(result.files.single.path!));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Ajukan Surat')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            DropdownButtonFormField(
              value: _jenisSurat,
              items: _jenisList.map((j) => DropdownMenuItem(value: j, child: Text(j))).toList(),
              onChanged: (v) => setState(() => _jenisSurat = v!),
              decoration: const InputDecoration(labelText: 'Jenis Surat'),
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _tempatLahirCtrl,
              decoration: const InputDecoration(labelText: 'Tempat Lahir'),
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _tglLahirCtrl,
              decoration: const InputDecoration(labelText: 'Tanggal Lahir'),
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _jkCtrl,
              decoration: const InputDecoration(labelText: 'Jenis Kelamin'),
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _keteranganCtrl,
              decoration: const InputDecoration(labelText: 'Keterangan'),
              maxLines: 3,
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                const Text('Dokumen Pendukung:'),
                const Spacer(),
                TextButton.icon(icon: const Icon(Icons.attach_file), label: const Text('Tambah'), onPressed: _pickFile),
              ],
            ),
            if (_dokumenPaths.isNotEmpty)
              Column(
                children: _dokumenPaths.map((path) => ListTile(
                  dense: true,
                  leading: const Icon(Icons.description),
                  title: Text(path.split('\\').last.split('/').last),
                  trailing: IconButton(
                    icon: const Icon(Icons.close, size: 18),
                    onPressed: () => setState(() => _dokumenPaths.remove(path)),
                  ),
                )).toList(),
              ),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: _loading ? null : _submit,
              child: _loading ? const CircularProgressIndicator() : const Text('Ajukan Surat'),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _loading = true);

    final msg = await ref.read(suratProvider.notifier).create(
      jenisSurat: _jenisSurat,
      dataForm: {
        'tempat_lahir': _tempatLahirCtrl.text,
        'tanggal_lahir': _tglLahirCtrl.text,
        'jenis_kelamin': _jkCtrl.text,
        'keterangan': _keteranganCtrl.text,
      },
      dokumenPaths: _dokumenPaths.isNotEmpty ? _dokumenPaths : null,
    );

    if (!mounted) return;
    setState(() => _loading = false);

    if (msg == 'Surat berhasil diajukan') {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg!)));
      Navigator.pop(context);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg ?? 'Gagal')));
    }
  }
}
