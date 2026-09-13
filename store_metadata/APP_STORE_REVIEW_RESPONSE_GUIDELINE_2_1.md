# App Store Review - Guideline 2.1 Information Needed Response

> **Instructions for Developer**:
> 1. Copy the text from **Section A** below and paste it directly into the **Resolution Center Reply** box in App Store Connect.
> 2. Also paste the text into the **Notes** field under **App Review Information** in App Store Connect.
> 3. Attach a brief screen recording (mp4 or mov) from your iPhone demonstrating the app launching, login, core features, and account deletion. (Follow **Section B** below for the 1-minute recording guide).

---

## SECTION A: Official Response Text (Copy & Paste to Apple Review Team)

```text
Dear Apple App Review Team,

Thank you for your review. We are pleased to provide the requested detailed information regarding RideMyCars (and RideMyCars Driver Partner) to help you complete the evaluation for our new developer account (New development Finance Group pty ltd).

Below are the detailed responses to each of the 6 points requested under Guideline 2.1:

----------------------------------------------------------------------
1. SCREEN RECORDING DEMONSTRATING FUNCTIONALITY & USER FLOWS
----------------------------------------------------------------------
We have attached a direct screen recording captured from a physical iOS device running the latest iOS version. The video demonstrates:
- App Launch & Splash Screen
- User Authentication & Login Flow via Demo Credentials (+19876543210 / OTP: 123456)
- Main Navigation & Live Map Interface (Browsing on-demand rides, luxury self-drive rentals, hourly chauffeurs, and express parcel delivery)
- Safety & Account Management (Manage Account screen)
- Explicit Account Deletion Flow (Manage Account > Safety & Privacy > Delete Account with irreversible confirmation dialog in compliance with Guideline 5.1.1(v))

Note: The app does not include unmoderated user-generated public forums, nor does it sell digital in-app purchase content (all payments are for real-world physical transportation and rental services exempt under Guideline 3.1.5(a)).

----------------------------------------------------------------------
2. PURPOSE, TARGET AUDIENCE, PROBLEM SOLVED & VALUE PROVIDED
----------------------------------------------------------------------
- App Purpose: RideMyCars is a unified, smart mobility and on-demand ground transportation platform.
- Target Audience: Daily urban commuters, corporate & business travelers, tourists needing vehicle hire, and individuals requiring reliable point-to-point courier delivery or private hourly chauffeurs.
- Problem Solved: Previously, customers had to switch between multiple separate apps for city cabs, luxury rental cars, dedicated chauffeur drivers, and parcel couriers. RideMyCars unifies these four vital ground mobility pillars into a single seamless experience with transparent upfront pricing and live GPS tracking.
- Value Provided: On-demand mobility booking, verified drivers, transparent flat and metered rates, instant digital vouchers, and 24/7 customer assistance.

----------------------------------------------------------------------
3. INSTRUCTIONS FOR SETTING UP & ACCESSING MAIN FEATURES
----------------------------------------------------------------------
To review the full functionality of the application:
1. Launch the app on your test device.
2. At the sign-in screen, enter the dedicated Demo Credentials:
   - Phone Number: +19876543210
   - Verification Code / OTP: 123456
3. Explore the Main Services:
   - On-Demand Rides: Enter pickup/destination to view available ride tiers, route polyline, and estimated fares.
   - Luxury Car Rentals: Browse available self-drive luxury fleet with daily/weekly rates and specs.
   - Hourly Chauffeurs: Select a date, duration, and vehicle class to hire a professional vetted driver.
   - Parcel Delivery: Enter sender/recipient details and parcel size for instant courier dispatch.
4. Access Account & Data Management:
   - Tap the Profile icon at the top right to open "Manage Account".
   - View profile details, saved places, safety emergency contacts, and the "Delete Account" button.

----------------------------------------------------------------------
4. LIST OF EXTERNAL SERVICES, TOOLS & PLATFORMS USED
----------------------------------------------------------------------
- Google Maps Platform (Google LLC): Google Maps SDK for iOS and Places API for high-precision map rendering, reverse geocoding, route calculation, and real-time GPS tracking.
- Firebase (Google LLC): Firebase Cloud Messaging (FCM) for instant push notifications and trip status alerts, and Firebase Core for crash diagnostics.
- Stripe (Stripe Inc.): PCI-DSS compliant payment processing for physical real-world transportation services, vehicle rental security authorizations, and driver payouts.
- Backend API Infrastructure: Secure REST API server hosted at https://ridemycars.com running Laravel cloud infrastructure with TLS 1.3 encryption and tokenized authentication.

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

## SECTION B: How to Record the 1-Minute Screen Recording from Your iPhone

1. **Enable Screen Recording on iPhone**:
   - Go to **Settings > Control Center** ➔ add **Screen Recording**.
2. **Perform the Flow**:
   - Swipe down to open Control Center, tap **Record** (3-second countdown).
   - Tap the **RideMyCars** app icon on your home screen.
   - Enter phone: `+19876543210` ➔ enter OTP: `123456`.
   - Scroll on the home map, tap **Rides**, **Rentals**, or **Chauffeurs**.
   - Tap your **Profile picture / Manage Account** at top right.
   - Scroll down to **Safety & Privacy** ➔ tap **Delete Account** ➔ show the confirmation popup ➔ tap **Cancel** (or test delete).
   - Stop recording.
3. **Attach Video in App Store Connect**:
   - In App Store Connect > Resolution Center message, click **Attach File** and select your recorded `.mp4` / `.mov` file.
   - Paste the Section A text above and click **Submit**.
