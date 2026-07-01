class SuratModel {
  final int id;
  final int? wargaId;
  final String jenisSurat;
  final String? nomorSurat;
  final Map<String, dynamic>? dataForm;
  final List<String>? dokumenPendukung;
  final String status;
  final String? alasanPenolakan;
  final String? filePdfPath;
  final String createdAt;

  SuratModel({
    required this.id,
    this.wargaId,
    required this.jenisSurat,
    this.nomorSurat,
    this.dataForm,
    this.dokumenPendukung,
    required this.status,
    this.alasanPenolakan,
    this.filePdfPath,
    required this.createdAt,
  });

  factory SuratModel.fromJson(Map<String, dynamic> json) {
    return SuratModel(
      id: json['id'],
      wargaId: json['warga_id'],
      jenisSurat: json['jenis_surat'] ?? '',
      nomorSurat: json['nomor_surat'],
      dataForm: json['data_form'] != null ? Map<String, dynamic>.from(json['data_form']) : null,
      dokumenPendukung: json['dokumen_pendukung'] != null ? List<String>.from(json['dokumen_pendukung']) : null,
      status: json['status'] ?? 'pending',
      alasanPenolakan: json['alasan_penolakan'],
      filePdfPath: json['file_pdf_path'],
      createdAt: json['created_at'] ?? '',
    );
  }
}
