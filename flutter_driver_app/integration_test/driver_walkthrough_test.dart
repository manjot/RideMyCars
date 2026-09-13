import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';
import 'package:flutter_driver_app/core/storage/token_storage.dart';
import 'package:flutter_driver_app/main.dart' as app;

void main() {
  IntegrationTestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('Driver App Full User Flow Walkthrough for Apple Review', (WidgetTester tester) async {
    // Ensure starting in logged-out state so full Login Flow is demonstrated in video
    await TokenStorage.clear();

    app.main();
    // Allow Splash screen delay (1.2s) to finish and transition to DriverLoginScreen
    await Future.delayed(const Duration(seconds: 3));
    for (int i = 0; i < 10; i++) {
      await tester.pump(const Duration(milliseconds: 300));
    }

    // STEP 1: Enter Phone Number on Driver Login Screen
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

    // Wait for authentication and Dashboard to load
    await Future.delayed(const Duration(seconds: 4));
    for (int i = 0; i < 10; i++) {
      await tester.pump(const Duration(milliseconds: 300));
    }

    // STEP 3: Open Driver Navigation Drawer via Driver Avatar in Top App Bar
    final avatarBtn = find.byType(CircleAvatar);
    if (avatarBtn.evaluate().isNotEmpty) {
      await tester.tap(avatarBtn.first);
    }
    for (int i = 0; i < 5; i++) {
      await tester.pump(const Duration(milliseconds: 300));
    }
    await Future.delayed(const Duration(seconds: 1));

    // STEP 4: Navigate to Manage Account & Profile
    final manageAccount = find.text('Manage Account & Profile');
    if (manageAccount.evaluate().isNotEmpty) {
      await tester.tap(manageAccount.first);
      for (int i = 0; i < 8; i++) {
        await tester.pump(const Duration(milliseconds: 300));
      }
    }
    await Future.delayed(const Duration(seconds: 2));

    // STEP 5: Scroll down to Safety & Privacy to show Delete Account
    final deleteAccountBtn = find.text('Delete Account');
    if (deleteAccountBtn.evaluate().isNotEmpty) {
      await tester.ensureVisible(deleteAccountBtn.first);
      for (int i = 0; i < 5; i++) {
        await tester.pump(const Duration(milliseconds: 300));
      }
      await Future.delayed(const Duration(seconds: 2));

      // STEP 6: Tap Delete Account Button to show Apple Guideline 5.1.1(v) Dialog
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

    // Navigate back to Dashboard
    final backBtn = find.byIcon(Icons.arrow_back_ios_new_rounded);
    if (backBtn.evaluate().isNotEmpty) {
      await tester.tap(backBtn.first);
      for (int i = 0; i < 5; i++) {
        await tester.pump(const Duration(milliseconds: 300));
      }
    }

    await Future.delayed(const Duration(seconds: 2));

    // STEP 7: Open Drawer again and view Earnings & Payouts
    final avatarBtn2 = find.byType(CircleAvatar);
    if (avatarBtn2.evaluate().isNotEmpty) {
      await tester.tap(avatarBtn2.first);
    }
    for (int i = 0; i < 5; i++) {
      await tester.pump(const Duration(milliseconds: 300));
    }
    await Future.delayed(const Duration(seconds: 1));

    final earningsTile = find.text('Earnings & Payouts');
    if (earningsTile.evaluate().isNotEmpty) {
      await tester.tap(earningsTile.first);
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

    // STEP 8: Open Drawer again and view Ride History / My Trips
    final avatarBtn3 = find.byType(CircleAvatar);
    if (avatarBtn3.evaluate().isNotEmpty) {
      await tester.tap(avatarBtn3.first);
    }
    for (int i = 0; i < 5; i++) {
      await tester.pump(const Duration(milliseconds: 300));
    }
    await Future.delayed(const Duration(seconds: 1));

    final tripsTile = find.text('Ride History / My Trips');
    if (tripsTile.evaluate().isNotEmpty) {
      await tester.tap(tripsTile.first);
      await Future.delayed(const Duration(seconds: 3));
      for (int i = 0; i < 6; i++) {
        await tester.pump(const Duration(milliseconds: 300));
      }

      final backBtn3 = find.byIcon(Icons.arrow_back_ios_new_rounded);
      if (backBtn3.evaluate().isNotEmpty) {
        await tester.tap(backBtn3.first);
        for (int i = 0; i < 5; i++) {
          await tester.pump(const Duration(milliseconds: 300));
        }
      }
    }

    await Future.delayed(const Duration(seconds: 2));
  });
}


