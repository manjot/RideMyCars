import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';
import 'package:flutter_app/main.dart' as app;

void main() {
  IntegrationTestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('Rider App Full User Flow Walkthrough for Apple Review', (WidgetTester tester) async {
    app.main();
    await tester.pumpAndSettle(const Duration(seconds: 3));

    // 1. On Login Screen, switch to Password login
    final passwordTab = find.text('Password');
    if (passwordTab.evaluate().isNotEmpty) {
      await tester.tap(passwordTab);
      await tester.pumpAndSettle(const Duration(seconds: 1));
    }

    // 2. Tap Sign In button
    final signInBtn = find.widgetWithText(ElevatedButton, 'Sign In');
    if (signInBtn.evaluate().isNotEmpty) {
      await tester.tap(signInBtn);
    } else {
      final anySignIn = find.text('Sign In');
      if (anySignIn.evaluate().isNotEmpty) {
        await tester.tap(anySignIn.first);
      }
    }

    // Wait for authentication & home screen load
    for (int i = 0; i < 6; i++) {
      await tester.pump(const Duration(seconds: 1));
    }
    await tester.pumpAndSettle();
    await Future.delayed(const Duration(seconds: 3));

    // 3. Browse Vehicle Tiers on Home Screen
    final executiveTab = find.text('Executive');
    if (executiveTab.evaluate().isNotEmpty) {
      await tester.tap(executiveTab);
      await tester.pumpAndSettle(const Duration(seconds: 2));
    }

    final luxuryTab = find.text('Luxury');
    if (luxuryTab.evaluate().isNotEmpty) {
      await tester.tap(luxuryTab);
      await tester.pumpAndSettle(const Duration(seconds: 2));
    }

    final suvTab = find.text('SUV');
    if (suvTab.evaluate().isNotEmpty) {
      await tester.tap(suvTab);
      await tester.pumpAndSettle(const Duration(seconds: 2));
    }

    // 4. Open Navigation Drawer
    final menuButton = find.byIcon(Icons.menu_rounded);
    if (menuButton.evaluate().isNotEmpty) {
      await tester.tap(menuButton);
    } else {
      final anyMenu = find.byIcon(Icons.menu);
      if (anyMenu.evaluate().isNotEmpty) {
        await tester.tap(anyMenu);
      }
    }
    await tester.pumpAndSettle(const Duration(seconds: 2));

    // 5. Navigate to Manage Account
    final manageAccountTile = find.text('Manage Account');
    if (manageAccountTile.evaluate().isNotEmpty) {
      await tester.tap(manageAccountTile);
      await tester.pumpAndSettle(const Duration(seconds: 2));

      // Scroll down to Safety & Privacy / Delete Account
      await tester.drag(find.byType(SingleChildScrollView).first, const Offset(0, -350));
      await tester.pumpAndSettle(const Duration(seconds: 2));

      // Tap Delete Account to demonstrate Apple Guideline 5.1.1(v) compliance
      final deleteAccountBtn = find.text('Delete Account');
      if (deleteAccountBtn.evaluate().isNotEmpty) {
        await tester.tap(deleteAccountBtn);
        await tester.pumpAndSettle(const Duration(seconds: 3)); // Show dialog clearly

        // Dismiss dialog
        final cancelBtn = find.text('Cancel');
        if (cancelBtn.evaluate().isNotEmpty) {
          await tester.tap(cancelBtn);
          await tester.pumpAndSettle(const Duration(seconds: 1));
        }
      }

      // Navigate back to Home
      final backBtn = find.byType(BackButton);
      if (backBtn.evaluate().isNotEmpty) {
        await tester.tap(backBtn.first);
        await tester.pumpAndSettle(const Duration(seconds: 1));
      }
    }

    // 6. Open Drawer again and view My Rides
    final menuButton2 = find.byIcon(Icons.menu_rounded);
    if (menuButton2.evaluate().isNotEmpty) {
      await tester.tap(menuButton2);
    } else {
      final anyMenu = find.byIcon(Icons.menu);
      if (anyMenu.evaluate().isNotEmpty) {
        await tester.tap(anyMenu);
      }
    }
    await tester.pumpAndSettle(const Duration(seconds: 1));

    final myRidesTile = find.text('My Rides');
    if (myRidesTile.evaluate().isNotEmpty) {
      await tester.tap(myRidesTile);
      await tester.pumpAndSettle(const Duration(seconds: 3));

      final backBtn = find.byType(BackButton);
      if (backBtn.evaluate().isNotEmpty) {
        await tester.tap(backBtn.first);
        await tester.pumpAndSettle(const Duration(seconds: 1));
      }
    }

    await Future.delayed(const Duration(seconds: 2));
  });
}
