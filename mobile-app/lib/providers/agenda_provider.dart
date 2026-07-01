import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:portal_warga/models/agenda_model.dart';
import 'package:portal_warga/services/api_service.dart';

class AgendaState {
  final List<AgendaModel> list;
  final bool isLoading;
  final String? error;

  const AgendaState({this.list = const [], this.isLoading = false, this.error});

  AgendaState copyWith({List<AgendaModel>? list, bool? isLoading, String? error}) {
    return AgendaState(list: list ?? this.list, isLoading: isLoading ?? this.isLoading, error: error);
  }
}

class AgendaNotifier extends StateNotifier<AgendaState> {
  final ApiService _api = ApiService();

  AgendaNotifier() : super(const AgendaState());

  Future<void> fetchList({String? periode}) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final params = <String, dynamic>{'per_page': '50'};
      if (periode != null) params['periode'] = periode;
      final response = await _api.get('/agenda', queryParams: params);
      final list = (response.data['data']['data'] as List).map((e) => AgendaModel.fromJson(e)).toList();
      state = state.copyWith(list: list, isLoading: false);
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }

  Future<String?> rsvp(int agendaId, String statusRsvp) async {
    try {
      await _api.post('/agenda/$agendaId/rsvp', data: {'status_rsvp': statusRsvp});
      return null;
    } catch (e) {
      return e.toString();
    }
  }
}

final agendaProvider = StateNotifierProvider<AgendaNotifier, AgendaState>((ref) {
  return AgendaNotifier();
});
