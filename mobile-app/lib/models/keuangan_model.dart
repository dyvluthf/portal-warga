class KeuanganModel {
  final int id;
  final String jenis;
  final String judul;
  final double jumlah;
  final String? kategori;
  final String? buktiFoto;
  final String? keterangan;
  final String tanggal;

  KeuanganModel({
    required this.id,
    required this.jenis,
    required this.judul,
    required this.jumlah,
    this.kategori,
    this.buktiFoto,
    this.keterangan,
    required this.tanggal,
  });

  factory KeuanganModel.fromJson(Map<String, dynamic> json) {
    return KeuanganModel(
      id: json['id'],
      jenis: json['jenis'] ?? '',
      judul: json['judul'] ?? '',
      jumlah: (json['jumlah'] ?? 0).toDouble(),
      kategori: json['kategori'],
      buktiFoto: json['bukti_foto'],
      keterangan: json['keterangan'],
      tanggal: json['tanggal'] ?? '',
    );
  }
}
