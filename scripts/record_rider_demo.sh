#!/bin/bash
set -e

SIM_UDID="60D7F70F-8B73-426B-86B7-8D8CA6F945D8"
OUTPUT_FILE="/Users/manjotsingh/RideMyCars/store_metadata/assets/RideMyCars_Rider_Demo.mp4"

echo "Setting clean status bar on Simulator..."
xcrun simctl status_bar "$SIM_UDID" override --time "09:41" --batteryState charged --batteryLevel 100 --wifiBars 3 --cellularBars 4

echo "Pre-granting all iOS permissions to prevent system dialog popups..."
xcrun simctl privacy "$SIM_UDID" grant all com.ridemycars.rider 2>/dev/null || true
xcrun simctl privacy "$SIM_UDID" grant location com.ridemycars.rider 2>/dev/null || true
xcrun simctl privacy "$SIM_UDID" grant location-always com.ridemycars.rider 2>/dev/null || true
xcrun simctl terminate "$SIM_UDID" com.ridemycars.rider 2>/dev/null || true

echo "Starting screen recording to $OUTPUT_FILE..."
xcrun simctl io "$SIM_UDID" recordVideo --codec=h264 --force "$OUTPUT_FILE" &
REC_PID=$!
sleep 2


cleanup() {
    echo "Stopping recording (PID: $REC_PID)..."
    kill -INT "$REC_PID" 2>/dev/null || true
    wait "$REC_PID" 2>/dev/null || true
    echo "Video finalized cleanly!"
}
trap cleanup EXIT

echo "Running Rider walkthrough test..."
cd /Users/manjotsingh/RideMyCars/flutter_app
flutter test integration_test/rider_walkthrough_test.dart -d "$SIM_UDID"

echo "Test finished, finalizing video..."
