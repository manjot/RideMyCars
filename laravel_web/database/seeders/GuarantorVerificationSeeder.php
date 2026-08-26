<?php

namespace Database\Seeders;

use App\Models\DriverProfile;
use App\Models\GuarantorVerification;
use Illuminate\Database\Seeder;

class GuarantorVerificationSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = DriverProfile::all();

        if ($profiles->isEmpty()) {
            return;
        }

        $kwameProfile = $profiles->firstWhere('country', 'Ghana') ?? $profiles->first();

        // 1. Pending Additional Proof Guarantor
        GuarantorVerification::create([
            'driver_profile_id' => $kwameProfile->id,
            'full_name' => 'Kofi Mensah Senior',
            'ghana_card_number' => 'GHA-719283014-9',
            'dob' => '1975-04-12',
            'relationship' => 'Father / Guardian',
            'primary_phone' => '+233 24 123 4567',
            'alt_phone' => '+233 20 987 6543',
            'digital_address' => 'GA-182-9012',
            'physical_address' => 'Plot 14, East Legon, Accra',
            'employer_business' => 'Ghana Ports Authority',
            'job_title' => 'Senior Logistics Manager',
            'workplace_address' => 'Tema Harbour Administration Building',
            'status' => 'pending_additional_proof',
            'admin_notes' => 'Awaiting upload of signed liability agreement document.',
        ]);

        // 2. Approved Guarantor
        if ($profiles->count() > 1) {
            $secondProfile = $profiles->skip(1)->first();
            GuarantorVerification::create([
                'driver_profile_id' => $secondProfile->id,
                'full_name' => 'Dr. Emmanuel Addo',
                'ghana_card_number' => 'GHA-982103948-2',
                'dob' => '1968-11-25',
                'relationship' => 'Uncle / Landlord',
                'primary_phone' => '+233 55 443 2100',
                'digital_address' => 'GA-049-1102',
                'physical_address' => 'House No. 8, Airport Residential Area',
                'employer_business' => 'Korle Bu Teaching Hospital',
                'job_title' => 'Chief Medical Officer',
                'workplace_address' => 'Korle Bu, Accra',
                'status' => 'approved',
                'admin_notes' => 'Ghana Card and Ghana Post GPS address verified by support team.',
            ]);
        }

        // 3. Rejected Guarantor
        if ($profiles->count() > 2) {
            $thirdProfile = $profiles->skip(2)->first();
            GuarantorVerification::create([
                'driver_profile_id' => $thirdProfile->id,
                'full_name' => 'Samuel Osei',
                'ghana_card_number' => 'GHA-110293847-0',
                'dob' => '1982-08-14',
                'relationship' => 'Business Partner',
                'primary_phone' => '+233 27 665 4321',
                'digital_address' => 'GA-999-0000',
                'physical_address' => 'Spintex Road, Accra',
                'employer_business' => 'Self Employed',
                'job_title' => 'Trader',
                'status' => 'rejected',
                'admin_notes' => 'Rejection Reason: Bounced Phone Check - Phone number disconnected.',
            ]);
        }
    }
}
