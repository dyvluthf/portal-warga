class PengaduanModel {
  final int id;
  final int? wargaId;
  final String kategori;
  final String deskripsi;
  final List<String>? foto;
  final String? lokasiLat;
  final String? lokasiLng;
  final String? lokasiManual;
  final String status;
  final String? tanggapanAdmin;
  final int? rating;
  final String? ulasan;
  final String? alasanPenolakan;
  final String createdAt;
  final String? wargaNama;
  final List<TimelineModel>? timeline;

  PengaduanModel({
    required this.id,
    this.wargaId,
    required this.kategori,
    required this.deskripsi,
    this.foto,
    this.lokasiLat,
    this.lokasiLng,
    this.lokasiManual,
    required this.status,
    this.tanggapanAdmin,
    this.rating,
    this.ulasan,
    this.alasanPenolakan,
    required this.createdAt,
    this.wargaNama,
    this.timeline,
  });

  factory PengaduanModel.fromJson(Map<String, dynamic> json) {
    return PengaduanModel(
      id: json['id'],
      wargaId: json['warga_id'],
      kategori: json['kategori'] ?? '',
      deskripsi: json['deskripsi'] ?? '',
      foto: json['foto'] != null ? List<String>.from(json['foto']) : null,
      lokasiLat: json['lokasi_lat'],
      lokasiLng: json['lokasi_lng'],
      lokasiManual: json['lokasi_manual'],
      status: json['status'] ?? 'baru',
      tanggapanAdmin: json['tanggapan_admin'],
      rating: json['rating'],
      ulasan: json['ulasan'],
      alasanPenolakan: json['alasan_penolakan'],
      createdAt: json['created_at'] ?? '',
      wargaNama: json['warga'] != null ? json['warga']['nama'] : null,
      timeline: json['timeline'] != null
          ? (json['timeline'] as List).map((t) => TimelineModel.fromJson(t)).toList()
          : null,
    );
  }
}

class TimelineModel {
  final String status;
  final String? keterangan;
  final String createdAt;

  TimelineModel({
    required this.status,
    this.keterangan,
    required this.createdAt,
  });

  factory TimelineModel.fromJson(Map<String, dynamic> json) {
    return TimelineModel(
      status: json['status'] ?? '',
      keterangan: json['keterangan'],
      createdAt: json['created_at'] ?? '',
    );
  }
}
