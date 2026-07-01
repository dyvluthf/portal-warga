import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:portal_warga/models/iuran_model.dart';
import 'package:portal_warga/services/api_service.dart';

class IuranState {
  final List<IuranModel> list;
  final bool isLoading;
  final String? error;
  final Map<String, dynamic>? dashboard;

  const IuranState({this.list = const [], this.isLoading = false, this.error, this.dashboard});

  IuranState copyWith({List<IuranModel>? list, bool? isLoading, String? error, Map<String, dynamic>? dashboard}) {
    return IuranState(
      list: list ?? this.list,
      isLoading: isLoading ?? this.isLoading,
      error: error,
      dashboard: dashboard ?? this.dashboard,
    );
  }
}

class IuranNotifier extends StateNotifier<IuranState> {
  final ApiService _api = ApiService();

  IuranNotifier() : super(const IuranState());

  Future<void> fetchList({String? bulan}) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final params = <String, dynamic>{'per_page': '50'};
      if (bulan != null) params['bulan'] = bulan;
      final response = await _api.get('/iuran', queryParams: params);
      final list = (response.data['data']['data'] as List).map((e) => IuranModel.fromJson(e)).toList();
      state = state.copyWith(list: list, isLoading: false);
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }

  Future<Map<String, dynamic>?> fetchDashboard({String? bulan}) async {
    try {
      final params = <String, dynamic>{};
      if (bulan != null) params['bulan'] = bulan;
      final response = await _api.get('/keuangan/dashboard', queryParams: params);
      return response.data['data'];
    } catch (_) {
      return null;
    }
  }

  Future<String?> bayarManual({
    required List<String> bulanList,
    required double nominal,
    required String buktiPath,
  }) async {
    try {
      final formData = FormData.fromMap({
        'bulan_list': bulanList,
        'nominal': nominal.toString(),
        'metode': 'manual',
        'bukti_transfer': await MultipartFile.fromFile(buktiPath),
      });
      await _api.postForm('/iuran/bayar', formData: formData);
      await fetchList();
      return 'Pembayaran diajukan';
    } catch (e) {
      return e.toString();
    }
  }

  Future<Map<String, dynamic>?> bayarQris({
    required List<String> bulanList,
    required double nominal,
  }) async {
    try {
      final response = await _api.post('/iuran/bayar', data: {
        'bulan_list': bulanList,
        'nominal': nominal,
        'metode': 'qris',
      });
      return response.data['data'];
    } catch (_) {
      return null;
    }
  }
}

final iuranProvider = StateNotifierProvider<IuranNotifier, IuranState>((ref) {
  return IuranNotifier();
});

final keuanganDashboardProvider = FutureProvider.family<Map<String, dynamic>?, String?>((ref, bulan) async {
  return ref.read(iuranProvider.notifier).fetchDashboard(bulan: bulan);
});
