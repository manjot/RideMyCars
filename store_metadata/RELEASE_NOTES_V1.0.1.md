# 🚀 RideMyCars — Release Notes (Version 1.0.1 / Build 4)

**Release Date:** September 27, 2026  
**Target Stores:** Google Play Store & Apple App Store  
**Version:** `1.0.1`  
**Version Code / Build Number:** `4`

---

## 📱 1. RideMyCars (Rider App — `com.ridemycars.app`)

### 📋 Google Play Store Release Notes (What's New)
```text
• Upgraded ride booking experience with accurate real-time dynamic pricing and fare estimates.
• Enhanced multi-category fleet selection (Standard, Premium, Luxury, and Van).
• Optimized live driver tracking, route mapping, and ETA accuracy.
• Faster and more reliable authentication and OTP sign-in flows.
• General performance enhancements, UI polish, and stability fixes.
```

### 📋 Full Release Summary
- **Dynamic Fare Engine:** Synchronized multi-tier vehicle categories with real-time distance and traffic estimation.
- **Booking Flow Enhancements:** Added robust auto-retry logic for network requests and transparent server routing.
- **Map & Navigation:** Smoother pickup/drop-off marker placement and optimized reverse geocoding.
- **Security & Auth:** Streamlined OTP-based login with improved error recovery and session security.

---

## 🚗 2. RideMyCars Driver (Driver App — `com.ridemycars.driver`)

### 📋 Google Play Store Release Notes (What's New)
```text
• Improved instant dispatch algorithm for faster trip matching and reduced idle times.
• Enhanced ride category handling and automated fare calculation.
• Optimized turn-by-turn navigation overlay and real-time trip status sync.
• Improved earnings overview, incentive tracking, and payout transparency.
• Bug fixes and battery efficiency optimizations.
```

### 📋 Full Release Summary
- **Dispatch Stability:** Enhanced socket and HTTP API fallback to ensure zero dropped dispatch requests.
- **Incentives & Payouts:** Upgraded driver wallet and earnings screens with breakdown of fares, bonuses, and incentives.
- **Location Reliability:** Reduced background battery consumption while maintaining high-precision driver location pings.
- **Driver Verification:** Faster document review and on-boarding workflows.

---

## 📦 Binary Build Artifacts

| Application | File Type | Build Version | Output Path |
| :--- | :--- | :--- | :--- |
| **RideMyCars Rider** | Release APK | `1.0.1+4` | `flutter_app/build/app/outputs/flutter-apk/app-release.apk` |
| **RideMyCars Rider** | Android App Bundle (AAB) | `1.0.1+4` | `flutter_app/build/app/outputs/bundle/release/app-release.aab` |
| **RideMyCars Driver** | Release APK | `1.0.1+4` | `flutter_driver_app/build/app/outputs/flutter-apk/app-release.apk` |
| **RideMyCars Driver** | Android App Bundle (AAB) | `1.0.1+4` | `flutter_driver_app/build/app/outputs/bundle/release/app-release.aab` |
