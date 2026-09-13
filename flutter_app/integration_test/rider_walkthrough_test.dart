import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';
import 'package:flutter_app/core/storage/token_storage.dart';
import 'package:flutter_app/main.dart' as app;

void main() {
  IntegrationTestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('Rider App Full User Flow Walkthrough for Apple Review', (WidgetTester tester) async {
    // Ensure starting in logged-out state so full Login Flow is demonstrated in video
    await TokenStorage.clear();

    app.main();
    // Allow Splash screen delay (1.2s) to finish and transition to LoginScreen
    await Future.delayed(const Duration(seconds: 3));
    for (int i = 0; i < 10; i++) {
      await tester.pump(const Duration(milliseconds: 300));
    }

    // STEP 1: Enter Phone Number on Login Screen (Demonstrating Mobile Auth Flow)
    final allFields = find.byType(TextField);
    if (allFields.evaluate().isNotEmpty) {
      await tester.enterText(allFields.first, '9876543210');
      for (int i = 0; i < 3; i++) {
        await tester.pump(const Duration(milliseconds: 300));
      }
    }

    await Future.delayed(const Duration(seconds: 2));

    // STEP 2: Switch to Password Login Tab & Sign In
    final passwordTab = find.textContaining('Password');
    if (passwordTab.evaluate().isNotEmpty) {
      await tester.tap(passwordTab.first);
      for (int i = 0; i < 5; i++) {
        await tester.pump(const Duration(milliseconds: 300));
      }
    }

    await Future.delayed(const Duration(seconds: 1));

    // Tap Sign In button
    final signInBtn = find.text('Sign In');
    if (signInBtn.evaluate().isNotEmpty) {
      await tester.tap(signInBtn.first);
    }

    // Wait for authentication and Home Screen to load
    await Future.delayed(const Duration(seconds: 4));
    for (int i = 0; i < 10; i++) {
      await tester.pump(const Duration(milliseconds: 300));
    }

    // STEP 3: Explore Vehicle Ride Categories on Live Map
    final execTier = find.textContaining('Executive');
    if (execTier.evaluate().isNotEmpty) {
      await tester.tap(execTier.first);
      for (int i = 0; i < 5; i++) {
        await tester.pump(const Duration(milliseconds: 300));
      }
    }

    final luxTier = find.textContaining('Luxury');
    if (luxTier.evaluate().isNotEmpty) {
      await tester.tap(luxTier.first);
      for (int i = 0; i < 5; i++) {
        await tester.pump(const Duration(milliseconds: 300));
      }
    }

    final suvTier = find.textContaining('SUV');
    if (suvTier.evaluate().isNotEmpty) {
      await tester.tap(suvTier.first);
      for (int i = 0; i < 5; i++) {
        await tester.pump(const Duration(milliseconds: 300));
      }
    }

    await Future.delayed(const Duration(seconds: 2));

    // STEP 4: Open Navigation Drawer
    final menuBtn = find.byIcon(Icons.menu_rounded);
    if (menuBtn.evaluate().isNotEmpty) {
      await tester.tap(menuBtn.first);
    } else if (find.byIcon(Icons.menu).evaluate().isNotEmpty) {
      await tester.tap(find.byIcon(Icons.menu).first);
    }
    for (int i = 0; i < 5; i++) {
      await tester.pump(const Duration(milliseconds: 300));
    }
    await Future.delayed(const Duration(seconds: 2));

    // STEP 5: Open Manage Account Screen
    final manageAccount = find.text('Manage Account');
    if (manageAccount.evaluate().isNotEmpty) {
      await tester.tap(manageAccount.first);
      for (int i = 0; i < 8; i++) {
        await tester.pump(const Duration(milliseconds: 300));
      }
    }
    await Future.delayed(const Duration(seconds: 2));

    // STEP 6: Scroll down to Safety & Privacy to show Delete Account
    final deleteAccountBtn = find.text('Delete Account');
    if (deleteAccountBtn.evaluate().isNotEmpty) {
      await tester.ensureVisible(deleteAccountBtn.first);
      for (int i = 0; i < 5; i++) {
        await tester.pump(const Duration(milliseconds: 300));
      }
      await Future.delayed(const Duration(seconds: 2));

      // STEP 7: Tap Delete Account Button to show Apple Guideline 5.1.1(v) Dialog
      await tester.tap(deleteAccountBtn.first, warnIfMissed: false);
      for (int i = 0; i < 5; i++) {
        await tester.pump(const Duration(milliseconds: 300));
      }

      // Pause on the Delete Account confirmation dialog for 5 seconds for Apple Review
      await Future.delayed(const Duration(seconds: 5));

      // Dismiss the dialog with Cancel
      final cancelBtn = find.text('Cancel');
      if (cancelBtn.evaluate().isNotEmpty) {
        await tester.tap(cancelBtn.first);
        for (int i = 0; i < 4; i++) {
          await tester.pump(const Duration(milliseconds: 300));
        }
      }
    }

    // Navigate back to Home
    final backBtn = find.byIcon(Icons.arrow_back_ios_new_rounded);
    if (backBtn.evaluate().isNotEmpty) {
      await tester.tap(backBtn.first);
      for (int i = 0; i < 5; i++) {
        await tester.pump(const Duration(milliseconds: 300));
      }
    }

    await Future.delayed(const Duration(seconds: 2));

    // STEP 8: Open Drawer again and view My Rides
    final menuBtn2 = find.byIcon(Icons.menu_rounded);
    if (menuBtn2.evaluate().isNotEmpty) {
      await tester.tap(menuBtn2.first);
    } else if (find.byIcon(Icons.menu).evaluate().isNotEmpty) {
      await tester.tap(find.byIcon(Icons.menu).first);
    }
    for (int i = 0; i < 5; i++) {
      await tester.pump(const Duration(milliseconds: 300));
    }
    await Future.delayed(const Duration(seconds: 1));

    final myRidesTile = find.text('My Rides');
    if (myRidesTile.evaluate().isNotEmpty) {
      await tester.tap(myRidesTile.first);
      await Future.delayed(const Duration(seconds: 3));
      for (int i = 0; i < 6; i++) {
        await tester.pump(const Duration(milliseconds: 300));
      }

      final backBtn2 = find.byIcon(Icons.arrow_back_ios_new_rounded);
      if (backBtn2.evaluate().isNotEmpty) {
        await tester.tap(backBtn2.first);
        for (int i = 0; i < 5; i++) {
          await tester.pump(const Duration(milliseconds: 300));
        }
      }
    }

    await Future.delayed(const Duration(seconds: 2));
  });
}


