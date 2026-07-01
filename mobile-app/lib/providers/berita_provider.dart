import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:portal_warga/models/berita_model.dart';
import 'package:portal_warga/services/api_service.dart';

class BeritaListState {
  final List<BeritaModel> list;
  final bool isLoading;
  final bool hasMore;
  final String? kategori;
  final int page;

  const BeritaListState({
    this.list = const [],
    this.isLoading = false,
    this.hasMore = true,
    this.kategori,
    this.page = 1,
  });

  BeritaListState copyWith({
    List<BeritaModel>? list,
    bool? isLoading,
    bool? hasMore,
    String? kategori,
    int? page,
  }) {
    return BeritaListState(
      list: list ?? this.list,
      isLoading: isLoading ?? this.isLoading,
      hasMore: hasMore ?? this.hasMore,
      kategori: kategori ?? this.kategori,
      page: page ?? this.page,
    );
  }
}

class BeritaNotifier extends StateNotifier<BeritaListState> {
  final ApiService _api = ApiService();

  BeritaNotifier() : super(const BeritaListState());

  Future<void> fetchBerita({bool refresh = false}) async {
    if (state.isLoading) return;

    final page = refresh ? 1 : state.page;
    if (!refresh && !state.hasMore) return;

    state = state.copyWith(isLoading: true);

    try {
      final params = <String, dynamic>{
        'page': page,
        'per_page': 10,
      };
      if (state.kategori != null) {
        params['kategori'] = state.kategori;
      }

      final response = await _api.get('/berita', queryParams: params);
      final data = response.data['data'];
      final List<dynamic> items = data['data'];
      final newList = items.map((e) => BeritaModel.fromJson(e)).toList();
      final lastPage = data['last_page'] as int;

      state = BeritaListState(
        list: refresh ? newList : [...state.list, ...newList],
        hasMore: page < lastPage,
        page: page + 1,
        kategori: state.kategori,
      );
    } catch (_) {
      state = state.copyWith(isLoading: false);
    }
  }

  void setKategori(String? kategori) {
    state = BeritaListState(kategori: kategori);
    fetchBerita(refresh: true);
  }
}

final beritaProvider = StateNotifierProvider<BeritaNotifier, BeritaListState>((ref) {
  return BeritaNotifier();
});

final beritaDetailProvider = FutureProvider.family<BeritaModel, int>((ref, id) async {
  final api = ApiService();
  final response = await api.get('/berita/$id');
  return BeritaModel.fromJson(response.data['data']);
});
