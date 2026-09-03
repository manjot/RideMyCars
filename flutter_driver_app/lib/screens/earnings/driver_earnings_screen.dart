import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/driver_provider.dart';

class DriverEarningsScreen extends StatelessWidget {
  const DriverEarningsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final driver = Provider.of<DriverProvider>(context);
    final today = (driver.earnings['today'] as num?)?.toDouble() ?? 0.0;
    final week = (driver.earnings['week'] as num?)?.toDouble() ?? 0.0;
    final month = (driver.earnings['month'] as num?)?.toDouble() ?? 0.0;
    final totalTrips = driver.earnings['total_trips'] ?? 0;

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
          'Earnings & Payouts',
          style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w800, fontSize: 18),
        ),
      ),
      body: RefreshIndicator(
        color: AppColors.primary,
        onRefresh: () async {
          await driver.fetchEarnings();
        },
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Total Balance Hero Card
              Container(
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF0F172A), Color(0xFF1E293B)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(24),
                  border: Border.all(color: AppColors.primary.withValues(alpha: 0.35), width: 1.5),
                  boxShadow: [
                    BoxShadow(
                      color: AppColors.primary.withValues(alpha: 0.1),
                      blurRadius: 20,
                      offset: const Offset(0, 8),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('AVAILABLE PAYOUT BALANCE', style: TextStyle(color: AppColors.textMuted, fontSize: 11, fontWeight: FontWeight.w800, letterSpacing: 0.5)),
                    const SizedBox(height: 10),
                    Text(
                      '\$${month.toStringAsFixed(2)}',
                      style: const TextStyle(color: AppColors.success, fontSize: 36, fontWeight: FontWeight.w900),
                    ),
                    const SizedBox(height: 18),
                    ElevatedButton.icon(
                      onPressed: () {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Instant Payout will be deposited to your linked bank account.')),
                        );
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        foregroundColor: AppColors.backgroundDark,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 20),
                      ),
                      icon: const Icon(Icons.bolt_rounded),
                      label: const Text('Request Instant Payout', style: TextStyle(fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              // Period Breakdown
              const Text('Earnings Summary', style: TextStyle(color: AppColors.textLight, fontSize: 16, fontWeight: FontWeight.w800)),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(child: _buildPeriodTile('TODAY', '\$${today.toStringAsFixed(2)}', AppColors.success)),
                  const SizedBox(width: 12),
                  Expanded(child: _buildPeriodTile('THIS WEEK', '\$${week.toStringAsFixed(2)}', AppColors.info)),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(child: _buildPeriodTile('THIS MONTH', '\$${month.toStringAsFixed(2)}', AppColors.purple)),
                  const SizedBox(width: 12),
                  Expanded(child: _buildPeriodTile('TOTAL TRIPS', '$totalTrips', AppColors.primary)),
                ],
              ),
              const SizedBox(height: 28),

              // Payout Schedule Info
              Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  color: AppColors.surfaceDark,
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(color: Colors.white.withValues(alpha: 0.06)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: const [
                    Row(
                      children: [
                        Icon(Icons.calendar_today_rounded, color: AppColors.primary, size: 18),
                        SizedBox(width: 8),
                        Text('Weekly Automatic Payouts', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 14)),
                      ],
                    ),
                    SizedBox(height: 8),
                    Text(
                      'All completed trips are automatically transferred every Monday directly to your registered bank account.',
                      style: TextStyle(color: AppColors.textMuted, fontSize: 12, height: 1.4),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildPeriodTile(String label, String value, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white.withValues(alpha: 0.06)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: const TextStyle(color: AppColors.textMuted, fontSize: 10, fontWeight: FontWeight.w800, letterSpacing: 0.5)),
          const SizedBox(height: 6),
          Text(value, style: TextStyle(color: color, fontSize: 22, fontWeight: FontWeight.w900)),
        ],
      ),
    );
  }
}
