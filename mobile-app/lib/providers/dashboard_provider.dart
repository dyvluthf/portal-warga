import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:portal_warga/services/api_service.dart';

class DashboardState {
  final Map<String, dynamic>? data;
  final bool isLoading;
  final String? error;

  const DashboardState({this.data, this.isLoading = false, this.error});

  DashboardState copyWith({Map<String, dynamic>? data, bool? isLoading, String? error}) {
    return DashboardState(data: data ?? this.data, isLoading: isLoading ?? this.isLoading, error: error);
  }
}

class DashboardNotifier extends StateNotifier<DashboardState> {
  final ApiService _api = ApiService();

  DashboardNotifier() : super(const DashboardState());

  Future<void> fetchWargaDashboard() async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final response = await _api.get('/dashboard/warga');
      state = DashboardState(data: response.data['data']);
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }
}

final dashboardProvider = StateNotifierProvider<DashboardNotifier, DashboardState>((ref) {
  return DashboardNotifier();
});
