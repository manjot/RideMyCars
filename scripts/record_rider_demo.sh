#!/bin/bash
# ─────────────────────────────────────────────────────────────────────────────
#  RideMyCars – Rider App Screen Recording Script for Apple App Store Review
#  Records full user walkthrough: Login → Home → Drawer → Account → Logout
# ─────────────────────────────────────────────────────────────────────────────
set -e

SIM_UDID="60D7F70F-8B73-426B-86B7-8D8CA6F945D8"
BUNDLE_ID="com.ridemycars.rider"
OUTPUT_DIR="/Users/manjotsingh/RideMyCars/store_metadata/assets"
OUTPUT_FILE="$OUTPUT_DIR/RideMyCars_Rider_Demo.mp4"
FLUTTER_PROJECT="/Users/manjotsingh/RideMyCars/flutter_app"

echo "======================================================"
echo "  RideMyCars Rider App – Apple Review Screen Recording"
echo "======================================================"

# ── Step 1: Free disk space (keep build so app stays registered on simulator) ──
echo "[1/7] Clearing DerivedData to free disk space (keeping flutter build)..."
rm -rf ~/Library/Developer/Xcode/DerivedData 2>/dev/null || true
# NOTE: We do NOT run flutter clean here — doing so unregisters the app from the
# simulator and breaks `simctl privacy grant` which requires the app to be known.

# ── Step 2: Set clean simulator status bar ───────────────────────────────────
echo "[2/7] Setting clean status bar (09:41, full battery, full signal)..."
xcrun simctl status_bar "$SIM_UDID" override \
  --time "09:41" \
  --batteryState charged \
  --batteryLevel 100 \
  --wifiBars 3 \
  --cellularBars 4 \
  --cellularMode active

# ── Step 3: Pre-grant all permissions (no system popups in recording) ─────────
echo "[3/7] Resetting then pre-granting all iOS permissions..."
# Reset first to clear any 'denied' state, then grant everything
xcrun simctl privacy "$SIM_UDID" reset all "$BUNDLE_ID" 2>/dev/null || true
sleep 1
# Build & install the app first so simctl privacy grant can register it
echo "     Building and installing app to register bundle ID..."
cd "$FLUTTER_PROJECT"
flutter build ios --simulator --debug -t lib/main.dart --suppress-analytics 2>/dev/null || true
APP_PATH=$(find "$FLUTTER_PROJECT/build/ios/iphonesimulator" -name "*.app" 2>/dev/null | head -1)
if [ -n "$APP_PATH" ]; then
  xcrun simctl install "$SIM_UDID" "$APP_PATH" 2>/dev/null || true
  xcrun simctl launch "$SIM_UDID" "$BUNDLE_ID" 2>/dev/null || true
  sleep 3
  xcrun simctl terminate "$SIM_UDID" "$BUNDLE_ID" 2>/dev/null || true
  sleep 1
fi
# Now grant — the app is registered, so these take effect
xcrun simctl privacy "$SIM_UDID" grant all "$BUNDLE_ID" 2>/dev/null || true
xcrun simctl privacy "$SIM_UDID" grant location "$BUNDLE_ID" 2>/dev/null || true
xcrun simctl privacy "$SIM_UDID" grant location-always "$BUNDLE_ID" 2>/dev/null || true
xcrun simctl privacy "$SIM_UDID" grant camera "$BUNDLE_ID" 2>/dev/null || true
xcrun simctl privacy "$SIM_UDID" grant microphone "$BUNDLE_ID" 2>/dev/null || true
xcrun simctl privacy "$SIM_UDID" grant notifications "$BUNDLE_ID" 2>/dev/null || true
xcrun simctl privacy "$SIM_UDID" grant photos "$BUNDLE_ID" 2>/dev/null || true
echo "     All permissions granted cleanly."
cd "$FLUTTER_PROJECT"

# ── Step 4: Terminate any existing instance ──────────────────────────────────
echo "[4/7] Terminating any existing app instance..."
xcrun simctl terminate "$SIM_UDID" "$BUNDLE_ID" 2>/dev/null || true
sleep 1

# ── Step 5: Start screen recording ──────────────────────────────────────────
echo "[5/7] Starting screen recording → $OUTPUT_FILE"
mkdir -p "$OUTPUT_DIR"
xcrun simctl io "$SIM_UDID" recordVideo --codec=h264 --force "$OUTPUT_FILE" &
REC_PID=$!
sleep 2

# ── Cleanup trap ─────────────────────────────────────────────────────────────
cleanup() {
  echo ""
  echo "[Cleanup] Stopping screen recording (PID: $REC_PID)..."
  kill -INT "$REC_PID" 2>/dev/null || true
  wait "$REC_PID" 2>/dev/null || true
  echo "[Cleanup] Resetting status bar..."
  xcrun simctl status_bar "$SIM_UDID" clear 2>/dev/null || true
  echo "──────────────────────────────────────────────────────"
  echo "  Recording saved to:"
  echo "  $OUTPUT_FILE"
  echo "──────────────────────────────────────────────────────"
}
trap cleanup EXIT

# ── Step 6: Run integration test walkthrough ─────────────────────────────────
echo "[6/7] Running Rider walkthrough integration test..."
cd "$FLUTTER_PROJECT"
flutter test integration_test/rider_walkthrough_test.dart \
  -d "$SIM_UDID" \
  --reporter expanded

# ── Step 7: Done ─────────────────────────────────────────────────────────────
echo "[7/7] Test complete – finalizing video..."
