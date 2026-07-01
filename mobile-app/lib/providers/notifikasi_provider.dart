import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:portal_warga/models/notifikasi_model.dart';
import 'package:portal_warga/services/api_service.dart';

class NotifikasiState {
  final List<NotifikasiModel> list;
  final int unreadCount;
  final bool isLoading;

  const NotifikasiState({
    this.list = const [],
    this.unreadCount = 0,
    this.isLoading = false,
  });

  NotifikasiState copyWith({
    List<NotifikasiModel>? list,
    int? unreadCount,
    bool? isLoading,
  }) {
    return NotifikasiState(
      list: list ?? this.list,
      unreadCount: unreadCount ?? this.unreadCount,
      isLoading: isLoading ?? this.isLoading,
    );
  }
}

class NotifikasiNotifier extends StateNotifier<NotifikasiState> {
  final ApiService _api = ApiService();

  NotifikasiNotifier() : super(const NotifikasiState());

  Future<void> fetchList() async {
    state = state.copyWith(isLoading: true);
    try {
      final response = await _api.get('/notifikasi', queryParams: {'per_page': '50'});
      final list = (response.data['data']['data'] as List)
          .map((e) => NotifikasiModel.fromJson(e))
          .toList();
      state = state.copyWith(list: list, isLoading: false);
    } catch (_) {
      state = state.copyWith(isLoading: false);
    }
  }

  Future<void> fetchUnreadCount() async {
    try {
      final response = await _api.get('/notifikasi/unread');
      state = state.copyWith(unreadCount: response.data['data']['count'] ?? 0);
    } catch (_) {}
  }

  Future<void> markRead(int id) async {
    try {
      await _api.post('/notifikasi/$id/read');
      await fetchUnreadCount();
      await fetchList();
    } catch (_) {}
  }

  Future<void> markAllRead() async {
    try {
      await _api.post('/notifikasi/read-all');
      state = state.copyWith(unreadCount: 0);
      await fetchList();
    } catch (_) {}
  }

  Future<void> updateFcmToken(String token) async {
    try {
      await _api.post('/notifikasi/fcm-token', data: {'fcm_token': token});
    } catch (_) {}
  }
}

final notifikasiProvider = StateNotifierProvider<NotifikasiNotifier, NotifikasiState>((ref) {
  return NotifikasiNotifier();
});
