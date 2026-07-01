import 'package:flutter_test/flutter_test.dart';
import 'package:portal_warga/main.dart';

void main() {
  testWidgets('App renders splash screen', (WidgetTester tester) async {
    await tester.pumpWidget(const PortalWargaApp());
    expect(find.text('Portal Warga'), findsOneWidget);
  });
}
