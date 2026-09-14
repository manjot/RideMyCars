# App Store Review - Guideline 2.1 Information Needed Response

> **Instructions for Developer**:
> 1. Copy the text from **Section A1** (for Rider App) or **Section A2** (for Driver App) and paste it directly into the **Resolution Center Reply** box in App Store Connect.
> 2. Also paste the text into the **Notes** field under **App Review Information** in App Store Connect.
> 3. Attach the corresponding screen recording (`RideMyCars_Rider_Demo.mp4` or `RideMyCars_Driver_Demo.mp4`) located in `store_metadata/assets/` or `store_metadata/driver_assets/`.

---

## SECTION A1: Response for Rider App — SHORT VERSION for App Store Connect Reply (under 4000 chars)

> ⚠️ **Use this version** when pasting into the App Store Connect Reply box (4000 char limit). It is **2,486 characters**.

```text
Dear Apple App Review Team,

Thank you for your review. Below are the detailed responses to each point requested under Guideline 2.1 for RideMyCars (com.ridemycars.rider) by New development Finance Group pty ltd.

1. SCREEN RECORDING
A complete screen recording (attached) was captured on iPhone 16 Pro Simulator (iOS 18.6) with a clean 09:41 status bar and all permissions pre-granted. It demonstrates:
- App launch & splash screen
- Login screen with all 3 auth methods (Phone OTP, Email OTP, Password)
- Sign in with demo credentials (customer@ridemycars.com / 123456)
- Home map with vehicle category browsing (Executive, Luxury, SUV)
- Navigation drawer: Wallet & Payments, My Rides, Notifications, Help & Support
- Manage Account screen scrolled to show Delete Account option
- Delete Account confirmation dialog (held open to show full data-purge warning, then dismissed with Cancel — Guideline 5.1.1(v) compliant)
- Log Out flow returning to Login screen

2. PURPOSE & VALUE
RideMyCars is an executive ground mobility platform unifying on-demand rides, luxury car rentals, hourly chauffeurs, and parcel delivery into one app. Target audience: urban commuters, corporate travelers, tourists, and delivery clients. We solve the fragmentation of needing multiple apps for different transport types.

3. DEMO CREDENTIALS & SETUP
- Email: customer@ridemycars.com
- Password: 123456
Steps: Launch app → tap "🔑 Password" tab → sign in → explore Home map → tap menu (☰) → navigate each screen. Delete Account is found under Menu → Manage Account → scroll to Safety & Privacy.

4. EXTERNAL SERVICES
- Google Maps SDK (iOS): Map rendering, geocoding, routing, GPS tracking
- Firebase FCM: Push notifications & trip alerts
- Stripe: PCI-DSS payment processing for real-world transport services (Guideline 3.1.5(a) exempt — no digital goods)
- Backend API: REST API at https://ridemycars.com with TLS 1.3 encryption

5. REGIONAL CONSISTENCY
The app operates in all supported regions without geofencing restrictions. Currency and distance units adapt automatically to the user's locale.

6. REGULATORY COMPLIANCE
Operating Entity: New development Finance Group pty ltd. All driver partners must submit valid driver licenses, vehicle registration, commercial insurance, and pass background verification before activation. All brand assets and UI are 100% proprietary.

Sincerely,
RideMyCars Development Team
New development Finance Group pty ltd
contact@ridemycars.com
```

---

## SECTION A1-FULL: Full Detail Version for Notes Field (no character limit)

```text
Dear Apple App Review Team,

Thank you for your review. We are pleased to provide the requested detailed information regarding RideMyCars (com.ridemycars.rider) to help you complete the evaluation for our developer account (New development Finance Group pty ltd).

Below are the detailed responses to each of the 6 points requested under Guideline 2.1:

----------------------------------------------------------------------
1. SCREEN RECORDING DEMONSTRATING FUNCTIONALITY & USER FLOWS
----------------------------------------------------------------------
We have attached a complete screen recording (RideMyCars_Rider_Demo.mp4) captured on iOS Simulator (iPhone 16 Pro, iOS 18.6) with a clean 09:41 status bar, full battery, and all permissions pre-granted. The recording begins with launching the app and shows the complete typical user flow:
- 00:00 - 00:08: App Launch & Brand Splash Screen
- 00:08 - 00:20: Authentication Screen — Phone OTP tab, Email OTP tab, then Password login tab with pre-filled demo credentials
- 00:20 - 00:30: Sign In via Demo Credentials (customer@ridemycars.com / 123456) & transition to Main Map Screen
- 00:30 - 00:45: Interactive Map & Browsing Vehicle Categories (Executive, Luxury, SUV) with fare display
- 00:45 - 00:52: Navigation Drawer opened — user profile, wallet balance, and menu items visible
- 00:52 - 01:00: Wallet & Payments screen
- 01:00 - 01:08: My Rides / Ride History screen
- 01:08 - 01:15: Notifications screen
- 01:15 - 01:22: Help & Support screen
- 01:22 - 01:35: Manage Account screen — profile info, scrolled to Safety & Privacy section
- 01:35 - 01:48: In-App Account Deletion Flow — Delete Account dialog clearly explaining irreversible data purge (Guideline 5.1.1(v) compliance), held open 6 seconds, then dismissed with Cancel
- 01:48 - 02:02: Log Out flow — drawer opened, Log Out tapped, returns to Login screen

Note: The app does not include unmoderated user-generated public forums, nor does it sell digital in-app purchase content (all payments are for real-world physical transportation and vehicle rental services exempt under Guideline 3.1.5(a)).

----------------------------------------------------------------------
2. PURPOSE, TARGET AUDIENCE, PROBLEM SOLVED & VALUE PROVIDED
----------------------------------------------------------------------
- App Purpose: RideMyCars is an executive ground mobility and on-demand transportation platform.
- Target Audience: Daily urban commuters, corporate & executive business travelers, tourists needing vehicle hire, and individuals requiring reliable point-to-point courier delivery or private hourly chauffeurs.
- Problem Solved: Previously, customers had to switch between multiple separate apps for city rides, luxury rental cars, dedicated chauffeur drivers, and parcel couriers. RideMyCars unifies these four vital ground mobility pillars into a single seamless experience with transparent upfront pricing and live GPS tracking.
- Value Provided: On-demand mobility booking, verified drivers, transparent flat and metered rates, instant digital vouchers, and 24/7 customer assistance.

----------------------------------------------------------------------
3. INSTRUCTIONS FOR SETTING UP & ACCESSING MAIN FEATURES
----------------------------------------------------------------------
To review the full functionality of the application:
1. Launch the app on your test device.
2. At the sign-in screen, enter the dedicated Demo Credentials:
   - Phone Number: +19876543210
   - Email: customer@ridemycars.com
   - Password: password (or 123456)
3. Explore the Main Services:
   - On-Demand Rides: Enter pickup/destination to view available ride tiers, route polyline, and estimated fares.
   - Luxury Car Rentals: Browse available self-drive luxury fleet with daily/weekly rates and specs.
   - Hourly Chauffeurs: Select a date, duration, and vehicle class to hire a professional vetted driver.
   - Parcel Delivery: Enter sender/recipient details and parcel size for instant courier dispatch.
4. Access Account & Data Management:
   - Open the Drawer Menu and tap "Manage Account".
   - View profile details, saved places, safety emergency contacts, and the "Delete Account" button.

----------------------------------------------------------------------
4. LIST OF EXTERNAL SERVICES, TOOLS & PLATFORMS USED
----------------------------------------------------------------------
- Google Maps Platform (Google LLC): Google Maps SDK for iOS and Places API for high-precision map rendering, reverse geocoding, route calculation, and real-time GPS tracking.
- Firebase (Google LLC): Firebase Cloud Messaging (FCM) for instant push notifications and trip status alerts, and Firebase Core for crash diagnostics.
- Stripe (Stripe Inc.): PCI-DSS compliant payment processing for physical real-world transportation services, vehicle rental security authorizations, and driver payouts.
- Backend API Infrastructure: Secure REST API server hosted at https://ridemycars.com running cloud infrastructure with TLS 1.3 encryption and tokenized authentication.

----------------------------------------------------------------------
5. REGIONAL DIFFERENCES & GLOBAL CONSISTENCY
----------------------------------------------------------------------
The app operates consistently across all supported countries and regions without geofencing restrictions or region-locked features. Currency and distance metrics adapt automatically to the user's localized storefront and device locale.

----------------------------------------------------------------------
6. REGULATORY COMPLIANCE, OPERATOR AUTHORIZATION & LICENSING
----------------------------------------------------------------------
- Operating Entity: New development Finance Group pty ltd (Corporate Entity & Registered Developer).
- Service Authorization: All ground transport partners and vehicle providers operating on the RideMyCars network are required to submit valid driver licenses, vehicle registration documents, commercial liability insurance, and background verification before being activated.
- Intellectual Property: All trademarks, brand assets, logos, and UI designs are 100% proprietary and owned by New development Finance Group pty ltd / RideMyCars.

Please let us know if you require any additional information or further clarification. We look forward to your approval.

Sincerely,
RideMyCars Development Team
New development Finance Group pty ltd
contact@ridemycars.com
```

---

## SECTION A2: Official Response Text for Driver App (com.ridemycars.driver)

```text
Dear Apple App Review Team,

Thank you for your review. We are pleased to provide the requested detailed information regarding RideMyCars Driver Partner (com.ridemycars.driver) to help you complete the evaluation for our developer account (New development Finance Group pty ltd).

Below are the detailed responses to each of the 6 points requested under Guideline 2.1:

----------------------------------------------------------------------
1. SCREEN RECORDING DEMONSTRATING FUNCTIONALITY & USER FLOWS
----------------------------------------------------------------------
We have attached a complete screen recording (RideMyCars_Driver_Demo.mp4) captured on iOS Simulator (iPhone 16 Pro, iOS 18.6) with a clean 09:41 status bar, full battery, and all permissions pre-granted. The recording begins with launching the app and shows the complete typical driver partner workflow:
- 00:00 - 00:08: App Launch & Brand Splash Screen
- 00:08 - 00:20: Driver Authentication Screen — Phone OTP tab, Email OTP tab, then Password login tab with demo credentials
- 00:20 - 00:32: Sign In via Demo Driver Credentials (michael.driver@ridemycars.com / 123456) & transition to Driver Dashboard
- 00:32 - 00:45: Live Driver Dashboard — Online/Offline toggle, live GPS map, daily earnings summary panel
- 00:45 - 00:52: Side Navigation Drawer opened via Driver Profile Avatar in AppBar
- 00:52 - 01:00: Earnings & Payouts screen — daily/weekly breakdown and withdrawal balances
- 01:00 - 01:08: Ride History / My Trips screen — completed passenger trips list
- 01:08 - 01:15: Notifications screen
- 01:15 - 01:22: Help & Support screen
- 01:22 - 01:35: Manage Account & Profile — license status, rating, profile photo, scrolled to Safety & Privacy
- 01:35 - 01:48: In-App Account Deletion Flow — Delete Account dialog clearly explaining irreversible data purge (Guideline 5.1.1(v) compliance), held open 6 seconds, then dismissed with Cancel
- 01:48 - 02:04: Log Out flow — drawer opened, Log Out tapped, returns to Driver Login screen

----------------------------------------------------------------------
2. PURPOSE, TARGET AUDIENCE, PROBLEM SOLVED & VALUE PROVIDED
----------------------------------------------------------------------
- App Purpose: RideMyCars Driver is the companion dispatch and console app for verified driver partners and executive chauffeurs on the RideMyCars network.
- Target Audience: Licensed commercial drivers, professional private chauffeurs, and delivery couriers.
- Problem Solved: Provides drivers with instant dispatch ride requests, turn-by-turn routing, real-time earnings analytics, and transparent weekly payouts without cumbersome paperwork.
- Value Provided: Flexible work schedule, automatic fare settlement, integrated emergency support, and direct passenger contact tools.

----------------------------------------------------------------------
3. INSTRUCTIONS FOR SETTING UP & ACCESSING MAIN FEATURES
----------------------------------------------------------------------
To review the full functionality of the application:
1. Launch the app on your test device.
2. At the sign-in screen, enter the dedicated Driver Demo Credentials:
   - Email: michael.driver@ridemycars.com
   - Password: password (or 123456)
3. Explore the Main Features:
   - Dashboard: Toggle Online/Offline dispatch readiness, view active pickup radar.
   - Drawer Menu: Tap driver avatar at top right to open full navigation.
   - Earnings & Payouts: Review earnings summaries, completed trips revenue, and payout accounts.
   - Ride History: View past completed passenger routes and fares.
   - Manage Account: Review driver license status and test in-app account deletion flow.

----------------------------------------------------------------------
4. LIST OF EXTERNAL SERVICES, TOOLS & PLATFORMS USED
----------------------------------------------------------------------
- Google Maps Platform (Google LLC): Map SDK for iOS for real-time driver tracking and navigation.
- Firebase (Google LLC): Firebase Cloud Messaging (FCM) for instant ride dispatch alerts.
- Stripe (Stripe Inc.): Stripe Connect for driver payout processing and verification.
- Backend API Infrastructure: Secure REST API server hosted at https://ridemycars.com with TLS 1.3 encryption.

----------------------------------------------------------------------
5. REGIONAL DIFFERENCES & GLOBAL CONSISTENCY
----------------------------------------------------------------------
The app operates consistently across all supported markets with automatic currency and distance unit localization.

----------------------------------------------------------------------
6. REGULATORY COMPLIANCE & LICENSING
----------------------------------------------------------------------
- Operating Entity: New development Finance Group pty ltd.
- All drivers must complete identity verification, license validation, and vehicle inspection before active dispatch.

Sincerely,
RideMyCars Development Team
New development Finance Group pty ltd
contact@ridemycars.com
```

---

## SECTION A2-SHORT: Response for Driver App — SHORT VERSION for App Store Connect Reply (under 4000 chars)

> ⚠️ **Use this version** when pasting into the App Store Connect Reply box (4000 char limit). It is **2,596 characters**.

```text
Dear Apple App Review Team,

Thank you for your review. Below are the detailed responses to each point requested under Guideline 2.1 for RideMyCars Driver Partner (com.ridemycars.driver) by New development Finance Group pty ltd.

1. SCREEN RECORDING
A complete screen recording (attached) was captured on iPhone 16 Pro Simulator (iOS 18.6) with a clean 09:41 status bar and all permissions pre-granted. It demonstrates:
- App launch & splash screen
- Driver login screen with all 3 auth methods (Phone OTP, Email OTP, Password)
- Sign in with demo credentials (michael.driver@ridemycars.com / 123456)
- Driver Dashboard — Online/Offline toggle, live GPS map, daily earnings panel
- Navigation drawer opened via driver avatar (top right)
- Earnings & Payouts screen — daily/weekly breakdown
- Ride History / My Trips screen — completed passenger trips
- Notifications screen
- Help & Support screen
- Manage Account screen scrolled to Safety & Privacy section
- Delete Account confirmation dialog (held open to show full data-purge warning, then dismissed with Cancel — Guideline 5.1.1(v) compliant)
- Log Out flow returning to Driver Login screen

2. PURPOSE & VALUE
RideMyCars Driver is the companion dispatch and console app for verified driver partners on the RideMyCars network. Target audience: licensed commercial drivers, professional chauffeurs, and couriers. It provides instant ride dispatch, turn-by-turn routing, real-time earnings analytics, and transparent weekly payouts.

3. DEMO CREDENTIALS & SETUP
- Email: michael.driver@ridemycars.com
- Password: 123456
Steps: Launch app → tap "🔑 Password" tab → sign in → Dashboard loads. Tap driver avatar (top right) to open drawer → navigate each screen. Delete Account is under drawer → Manage Account → scroll to Safety & Privacy.

4. EXTERNAL SERVICES
- Google Maps SDK (iOS): Real-time driver tracking and navigation
- Firebase FCM: Instant ride dispatch push alerts
- Stripe Connect: Driver payout processing and verification
- Backend API: REST API at https://ridemycars.com with TLS 1.3 encryption

5. REGIONAL CONSISTENCY
The app operates in all supported markets without geofencing restrictions. Currency and distance units localize automatically.

6. REGULATORY COMPLIANCE
Operating Entity: New development Finance Group pty ltd. All drivers must complete identity verification, license validation, vehicle inspection, and background checks before active dispatch. All brand assets are 100% proprietary.

Sincerely,
RideMyCars Development Team
New development Finance Group pty ltd
contact@ridemycars.com
```

---

## SECTION B: Location of Demo Video Files

Both video recordings are generated in Apple QuickTime-compatible H.264 format (`.mp4`) captured on iPhone 16 Pro Simulator (iOS 18.6) with:
- Clean status bar: 09:41 time, 100% battery, full WiFi & cellular bars
- All permissions pre-granted (location, camera, microphone, notifications, photos) — **no system dialog popups appear**
- Duration: ~2 minutes each covering the complete user flow

1. **Rider App Video**:
   - File Path: `store_metadata/assets/RideMyCars_Rider_Demo.mp4`
   - File Size: ~35 MB
   - Duration: 2:02
2. **Driver App Video**:
   - File Path: `store_metadata/driver_assets/RideMyCars_Driver_Demo.mp4`
   - File Size: ~18 MB
   - Duration: 2:04
