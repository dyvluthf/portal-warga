import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:photo_view/photo_view.dart';
import 'package:photo_view/photo_view_gallery.dart';
import 'package:portal_warga/providers/galeri_provider.dart';

class DetailAlbumScreen extends ConsumerStatefulWidget {
  final int albumId;
  final String albumNama;

  const DetailAlbumScreen({
    super.key,
    required this.albumId,
    required this.albumNama,
  });

  @override
  ConsumerState<DetailAlbumScreen> createState() => _DetailAlbumScreenState();
}

class _DetailAlbumScreenState extends ConsumerState<DetailAlbumScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => _fetch());
  }

  Future<void> _fetch() async {
    await ref.read(galeriProvider.notifier).getDetail(widget.albumId);
    if (mounted) setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(galeriProvider);
    final album = state.list.where((a) => a.id == widget.albumId).firstOrNull;

    return Scaffold(
      appBar: AppBar(title: Text(widget.albumNama)),
      body: album == null
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _fetch,
              child: GridView.builder(
                padding: const EdgeInsets.all(8),
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 3,
                  crossAxisSpacing: 4,
                  mainAxisSpacing: 4,
                ),
                itemCount: album.foto?.length ?? 0,
                itemBuilder: (context, index) {
                  final foto = album.foto![index];
                  return GestureDetector(
                    onTap: () => Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => _FullScreenGallery(
                          images: album.foto!.map((f) => 'http://10.0.2.2:8000/storage/${f.pathFoto}').toList(),
                          initialIndex: index,
                        ),
                      ),
                    ),
                    child: Image.network(
                      'http://10.0.2.2:8000/storage/${foto.pathFoto}',
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => Container(color: Colors.grey[200], child: const Icon(Icons.broken_image)),
                    ),
                  );
                },
              ),
            ),
    );
  }
}

class _FullScreenGallery extends StatelessWidget {
  final List<String> images;
  final int initialIndex;

  const _FullScreenGallery({required this.images, required this.initialIndex});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(backgroundColor: Colors.black, iconTheme: const IconThemeData(color: Colors.white)),
      body: PhotoViewGallery.builder(
        scrollPhysics: const BouncingScrollPhysics(),
        builder: (context, index) {
          return PhotoViewGalleryPageOptions(
            imageProvider: NetworkImage(images[index]),
            minScale: PhotoViewComputedScale.contained,
            maxScale: PhotoViewComputedScale.covered * 2,
          );
        },
        itemCount: images.length,
        pageController: PageController(initialPage: initialIndex),
      ),
    );
  }
}
