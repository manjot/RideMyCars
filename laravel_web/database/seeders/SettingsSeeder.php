<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Home Page
            ['key' => 'home.hero.title', 'label' => 'Home - Hero Title', 'value' => 'One App.<br><span class="text-orange-500">Three Ways</span><br>to Move.', 'group' => 'Home Page', 'type' => 'text'],
            ['key' => 'home.hero.subtitle', 'label' => 'Home - Hero Subtitle', 'value' => 'Book a ride, rent a vehicle, or hire a professional driver — all from a single platform built for the modern traveler.', 'group' => 'Home Page', 'type' => 'textarea'],
            ['key' => 'home.features.title', 'label' => 'Home - Features Title', 'value' => 'Everything you need. Nothing you don\'t.', 'group' => 'Home Page', 'type' => 'text'],
            
            // Footer
            ['key' => 'footer.support_email', 'label' => 'Footer - Support Email', 'value' => 'support@ridemycars.com', 'group' => 'Footer', 'type' => 'text'],
            ['key' => 'footer.support_phone', 'label' => 'Footer - Support Phone', 'value' => '+1 855 203 3177', 'group' => 'Footer', 'type' => 'text'],
            ['key' => 'footer.location', 'label' => 'Footer - Location', 'value' => 'Washington, DC', 'group' => 'Footer', 'type' => 'text'],
            ['key' => 'footer.copyright', 'label' => 'Footer - Copyright', 'value' => '© 2026 RideMyCars • A New Development Finance Group Company. All rights reserved.', 'group' => 'Footer', 'type' => 'text'],

            // Social Media
            ['key' => 'social.instagram_url', 'label' => 'Social - Instagram Profile URL', 'value' => 'https://www.instagram.com/ridemycars1?igsi=ZHc2ZjltdHdiaDNj&utm_source=qr', 'group' => 'Social Media', 'type' => 'text'],
            ['key' => 'social.x_url', 'label' => 'Social - X (Twitter) Profile URL', 'value' => 'https://x.com/ridemycars', 'group' => 'Social Media', 'type' => 'text'],
            ['key' => 'social.tiktok_url', 'label' => 'Social - TikTok Profile URL', 'value' => 'https://www.tiktok.com/@ridemycars', 'group' => 'Social Media', 'type' => 'text'],
            ['key' => 'social.facebook_url', 'label' => 'Social - Facebook Profile URL', 'value' => 'https://www.facebook.com/profile.php?id=61594184214102', 'group' => 'Social Media', 'type' => 'text'],
            ['key' => 'social.linkedin_url', 'label' => 'Social - LinkedIn Profile URL', 'value' => 'https://www.linkedin.com/in/ride-mycars-587b03432', 'group' => 'Social Media', 'type' => 'text'],

            // App Links
            ['key' => 'app.ios_link', 'label' => 'App - iOS Download Link', 'value' => '#', 'group' => 'App Links', 'type' => 'text'],
            ['key' => 'app.android_link', 'label' => 'App - Android Download Link', 'value' => '#', 'group' => 'App Links', 'type' => 'text'],

            // Financial & Commission Rules
            ['key' => 'ride_hailing.platform_commission', 'label' => 'Ride Hailing - Platform Commission (%)', 'value' => '10', 'group' => 'Commissions', 'type' => 'text'],
            ['key' => 'ride_hailing.maintenance_fee_percent', 'label' => 'Ride Hailing - App Maintenance Fee (% of Owner Share)', 'value' => '2.5', 'group' => 'Commissions', 'type' => 'text'],
            ['key' => 'ride_hailing.gateway_fee_absorber', 'label' => 'Ride Hailing - Gateway Fee Absorber (passenger, platform, fleet_owner)', 'value' => 'fleet_owner', 'group' => 'Commissions', 'type' => 'text'],
            ['key' => 'driver_hiring.platform_commission', 'label' => 'Driver Hiring - Platform Commission (%)', 'value' => '15', 'group' => 'Commissions', 'type' => 'text'],
            ['key' => 'vehicle_rental.platform_commission', 'label' => 'Vehicle Rental - Platform Commission (%)', 'value' => '20', 'group' => 'Commissions', 'type' => 'text'],
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
