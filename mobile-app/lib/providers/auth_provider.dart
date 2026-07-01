import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:portal_warga/models/user_model.dart';
import 'package:portal_warga/services/api_service.dart';

class AuthState {
  final UserModel? user;
  final String? token;
  final bool isLoading;
  final String? error;
  final bool isInitialized;

  const AuthState({
    this.user,
    this.token,
    this.isLoading = false,
    this.error,
    this.isInitialized = false,
  });

  AuthState copyWith({
    UserModel? user,
    String? token,
    bool? isLoading,
    String? error,
    bool? isInitialized,
  }) {
    return AuthState(
      user: user ?? this.user,
      token: token ?? this.token,
      isLoading: isLoading ?? this.isLoading,
      error: error,
      isInitialized: isInitialized ?? this.isInitialized,
    );
  }
}

class AuthNotifier extends StateNotifier<AuthState> {
  final ApiService _api = ApiService();

  AuthNotifier() : super(const AuthState());

  Future<void> checkToken() async {
    final token = await _api.getToken();
    if (token != null) {
      try {
        final response = await _api.get('/auth/me');
        final user = UserModel.fromJson(response.data['data']['user']);
        state = AuthState(
          user: user,
          token: token,
          isInitialized: true,
        );
      } catch (_) {
        await _api.removeToken();
        state = const AuthState(isInitialized: true);
      }
    } else {
      state = const AuthState(isInitialized: true);
    }
  }

  Future<void> login(String email, String password) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final response = await _api.post('/auth/login', data: {
        'email': email,
        'password': password,
      });
      final data = response.data['data'];
      final token = data['token'] as String;
      final user = UserModel.fromJson(data['user']);
      await _api.setToken(token);
      state = AuthState(user: user, token: token, isInitialized: true);
    } catch (e) {
      final message = _extractError(e);
      state = state.copyWith(isLoading: false, error: message);
    }
  }

  Future<void> register({
    required String nik,
    required String nama,
    String? alamat,
    String? rtId,
    String? rwId,
    String? noTelp,
    required String password,
    required String passwordConfirmation,
  }) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final response = await _api.post('/auth/register', data: {
        'nik': nik,
        'nama': nama,
        'alamat': alamat,
        'rt_id': rtId,
        'rw_id': rwId,
        'no_telp': noTelp,
        'password': password,
        'password_confirmation': passwordConfirmation,
      });
      final data = response.data['data'];
      final token = data['token'] as String;
      final user = UserModel.fromJson(data['user']);
      await _api.setToken(token);
      state = AuthState(user: user, token: token, isInitialized: true);
    } catch (e) {
      final message = _extractError(e);
      state = state.copyWith(isLoading: false, error: message);
    }
  }

  Future<void> logout() async {
    try {
      await _api.post('/auth/logout');
    } catch (_) {}
    await _api.removeToken();
    state = const AuthState(isInitialized: true);
  }

  void clearError() {
    state = state.copyWith(error: null);
  }

  String _extractError(dynamic e) {
    if (e is Exception) {
      final errorStr = e.toString();
      if (errorStr.contains('message')) {
        try {
          final dioError = e as dynamic;
          if (dioError.response?.data != null) {
            return dioError.response.data['message'] ?? 'Terjadi kesalahan';
          }
        } catch (_) {}
      }
      return 'Terjadi kesalahan. Silakan coba lagi.';
    }
    return 'Terjadi kesalahan. Silakan coba lagi.';
  }
}

final authProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  return AuthNotifier();
});
