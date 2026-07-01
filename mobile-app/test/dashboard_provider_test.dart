import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';
import 'package:portal_warga/providers/dashboard_provider.dart';
import 'package:portal_warga/services/api_service.dart';

class MockApiService extends Mock implements ApiService {}

class MockResponse extends Mock implements Response {}

void main() {
  late DashboardNotifier notifier;
  late MockApiService mockApi;

  setUp(() {
    mockApi = MockApiService();
    ApiService.setInstanceForTest(mockApi);
    notifier = DashboardNotifier();
  });

  group('fetchWargaDashboard', () {
    test('initial state has no data', () {
      expect(notifier.state.data, isNull);
      expect(notifier.state.isLoading, false);
    });

    test('sets loading state while fetching', () async {
      when(() => mockApi.get('/dashboard/warga', queryParams: any(named: 'queryParams')))
          .thenAnswer((_) async {
        await Future.delayed(const Duration(milliseconds: 10));
        throw Exception('timeout');
      });

      final future = notifier.fetchWargaDashboard();

      expect(notifier.state.isLoading, true);

      await future;
    });

    test('stores data on successful fetch', () async {
      final mockResponse = MockResponse();
      when(() => mockResponse.data).thenReturn({
        'data': {'total_warga': 50, 'total_pengaduan': 5},
      });

      when(() => mockApi.get('/dashboard/warga', queryParams: any(named: 'queryParams')))
          .thenAnswer((_) async => mockResponse);

      await notifier.fetchWargaDashboard();

      expect(notifier.state.data, isNotNull);
      expect(notifier.state.isLoading, false);
      expect(notifier.state.error, isNull);
    });

    test('stores error on failed fetch', () async {
      when(() => mockApi.get('/dashboard/warga', queryParams: any(named: 'queryParams')))
          .thenThrow(Exception('Network error'));

      await notifier.fetchWargaDashboard();

      expect(notifier.state.error, isNotNull);
      expect(notifier.state.isLoading, false);
    });
  });
}
