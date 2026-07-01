import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';
import 'package:portal_warga/providers/pengaduan_provider.dart';

class BuatPengaduanScreen extends ConsumerStatefulWidget {
  const BuatPengaduanScreen({super.key});

  @override
  ConsumerState<BuatPengaduanScreen> createState() => _BuatPengaduanScreenState();
}

class _BuatPengaduanScreenState extends ConsumerState<BuatPengaduanScreen> {
  final _formKey = GlobalKey<FormState>();
  final _kategoriCtrl = TextEditingController();
  final _deskripsiCtrl = TextEditingController();
  final _lokasiManualCtrl = TextEditingController();
  final _picker = ImagePicker();
  final List<String> _fotoPaths = [];
  String? _lokasiLat;
  String? _lokasiLng;
  bool _loading = false;

  @override
  void dispose() {
    _kategoriCtrl.dispose();
    _deskripsiCtrl.dispose();
    _lokasiManualCtrl.dispose();
    super.dispose();
  }

  Future<void> _pickImage() async {
    if (_fotoPaths.length >= 3) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Maksimal 3 foto')));
      return;
    }
    final file = await _picker.pickImage(source: ImageSource.gallery);
    if (file != null) setState(() => _fotoPaths.add(file.path));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Buat Pengaduan')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            TextFormField(
              controller: _kategoriCtrl,
              decoration: const InputDecoration(labelText: 'Kategori', hintText: 'Infrastruktur, Kebersihan, dll'),
              validator: (v) => (v == null || v.isEmpty) ? 'Wajib diisi' : null,
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _deskripsiCtrl,
              decoration: const InputDecoration(labelText: 'Deskripsi'),
              maxLines: 5,
              validator: (v) => (v == null || v.isEmpty) ? 'Wajib diisi' : null,
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _lokasiManualCtrl,
              decoration: const InputDecoration(labelText: 'Lokasi (manual)'),
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                ElevatedButton.icon(
                  icon: const Icon(Icons.location_on),
                  label: const Text('Ambil Lokasi'),
                  onPressed: () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Geolocation akan diintegrasikan')),
                    );
                  },
                ),
              ],
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                const Text('Foto (maks 3):'),
                const Spacer(),
                TextButton.icon(icon: const Icon(Icons.add_a_photo), label: const Text('Tambah'), onPressed: _pickImage),
              ],
            ),
            if (_fotoPaths.isNotEmpty)
              SizedBox(
                height: 80,
                child: ListView.builder(
                  scrollDirection: Axis.horizontal,
                  itemCount: _fotoPaths.length,
                  itemBuilder: (context, index) => Stack(
                    children: [
                      Padding(
                        padding: const EdgeInsets.only(right: 8),
                        child: Image.file(
                          _fotoPaths[index] as dynamic,
                          width: 80, height: 80, fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => Container(width: 80, height: 80, color: Colors.grey[200]),
                        ),
                      ),
                      Positioned(
                        top: 0, right: 4,
                        child: GestureDetector(
                          onTap: () => setState(() => _fotoPaths.removeAt(index)),
                          child: Container(
                            decoration: const BoxDecoration(shape: BoxShape.circle, color: Colors.red),
                            child: const Icon(Icons.close, size: 18, color: Colors.white),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: _loading ? null : _submit,
              child: _loading ? const CircularProgressIndicator() : const Text('Kirim Pengaduan'),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _loading = true);

    final msg = await ref.read(pengaduanProvider.notifier).create(
      kategori: _kategoriCtrl.text,
      deskripsi: _deskripsiCtrl.text,
      lokasiManual: _lokasiManualCtrl.text.isNotEmpty ? _lokasiManualCtrl.text : null,
      fotoPaths: _fotoPaths.isNotEmpty ? _fotoPaths : null,
    );

    if (!mounted) return;
    setState(() => _loading = false);

    if (msg == 'Pengaduan berhasil dikirim') {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg!)));
      Navigator.pop(context);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg ?? 'Gagal')));
    }
  }
}
