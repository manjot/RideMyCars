# App Store Review Response — Submission ID: cbcca4f3-50c3-4bb6-b037-782b664b99b5
**Review Date**: September 30, 2026  
**Review Device**: iPad Air 11-inch (M3), iPadOS 27.0.1  
**Version Reviewed**: 1.0 (3)  
**App**: RideMyCars (`com.ridemycars.rider`)  

---

## Quick Action Checklist for Developer

1. **Update App Store Screenshots (Guideline 2.3.10)**:
   - Go to [App Store Connect](https://appstoreconnect.apple.com/) > **RideMyCars** > **App Previews and Screenshots**.
   - Click **"View All Sizes in Media Manager"**.
   - For each iOS device size, upload the freshly generated screenshots from:
     - **6.7" Display (1290x2796)**: `store_metadata/assets/ios/iphone_6_7_inch/`
     - **6.5" Display (1242x2688)**: `store_metadata/assets/ios/iphone_6_5_inch/`
     - **5.5" Display (1242x2208)**: `store_metadata/assets/ios/iphone_5_5_inch/`
     - **12.9" iPad Pro / iPad Air Display (2048x2732)**: `store_metadata/assets/ios/ipad_12_9_inch/`
2. **Reply to Apple in App Store Connect Resolution Center**:
   - Copy the text from **Section 1** below and paste it directly into the App Store Connect Resolution Center reply box.
3. **Update Review Information Notes**:
   - Paste the credentials from **Section 2** into the **App Review Information > Notes** field.

---

## SECTION 1: Resolution Center Reply Text (Ready to Paste)

```text
Dear Apple App Review Team,

Thank you for your feedback regarding Submission ID: cbcca4f3-50c3-4bb6-b037-782b664b99b5. We have resolved both identified items in full:

1. Guideline 2.3.10 - Accurate Metadata (Screenshots Updated)
We have completely revised the App Store screenshots across all supported iOS display sizes (6.7" iPhone, 6.5" iPhone, 5.5" iPhone, and 12.9" iPad Pro / iPad Air). All non-iOS status bar indicators and third-party UI have been removed and replaced with authentic, native iOS status bar styling (clean 09:41 time, iOS cellular signal bars, Wi-Fi glyph, battery glyph, and iOS Home Indicator). The updated screenshots have been uploaded via Media Manager and accurately highlight the core features:
- On-Demand City Rides & Airport Cabs
- Luxury & Self-Drive Car Rentals
- Hourly Dedicated Chauffeurs
- Express Door-to-Door Courier Delivery
- Instant Digital Vouchers & Booking Confirmations
- Live GPS Trip Tracking & Booking History

2. Guideline 2.1(a) - App Completeness (Sign-In Verified on Production)
We investigated the sign-in issue reported on the iPad Air 11-inch (M3). The backend authentication service on our production server (https://www.ridemycars.com) has been updated and verified to ensure immediate, zero-friction access across all three sign-in tabs on both iPad and iPhone:

DEMO LOGIN CREDENTIALS:
Option A — Phone OTP Tab (Default launch tab):
- Phone Number: +19876543210
- Verification Code / OTP: 1234 (or 123456)
- Result: Authenticates immediately into active demo rider "John Client (Demo)"

Option B — Email OTP Tab:
- Email Address: customer@ridemycars.com
- Verification Code / OTP: 1234 (or 123456)
- Result: Authenticates immediately into active demo rider "John Client (Demo)"

Option C — Password Tab:
- Email Address: customer@ridemycars.com
- Password: RideMyCars@2026! (or 123456)
- Result: Authenticates immediately into active demo rider "John Client (Demo)"

Driver Demo Account (for Driver Partner features):
- Phone Number: +19876543211 | OTP: 1234
- Email Address: michael.driver@ridemycars.com | Password: RideMyCars@2026!

All endpoints have been tested and verified live with 200 OK responses. We appreciate your continued support and look forward to your review.

Sincerely,
RideMyCars Development Team
New development Finance Group pty ltd
contact@ridemycars.com
```

---

## SECTION 2: App Review Information Notes (For App Store Connect)

```text
DEMO ACCOUNT CREDENTIALS:

Role: Customer / Rider Demo Account
- Phone Number: +19876543210 (Code: 1234 or 123456)
- Email Address: customer@ridemycars.com (Code: 1234 or Password: RideMyCars@2026!)
- Sign-In Steps: Launch the app on iPad or iPhone -> Enter the phone or email above -> Enter code 1234 -> Instant access to home booking map and all features.

Role: Driver Partner Demo Account
- Phone Number: +19876543211 (Code: 1234)
- Email Address: michael.driver@ridemycars.com (Password: RideMyCars@2026!)

All demo authentication bypasses are live on production (https://www.ridemycars.com).
```
