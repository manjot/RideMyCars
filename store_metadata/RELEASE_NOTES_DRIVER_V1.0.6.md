# 🚀 RideMyCars Driver — Release Notes (Version 1.0.6 / Build 11)

**Application:** RideMyCar Driver: Drive & Earn (`com.ridemycars.driver`)  
**Release Version:** `1.0.6`  
**Build Number / Version Code:** `11`  
**Release Date:** October 28, 2026  
**Target Platforms:** Google Play Store (Production Track) & Apple App Store (iOS TestFlight & Production)  

---

## 📋 Store Release Notes (What's New)
*(Copy and paste directly into Google Play Console -> Release Notes -> `en-US` and App Store Connect -> What's New in This Version)*

```text
• Driver Cash Commission Wallet: Manage cash fares, track platform commission balances, and settle remittances directly.
• Instant Driver Withdrawals: Request immediate payouts of trip earnings directly to linked accounts.
• Active Chauffeur Switch: Toggle dedicated private chauffeur and full-day rental availability on the go.
• High-Priority Background FCM Dispatch: Distinct audio chime and vibration ring even when app is backgrounded or device is locked.
• Chained Ride Acceptance: Accept upcoming trips while nearing current drop-off to eliminate downtime between rides.
• Driver Verification Vault: Upload and verify Vehicle Roadworthiness, Insurance, Driver License, and Chauffeur Guarantor details.
• Multi-Rail Payouts: Configure payouts for Mobile Money (MTN, Telecel, AirtelTigo) and direct Bank Wire transfers.
• End-Ride Safety Guard: Confirmation prompts before ending rides and mandatory 4-digit passenger PIN verification.
```

---

## 🌟 Detailed Technical Improvements & Changelog

### 1. 💰 Cash Commission Wallet & Platform Settlement
- **Cash Fare Accounting:** Real-time ledger recording cash collected directly from passengers.
- **Dynamic Commission Tracking:** Automatically separates gross fares, platform service fees, and driver net income.
- **In-App Commission Top-Up:** Instant wallet settlement options enabling drivers to clear commission dues without disruption.

### 2. ⚡ Instant Driver Withdrawals & Earnings Hub
- **One-Tap Payout Requests:** Immediate withdrawal processing directly from the Driver Earnings dashboard.
- **Transparent Transaction Ledger:** Detailed history showing ride payouts, cancellation fees, referral rewards, and withdrawal disbursements.
- **Selectable Payout Channels:** Seamless support for Ghana Mobile Money (MTN MoMo, Telecel Cash, AirtelTigo/AT Money) and Bank Account transfers.

### 3. 🎩 Chauffeur & Private Driver Availability Switch
- **Chauffeur Toggle:** Dedicated switch on the driver home dashboard allowing drivers to opt-in or opt-out of luxury chauffeur requests.
- **Multi-Day Rental Dispatch:** Transparent booking vouchers displaying passenger notes, pickup schedules, and daily rental rates.

### 4. 🔔 High-Priority Background Ringing & Chained Dispatch
- **FCM High-Priority Chime:** Custom push notification channel ensuring incoming ride requests ring loudly with vibration even in background or do-not-disturb modes.
- **Chained Ride Acceptance:** Drivers can preview and accept incoming requests when approaching the final destination of an active trip.

### 5. 🛡️ Verification Documents & Compliance
- **Digital Document Vault:** Dedicated interface for uploading and renewing Vehicle Roadworthiness, Comprehensive Insurance, National ID Card, and Driver's License.
- **Live Verification Status:** Clear badges indicating Verified, Pending Review, or Action Required.

### 6. 🔒 Passenger Safety & PIN Verification
- **Ride Start PIN Verification:** Drivers must enter the 4-digit passenger PIN before beginning a trip to prevent mistaken rider pickups.
- **Accidental Completion Guard:** Double-confirmation modal preventing unintended completion of active rides.

---

## 📦 Distribution Packages

### 🤖 Android (Google Play Store)
| Attribute | Specification |
| :--- | :--- |
| **Package ID** | `com.ridemycars.driver` |
| **Version Name** | `1.0.6` |
| **Version Code** | `11` |
| **Min SDK** | `21` (Android 5.0) |
| **Target SDK** | `34` (Android 14) |
| **Signing Key Alias** | `upload` (`upload-keystore.jks`) |
| **Play Store Bundle (`.aab`)** | [`dist/playstore/RideMyCars_Driver_v1.0.6_build11.aab`](file:///Users/manjotsingh/RideMyCars/dist/playstore/RideMyCars_Driver_v1.0.6_build11.aab) |
| **Standard Release AAB** | [`release_builds/RideMyCars-Driver-release.aab`](file:///Users/manjotsingh/RideMyCars/release_builds/RideMyCars-Driver-release.aab) |
| **File Size** | `44.4 MB` |

### 🍎 iOS (Apple App Store / TestFlight)
| Attribute | Specification |
| :--- | :--- |
| **Bundle ID** | `com.ridemycars.driver` |
| **Marketing Version** | `1.0.6` |
| **Build Number (`CFBundleVersion`)** | `11` |
| **Deployment Target** | iOS `15.0` |
| **Signing Certificate** | `Cloud Managed Apple Distribution` |
| **Team ID** | `3RLRZVVCJR` |
| **Distribution IPA File** | [`release_builds/RideMyCars_Driver_v1.0.6_build11.ipa`](file:///Users/manjotsingh/RideMyCars/release_builds/RideMyCars_Driver_v1.0.6_build11.ipa) |
| **Export Path** | [`release_builds/ios/Export_Driver/RideMyCars Driver.ipa`](file:///Users/manjotsingh/RideMyCars/release_builds/ios/Export_Driver/RideMyCars%20Driver.ipa) |
| **Xcode Archive (`.xcarchive`)** | [`flutter_driver_app/build/ios/archive/Runner.xcarchive`](file:///Users/manjotsingh/RideMyCars/flutter_driver_app/build/ios/archive/Runner.xcarchive) |
| **File Size** | `28.4 MB` |

---

## 📲 How to Upload the iOS IPA to App Store Connect

### Option 1: Apple Transporter (Recommended)
1. Open the **Transporter** app on macOS.
2. Drag and drop:
   `/Users/manjotsingh/RideMyCars/release_builds/ios/Export_Driver/RideMyCars Driver.ipa`
3. Click **Deliver**. Once processed in App Store Connect (typically 5–10 minutes), the build will be available under TestFlight and ready for App Store submission.

### Option 2: Command-Line (`altool` / App Store Connect API)
```bash
xcrun altool --upload-app --type ios \
  --file "/Users/manjotsingh/RideMyCars/release_builds/ios/Export_Driver/RideMyCars Driver.ipa" \
  --apiKey "<KEY_ID>" \
  --apiIssuer "<ISSUER_UUID>"
```
