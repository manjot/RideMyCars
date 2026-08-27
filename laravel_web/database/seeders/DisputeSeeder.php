<?php

namespace Database\Seeders;

use App\Models\Dispute;
use App\Models\User;
use Illuminate\Database\Seeder;

class DisputeSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('role', 'customer')->first() ?? User::first();
        if (!$user) {
            return;
        }

        $sampleDisputes = [
            [
                'dispute_code' => 'DSP-2026-88101',
                'user_id' => $user->id,
                'service_type' => 'ride',
                'booking_reference' => 'RIDE-99201',
                'category' => 'Cancellation Fee Waiver Request',
                'description' => 'Driver accepted the ride but went in the opposite direction for 5 minutes before I cancelled. Requesting waiver of the $5.00 cancellation fee under the grace window rule.',
                'contact_email' => $user->email ?? 'customer@ridemycars.com',
                'contact_phone' => '+1 410 570 6639',
                'status' => 'submitted',
                'is_within_72h' => true,
                'event_completed_at' => now()->subHours(12),
                'deadline_at' => now()->addHours(60),
                'admin_notes' => 'Awaiting GPS trajectory logs review from dispatch service.',
            ],
            [
                'dispute_code' => 'DSP-2026-88102',
                'user_id' => $user->id,
                'service_type' => 'rental',
                'booking_reference' => 'RENT-2026-4421',
                'category' => 'Security Deposit Authorization Refund',
                'description' => 'Vehicle Toyota RAV4 returned clean with full tank. Requesting release of the $250.00 pre-authorization security hold as digital inspection cleared.',
                'contact_email' => 'john.client@example.com',
                'contact_phone' => '+1 410 570 6639',
                'status' => 'under_review',
                'is_within_72h' => true,
                'event_completed_at' => now()->subHours(24),
                'deadline_at' => now()->addHours(48),
                'admin_notes' => 'Inspections verified. Payment gateway hold release in progress.',
            ],
            [
                'dispute_code' => 'DSP-2026-88103',
                'user_id' => $user->id,
                'service_type' => 'chauffeur',
                'booking_reference' => 'CHFR-2026-1109',
                'category' => 'Deposit Penalty Dispute (24h Window)',
                'description' => 'Cancelled chauffeur booking 26 hours in advance due to flight rescheduling. Requesting 100% deposit refund according to Article II of Refund Policy.',
                'contact_email' => 'corporate.booking@client.com',
                'contact_phone' => '+1 410 570 6639',
                'status' => 'resolved',
                'is_within_72h' => true,
                'event_completed_at' => now()->subDays(2),
                'deadline_at' => now()->subHours(24),
                'admin_notes' => 'Verified 26h notice time stamp. 100% refund processed via Stripe API.',
                'resolved_at' => now()->subHours(6),
            ],
            [
                'dispute_code' => 'DSP-2026-88104',
                'user_id' => $user->id,
                'service_type' => 'delivery',
                'booking_reference' => 'DELV-2026-7712',
                'category' => 'Return-to-Sender Fee Clarification',
                'description' => 'Recipient was present at destination but courier did not request the 4-digit PIN before marking return. Requesting credit for return transit fee.',
                'contact_email' => 'logistics@client.org',
                'contact_phone' => '+1 410 570 6639',
                'status' => 'awaiting_info',
                'is_within_72h' => true,
                'event_completed_at' => now()->subHours(36),
                'deadline_at' => now()->addHours(36),
                'admin_notes' => 'Requested recipient confirmation details from courier partner.',
            ],
            [
                'dispute_code' => 'DSP-2026-88105',
                'user_id' => $user->id,
                'service_type' => 'rental',
                'booking_reference' => 'RENT-2026-3310',
                'category' => 'No-Show Fee Claim Exceeding 2h',
                'description' => 'Arrived 2.5 hours late for vehicle collection due to airport customs delay. Host marked vehicle as No-Show. Submitting airline delay proof for review.',
                'contact_email' => 'traveller@express.com',
                'contact_phone' => '+1 410 570 6639',
                'status' => 'rejected',
                'is_within_72h' => false,
                'event_completed_at' => now()->subDays(4),
                'deadline_at' => now()->subHours(24),
                'admin_notes' => 'Dispute submitted past the 72-hour contractual deadline (96 hours). Rejected per Article V §5.2.',
                'resolved_at' => now()->subHours(12),
            ],
        ];

        foreach ($sampleDisputes as $disputeData) {
            Dispute::updateOrCreate(
                ['dispute_code' => $disputeData['dispute_code']],
                $disputeData
            );
        }
    }
}
