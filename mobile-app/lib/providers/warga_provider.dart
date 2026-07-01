import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:portal_warga/services/api_service.dart';

class ProfileState {
  final Map<String, dynamic>? data;
  final bool isLoading;
  final String? error;

  const ProfileState({this.data, this.isLoading = false, this.error});

  ProfileState copyWith({Map<String, dynamic>? data, bool? isLoading, String? error}) {
    return ProfileState(data: data ?? this.data, isLoading: isLoading ?? this.isLoading, error: error);
  }
}

class ProfileNotifier extends StateNotifier<ProfileState> {
  final ApiService _api = ApiService();

  ProfileNotifier() : super(const ProfileState());

  Future<void> fetchProfile() async {
    state = state.copyWith(isLoading: true);
    try {
      final res = await _api.get('/profile');
      state = ProfileState(data: res.data['data']);
    } catch (e) {
      state = ProfileState(error: 'Gagal memuat profil');
    }
  }

  Future<void> updateProfile(Map<String, Object> data) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final formData = FormData.fromMap(data.cast<String, dynamic>());
      final res = await _api.postForm('/profile', formData: formData);
      state = ProfileState(data: res.data['data']);
    } catch (e) {
      state = state.copyWith(isLoading: false, error: 'Gagal memperbarui profil');
    }
  }

  Future<void> fetchInfoRtRw() async {
    state = state.copyWith(isLoading: true);
    try {
      final res = await _api.get('/rt-rw');
      final list = res.data['data'] as List;
      if (list.isNotEmpty) {
        state = ProfileState(data: list.first);
      } else {
        state = ProfileState(error: 'Belum ada data RT/RW');
      }
    } catch (e) {
      state = ProfileState(error: 'Gagal memuat informasi RT/RW');
    }
  }
}

final profileProvider = StateNotifierProvider<ProfileNotifier, ProfileState>((ref) {
  return ProfileNotifier();
});
