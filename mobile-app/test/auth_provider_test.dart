import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';
import 'package:portal_warga/providers/auth_provider.dart';
import 'package:portal_warga/services/api_service.dart';

class MockApiService extends Mock implements ApiService {}

class MockResponse extends Mock implements Response {}

void main() {
  late AuthNotifier notifier;
  late MockApiService mockApi;

  setUp(() {
    mockApi = MockApiService();
    ApiService.setInstanceForTest(mockApi);
    notifier = AuthNotifier();
  });

  group('login', () {
    test('initial state is unauthenticated', () {
      expect(notifier.state.isInitialized, false);
      expect(notifier.state.user, isNull);
      expect(notifier.state.token, isNull);
    });

    test('sets loading state during login', () async {
      when(() => mockApi.post('/auth/login', data: any(named: 'data')))
          .thenAnswer((_) async {
        await Future.delayed(const Duration(milliseconds: 10));
        throw Exception('timeout');
      });

      final future = notifier.login('test@test.com', 'password');

      expect(notifier.state.isLoading, true);

      await future;
    });

    test('stores error on failed login', () async {
      when(() => mockApi.post('/auth/login', data: any(named: 'data')))
          .thenThrow(Exception('Kredensial tidak valid'));

      await notifier.login('test@test.com', 'wrong');

      expect(notifier.state.error, isNotNull);
      expect(notifier.state.isLoading, false);
    });

    test('stores user and token on successful login', () async {
      final mockResponse = MockResponse();
      when(() => mockResponse.data).thenReturn({
        'data': {
          'user': {'id': 1, 'name': 'Test', 'email': 't@t.com', 'role': 'warga', 'status_aktif': true},
          'token': 'abc123',
        }
      });
      when(() => mockApi.post('/auth/login', data: any(named: 'data')))
          .thenAnswer((_) async => mockResponse);
      when(() => mockApi.setToken(any())).thenAnswer((_) async => {});

      await notifier.login('test@test.com', 'password');

      expect(notifier.state.token, 'abc123');
      expect(notifier.state.user, isNotNull);
      expect(notifier.state.user!.name, 'Test');
    });
  });

  group('register', () {
    test('stores error on failed registration', () async {
      when(() => mockApi.post('/auth/register', data: any(named: 'data')))
          .thenThrow(Exception('NIK sudah terdaftar'));

      await notifier.register(
        nik: '3201010101010101',
        nama: 'Test',
        password: 'password',
        passwordConfirmation: 'password',
      );

      expect(notifier.state.error, isNotNull);
      expect(notifier.state.isLoading, false);
    });
  });

  group('logout', () {
    test('clears user state after logout', () async {
      when(() => mockApi.post('/auth/logout'))
          .thenAnswer((_) async => MockResponse());
      when(() => mockApi.removeToken())
          .thenAnswer((_) async => {});

      await notifier.logout();

      expect(notifier.state.user, isNull);
      expect(notifier.state.token, isNull);
    });
  });

  group('clearError', () {
    test('clears error message', () {
      notifier.clearError();
      expect(notifier.state.error, isNull);
    });
  });
}
