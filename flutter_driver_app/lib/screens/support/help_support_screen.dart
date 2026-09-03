import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';

class HelpSupportScreen extends StatelessWidget {
  const HelpSupportScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.backgroundDark,
      appBar: AppBar(
        backgroundColor: AppColors.surfaceDark,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: AppColors.textLight, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Driver Support & Safety',
          style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w800, fontSize: 18),
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          // 24/7 Driver Support Banner
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF0F172A), Color(0xFF1E293B)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: AppColors.primary.withValues(alpha: 0.3)),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: AppColors.primary.withValues(alpha: 0.2),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.headset_mic_rounded, color: AppColors.primary, size: 28),
                ),
                const SizedBox(width: 16),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Driver Partner Help Desk',
                        style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 16),
                      ),
                      SizedBox(height: 2),
                      Text(
                        'Dedicated priority assistance for verified chauffeurs and drivers.',
                        style: TextStyle(color: AppColors.textMuted, fontSize: 12),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),

          // FAQ Options
          const Text('Driver Partner FAQ', style: TextStyle(color: AppColors.textLight, fontSize: 16, fontWeight: FontWeight.bold)),
          const SizedBox(height: 12),
          _buildFAQTile('When do I receive trip payouts?', 'Weekly automatic payouts are transferred every Monday directly to your registered bank account, or instantly via Instant Payout.'),
          _buildFAQTile('How does ride verification work?', 'When a rider books a trip with Stripe or cash, you verify and approve the booking details directly from your Driver Console.'),
          _buildFAQTile('How does live navigation work?', 'Tap the Live Navigation button on any active trip to open turn-by-turn routing in Google Maps.'),
          _buildFAQTile('What if a rider does not show up?', 'After waiting 5 minutes at the pickup location, you can update the trip status or contact support.'),
        ],
      ),
    );
  }

  Widget _buildFAQTile(String title, String answer) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white.withValues(alpha: 0.06)),
      ),
      child: ExpansionTile(
        title: Text(title, style: const TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w600, fontSize: 14)),
        iconColor: AppColors.primary,
        collapsedIconColor: AppColors.textMuted,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
            child: Text(answer, style: const TextStyle(color: AppColors.textMuted, fontSize: 13, height: 1.4)),
          ),
        ],
      ),
    );
  }
}
