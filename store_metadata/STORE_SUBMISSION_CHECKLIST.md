# 📋 RideMyCars — App Store & Play Store Launch Checklist

This checklist guides you through uploading and submitting **RideMyCars** to both the **Apple App Store Connect** and **Google Play Console**.

---

## 🍎 1. Apple App Store Submission Checklist

### Prerequisites
- [ ] Active Apple Developer Account ($99/year) at [developer.apple.com](https://developer.apple.com).
- [ ] App ID registered in Apple Developer Portal with Bundle ID: `com.ridemycars.app`.
- [ ] Push Notifications and Sign In with Apple capabilities enabled in App ID (if using APNS).

### Uploading the iOS Build
1. Open the project in Xcode:
   ```bash
   open /Users/manjotsingh/RideMyCars/flutter_app/ios/Runner.xcworkspace
   ```
2. Select **Any iOS Device (arm64)** from the target device dropdown.
3. In the top menu, go to **Product** > **Archive**.
4. Once the Organizer window opens, click **Distribute App** > **App Store Connect** > **Upload**.
5. Wait 5-10 minutes for App Store Connect to process the build.

### In App Store Connect ([appstoreconnect.apple.com](https://appstoreconnect.apple.com))
1. Go to **My Apps** > Click **+** > **New App**.
2. Select **iOS**, enter App Name: `RideMyCars: Cabs, Rentals & Rides`, Bundle ID: `com.ridemycars.app`.
3. Fill in the metadata from [`APP_STORE_METADATA.md`](./APP_STORE_METADATA.md).
4. Upload Screenshots from:
   - `store_metadata/assets/ios/iphone_6_7_inch/` (6.7" Display)
   - `store_metadata/assets/ios/iphone_6_5_inch/` (6.5" Display)
   - `store_metadata/assets/ios/iphone_5_5_inch/` (5.5" Display)
   - `store_metadata/assets/ios/ipad_12_9_inch/` (12.9" iPad Pro)
5. Select the processed build.
6. Fill in **App Privacy** nutrition labels using the table in [`APP_STORE_METADATA.md`](./APP_STORE_METADATA.md).
7. Paste test credentials into **App Review Information**.
8. Click **Submit for Review**.

---

## 🤖 2. Google Play Store Submission Checklist

### Prerequisites
- [ ] Active Google Play Console Account ($25 one-time) at [play.google.com/console](https://play.google.com/console).

### Uploading the Android Build
1. Locate the generated App Bundle:
   `/Users/manjotsingh/RideMyCars/release_builds/android/app-release.aab`
2. In Google Play Console, click **Create app**.
3. App Name: `RideMyCars: Cabs & Car Rentals`, Free, accept declarations.
4. Go to **Production** > **Create new release** > Upload `app-release.aab`.

### Store Listing & Content
1. In **Main store listing**, fill in data from [`PLAY_STORE_METADATA.md`](./PLAY_STORE_METADATA.md).
2. Upload Graphics:
   - App Icon: `store_metadata/assets/android/play_store_icon_512x512.png`
   - Feature Graphic: `store_metadata/assets/android/feature_graphic_1024x500.png`
   - Phone Screenshots: `store_metadata/assets/android/phone/`
   - Tablet Screenshots: `store_metadata/assets/android/tablet_7_inch/` & `tablet_10_inch/`
3. Complete Policy Sections:
   - **Privacy Policy URL**: `https://ridemycars.com/privacy-policy`
   - **App Access**: All functionality is available without restrictions (or provide test credentials).
   - **Content Rating**: Complete questionnaire (Everyone / PEGI 3).
   - **Target Audience**: 18 and over.
   - **Data Safety**: Complete using table in [`PLAY_STORE_METADATA.md`](./PLAY_STORE_METADATA.md).
4. Send for review.
