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
        final auth = Provider.of<AuthProvider>(context, listen: false);
        final foundAvatar = _driverProfile?['image_url'] ?? _userProfile?['avatar_url'] ?? _userProfile?['avatar'];
        if (foundAvatar != null && foundAvatar.toString().isNotEmpty) {
          final resolved = foundAvatar.toString().startsWith('http')
              ? foundAvatar.toString()
              : '${ApiConstants.storageBaseUrl}/${foundAvatar.toString().replaceFirst(RegExp(r"^storage/"), "")}';
          auth.setAvatarUrl(resolved);
        }
      }
    } catch (e) {
      debugPrint('Error fetching driver profile: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Widget _buildAvatarFallback(String name) {
    return CircleAvatar(
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
    );
  }

  void _showEditProfileDialog() {
    final nameCtrl = TextEditingController(text: _userProfile?['name'] ?? '');
    final phoneCtrl = TextEditingController(text: _userProfile?['phone'] ?? '');
    final photoCtrl = TextEditingController(text: _driverProfile?['image_url'] ?? '');
    final hourlyCtrl = TextEditingController(text: (_driverProfile?['hourly_rate'] ?? 25.0).toString());
    final bioCtrl = TextEditingController(text: _driverProfile?['bio'] ?? '');
    bool isSaving = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.surfaceDark,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
      ),
      builder: (sheetContext) => StatefulBuilder(
        builder: (context, setSheetState) => Padding(
          padding: EdgeInsets.only(
            left: 20,
            right: 20,
            top: 20,
            bottom: MediaQuery.of(sheetContext).viewInsets.bottom + 20,
          ),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Center(
                  child: Container(
                    width: 40,
                    height: 4,
                    decoration: BoxDecoration(
                      color: Colors.white24,
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                const Text(
                  'Edit Driver Profile & Photo',
                  style: TextStyle(
                    color: AppColors.textLight,
                    fontSize: 18,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: nameCtrl,
                  style: const TextStyle(color: AppColors.textLight),
                  decoration: InputDecoration(
                    labelText: 'Full Name',
                    labelStyle: const TextStyle(color: AppColors.textMuted),
                    filled: true,
                    fillColor: AppColors.backgroundDark,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: phoneCtrl,
                  style: const TextStyle(color: AppColors.textLight),
                  decoration: InputDecoration(
                    labelText: 'Phone Number',
                    labelStyle: const TextStyle(color: AppColors.textMuted),
                    filled: true,
                    fillColor: AppColors.backgroundDark,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: photoCtrl,
                  style: const TextStyle(color: AppColors.textLight),
                  decoration: InputDecoration(
                    labelText: 'Driver Photo URL',
                    hintText: 'https://... or sample URL',
                    hintStyle: const TextStyle(color: AppColors.textMuted, fontSize: 12),
                    labelStyle: const TextStyle(color: AppColors.textMuted),
                    filled: true,
                    fillColor: AppColors.backgroundDark,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
                const SizedBox(height: 8),
                const Text(
                  'Or pick a formal driver photo preset:',
                  style: TextStyle(color: AppColors.textMuted, fontSize: 11),
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400',
                    'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400',
                    'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400',
                    'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400',
                  ].map((url) => Padding(
                    padding: const EdgeInsets.only(right: 8.0),
                    child: GestureDetector(
                      onTap: () {
                        setSheetState(() => photoCtrl.text = url);
                      },
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(10),
                        child: Image.network(url, width: 44, height: 44, fit: BoxFit.cover),
                      ),
                    ),
                  )).toList(),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: hourlyCtrl,
                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                  style: const TextStyle(color: AppColors.textLight),
                  decoration: InputDecoration(
                    labelText: 'Hourly Rate (\$)',
                    labelStyle: const TextStyle(color: AppColors.textMuted),
                    filled: true,
                    fillColor: AppColors.backgroundDark,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: bioCtrl,
                  maxLines: 2,
                  style: const TextStyle(color: AppColors.textLight),
                  decoration: InputDecoration(
                    labelText: 'Bio / Experience',
                    labelStyle: const TextStyle(color: AppColors.textMuted),
                    filled: true,
                    fillColor: AppColors.backgroundDark,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
                const SizedBox(height: 18),
                ElevatedButton(
                  onPressed: isSaving ? null : () async {
                    setSheetState(() => isSaving = true);
                    try {
                      await _dio.post(ApiConstants.driverProfile, data: {
                        'name': nameCtrl.text.trim(),
                        'phone': phoneCtrl.text.trim(),
                        'photo_url': photoCtrl.text.trim(),
                        'driver_photo': photoCtrl.text.trim(),
                        'hourly_rate': double.tryParse(hourlyCtrl.text.trim()),
                        'bio': bioCtrl.text.trim(),
                      });
                      if (context.mounted) {
                        Navigator.pop(sheetContext);
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Profile and photo updated successfully!'), backgroundColor: AppColors.success),
                        );
                        _fetchProfile();
                      }
                    } catch (e) {
                      setSheetState(() => isSaving = false);
                      if (context.mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(content: Text('Failed to update: $e'), backgroundColor: AppColors.danger),
                        );
                      }
                    }
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    foregroundColor: AppColors.backgroundDark,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  child: isSaving
                      ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.backgroundDark))
                      : const Text('Save Profile & Photo', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                ),
              ],
            ),
          ),
        ),
      ),
    );
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

    final rawPhoto = _driverProfile?['image_url'] ?? _userProfile?['avatar_url'] ?? _userProfile?['avatar'] ?? _userProfile?['profile_photo_path'] ?? auth.avatarUrl;
    final photoUrl = rawPhoto != null && rawPhoto.toString().isNotEmpty
        ? (rawPhoto.toString().startsWith('http') ? rawPhoto.toString() : '${ApiConstants.storageBaseUrl}/${rawPhoto.toString().replaceFirst(RegExp(r"^storage/"), "")}')
        : null;

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
                    child: GestureDetector(
                      onTap: _showEditProfileDialog,
                      child: Stack(
                        children: [
                          Container(
                            width: 92,
                            height: 92,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              border: Border.all(color: AppColors.primary, width: 2.5),
                              boxShadow: [
                                BoxShadow(
                                  color: AppColors.primary.withValues(alpha: 0.2),
                                  blurRadius: 16,
                                  offset: const Offset(0, 4),
                                ),
                              ],
                            ),
                            child: ClipOval(
                              child: photoUrl != null
                                  ? Image.network(
                                      photoUrl,
                                      fit: BoxFit.cover,
                                      errorBuilder: (_, __, ___) => _buildAvatarFallback(name),
                                    )
                                  : _buildAvatarFallback(name),
                            ),
                          ),
                          Positioned(
                            bottom: 0,
                            right: 0,
                            child: Container(
                              padding: const EdgeInsets.all(6),
                              decoration: const BoxDecoration(
                                color: AppColors.primary,
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.camera_alt_rounded, color: AppColors.backgroundDark, size: 16),
                            ),
                          ),
                          if (isVerified)
                            Positioned(
                              top: 0,
                              right: 0,
                              child: Container(
                                padding: const EdgeInsets.all(4),
                                decoration: const BoxDecoration(
                                  color: AppColors.surfaceDark,
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(Icons.verified_rounded, color: AppColors.success, size: 18),
                              ),
                            ),
                        ],
                      ),
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
                  const SizedBox(height: 10),
                  Center(
                    child: OutlinedButton.icon(
                      onPressed: _showEditProfileDialog,
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppColors.primary,
                        side: const BorderSide(color: AppColors.primary),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      ),
                      icon: const Icon(Icons.edit_rounded, size: 15),
                      label: const Text('Edit Profile & Photo', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                    ),
                  ),
                  const SizedBox(height: 20),

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
