class IuranModel {
  final int id;
  final int wargaId;
  final String bulan;
  final double nominal;
  final String status;
  final String? metode;
  final String? buktiTransfer;
  final String? tanggalBayar;
  final String? paymentUrl;
  final String? transactionId;

  IuranModel({
    required this.id,
    required this.wargaId,
    required this.bulan,
    required this.nominal,
    required this.status,
    this.metode,
    this.buktiTransfer,
    this.tanggalBayar,
    this.paymentUrl,
    this.transactionId,
  });

  factory IuranModel.fromJson(Map<String, dynamic> json) {
    return IuranModel(
      id: json['id'],
      wargaId: json['warga_id'],
      bulan: json['bulan'] ?? '',
      nominal: (json['nominal'] ?? 0).toDouble(),
      status: json['status'] ?? 'belum_bayar',
      metode: json['metode'],
      buktiTransfer: json['bukti_transfer'],
      tanggalBayar: json['tanggal_bayar'],
      paymentUrl: json['payment_url'],
      transactionId: json['transaction_id'],
    );
  }
}
