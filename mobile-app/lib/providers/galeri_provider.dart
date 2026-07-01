import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:portal_warga/models/galeri_model.dart';
import 'package:portal_warga/services/api_service.dart';

class GaleriState {
  final List<GaleriAlbumModel> list;
  final bool isLoading;
  final String? error;

  const GaleriState({this.list = const [], this.isLoading = false, this.error});

  GaleriState copyWith({List<GaleriAlbumModel>? list, bool? isLoading, String? error}) {
    return GaleriState(list: list ?? this.list, isLoading: isLoading ?? this.isLoading, error: error);
  }
}

class GaleriNotifier extends StateNotifier<GaleriState> {
  final ApiService _api = ApiService();

  GaleriNotifier() : super(const GaleriState());

  Future<void> fetchList() async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final response = await _api.get('/galeri', queryParams: {'per_page': '50'});
      final list = (response.data['data']['data'] as List).map((e) => GaleriAlbumModel.fromJson(e)).toList();
      state = state.copyWith(list: list, isLoading: false);
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }

  Future<GaleriAlbumModel?> getDetail(int id) async {
    try {
      final response = await _api.get('/galeri/$id');
      return GaleriAlbumModel.fromJson(response.data['data']);
    } catch (_) {
      return null;
    }
  }
}

final galeriProvider = StateNotifierProvider<GaleriNotifier, GaleriState>((ref) {
  return GaleriNotifier();
});
