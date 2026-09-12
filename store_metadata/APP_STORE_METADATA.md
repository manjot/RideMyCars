# 🍎 Apple App Store Connect — Complete Submission Metadata

> **App Name:** RideMyCars  
> **Bundle ID:** `com.ridemycars.rider`  
> **SKU:** `RIDEMYCARS_IOS_001`  
> **Primary Language:** English (US)  

---

## 1. App Information (General)

| Field | Value | Constraints |
| :--- | :--- | :--- |
| **App Name** | `RideMyCars: Cabs, Rentals & Rides` | Max 30 chars |
| **Subtitle** | `Rides, Luxury Rentals & Driver` | Max 30 chars |
| **Primary Category** | `Travel` | — |
| **Secondary Category** | `Navigation` / `Lifestyle` | — |
| **Privacy Policy URL** | `https://ridemycars.com/privacy-policy` | Live URL |
| **User Access / User Registration** | Free account registration via Mobile OTP | — |

---

## 2. Version Information (App Store Listing)

### 📌 Promotional Text (Max 170 characters)
```text
Book on-demand city cabs, rent luxury self-drive cars with daily rates, hire vetted hourly chauffeurs, and send express parcel deliveries—all in one smart app!
```

### 📌 Keywords (Max 100 characters, comma-separated, no spaces after commas recommended)
```text
ride,cab,taxi,car rental,self drive,chauffeur,driver,luxury car,parcel delivery,airport transfer,auto
```

### 📌 Description (Max 4,000 characters)
```text
RideMyCars is your all-in-one smart urban mobility and automotive services platform. Whether you need a quick ride across town, a luxury self-drive vehicle for a weekend getaway, a verified hourly chauffeur to drive your personal car, or express door-to-door parcel delivery, RideMyCars brings top-tier transport directly to your fingertips.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✨ CORE SERVICES IN RIDEMYCARS:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━

1. 🚕 ON-DEMAND CITY RIDES & CABS
• Instant pickups with transparent, upfront pricing—no hidden surges.
• Choose from Budget Sedans, Premium SUVs, and Executive Luxury rides.
• Real-time GPS driver tracking with live ETA and optimized route navigation.
• In-app safety features: Share Live Trip status, Emergency SOS, and verified driver profiles.

2. 🔑 LUXURY & SELF-DRIVE CAR RENTALS
• Explore a premium fleet of hatchbacks, sedans, SUVs, and luxury sports cars.
• Transparent daily, weekly, and monthly rental packages with instant digital vouchers.
• Seamless door-to-door vehicle delivery and doorstep pickup options.
• Zero paperwork hassle with digital KYC verification.

3. 👨‍✈️ HOURLY DEDICATED CHAUFFEURS
• Need someone to drive your personal car? Hire professionally vetted drivers on an hourly basis.
• Ideal for business meetings, night outs, outstation weekend trips, or daily commutes.
• Background-verified, courteous drivers with proven track records.

4. 📦 EXPRESS PARCEL & COURIER DELIVERY
• Fast, on-demand door-to-door delivery for documents, packages, groceries, and gifts.
• Real-time parcel tracking from pickup to recipient handover with secure OTP confirmation.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🛡️ SAFETY & RELIABILITY FIRST:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━
• 24/7 Dedicated Customer Support via in-app chat and phone helpline.
• Secure payments: Apple Pay, Credit/Debit Cards, Net Banking, UPI & In-App Wallet.
• Transparent digital receipts and itemized invoices sent directly to your email.

Download RideMyCars today and experience the next generation of smart, reliable mobility!
```

### 📌 Support & Marketing URLs
- **Support URL:** `https://ridemycars.com/contact`
- **Marketing URL:** `https://ridemycars.com`
- **Privacy Policy URL:** `https://ridemycars.com/privacy-policy`

---

## 3. App Review Information (For Apple Review Team)

| Field | Details |
| :--- | :--- |
| **Demo / Test Account Phone** | `+1 555-019-2834` (or test OTP: `123456`) |
| **Sign-in Required?** | Yes (Phone OTP or Guest Preview) |
| **Contact First Name** | Manjot |
| **Contact Last Name** | Singh |
| **Contact Phone Number** | Provided in developer portal |
| **Contact Email** | `support@ridemycars.com` |

### 📝 Review Notes (Paste in App Store Connect "Notes" field):
```text
Hello Apple App Review Team,

RideMyCars is an on-demand mobility application offering city rides, self-drive rentals, hourly driver hire, and parcel deliveries.

To test the application:
1. Launch the app and enter the demo phone number: +1 555-019-2834 (or any valid number).
2. Enter the verification code: 123456 to log in.
3. You can test booking rides, browsing luxury rental fleets, booking hourly chauffeurs, and tracking active demo bookings on the live map.

Permissions usage rationale:
- NSLocationWhenInUseUsageDescription & NSLocationAlwaysAndWhenInUseUsageDescription: Used strictly to calculate pickup/drop-off fares, locate nearby drivers, and provide turn-by-turn live navigation during active rides.
- NSCameraUsageDescription & NSPhotoLibraryUsageDescription: Used solely when riders upload custom profile avatars or submit identity documents for self-drive rental verification.

Thank you for your review!
```

---

## 4. App Privacy Nutrition Label (App Store Connect Questionnaire)

| Data Type | Collected? | Linked to User? | Used for Tracking? | Purpose |
| :--- | :--- | :--- | :--- | :--- |
| **Precise Location** | Yes | Yes | No | App Functionality (Pickup, trip routing, driver matching) |
| **Name / Phone / Email** | Yes | Yes | No | Account creation, booking confirmations, customer support |
| **Payment Info** | Yes | Yes | No | In-app purchases, ride transactions via secure gateway |
| **User Content (Photos)** | Optional | Yes | No | Profile photo, KYC document uploads |
| **Diagnostics / Crash Data** | Yes | No | No | App stability and performance improvements |

---

## 5. Export Compliance & Encryption
- **Is your app designed to use cryptography?** `Yes (standard HTTPS / SSL / Firebase Auth)`.
- **Does the app qualify for standard export compliance exemption?** `Yes` (already configured in `Info.plist` with `ITSAppUsesNonExemptEncryption = false`).
