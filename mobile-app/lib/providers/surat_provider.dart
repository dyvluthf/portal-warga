import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:portal_warga/models/surat_model.dart';
import 'package:portal_warga/services/api_service.dart';

class SuratState {
  final List<SuratModel> list;
  final bool isLoading;
  final String? error;
  final String filterStatus;
  final int currentPage;
  final int? lastPage;

  const SuratState({
    this.list = const [],
    this.isLoading = false,
    this.error,
    this.filterStatus = '',
    this.currentPage = 1,
    this.lastPage,
  });

  SuratState copyWith({
    List<SuratModel>? list,
    bool? isLoading,
    String? error,
    String? filterStatus,
    int? currentPage,
    int? lastPage,
  }) {
    return SuratState(
      list: list ?? this.list,
      isLoading: isLoading ?? this.isLoading,
      error: error,
      filterStatus: filterStatus ?? this.filterStatus,
      currentPage: currentPage ?? this.currentPage,
      lastPage: lastPage ?? this.lastPage,
    );
  }
}

class SuratNotifier extends StateNotifier<SuratState> {
  final ApiService _api = ApiService();

  SuratNotifier() : super(const SuratState());

  Future<void> fetchList({String? status, int? page}) async {
    state = state.copyWith(isLoading: true, error: null);
    if (page != null) state = state.copyWith(currentPage: page);
    if (status != null) state = state.copyWith(filterStatus: status);

    try {
      final params = {'per_page': '15', 'page': '${state.currentPage}'};
      if (state.filterStatus.isNotEmpty) params['status'] = state.filterStatus;

      final response = await _api.get('/surat', queryParams: params);
      final data = response.data['data'];
      final list = (data['data'] as List).map((e) => SuratModel.fromJson(e)).toList();

      state = state.copyWith(list: list, isLoading: false, lastPage: data['last_page']);
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }

  Future<SuratModel?> getDetail(int id) async {
    try {
      final response = await _api.get('/surat/$id');
      return SuratModel.fromJson(response.data['data']);
    } catch (_) {
      return null;
    }
  }

  Future<String?> create({
    required String jenisSurat,
    required Map<String, dynamic> dataForm,
    List<String>? dokumenPaths,
  }) async {
    try {
      final formData = FormData.fromMap({
        'jenis_surat': jenisSurat,
        for (final entry in dataForm.entries) 'data_form[${entry.key}]': '${entry.value}',
        if (dokumenPaths != null)
          for (int i = 0; i < dokumenPaths.length; i++)
            'dokumen_pendukung[$i]': await MultipartFile.fromFile(dokumenPaths[i]),
      });

      await _api.postForm('/surat', formData: formData);
      await fetchList();
      return 'Surat berhasil diajukan';
    } catch (e) {
      return e.toString();
    }
  }
}

final suratProvider = StateNotifierProvider<SuratNotifier, SuratState>((ref) {
  return SuratNotifier();
});
