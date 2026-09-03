import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/api/api_client.dart';
import '../../core/constants/api_constants.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/auth_provider.dart';

class ManageAccountScreen extends StatefulWidget {
  const ManageAccountScreen({super.key});

  @override
  State<ManageAccountScreen> createState() => _ManageAccountScreenState();
}

class _ManageAccountScreenState extends State<ManageAccountScreen> {
  final Dio _dio = ApiClient().dio;
  bool _isLoading = false;
  Map<String, dynamic>? _userProfile;
  Map<String, dynamic>? _driverProfile;

  @override
  void initState() {
    super.initState();
    _fetchProfile();
  }

  Future<void> _fetchProfile() async {
    setState(() => _isLoading = true);
    try {
      final res = await _dio.get(ApiConstants.me);
      if (res.statusCode == 200 && res.data['success'] == true) {
        setState(() {
          _userProfile = res.data['user'];
          _driverProfile = res.data['driver_profile'];
        });
      }
    } catch (e) {
      debugPrint('Error fetching driver profile: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final name = _userProfile?['name'] ?? auth.userName ?? 'Sipho Ndlovu';
    final email = _userProfile?['email'] ?? auth.userEmail ?? '';
    final license = _driverProfile?['masked_license'] ?? _driverProfile?['license_number'] ?? 'DL*****18';
    final rating = (_driverProfile?['rating'] ?? 5.0).toString();
    final totalTrips = (_driverProfile?['total_trips'] ?? 40).toString();
    final hourlyRate = (_driverProfile?['hourly_rate'] ?? 35.0).toString();
    final isVerified = _driverProfile?['is_verified'] == true || _driverProfile?['verification_status'] == 'verified';

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
          'Manage Account',
          style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w800, fontSize: 18),
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Profile Avatar Banner
                  Center(
                    child: Stack(
                      children: [
                        CircleAvatar(
                          radius: 46,
                          backgroundColor: AppColors.primary,
                          child: Text(
                            name.isNotEmpty ? name[0].toUpperCase() : 'D',
                            style: const TextStyle(
                              color: AppColors.backgroundDark,
                              fontSize: 36,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                        ),
                        if (isVerified)
                          Positioned(
                            bottom: 0,
                            right: 0,
                            child: Container(
                              padding: const EdgeInsets.all(6),
                              decoration: const BoxDecoration(
                                color: AppColors.surfaceDark,
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.verified_rounded, color: AppColors.success, size: 20),
                            ),
                          ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 14),
                  Center(
                    child: Text(
                      name,
                      style: const TextStyle(color: AppColors.textLight, fontSize: 22, fontWeight: FontWeight.w800),
                    ),
                  ),
                  const SizedBox(height: 4),
                  Center(
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: AppColors.primary.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Text(
                        'VERIFIED PROFESSIONAL CHAUFFEUR',
                        style: TextStyle(
                          color: AppColors.primary,
                          fontSize: 10,
                          fontWeight: FontWeight.w900,
                          letterSpacing: 0.5,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),

                  // Key Metrics Row
                  Row(
                    children: [
                      Expanded(child: _buildMetricCard('RATING', '★ $rating', AppColors.primary)),
                      const SizedBox(width: 12),
                      Expanded(child: _buildMetricCard('TOTAL TRIPS', totalTrips, AppColors.success)),
                      const SizedBox(width: 12),
                      Expanded(child: _buildMetricCard('HOURLY RATE', '\$$hourlyRate', AppColors.info)),
                    ],
                  ),
                  const SizedBox(height: 28),

                  // Driver Details Section
                  const Text(
                    'Driver & Verification Details',
                    style: TextStyle(color: AppColors.textLight, fontSize: 16, fontWeight: FontWeight.w800),
                  ),
                  const SizedBox(height: 12),
                  _buildInfoTile(Icons.badge_outlined, 'Driver License Number', license),
                  const SizedBox(height: 10),
                  _buildInfoTile(Icons.email_outlined, 'Registered Email', email),
                  const SizedBox(height: 10),
                  _buildInfoTile(Icons.verified_user_outlined, 'KYC & Background Check', isVerified ? 'Verified & Approved' : 'Pending Verification'),
                  const SizedBox(height: 10),
                  _buildInfoTile(Icons.directions_car_outlined, 'Assigned Vehicle', 'Standard Executive Sedan'),
                  const SizedBox(height: 24),

                  // Settings & Preferences
                  const Text(
                    'Preferences & Payouts',
                    style: TextStyle(color: AppColors.textLight, fontSize: 16, fontWeight: FontWeight.w800),
                  ),
                  const SizedBox(height: 12),
                  _buildActionTile(Icons.account_balance_outlined, 'Payout Bank Account', 'Stripe Connect / Direct Deposit'),
                  const SizedBox(height: 10),
                  _buildActionTile(Icons.navigation_outlined, 'Navigation App', 'Google Maps (Default)'),
                  const SizedBox(height: 32),
                ],
              ),
            ),
    );
  }

  Widget _buildMetricCard(String label, String value, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 10),
      decoration: BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white.withValues(alpha: 0.06)),
      ),
      child: Column(
        children: [
          Text(label, style: const TextStyle(color: AppColors.textMuted, fontSize: 10, fontWeight: FontWeight.w800)),
          const SizedBox(height: 4),
          Text(value, style: TextStyle(color: color, fontSize: 18, fontWeight: FontWeight.w900)),
        ],
      ),
    );
  }

  Widget _buildInfoTile(IconData icon, String label, String value) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white.withValues(alpha: 0.06)),
      ),
      child: Row(
        children: [
          Icon(icon, color: AppColors.primary, size: 22),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(label, style: const TextStyle(color: AppColors.textMuted, fontSize: 11, fontWeight: FontWeight.w600)),
                const SizedBox(height: 2),
                Text(value, style: const TextStyle(color: AppColors.textLight, fontSize: 14, fontWeight: FontWeight.w700)),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildActionTile(IconData icon, String title, String subtitle) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white.withValues(alpha: 0.06)),
      ),
      child: Row(
        children: [
          Icon(icon, color: AppColors.primary, size: 22),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: const TextStyle(color: AppColors.textLight, fontSize: 14, fontWeight: FontWeight.bold)),
                Text(subtitle, style: const TextStyle(color: AppColors.textMuted, fontSize: 12)),
              ],
            ),
          ),
          const Icon(Icons.arrow_forward_ios_rounded, color: Colors.white24, size: 14),
        ],
      ),
    );
  }
}
