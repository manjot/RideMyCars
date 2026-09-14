import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';
import 'package:flutter_app/core/storage/token_storage.dart';
import 'package:flutter_app/main.dart' as app;

/// ─────────────────────────────────────────────────────────────────────────────
///  RideMyCars – Rider App  |  Apple Review Full Walkthrough
///  Demonstrates: App Launch → Login → Home Map → Drawer → Manage Account →
///                Delete Account Dialog → Wallet → My Rides → Log Out
/// ─────────────────────────────────────────────────────────────────────────────
void main() {
  IntegrationTestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('Rider App Full User Flow Walkthrough for Apple Review',
      (WidgetTester tester) async {
    // ── 0. RESET: ensure fresh logged-out state ──────────────────────────────
    await TokenStorage.clear();

    // ── 1. LAUNCH APP ────────────────────────────────────────────────────────
    app.main();
    // Let the Splash screen run its full animation (~2 s) and then transition
    await _pause(tester, seconds: 4);

    // ── 2. LOGIN SCREEN – show all three auth tabs clearly ───────────────────
    // The app pre-fills credentials; we just demonstrate the UI.

    // Show Phone OTP tab (default)
    await _tapTextContaining(tester, 'Phone');
    await _pause(tester, seconds: 2);

    // Show Email OTP tab
    await _tapTextContaining(tester, 'Email OTP');
    await _pause(tester, seconds: 2);

    // Switch to Password tab
    await _tapTextContaining(tester, 'Password');
    await _pause(tester, seconds: 2);

    // The email + password fields are now visible (pre-filled with demo creds).
    // Demonstrate the password field by clearing and re-typing
    final passwordFields = find.byType(TextFormField);
    if (passwordFields.evaluate().length >= 2) {
      // Focus the password field (second TextFormField in this section)
      await tester.tap(passwordFields.last);
      await _pumpFrames(tester, frames: 5);
    }
    await _pause(tester, seconds: 2);

    // ── 3. SIGN IN ───────────────────────────────────────────────────────────
    final signInBtn = find.text('Sign In');
    if (signInBtn.evaluate().isNotEmpty) {
      await tester.tap(signInBtn.first, warnIfMissed: false);
    }
    // Wait for network auth + home screen to fully render
    await _pause(tester, seconds: 6);
    await _pumpFrames(tester, frames: 20);

    // ── 4. HOME SCREEN – show ride categories ────────────────────────────────
    // Tap through vehicle class pills / tabs if present
    for (final label in ['Executive', 'Luxury', 'SUV', 'Economy']) {
      final pill = find.textContaining(label);
      if (pill.evaluate().isNotEmpty) {
        await tester.tap(pill.first, warnIfMissed: false);
        await _pause(tester, seconds: 2);
      }
    }

    // ── 5. OPEN NAVIGATION DRAWER ────────────────────────────────────────────
    await _openDrawer(tester);
    await _pause(tester, seconds: 3);

    // ── 6. DRAWER → WALLET & PAYMENTS ────────────────────────────────────────
    final walletTile = find.textContaining('Wallet');
    if (walletTile.evaluate().isNotEmpty) {
      await tester.tap(walletTile.first, warnIfMissed: false);
      await _pause(tester, seconds: 4);
      await _goBack(tester);
      await _pause(tester, seconds: 2);
    }

    // ── 7. OPEN DRAWER → MY RIDES ────────────────────────────────────────────
    await _openDrawer(tester);
    await _pause(tester, seconds: 2);

    final myRidesTile = find.text('My Rides');
    if (myRidesTile.evaluate().isNotEmpty) {
      await tester.tap(myRidesTile.first, warnIfMissed: false);
      await _pause(tester, seconds: 4);
      await _goBack(tester);
      await _pause(tester, seconds: 2);
    }

    // ── 8. OPEN DRAWER → NOTIFICATIONS ───────────────────────────────────────
    await _openDrawer(tester);
    await _pause(tester, seconds: 2);

    final notifTile = find.textContaining('Notification');
    if (notifTile.evaluate().isNotEmpty) {
      await tester.tap(notifTile.first, warnIfMissed: false);
      await _pause(tester, seconds: 3);
      await _goBack(tester);
      await _pause(tester, seconds: 2);
    }

    // ── 9. OPEN DRAWER → HELP & SUPPORT ──────────────────────────────────────
    await _openDrawer(tester);
    await _pause(tester, seconds: 2);

    final helpTile = find.textContaining('Help');
    if (helpTile.evaluate().isNotEmpty) {
      await tester.tap(helpTile.first, warnIfMissed: false);
      await _pause(tester, seconds: 3);
      await _goBack(tester);
      await _pause(tester, seconds: 2);
    }

    // ── 10. OPEN DRAWER → MANAGE ACCOUNT (shows Delete Account) ──────────────
    await _openDrawer(tester);
    await _pause(tester, seconds: 2);

    final manageAccountTile = find.text('Manage Account');
    if (manageAccountTile.evaluate().isNotEmpty) {
      await tester.tap(manageAccountTile.first, warnIfMissed: false);
      await _pause(tester, seconds: 4);

      // Scroll down slowly to show all profile info
      final scrollable = find.byType(SingleChildScrollView).first;
      await tester.drag(scrollable, const Offset(0, -250));
      await _pause(tester, seconds: 2);

      // Show "Delete Account" tile (Apple Guideline 5.1.1(v) compliance)
      final deleteAccountTile = find.textContaining('Delete Account');
      if (deleteAccountTile.evaluate().isNotEmpty) {
        await tester.ensureVisible(deleteAccountTile.first);
        await _pause(tester, seconds: 3);

        // Tap Delete Account – show the confirmation dialog
        await tester.tap(deleteAccountTile.first, warnIfMissed: false);
        await _pumpFrames(tester, frames: 8);
        await _pause(tester, seconds: 6); // hold dialog open for reviewer

        // Dismiss with Cancel (we do NOT delete the demo account)
        final cancelBtn = find.text('Cancel');
        if (cancelBtn.evaluate().isNotEmpty) {
          await tester.tap(cancelBtn.first, warnIfMissed: false);
          await _pumpFrames(tester, frames: 5);
        }
      }

      await _pause(tester, seconds: 2);
      await _goBack(tester);
    }

    // ── 11. BACK ON HOME – brief map view ────────────────────────────────────
    await _pause(tester, seconds: 3);

    // ── 12. OPEN DRAWER → LOG OUT (demonstrate sign-out feature) ─────────────
    await _openDrawer(tester);
    await _pause(tester, seconds: 2);

    final logoutTile = find.textContaining('Log Out');
    if (logoutTile.evaluate().isNotEmpty) {
      await tester.tap(logoutTile.first, warnIfMissed: false);
      await _pause(tester, seconds: 4);
    }

    // Final pause – recording ends cleanly on Login screen
    await _pause(tester, seconds: 3);
  });
}

// ─── Helpers ─────────────────────────────────────────────────────────────────

/// Pump [frames] widget-tree frames (each 300 ms).
Future<void> _pumpFrames(WidgetTester tester, {int frames = 6}) async {
  for (int i = 0; i < frames; i++) {
    await tester.pump(const Duration(milliseconds: 300));
  }
}

/// Real-time pause without blocking the widget tree (uses repeated pump).
Future<void> _pause(WidgetTester tester, {required int seconds}) async {
  final frames = (seconds * 1000 / 300).ceil();
  await _pumpFrames(tester, frames: frames);
}

/// Tap the first widget whose text *contains* [text].
Future<void> _tapTextContaining(WidgetTester tester, String text) async {
  final finder = find.textContaining(text);
  if (finder.evaluate().isNotEmpty) {
    await tester.tap(finder.first, warnIfMissed: false);
    await _pumpFrames(tester, frames: 5);
  }
}

/// Open the side drawer via menu icon or scaffold gesture.
Future<void> _openDrawer(WidgetTester tester) async {
  // Try standard hamburger menu icons
  for (final icon in [Icons.menu_rounded, Icons.menu, Icons.dehaze_rounded]) {
    final btn = find.byIcon(icon);
    if (btn.evaluate().isNotEmpty) {
      await tester.tap(btn.first, warnIfMissed: false);
      await _pumpFrames(tester, frames: 6);
      return;
    }
  }
  // Fallback: swipe from left edge
  await tester.dragFrom(const Offset(5, 300), const Offset(200, 300));
  await _pumpFrames(tester, frames: 6);
}

/// Pop the current route (back button).
Future<void> _goBack(WidgetTester tester) async {
  for (final icon in [
    Icons.arrow_back_ios_new_rounded,
    Icons.arrow_back_ios,
    Icons.arrow_back_rounded,
    Icons.arrow_back,
  ]) {
    final btn = find.byIcon(icon);
    if (btn.evaluate().isNotEmpty) {
      await tester.tap(btn.first, warnIfMissed: false);
      await _pumpFrames(tester, frames: 6);
      return;
    }
  }
  // Fallback: Navigator.pop via keyboard back
  final NavigatorState? nav =
      tester.state<NavigatorState>(find.byType(Navigator).last);
  if (nav != null && nav.canPop()) nav.pop();
  await _pumpFrames(tester, frames: 6);
}
