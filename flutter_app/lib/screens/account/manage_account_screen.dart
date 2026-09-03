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
        });
      }
    } catch (e) {
      debugPrint('Error fetching profile: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final name = _userProfile?['name'] ?? auth.userName ?? 'Rider Account';
    final email = _userProfile?['email'] ?? auth.userEmail ?? '';
    final phone = _userProfile?['phone'] ?? '+1 (555) 019-2834';
    final role = (_userProfile?['role'] ?? 'Customer').toString().toUpperCase();

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
                            name.isNotEmpty ? name[0].toUpperCase() : 'U',
                            style: const TextStyle(
                              color: AppColors.backgroundDark,
                              fontSize: 36,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                        ),
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
                      style: const TextStyle(
                        color: AppColors.textLight,
                        fontSize: 22,
                        fontWeight: FontWeight.w800,
                      ),
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
                      child: Text(
                        role,
                        style: const TextStyle(
                          color: AppColors.primary,
                          fontSize: 11,
                          fontWeight: FontWeight.w800,
                          letterSpacing: 0.5,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 28),

                  // Personal Information Section
                  const Text(
                    'Personal Information',
                    style: TextStyle(color: AppColors.textLight, fontSize: 16, fontWeight: FontWeight.w800),
                  ),
                  const SizedBox(height: 12),
                  _buildInfoTile(Icons.email_outlined, 'Email Address', email),
                  const SizedBox(height: 10),
                  _buildInfoTile(Icons.phone_outlined, 'Phone Number', phone),
                  const SizedBox(height: 10),
                  _buildInfoTile(Icons.shield_outlined, 'Account Security', 'Password Protected & 2FA Ready'),
                  const SizedBox(height: 24),

                  // Saved Places Section
                  const Text(
                    'Saved Places',
                    style: TextStyle(color: AppColors.textLight, fontSize: 16, fontWeight: FontWeight.w800),
                  ),
                  const SizedBox(height: 12),
                  _buildSavedPlaceTile(Icons.home_rounded, 'Home', 'Add Home Address'),
                  const SizedBox(height: 10),
                  _buildSavedPlaceTile(Icons.work_rounded, 'Work', 'Add Work Address'),
                  const SizedBox(height: 24),

                  // Safety & Privacy
                  const Text(
                    'Safety & Privacy',
                    style: TextStyle(color: AppColors.textLight, fontSize: 16, fontWeight: FontWeight.w800),
                  ),
                  const SizedBox(height: 12),
                  _buildActionTile(Icons.emergency_outlined, 'Emergency Contacts', 'Manage trusted contacts'),
                  const SizedBox(height: 10),
                  _buildActionTile(Icons.lock_outline_rounded, 'Privacy & Permissions', 'Location, notifications & data'),
                  const SizedBox(height: 32),
                ],
              ),
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

  Widget _buildSavedPlaceTile(IconData icon, String title, String subtitle) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white.withValues(alpha: 0.06)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: AppColors.info.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: AppColors.info, size: 20),
          ),
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
