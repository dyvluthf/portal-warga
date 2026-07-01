class AgendaModel {
  final int id;
  final String nama;
  final String tanggal;
  final String jam;
  final String lokasi;
  final String? deskripsi;

  AgendaModel({
    required this.id,
    required this.nama,
    required this.tanggal,
    required this.jam,
    required this.lokasi,
    this.deskripsi,
  });

  factory AgendaModel.fromJson(Map<String, dynamic> json) {
    return AgendaModel(
      id: json['id'],
      nama: json['nama'] ?? '',
      tanggal: json['tanggal'] ?? '',
      jam: json['jam'] ?? '',
      lokasi: json['lokasi'] ?? '',
      deskripsi: json['deskripsi'],
    );
  }
}
