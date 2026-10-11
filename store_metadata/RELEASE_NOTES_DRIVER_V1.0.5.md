# 🚀 RideMyCars Driver — Release Notes (Version 1.0.5 / Build 9)

**Release Date:** October 10, 2026  
**Target Platform:** Google Play Store (Production / Internal Release)  
**App Name:** RideMyCar Driver: Drive & Earn  
**Package Name:** `com.ridemycars.driver`  
**Version:** `1.0.5`  
**Version Code / Build Number:** `9`  

---

### 📋 Google Play Store Release Notes (What's New)
```text
• Added 4-digit ride start PIN verification modal to guarantee passenger safety and authentication.
• Enhanced system push notifications with audible alerts, device vibration, and status bar heads-up notifications.
• Added support for Pharmeasy medical deliveries with doctor's prescription count inspection.
• Centralized 3-tier dynamic location-based pricing and multi-currency rate calculation across all regions.
• Refined active trip card UI, eliminated button clipping, and added pull-to-refresh on trips history.
• Digital receipt preview modal directly from completed trip cards.
```

---

### 📋 Detailed Changelog & Technical Improvements

1. **4-Digit Ride PIN Security Verification:**
   - Introduced an interactive 4-digit verification PIN modal directly on the driver active trip card.
   - Drivers must enter and verify the passenger's secure 4-digit PIN before starting any trip to prevent wrongful pickups.
   - Real-time backend validation with instant visual feedback and clear error reporting.

2. **System Push Notifications & Alert Engine:**
   - Upgraded local notification architecture with `flutter_local_notifications` and FCM foreground/background messaging.
   - Android 13+ (API 33) runtime notification permissions and Java 8 desugaring enabled.
   - Distinct audible chime sound, device vibration patterns, and persistent status-bar heads-up alerts for new dispatch orders.
   - Notification center enhancements: swipe-to-dismiss, delete-all, and one-tap test phone notification dispatch.

3. **Pharmeasy & Medical Deliveries:**
   - Full support for medicine delivery requests requiring doctor's prescriptions.
   - Drivers can view the prescription verification status and count badge directly on delivery cards before dispatch.

4. **Multi-Currency & Location-Based Dispatch:**
   - Centralized 3-tier dynamic pricing engine with localized currency symbols (GH₵, ₹, $, etc.) based on driver's operating country.
   - Removed legacy USD currency suffix and updated fare formatting across all card views.

5. **Trip Lifecycle & Dashboard Polish:**
   - Streamlined Active Trip Card UI layout with optimized button spacing, eliminating SOS button overlap.
   - Added explicit trip requested date and time display on booking and trip cards.
   - Non-blocking pull-to-refresh on dashboard and past trips screen.
   - Integrated in-app digital receipt modal viewer for instant fare breakdown inspection.

---

### 📦 Generated Production Binaries

| Artifact | Version Code | Format | Destination File Path |
| :--- | :--- | :--- | :--- |
| **Driver Release APK** | `9` (`1.0.5`) | **APK (Direct Install / Test)** | [`dist/playstore/RideMyCars_Driver_v1.0.5_build9.apk`](file:///Users/manjotsingh/RideMyCars/dist/playstore/RideMyCars_Driver_v1.0.5_build9.apk) |
| **Driver App Bundle (AAB)** | `9` (`1.0.5`) | **AAB (Upload to Play Console)** | [`dist/playstore/RideMyCars_Driver_v1.0.5_build9.aab`](file:///Users/manjotsingh/RideMyCars/dist/playstore/RideMyCars_Driver_v1.0.5_build9.aab) |
| **Release Builds Directory** | `9` (`1.0.5`) | **APK** | [`release_builds/RideMyCars-Driver-release.apk`](file:///Users/manjotsingh/RideMyCars/release_builds/RideMyCars-Driver-release.apk) |
| **Release Builds Directory** | `9` (`1.0.5`) | **AAB** | [`release_builds/RideMyCars-Driver-release.aab`](file:///Users/manjotsingh/RideMyCars/release_builds/RideMyCars-Driver-release.aab) |

---

### 📲 Google Play Console Upload Instructions

1. Log into your [Google Play Console](https://play.google.com/console).
2. Select **RideMyCar Driver: Drive & Earn** (`com.ridemycars.driver`).
3. Navigate to **Production** (or **Closed Testing** / **Internal Testing**) in the left navigation sidebar.
4. Click **Create new release**.
5. Upload the signed App Bundle:
   - [`RideMyCars_Driver_v1.0.5_build9.aab`](file:///Users/manjotsingh/RideMyCars/dist/playstore/RideMyCars_Driver_v1.0.5_build9.aab)
6. Set Release name: `1.0.5 (9)`.
7. Paste the **What's New** text from above into the release notes editor.
8. Click **Next** > **Save** > **Review release** > **Start rollout to Production**.
