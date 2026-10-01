# 🚀 RideMyCars Driver — Release Notes (Version 1.0.4 / Build 8)

**Release Date:** October 01, 2026  
**Target Platform:** Google Play Store (Production Release)  
**App Name:** RideMyCar Driver: Drive & Earn  
**Package Name:** `com.ridemycars.driver`  
**Version:** `1.0.4`  
**Version Code / Build Number:** `8`  

---

### 📋 Google Play Store Release Notes (What's New)
```text
• Fixed online availability toggle and improved driver dispatch connectivity.
• Streamlined secure account sign-out and session lifecycle management.
• Enhanced OTP delivery reliability with dual SMS gateway carrier routing.
• Optimized driver dashboard status bar and trip request overlay responsiveness.
• Performance and stability enhancements for background location synchronization.
```

---

### 📋 Detailed Changelog & Technical Improvements

1. **Online Availability & Dispatch Synchronization:**
   - Resolved online status toggle rejection for newly verified drivers.
   - Eliminated switch flicker by synchronizing optimistic UI state with verified server responses.
   - Auto-activation of live driver status upon successful shift start.

2. **Authentication & Session Lifecycle:**
   - Implemented secure, graceful logout workflow with local token purge.
   - Added visual loading indicator during session termination to prevent duplicate requests.
   - Fixed unauthenticated 401 exceptions on token expiry.

3. **SMS Verification & Authentication Delivery:**
   - Integrated dual carrier routing (Nalo Solutions for West Africa / Ghana and Twilio fallback globally) to guarantee instant OTP delivery.

4. **UI & Background Location Engine:**
   - Improved UI scaling on high-density displays and modern Android devices.
   - Enhanced background location ping accuracy for real-time ride dispatch matching.

---

### 📦 Generated Production Binaries

| Artifact | Version Code | Format | File Path |
| :--- | :--- | :--- | :--- |
| **Driver App Bundle (AAB)** | `8` (`1.0.4`) | **AAB (Upload to Play Console)** | [`dist/playstore/RideMyCars_Driver_v1.0.4_build8.aab`](file:///Users/manjotsingh/RideMyCars/dist/playstore/RideMyCars_Driver_v1.0.4_build8.aab) |
| **Driver Release APK** | `8` (`1.0.4`) | **APK (Direct Install / Test)** | [`dist/playstore/RideMyCars_Driver_v1.0.4_build8.apk`](file:///Users/manjotsingh/RideMyCars/dist/playstore/RideMyCars_Driver_v1.0.4_build8.apk) |

---

### 📲 Google Play Console Upload Instructions

1. Log into your [Google Play Console](https://play.google.com/console).
2. Select **RideMyCar Driver: Drive & Earn** (`com.ridemycars.driver`).
3. Navigate to **Production** (or **Closed Testing** / **Open Testing**) in the left navigation sidebar.
4. Click **Create new release**.
5. Upload the signed App Bundle:
   - [`RideMyCars_Driver_v1.0.4_build8.aab`](file:///Users/manjotsingh/RideMyCars/dist/playstore/RideMyCars_Driver_v1.0.4_build8.aab)
6. Set Release name: `1.0.4 (8)`.
7. Paste the **What's New** text from above into the release notes editor.
8. Click **Next** > **Save** > **Review release** > **Start rollout to Production**.
