# 🚀 RideMyCars (Rider App) — Google Play Store Release Notes (v1.0.3 / Build 8)

**Application:** RideMyCars: Cabs, Rental, Ride (`com.ridemycars.app`)  
**Release Version:** `1.0.3`  
**Version Code / Build Number:** `8`  
**Release Date:** October 11, 2026  
**Target Platform:** Google Play Store (Production Track)  

---

## 📋 Google Play Store Release Notes (What's New)
*(Copy and paste directly into Google Play Console -> Release Notes -> `en-US`)*

```text
• Real-Time Push Notifications: Stay updated with instant alerts for ride confirmations, driver arrival, and trip status.
• 4-Digit Secure Ride PIN: Added passenger safety verification PIN to ensure you connect with the right driver every time.
• Enhanced Live Trip Tracker: Smoother map navigation, real-time arrival countdowns, and dynamic route tracking.
• In-App Digital Receipts & Invoices: View detailed fare breakdowns, download official trip receipts, and track rental vouchers directly.
• Pharmeasy & Express Delivery: Send packages with enhanced tracking and prescription upload support for medical deliveries.
• Smarter Search: Faster autocomplete address search with keyboard overlap fixes.
• Performance & Stability: App startup optimizations and enhanced Android 14/15 compatibility.
```

---

## 🌟 Detailed Change Summary

### 1. 🔔 Real-Time Push Notifications & Notification Center
- Integrated Firebase Cloud Messaging (FCM) and `flutter_local_notifications` with core library desugaring for full Android 13+ runtime notification permissions.
- Added in-app Notification Center with badge counts, background notification channels, and swipe-to-delete dismissals.

### 2. 🛡️ 4-Digit Secure Ride PIN
- Introduced a 4-digit security PIN generated on ride confirmation.
- Drivers must enter the passenger's PIN before starting the ride, guaranteeing rider identity and trip authenticity.

### 3. 🗺️ Enhanced Live GPS Map & Turn-by-Turn Tracker
- Optimized Google Maps polyline rendering and live vehicle location marker animations.
- Added dynamic pickup/dropoff centering, driver waiting time countdown, and interactive route recalculation.

### 4. 📄 In-App Digital Receipts & Booking History
- Rebuilt trip completion summary with an in-app digital invoice modal and direct receipt viewer.
- Displays transparent fare breakdowns: base fare, distance rate, wait time, taxes, and promotional discounts.

### 5. 📦 Pharmeasy & Prescription Delivery Workflow
- Added support for medical deliveries with mandatory doctor's prescription upload and driver Rx counter verification.
- Real-time courier dispatch status with dedicated pickup and drop-off instructions.

### 6. 🔍 Dual-Provider Address Autocomplete & UI Polish
- Optimized address search with instant dual-provider suggestions.
- Resolved soft-keyboard overlap across all device screen aspect ratios.
- Refined payment selection bottom sheet with verified brand icons (Visa, Mastercard, Mobile Money, Cash).

---

## 📦 Production Binary Artifacts

| Binary Type | File Name | Size | File Path |
| :--- | :--- | :--- | :--- |
| **Android App Bundle (AAB)** *(Required by Play Store)* | `RideMyCars_Rider_v1.0.3_build8.aab` | **44 MB** | [`dist/playstore/RideMyCars_Rider_v1.0.3_build8.aab`](file:///Users/manjotsingh/RideMyCars/dist/playstore/RideMyCars_Rider_v1.0.3_build8.aab) |
| **Signed Release APK** *(For Direct Testing / Sideloading)* | `RideMyCars_Rider_v1.0.3_build8.apk` | **56 MB** | [`dist/playstore/RideMyCars_Rider_v1.0.3_build8.apk`](file:///Users/manjotsingh/RideMyCars/dist/playstore/RideMyCars_Rider_v1.0.3_build8.apk) |
| **Release Builds Mirror (AAB)** | `RideMyCars-Rider-release.aab` | **44 MB** | [`release_builds/RideMyCars-Rider-release.aab`](file:///Users/manjotsingh/RideMyCars/release_builds/RideMyCars-Rider-release.aab) |
| **Release Builds Mirror (APK)** | `RideMyCars-Rider-release.apk` | **56 MB** | [`release_builds/RideMyCars-Rider-release.apk`](file:///Users/manjotsingh/RideMyCars/release_builds/RideMyCars-Rider-release.apk) |

---

## 🚀 Play Store Console Upload Instructions

1. Log into the [Google Play Console](https://play.google.com/console).
2. Select **RideMyCars** (`com.ridemycars.app`).
3. Navigate to **Release** > **Production** (or **Open Testing** / **Internal Testing**).
4. Click **Create new release**.
5. Upload the signed App Bundle:  
   [`dist/playstore/RideMyCars_Rider_v1.0.3_build8.aab`](file:///Users/manjotsingh/RideMyCars/dist/playstore/RideMyCars_Rider_v1.0.3_build8.aab)
6. Set the **Release name**: `1.0.3 (8)`
7. In the **Release notes** section, paste the *What's New* block above.
8. Click **Next** > **Save and Review** > **Start Rollout to Production**.
