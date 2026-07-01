import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';
import 'package:portal_warga/providers/surat_provider.dart';
import 'package:portal_warga/services/api_service.dart';

class MockApiService extends Mock implements ApiService {}

class MockResponse extends Mock implements Response {}

void main() {
  late SuratNotifier notifier;
  late MockApiService mockApi;

  setUp(() {
    mockApi = MockApiService();
    ApiService.setInstanceForTest(mockApi);
    notifier = SuratNotifier();
  });

  group('fetchList', () {
    test('initial state has empty list', () {
      expect(notifier.state.list, isEmpty);
      expect(notifier.state.isLoading, false);
    });

    test('populates list on successful fetch', () async {
      final mockResponse = MockResponse();
      when(() => mockResponse.data).thenReturn({
        'data': {
          'data': [
            {
              'id': 1,
              'jenis_surat': 'sktm',
              'status': 'menunggu',
              'created_at': '2026-07-01T00:00:00.000000Z',
            }
          ],
          'last_page': 1,
        }
      });

      when(() => mockApi.get('/surat', queryParams: any(named: 'queryParams')))
          .thenAnswer((_) async => mockResponse);

      await notifier.fetchList();

      expect(notifier.state.list.length, 1);
      expect(notifier.state.list.first.jenisSurat, 'sktm');
      expect(notifier.state.isLoading, false);
    });

    test('stores error on failed fetch', () async {
      when(() => mockApi.get('/surat', queryParams: any(named: 'queryParams')))
          .thenThrow(Exception('Gagal memuat data'));

      await notifier.fetchList();

      expect(notifier.state.error, isNotNull);
      expect(notifier.state.isLoading, false);
    });
  });

  group('getDetail', () {
    test('returns null on error', () async {
      when(() => mockApi.get('/surat/999', queryParams: any(named: 'queryParams')))
          .thenThrow(Exception('Not found'));

      final result = await notifier.getDetail(999);
      expect(result, isNull);
    });
  });
}
