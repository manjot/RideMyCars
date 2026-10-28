# 🚀 RideMyCars (Rider App) — Google Play Store Release Notes (v1.0.4 / Build 10)

**Application:** RideMyCars: Cabs, Rental, Ride (`com.ridemycars.app`)  
**Release Version:** `1.0.4`  
**Version Code / Build Number:** `10`  
**Release Date:** October 28, 2026  
**Target Platform:** Google Play Store (Production Track)  

---

## 📋 Google Play Store Release Notes (What's New)
*(Copy and paste directly into Google Play Console -> Releases -> Release Notes -> `en-US`)*

```text
• Authorize.Net & Stripe Live Card Payments: Fast, secure PCI-DSS Level 1 card tokenization and verification.
• Dynamic Payment Methods: Automatically loads active payment gateways (Card, MoMo, Cash) with real-time currency pricing.
• Rider Referral Program: Share your referral link with friends and earn instant ride credits in your wallet.
• Rented Fleet & Chauffeur Tracker: Seamlessly track booked vehicle rentals and private chauffeur reservations.
• Enhanced Real-Time GPS Tracking: Smoother vehicle marker movements, route polylines, and dynamic driver arrival countdowns.
• Instant Support & Safety: One-tap WhatsApp chat, emergency hotline calling, and quick dispute resolution.
• Stability & Performance: Optimized app cold-start time and improved Android 14/15 compatibility.
```

---

## 🌟 Detailed Feature Summary & Technical Improvements

### 1. 💳 Multi-Gateway Payment Rails & Authorize.Net Integration
- **Live Authorize.Net Integration:** Added end-to-end support for Authorize.Net card processing alongside Stripe.
- **Dynamic Payment Gateway Configuration:** Integrated real-time `PaymentConfigService` to load enabled payment options dynamically from the platform backend.
- **PCI-DSS Level 1 Encryption:** Client-side card brand recognition (Visa, MasterCard, Amex, Discover) and encrypted tokenized submission.
- **Pre-dispatch Payment Hold:** Automatic fare hold prior to dispatch search countdown to ensure guaranteed trip compensation.

### 2. 🎁 Rider Referral & Wallet Rewards
- **In-App Referral System:** Personalized referral codes with one-tap native share sheet support for WhatsApp, SMS, and social media.
- **Automated Wallet Credits:** Rewards credited automatically upon referred rider’s first completed trip.
- **Dynamic Wallet Balance:** Instant real-time balance updates across rides, deliveries, and rental bookings.

### 3. 🚗 Rented Fleet & Chauffeur Booking Tracker
- **Dedicated Rental Dashboard:** Track active and upcoming vehicle rental bookings and private chauffeur assignments.
- **Inspection & Checkpoint Visibility:** Easy access to vehicle roadworthiness, host details, and pickup/drop-off schedules.

### 4. 📦 Parcel Delivery Flow Optimization
- **Dynamic Delivery Dispatch:** Improved package pickup and drop-off coordinates calculation.
- **Real-Time Delivery Notifications:** Instant alerts for driver acceptance, package pickup, and completed deliveries.

### 5. 📍 Real-Time Navigation & Safety Controls
- **Fluid Vehicle Motion:** Synchronized bearing and smooth interpolation for driver vehicle markers on Google Maps.
- **PIN Verification Protection:** Rider PIN visibility and safety checks ensuring passengers only board their matched vehicle.
- **SOS Hotline:** Instant emergency trigger and customer support connectivity via phone, WhatsApp, and email.

---

## 📦 Distribution Artifacts

| Detail | Specification |
| :--- | :--- |
| **Package Name** | `com.ridemycars.app` |
| **Version Name** | `1.0.4` |
| **Version Code** | `10` |
| **Min SDK** | `21` (Android 5.0 Lollipop) |
| **Target SDK** | `34` (Android 14) |
| **Signing Key Alias** | `upload` (`upload-keystore.jks`) |
| **Signature Scheme** | `v1`, `v2`, `v3` verified |
| **Play Store Bundle (`.aab`)** | [`dist/playstore/RideMyCars_Rider_v1.0.4_build10.aab`](file:///Users/manjotsingh/RideMyCars/dist/playstore/RideMyCars_Rider_v1.0.4_build10.aab) |
| **Standard Release AAB** | [`release_builds/RideMyCars-Rider-release.aab`](file:///Users/manjotsingh/RideMyCars/release_builds/RideMyCars-Rider-release.aab) |
| **File Size** | `44.6 MB` |
