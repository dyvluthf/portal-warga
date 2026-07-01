import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:portal_warga/models/pengaduan_model.dart';
import 'package:portal_warga/services/api_service.dart';
import 'dart:convert';

class PengaduanState {
  final List<PengaduanModel> list;
  final bool isLoading;
  final String? error;
  final String filterStatus;
  final int currentPage;
  final int? lastPage;

  const PengaduanState({
    this.list = const [],
    this.isLoading = false,
    this.error,
    this.filterStatus = '',
    this.currentPage = 1,
    this.lastPage,
  });

  PengaduanState copyWith({
    List<PengaduanModel>? list,
    bool? isLoading,
    String? error,
    String? filterStatus,
    int? currentPage,
    int? lastPage,
  }) {
    return PengaduanState(
      list: list ?? this.list,
      isLoading: isLoading ?? this.isLoading,
      error: error,
      filterStatus: filterStatus ?? this.filterStatus,
      currentPage: currentPage ?? this.currentPage,
      lastPage: lastPage ?? this.lastPage,
    );
  }
}

class PengaduanNotifier extends StateNotifier<PengaduanState> {
  final ApiService _api = ApiService();

  PengaduanNotifier() : super(const PengaduanState());

  Future<void> fetchList({String? status, int? page}) async {
    state = state.copyWith(isLoading: true, error: null);
    if (page != null) state = state.copyWith(currentPage: page);
    if (status != null) state = state.copyWith(filterStatus: status);

    try {
      final params = {'per_page': '15', 'page': '${state.currentPage}'};
      if (state.filterStatus.isNotEmpty) params['status'] = state.filterStatus;

      final response = await _api.get('/pengaduan', queryParams: params);
      final data = response.data['data'];
      final list = (data['data'] as List).map((e) => PengaduanModel.fromJson(e)).toList();

      state = state.copyWith(
        list: list,
        isLoading: false,
        lastPage: data['last_page'],
      );
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }

  Future<PengaduanModel?> getDetail(int id) async {
    try {
      final response = await _api.get('/pengaduan/$id');
      return PengaduanModel.fromJson(response.data['data']);
    } catch (_) {
      return null;
    }
  }

  Future<String?> create({
    required String kategori,
    required String deskripsi,
    String? lokasiLat,
    String? lokasiLng,
    String? lokasiManual,
    List<String>? fotoPaths,
  }) async {
    try {
      final data = {
        'kategori': kategori,
        'deskripsi': deskripsi,
        if (lokasiLat != null) 'lokasi_lat': lokasiLat,
        if (lokasiLng != null) 'lokasi_lng': lokasiLng,
        if (lokasiManual != null) 'lokasi_manual': lokasiManual,
      };

      final formData = FormData.fromMap({
        ...data,
        if (fotoPaths != null)
          for (int i = 0; i < fotoPaths.length; i++)
            'foto[$i]': await MultipartFile.fromFile(fotoPaths[i]),
      });

      await _api.postForm('/pengaduan', formData: formData);
      await fetchList();
      return 'Pengaduan berhasil dikirim';
    } catch (e) {
      return e.toString();
    }
  }

  Future<String?> beriRating(int id, int rating, {String? ulasan}) async {
    try {
      await _api.post('/pengaduan/$id/rating', data: {
        'rating': rating,
        if (ulasan != null) 'ulasan': ulasan,
      });
      return null;
    } catch (e) {
      return e.toString();
    }
  }
}

final pengaduanProvider = StateNotifierProvider<PengaduanNotifier, PengaduanState>((ref) {
  return PengaduanNotifier();
});
