# App Review: Guideline 2.3.10 (second rejection), Oct 30, 2026

**Submission ID:** 84a51acf-f763-4665-bbf0-35d892dc402c · **Version:** 1.0 (3) · **Review device:** iPad Air 11-inch (M3)

## Root cause

The screenshots App Review saw were the **old Sep 12 set** (gold device frame), not the Oct 11 regenerated set:

1. The original generator cropped only **89px** from the bottom of each 1080x2340 Android capture. The Android 3-button nav bar is **135px** tall, so the tops of the `|||  O  <` glyphs stayed visible just above the fake iOS home indicator.
2. The corrected set was never uploaded. Every slot (6.9", 6.5", 5.5" iPhone, iPad 13", iPad 12.9") still held the old gold-frame set. App Store Connect keeps custom screenshots per size, so every slot must be cleared.
3. The iPad slots held iPhone UI on an iPad canvas, which also risks a 2.3.3 rejection.

## Fix applied

- **Real device captures replace the mock-ups.** [`integration_test/store_screenshots_test.dart`](../flutter_app/integration_test/store_screenshots_test.dart) drives the real app on iOS Simulators, and [`scripts/capture_ios_screenshots.sh`](../scripts/capture_ios_screenshots.sh) grabs native `simctl` screenshots. Every image has the genuine iOS/iPadOS status bar and home indicator.
  - iPhone 6.9" (iPhone 16 Pro Max, 1320x2868): `store_metadata/assets/ios/real_iphone_6_9_inch/`
  - iPad 13" (iPad Pro 13-inch M4, 2064x2752, native iPad layout): `store_metadata/assets/ios/real_ipad_13_inch/`
  - Re-run: `scripts/capture_ios_screenshots.sh <SIM_UDID> <output_dir>`
- The mock-up generators (`generate_store_assets.py`, `generate_driver_store_assets.py`) now crop the full Android status bar and nav bar, and the Rider generator fails if any nav bar remains. They are still used for Play Store assets.

## App Store Connect status

- [x] All 30 old screenshots deleted from every iPhone and iPad slot (via Media Manager).
- [x] Uploaded `UPLOAD_iPhone_6.9/` (rmc_iphone69_1–6) to **iPhone with Dynamic Island (large display) 6.9"**, 6/10. The required 6.1"/6.3" slot shows "Using Existing Assets".
- [x] Uploaded `real_ipad_13_inch/` (1–6) to **iPad 13" display**, 6/10. Smaller iPad sizes scale from it.
- [x] Reply sent to App Review (Oct 30, 10:04 PM IST).
- [x] Resubmitted to App Review (Oct 30, 10:18 PM IST). Status: **Waiting for Review**.

## Reply to App Review

```text
Hello App Review Team,

Thank you for the feedback on Guideline 2.3.10.

We found that our screenshot sets (across the iPhone and iPad display sizes under "View All Sizes in Media Manager") still contained outdated images with non-iOS system UI. We have deleted every screenshot in every display size and replaced them with new screenshots captured directly from the app running on iPhone (6.9") and iPad (13"). The new screenshots:

- show only the native iOS / iPadOS status bar, with no non-iOS status bars, navigation buttons, or third-party device frames,
- show the app as it actually runs on each supported device, including the native iPad layout,
- highlight the app's main features: on-demand rides, self-drive car rentals, hourly chauffeurs, parcel delivery, digital receipts, and booking history.

Please let us know if anything else is needed.

Best regards,
RideMyCars Team
```
