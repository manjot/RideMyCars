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
            ['key' => 'footer.support_phone', 'label' => 'Footer - Support Phone', 'value' => '+1 800 123 4567', 'group' => 'Footer', 'type' => 'text'],
            ['key' => 'footer.location', 'label' => 'Footer - Location', 'value' => 'San Francisco, CA', 'group' => 'Footer', 'type' => 'text'],
            ['key' => 'footer.copyright', 'label' => 'Footer - Copyright', 'value' => '© 2026 RideMyCars. All rights reserved.', 'group' => 'Footer', 'type' => 'text'],

            // App Links
            ['key' => 'app.ios_link', 'label' => 'App - iOS Download Link', 'value' => '#', 'group' => 'App Links', 'type' => 'text'],
            ['key' => 'app.android_link', 'label' => 'App - Android Download Link', 'value' => '#', 'group' => 'App Links', 'type' => 'text'],
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
