# RideMyCars Driver - Step-by-Step App Store Submission Checklist

Follow this checklist to publish the **RideMyCars Driver Partner** iOS app to Apple App Store Connect and TestFlight.

---

## Pre-Requisites
- [x] Apple Developer Account: Active under Team ID `3RLRZVVCJR` (*New development Finance Group pty ltd*).
- [x] Bundle ID Configured: `com.ridemycars.driver`.
- [x] iOS Deployment Target: `14.0`.
- [x] App Store Icons & Screenshots Generated (No Alpha transparency).
- [x] Signed Production `.ipa` built in `release_builds/ios/Export_Driver/`.

---

## Step 1: Register Bundle Identifier in Apple Developer Portal
1. Go to [Apple Developer Identifiers](https://developer.apple.com/account/resources/identifiers/list).
2. Click **+** to add a new App ID.
3. Select **App IDs** > **App** > **Continue**.
4. Set:
   - **Description**: `RideMyCars Driver`
   - **Bundle ID**: `com.ridemycars.driver` (Explicit)
5. Enable Capabilities:
   - **Push Notifications**
   - **Background Modes** (Location updates, Remote notifications, Background fetch)
6. Click **Register**.

---

## Step 2: Create App Record in App Store Connect
1. Go to [App Store Connect - Apps](https://appstoreconnect.apple.com/apps).
2. Click the **+** icon > **New App**.
3. Fill in the modal:
   - **Platforms**: `iOS`
   - **Name**: `RideMyCars Driver Partner`
   - **Primary Language**: `English (U.S.)` (or your preferred primary language)
   - **Bundle ID**: Choose `com.ridemycars.driver`
   - **SKU**: `ridemycars-driver-ios-01`
   - **User Access**: `Full Access`
4. Click **Create**.

---

## Step 3: Upload Binary (.ipa)
There are two easy methods to upload the binary:

### Method A: Apple Transporter App (Easiest)
1. Download **Transporter** from the Mac App Store.
2. Sign in with your Apple Developer Apple ID.
3. Drag & drop `/Users/manjotsingh/RideMyCars/release_builds/ios/Export_Driver/RideMyCars Driver.ipa` (or `Runner.ipa`).
4. Click **Deliver**.

### Method B: Xcode Organizer / Command Line (xcrun altool)
```bash
xcrun altool --upload-app --type ios --file "/Users/manjotsingh/RideMyCars/release_builds/ios/Export_Driver/Runner.ipa" --username "YOUR_APPLE_ID" --password "APP_SPECIFIC_PASSWORD"
```

---

## Step 4: Upload Store Graphics & Screenshots
Navigate to your App version in App Store Connect and upload assets from `/Users/manjotsingh/RideMyCars/store_metadata/driver_assets/ios/`:

1. **6.7" iPhone Display** (iPhone 16 Pro Max / 15 Pro Max):
   - Upload 5 screenshots from `store_metadata/driver_assets/ios/iphone_6_7_inch/`
2. **6.5" iPhone Display** (iPhone 11 Pro Max / XS Max):
   - Upload 5 screenshots from `store_metadata/driver_assets/ios/iphone_6_5_inch/`
3. **5.5" iPhone Display** (iPhone 8 Plus / 7 Plus):
   - Upload 5 screenshots from `store_metadata/driver_assets/ios/iphone_5_5_inch/`
4. **12.9" iPad Display** (3rd Gen+ iPad Pro):
   - Upload 5 screenshots from `store_metadata/driver_assets/ios/ipad_12_9_inch/`
5. **App Store Icon**:
   - `store_metadata/driver_assets/ios/driver_app_store_icon_1024x1024.png` (24-bit RGB, No Alpha).

---

## Step 5: Fill In Metadata & Review Notes
Copy and paste all fields directly from [DRIVER_APP_STORE_METADATA.md](file:///Users/manjotsingh/RideMyCars/store_metadata/DRIVER_APP_STORE_METADATA.md):
- **Promotional Text**
- **Description**
- **Keywords**
- **Support URL**: `https://ridemycars.com/contact`
- **Marketing URL**: `https://ridemycars.com/driver`
- **Privacy Policy URL**: `https://ridemycars.com/privacy`
- **Copyright**: `2026 RideMyCars`
- **Review Information**: Demo credentials (`+19876543210` / `123456`) and Reviewer notes explaining location & camera permissions.

---

## Step 6: Select Build & Submit for Review
1. Once Transporter finishes processing your build (usually 5–15 minutes), select the build in App Store Connect.
2. In the **Export Compliance** popup, select: **No** (*ITSAppUsesNonExemptEncryption is configured to false*).
3. Click **Save** > **Add for Review** > **Submit to App Review**.
