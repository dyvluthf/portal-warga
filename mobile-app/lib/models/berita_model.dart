class BeritaModel {
  final int id;
  final String judul;
  final String slug;
  final String konten;
  final String? kategori;
  final String? gambar;
  final String status;
  final String? tanggalPublish;
  final String createdAt;

  BeritaModel({
    required this.id,
    required this.judul,
    required this.slug,
    required this.konten,
    this.kategori,
    this.gambar,
    required this.status,
    this.tanggalPublish,
    required this.createdAt,
  });

  factory BeritaModel.fromJson(Map<String, dynamic> json) {
    return BeritaModel(
      id: json['id'],
      judul: json['judul'],
      slug: json['slug'],
      konten: json['konten'],
      kategori: json['kategori'],
      gambar: json['gambar'],
      status: json['status'],
      tanggalPublish: json['tanggal_publish'],
      createdAt: json['created_at'],
    );
  }
}
