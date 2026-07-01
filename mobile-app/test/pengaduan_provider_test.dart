import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';
import 'package:portal_warga/providers/pengaduan_provider.dart';
import 'package:portal_warga/services/api_service.dart';

class MockApiService extends Mock implements ApiService {}

class MockResponse extends Mock implements Response {}

void main() {
  late PengaduanNotifier notifier;
  late MockApiService mockApi;

  setUp(() {
    mockApi = MockApiService();
    ApiService.setInstanceForTest(mockApi);
    notifier = PengaduanNotifier();
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
              'kategori': 'kebersihan',
              'deskripsi': 'Sampah menumpuk',
              'status': 'menunggu',
              'created_at': '2026-07-01T00:00:00.000000Z',
            }
          ],
          'last_page': 1,
        }
      });

      when(() => mockApi.get('/pengaduan', queryParams: any(named: 'queryParams')))
          .thenAnswer((_) async => mockResponse);

      await notifier.fetchList();

      expect(notifier.state.list.length, 1);
      expect(notifier.state.list.first.kategori, 'kebersihan');
      expect(notifier.state.isLoading, false);
    });

    test('stores error on failed fetch', () async {
      when(() => mockApi.get('/pengaduan', queryParams: any(named: 'queryParams')))
          .thenThrow(Exception('Gagal memuat data'));

      await notifier.fetchList();

      expect(notifier.state.error, isNotNull);
      expect(notifier.state.isLoading, false);
    });
  });

  group('beriRating', () {
    test('returns null on successful rating', () async {
      when(() => mockApi.post('/pengaduan/1/rating', data: any(named: 'data')))
          .thenAnswer((_) async => MockResponse());

      final result = await notifier.beriRating(1, 5, ulasan: 'Bagus');
      expect(result, isNull);
    });

    test('returns error message on failed rating', () async {
      when(() => mockApi.post('/pengaduan/1/rating', data: any(named: 'data')))
          .thenThrow(Exception('Gagal'));

      final result = await notifier.beriRating(1, 5);
      expect(result, isNotNull);
    });
  });
}
