import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_app/main.dart';

void main() {
  testWidgets('App smoke test', (WidgetTester tester) async {
    await tester.pumpWidget(const RideMyCarsRiderApp());
    expect(find.byType(RideMyCarsRiderApp), findsOneWidget);
    await tester.pump(const Duration(seconds: 2));
  });
}
