class GaleriAlbumModel {
  final int id;
  final String namaAlbum;
  final int fotoCount;
  final List<GaleriFotoModel>? foto;

  GaleriAlbumModel({
    required this.id,
    required this.namaAlbum,
    this.fotoCount = 0,
    this.foto,
  });

  factory GaleriAlbumModel.fromJson(Map<String, dynamic> json) {
    return GaleriAlbumModel(
      id: json['id'],
      namaAlbum: json['nama_album'] ?? '',
      fotoCount: json['foto_count'] ?? 0,
      foto: json['foto'] != null
          ? (json['foto'] as List).map((f) => GaleriFotoModel.fromJson(f)).toList()
          : null,
    );
  }
}

class GaleriFotoModel {
  final int id;
  final String pathFoto;
  final String? caption;

  GaleriFotoModel({required this.id, required this.pathFoto, this.caption});

  factory GaleriFotoModel.fromJson(Map<String, dynamic> json) {
    return GaleriFotoModel(
      id: json['id'],
      pathFoto: json['path_foto'] ?? '',
      caption: json['caption'],
    );
  }
}
