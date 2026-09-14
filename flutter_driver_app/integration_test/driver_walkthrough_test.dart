import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';
import 'package:flutter_driver_app/core/storage/token_storage.dart';
import 'package:flutter_driver_app/main.dart' as app;

/// ─────────────────────────────────────────────────────────────────────────────
///  RideMyCars – Driver App  |  Apple Review Full Walkthrough
///  Demonstrates: App Launch → Login → Dashboard → Drawer → Manage Account →
///                Delete Account Dialog → Earnings → Ride History → Log Out
/// ─────────────────────────────────────────────────────────────────────────────
void main() {
  IntegrationTestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('Driver App Full User Flow Walkthrough for Apple Review',
      (WidgetTester tester) async {
    // ── 0. RESET: ensure fresh logged-out state ──────────────────────────────
    await TokenStorage.clear();

    // ── 1. LAUNCH APP ────────────────────────────────────────────────────────
    app.main();
    // Let the Splash screen run its full animation and transition to Login
    await _pause(tester, seconds: 4);

    // ── 2. DRIVER LOGIN SCREEN – demonstrate all three auth tabs ─────────────

    // Show Phone OTP tab (default)
    await _tapTextContaining(tester, 'Phone');
    await _pause(tester, seconds: 2);

    // Show Email OTP tab
    await _tapTextContaining(tester, 'Email OTP');
    await _pause(tester, seconds: 2);

    // Switch to Password tab (pre-filled with driver demo credentials)
    await _tapTextContaining(tester, 'Password');
    await _pause(tester, seconds: 3);

    // ── 3. SIGN IN TO DASHBOARD ──────────────────────────────────────────────
    // Button text is "Sign In to Dashboard"
    final signInBtn = find.textContaining('Sign In');
    if (signInBtn.evaluate().isNotEmpty) {
      await tester.tap(signInBtn.first, warnIfMissed: false);
    }
    // Wait for network auth + dashboard + map to fully render
    await _pause(tester, seconds: 7);
    await _pumpFrames(tester, frames: 20);

    // ── 4. DASHBOARD – show online/offline toggle & earnings summary ─────────
    // Tap the online/offline toggle if visible
    final goOnlineBtn = find.textContaining('Go Online');
    if (goOnlineBtn.evaluate().isNotEmpty) {
      await tester.tap(goOnlineBtn.first, warnIfMissed: false);
      await _pause(tester, seconds: 3);
    }

    final goOfflineBtn = find.textContaining('Go Offline');
    if (goOfflineBtn.evaluate().isNotEmpty) {
      await tester.tap(goOfflineBtn.first, warnIfMissed: false);
      await _pause(tester, seconds: 2);
    }

    // Show Earnings Summary on dashboard
    final earningsSummary = find.textContaining('Earnings');
    if (earningsSummary.evaluate().isNotEmpty) {
      await tester.ensureVisible(earningsSummary.first);
      await _pause(tester, seconds: 2);
    }

    // ── 5. OPEN DRIVER DRAWER via CircleAvatar in AppBar ─────────────────────
    await _openDriverDrawer(tester);
    await _pause(tester, seconds: 3);

    // ── 6. DRAWER → EARNINGS & PAYOUTS ───────────────────────────────────────
    final earningsTile = find.textContaining('Earnings');
    if (earningsTile.evaluate().isNotEmpty) {
      await tester.tap(earningsTile.first, warnIfMissed: false);
      await _pause(tester, seconds: 4);
      await _goBack(tester);
      await _pause(tester, seconds: 2);
    }

    // ── 7. DRAWER → RIDE HISTORY / MY TRIPS ──────────────────────────────────
    await _openDriverDrawer(tester);
    await _pause(tester, seconds: 2);

    final rideHistoryTile = find.textContaining('Ride History');
    if (rideHistoryTile.evaluate().isNotEmpty) {
      await tester.tap(rideHistoryTile.first, warnIfMissed: false);
      await _pause(tester, seconds: 4);
      await _goBack(tester);
      await _pause(tester, seconds: 2);
    }

    // ── 8. DRAWER → NOTIFICATIONS ─────────────────────────────────────────────
    await _openDriverDrawer(tester);
    await _pause(tester, seconds: 2);

    final notifTile = find.textContaining('Notification');
    if (notifTile.evaluate().isNotEmpty) {
      await tester.tap(notifTile.first, warnIfMissed: false);
      await _pause(tester, seconds: 3);
      await _goBack(tester);
      await _pause(tester, seconds: 2);
    }

    // ── 9. DRAWER → HELP & SUPPORT ───────────────────────────────────────────
    await _openDriverDrawer(tester);
    await _pause(tester, seconds: 2);

    final helpTile = find.textContaining('Help');
    if (helpTile.evaluate().isNotEmpty) {
      await tester.tap(helpTile.first, warnIfMissed: false);
      await _pause(tester, seconds: 3);
      await _goBack(tester);
      await _pause(tester, seconds: 2);
    }

    // ── 10. DRAWER → MANAGE ACCOUNT & PROFILE (Delete Account) ───────────────
    await _openDriverDrawer(tester);
    await _pause(tester, seconds: 2);

    final manageAccountTile = find.textContaining('Manage Account');
    if (manageAccountTile.evaluate().isNotEmpty) {
      await tester.tap(manageAccountTile.first, warnIfMissed: false);
      await _pause(tester, seconds: 4);

      // Scroll down slowly to reveal all profile sections
      final scrollable = find.byType(SingleChildScrollView);
      if (scrollable.evaluate().isNotEmpty) {
        await tester.drag(scrollable.first, const Offset(0, -250));
        await _pause(tester, seconds: 2);
        await tester.drag(scrollable.first, const Offset(0, -250));
        await _pause(tester, seconds: 2);
      }

      // Locate and demonstrate "Delete Account" (Apple Guideline 5.1.1(v))
      final deleteAccountTile = find.textContaining('Delete Account');
      if (deleteAccountTile.evaluate().isNotEmpty) {
        await tester.ensureVisible(deleteAccountTile.first);
        await _pause(tester, seconds: 3);

        // Tap to open the confirmation dialog
        await tester.tap(deleteAccountTile.first, warnIfMissed: false);
        await _pumpFrames(tester, frames: 8);
        await _pause(tester, seconds: 6); // hold for reviewer

        // Dismiss with Cancel (demo account preserved)
        final cancelBtn = find.text('Cancel');
        if (cancelBtn.evaluate().isNotEmpty) {
          await tester.tap(cancelBtn.first, warnIfMissed: false);
          await _pumpFrames(tester, frames: 5);
        }
      }

      await _pause(tester, seconds: 2);
      await _goBack(tester);
    }

    // ── 11. BACK ON DASHBOARD ─────────────────────────────────────────────────
    await _pause(tester, seconds: 3);

    // ── 12. DRAWER → LOG OUT (demonstrate sign-out feature) ───────────────────
    await _openDriverDrawer(tester);
    await _pause(tester, seconds: 2);

    final logoutTile = find.textContaining('Log Out');
    if (logoutTile.evaluate().isNotEmpty) {
      await tester.tap(logoutTile.first, warnIfMissed: false);
      await _pause(tester, seconds: 4);
    }

    // Final pause – recording ends cleanly on Driver Login screen
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

/// Real-time pause without blocking the widget tree.
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

/// Open the driver drawer via the CircleAvatar in the AppBar.
Future<void> _openDriverDrawer(WidgetTester tester) async {
  // The driver dashboard uses a CircleAvatar to open the drawer
  final avatar = find.byType(CircleAvatar);
  if (avatar.evaluate().isNotEmpty) {
    await tester.tap(avatar.first, warnIfMissed: false);
    await _pumpFrames(tester, frames: 6);
    return;
  }
  // Fallback: hamburger icons
  for (final icon in [Icons.menu_rounded, Icons.menu, Icons.dehaze_rounded]) {
    final btn = find.byIcon(icon);
    if (btn.evaluate().isNotEmpty) {
      await tester.tap(btn.first, warnIfMissed: false);
      await _pumpFrames(tester, frames: 6);
      return;
    }
  }
  // Final fallback: swipe from left edge
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
  // Fallback: Navigator.pop
  final NavigatorState? nav =
      tester.state<NavigatorState>(find.byType(Navigator).last);
  if (nav != null && nav.canPop()) nav.pop();
  await _pumpFrames(tester, frames: 6);
}
