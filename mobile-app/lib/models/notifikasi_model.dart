class NotifikasiModel {
  final int id;
  final String judul;
  final String pesan;
  final String? tipe;
  final String? referensiId;
  final bool isRead;
  final String createdAt;

  NotifikasiModel({
    required this.id,
    required this.judul,
    required this.pesan,
    this.tipe,
    this.referensiId,
    required this.isRead,
    required this.createdAt,
  });

  factory NotifikasiModel.fromJson(Map<String, dynamic> json) {
    return NotifikasiModel(
      id: json['id'],
      judul: json['judul'] ?? '',
      pesan: json['pesan'] ?? '',
      tipe: json['tipe'],
      referensiId: json['referensi_id'],
      isRead: json['is_read'] ?? false,
      createdAt: json['created_at'] ?? '',
    );
  }
}
