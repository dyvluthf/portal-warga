import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';
import 'package:portal_warga/providers/warga_provider.dart';

class EditProfilScreen extends ConsumerStatefulWidget {
  const EditProfilScreen({super.key});

  @override
  ConsumerState<EditProfilScreen> createState() => _EditProfilScreenState();
}

class _EditProfilScreenState extends ConsumerState<EditProfilScreen> {
  final _formKey = GlobalKey<FormState>();
  final _namaController = TextEditingController();
  final _alamatController = TextEditingController();
  final _noTelpController = TextEditingController();
  File? _imageFile;

  @override
  void initState() {
    super.initState();
    final data = ref.read(profileProvider).data;
    if (data != null) {
      _namaController.text = data['nama'] ?? '';
      _alamatController.text = data['alamat'] ?? '';
      _noTelpController.text = data['no_telp'] ?? '';
    }
  }

  @override
  void dispose() {
    _namaController.dispose();
    _alamatController.dispose();
    _noTelpController.dispose();
    super.dispose();
  }

  Future<void> _pickImage() async {
    final picked = await ImagePicker().pickImage(source: ImageSource.gallery);
    if (picked != null) {
      setState(() => _imageFile = File(picked.path));
    }
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;

    final data = <String, Object>{
      'nama': _namaController.text,
      'alamat': _alamatController.text,
      'no_telp': _noTelpController.text,
    };

    if (_imageFile != null) {
      data['foto_profil'] = await MultipartFile.fromFile(_imageFile!.path, filename: 'profile.jpg');
    }

    await ref.read(profileProvider.notifier).updateProfile(data);
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Profil berhasil diperbarui')));
      context.pop();
    }
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(profileProvider);
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(title: const Text('Edit Profil')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              GestureDetector(
                onTap: _pickImage,
                child: CircleAvatar(
                  radius: 48,
                  backgroundColor: theme.colorScheme.primary,
                  backgroundImage: _imageFile != null
                      ? FileImage(_imageFile!)
                      : (state.data?['foto_profil'] != null
                          ? NetworkImage('http://10.0.2.2:8000/storage/${state.data!['foto_profil']}')
                          : null),
                  child: (_imageFile == null && state.data?['foto_profil'] == null)
                      ? const Icon(Icons.camera_alt, size: 32, color: Colors.white)
                      : null,
                ),
              ),
              const SizedBox(height: 4),
              TextButton(onPressed: _pickImage, child: const Text('Ubah Foto')),
              const SizedBox(height: 24),
              TextFormField(
                controller: _namaController,
                decoration: const InputDecoration(labelText: 'Nama', border: OutlineInputBorder()),
                validator: (v) => v == null || v.isEmpty ? 'Nama wajib diisi' : null,
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _alamatController,
                decoration: const InputDecoration(labelText: 'Alamat', border: OutlineInputBorder()),
                maxLines: 2,
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _noTelpController,
                decoration: const InputDecoration(labelText: 'No. Telepon', border: OutlineInputBorder()),
                keyboardType: TextInputType.phone,
              ),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                height: 48,
                child: FilledButton(
                  onPressed: state.isLoading ? null : _save,
                  child: state.isLoading
                      ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2))
                      : const Text('Simpan'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
