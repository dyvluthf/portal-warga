import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:portal_warga/providers/galeri_provider.dart';
import 'package:portal_warga/screens/detail_album_screen.dart';

class GaleriGridScreen extends ConsumerStatefulWidget {
  const GaleriGridScreen({super.key});

  @override
  ConsumerState<GaleriGridScreen> createState() => _GaleriGridScreenState();
}

class _GaleriGridScreenState extends ConsumerState<GaleriGridScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(galeriProvider.notifier).fetchList());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(galeriProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Galeri')),
      body: state.isLoading
          ? const Center(child: CircularProgressIndicator())
          : state.list.isEmpty
              ? const Center(child: Text('Belum ada album'))
              : RefreshIndicator(
                  onRefresh: () => ref.read(galeriProvider.notifier).fetchList(),
                  child: GridView.builder(
                    padding: const EdgeInsets.all(12),
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 2,
                      crossAxisSpacing: 8,
                      mainAxisSpacing: 8,
                      childAspectRatio: 1.2,
                    ),
                    itemCount: state.list.length,
                    itemBuilder: (context, index) {
                      final album = state.list[index];
                      return Card(
                        clipBehavior: Clip.antiAlias,
                        child: InkWell(
                          onTap: () => Navigator.push(
                            context,
                            MaterialPageRoute(builder: (_) => DetailAlbumScreen(albumId: album.id, albumNama: album.namaAlbum)),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Expanded(
                                child: Container(
                                  width: double.infinity,
                                  color: Colors.grey[200],
                                  child: const Icon(Icons.photo_album, size: 48, color: Colors.grey),
                                ),
                              ),
                              Padding(
                                padding: const EdgeInsets.all(8),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(album.namaAlbum, style: const TextStyle(fontWeight: FontWeight.w600), maxLines: 1, overflow: TextOverflow.ellipsis),
                                    Text('${album.fotoCount} foto', style: const TextStyle(fontSize: 12, color: Colors.grey)),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
                ),
    );
  }
}
