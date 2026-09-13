# App Store Review - Guideline 2.1 Information Needed Response

> **Instructions for Developer**:
> 1. Copy the text from **Section A1** (for Rider App) or **Section A2** (for Driver App) and paste it directly into the **Resolution Center Reply** box in App Store Connect.
> 2. Also paste the text into the **Notes** field under **App Review Information** in App Store Connect.
> 3. Attach the corresponding screen recording (`RideMyCars_Rider_Demo.mp4` or `RideMyCars_Driver_Demo.mp4`) located in `store_metadata/assets/` or `store_metadata/driver_assets/`.

---

## SECTION A1: Official Response Text for Rider App (com.ridemycars.rider)

```text
Dear Apple App Review Team,

Thank you for your review. We are pleased to provide the requested detailed information regarding RideMyCars (com.ridemycars.rider) to help you complete the evaluation for our developer account (New development Finance Group pty ltd).

Below are the detailed responses to each of the 6 points requested under Guideline 2.1:

----------------------------------------------------------------------
1. SCREEN RECORDING DEMONSTRATING FUNCTIONALITY & USER FLOWS
----------------------------------------------------------------------
We have attached a complete screen recording (RideMyCars_Rider_Demo.mp4) captured on iOS. The recording begins with launching the app and shows the complete typical user flow:
- 00:00 - 00:06: App Launch & Brand Splash Screen
- 00:07 - 00:14: Authentication Screen displaying Phone OTP entry, country code picker, and switching to Password login tab
- 00:15 - 00:22: Sign In via Demo Credentials (+19876543210 / customer@ridemycars.com) & transition to Main Screen
- 00:23 - 00:33: Interactive Map & Browsing Vehicle Categories (Sedan, Executive SUV, VIP Luxury) with dynamic fare calculation
- 00:34 - 00:40: Navigation Drawer opening to display user profile and account options
- 00:41 - 00:48: Manage Account & Profile navigation
- 00:49 - 00:58: In-App Account Deletion Flow (Manage Account > Safety & Privacy > Delete Account dialog clearly explaining data purge in compliance with Guideline 5.1.1(v), followed by Cancel/Dismiss)
- 00:59 - 01:10: Navigation to Ride History / My Rides screen and return to Home

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
We have attached a complete screen recording (RideMyCars_Driver_Demo.mp4) captured on iOS. The recording begins with launching the app and shows the complete typical driver partner workflow:
- 00:00 - 00:06: App Launch & Brand Splash Screen
- 00:07 - 00:14: Driver Authentication Screen displaying mobile number input and switching to Password login tab
- 00:15 - 00:22: Sign In via Demo Driver Credentials (michael.driver@ridemycars.com) & transition to Driver Console
- 00:23 - 00:30: Live Driver Dashboard with Online/Offline toggle, live GPS map, daily earnings stats, and ride request dispatcher
- 00:31 - 00:37: Side Navigation Drawer opening via Driver Profile Avatar
- 00:38 - 00:46: Manage Account & Profile navigation (viewing license verification, rating, hourly rate, and profile photo)
- 00:47 - 00:56: In-App Account Deletion Flow (Manage Account > Safety & Privacy > Delete Account dialog clearly explaining data purge in compliance with Guideline 5.1.1(v), followed by Cancel/Dismiss)
- 00:57 - 01:05: Earnings & Payouts Screen showing daily/weekly breakdowns and withdrawal balances
- 01:06 - 01:15: Ride History / My Trips screen showing completed passenger trips

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

## SECTION B: Location of Demo Video Files

Both video recordings are generated in Apple QuickTime-compatible H.264 format (`.mp4`) with clean `09:41` status bars:

1. **Rider App Video**:
   - File Path: `store_metadata/assets/RideMyCars_Rider_Demo.mp4`
   - File Size: ~5.9 MB
2. **Driver App Video**:
   - File Path: `store_metadata/driver_assets/RideMyCars_Driver_Demo.mp4`
   - File Size: ~4.2 MB
