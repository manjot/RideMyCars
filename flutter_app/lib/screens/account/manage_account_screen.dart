import 'dart:io';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../../core/api/api_client.dart';
import '../../core/constants/api_constants.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../providers/country_provider.dart';
import '../../core/constants/countries_data.dart';
import '../../widgets/country_picker_modal.dart';
import 'receipts_screen.dart';
import 'package:url_launcher/url_launcher.dart';
import '../safety/sos_contacts_screen.dart';
import '../rides/my_rides_screen.dart';
import '../referral/referral_screen.dart';

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

  Future<void> _showPhotoSourceSheet(AuthProvider auth) async {
    showModalBottomSheet(
      context: context,
      backgroundColor: AppColors.surfaceDark,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Text(
                'Upload Profile Photo',
                style: TextStyle(
                  color: AppColors.textLight,
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 6),
              const Text(
                'Choose an image source to update your profile photo',
                style: TextStyle(color: AppColors.textMuted, fontSize: 13),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 20),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () async {
                        Navigator.pop(ctx);
                        final picker = ImagePicker();
                        final picked = await picker.pickImage(
                          source: ImageSource.camera,
                          maxWidth: 1024,
                          maxHeight: 1024,
                          imageQuality: 85,
                        );
                        if (picked != null) {
                          _uploadDirectPhoto(picked, auth);
                        }
                      },
                      icon: const Icon(Icons.camera_alt_rounded, color: AppColors.primary),
                      label: const Text('Camera'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppColors.textLight,
                        side: const BorderSide(color: AppColors.primary),
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: () async {
                        Navigator.pop(ctx);
                        final picker = ImagePicker();
                        final picked = await picker.pickImage(
                          source: ImageSource.gallery,
                          maxWidth: 1024,
                          maxHeight: 1024,
                          imageQuality: 85,
                        );
                        if (picked != null) {
                          _uploadDirectPhoto(picked, auth);
                        }
                      },
                      icon: const Icon(Icons.photo_library_rounded, color: AppColors.backgroundDark),
                      label: const Text('Gallery'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        foregroundColor: AppColors.backgroundDark,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: const Text('Cancel', style: TextStyle(color: AppColors.textMuted)),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _uploadDirectPhoto(XFile file, AuthProvider auth) async {
    setState(() => _isLoading = true);
    try {
      MultipartFile multipartFile;
      if (kIsWeb) {
        final bytes = await file.readAsBytes();
        multipartFile = MultipartFile.fromBytes(bytes, filename: file.name);
      } else {
        multipartFile = await MultipartFile.fromFile(file.path, filename: file.name);
      }

      final formData = FormData.fromMap({
        'avatar': multipartFile,
        'photo': multipartFile,
      });

      final res = await _dio.post(ApiConstants.userAvatar, data: formData);
      if (res.statusCode == 200 && res.data['success'] == true) {
        final newAvatarUrl = res.data['avatar_url'] ?? res.data['user']?['avatar_url'];
        if (newAvatarUrl != null) {
          auth.setAvatarUrl(newAvatarUrl.toString());
          if (_userProfile != null) {
            _userProfile!['avatar_url'] = newAvatarUrl.toString();
          }
        }
        await _fetchProfile();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: const Row(
                children: [
                  Icon(Icons.check_circle_rounded, color: Colors.white),
                  SizedBox(width: 8),
                  Text('Profile photo updated successfully!'),
                ],
              ),
              backgroundColor: AppColors.success,
              behavior: SnackBarBehavior.floating,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
          );
        }
      } else {
        throw Exception(res.data['message'] ?? 'Upload failed');
      }
    } catch (e) {
      debugPrint('Error uploading photo: $e');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to upload photo: $e'),
            backgroundColor: AppColors.error,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _showEditProfileModal(AuthProvider auth) {
    final nameCtrl = TextEditingController(text: _userProfile?['name'] ?? auth.userName ?? '');
    final phoneCtrl = TextEditingController(text: _userProfile?['phone'] ?? '');
    XFile? localPickedImage;
    bool isSaving = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.surfaceDark,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setModalState) {
          final currentAvatar = _userProfile?['avatar_url'] ?? auth.avatarUrl;

          return Padding(
            padding: EdgeInsets.only(
              left: 20,
              right: 20,
              top: 20,
              bottom: MediaQuery.of(ctx).viewInsets.bottom + 20,
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Edit Profile & Photo',
                        style: TextStyle(
                          color: AppColors.textLight,
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.close_rounded, color: AppColors.textMuted),
                        onPressed: () => Navigator.pop(ctx),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  // Photo Upload Card with Preview & Direct Camera/Gallery options
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: AppColors.backgroundDark,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: AppColors.primary.withOpacity(0.3)),
                    ),
                    child: Column(
                      children: [
                        Center(
                          child: Stack(
                            children: [
                              CircleAvatar(
                                radius: 46,
                                backgroundColor: AppColors.surfaceDark,
                                backgroundImage: localPickedImage != null
                                    ? (kIsWeb
                                        ? NetworkImage(localPickedImage!.path)
                                        : FileImage(File(localPickedImage!.path)) as ImageProvider)
                                    : (currentAvatar != null && currentAvatar.toString().isNotEmpty)
                                        ? NetworkImage(currentAvatar.toString())
                                        : null,
                                child: (localPickedImage == null &&
                                        (currentAvatar == null || currentAvatar.toString().isEmpty))
                                    ? const Icon(Icons.person_rounded, size: 48, color: AppColors.primary)
                                    : null,
                              ),
                              if (localPickedImage != null)
                                Positioned(
                                  top: 0,
                                  right: 0,
                                  child: GestureDetector(
                                    onTap: () => setModalState(() => localPickedImage = null),
                                    child: Container(
                                      padding: const EdgeInsets.all(4),
                                      decoration: const BoxDecoration(
                                        color: AppColors.error,
                                        shape: BoxShape.circle,
                                      ),
                                      child: const Icon(Icons.close_rounded, size: 14, color: Colors.white),
                                    ),
                                  ),
                                ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 12),
                        const Text(
                          'Upload Profile Photo',
                          style: TextStyle(
                            color: AppColors.textLight,
                            fontSize: 14,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        const SizedBox(height: 4),
                        const Text(
                          'Upload directly from your camera or photo gallery',
                          style: TextStyle(color: AppColors.textMuted, fontSize: 11),
                        ),
                        const SizedBox(height: 14),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            OutlinedButton.icon(
                              onPressed: () async {
                                final picker = ImagePicker();
                                final img = await picker.pickImage(
                                  source: ImageSource.camera,
                                  maxWidth: 1024,
                                  maxHeight: 1024,
                                  imageQuality: 85,
                                );
                                if (img != null) {
                                  setModalState(() => localPickedImage = img);
                                }
                              },
                              icon: const Icon(Icons.camera_alt_rounded, size: 16, color: AppColors.primary),
                              label: const Text('Camera'),
                              style: OutlinedButton.styleFrom(
                                foregroundColor: AppColors.textLight,
                                side: const BorderSide(color: AppColors.primary),
                                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              ),
                            ),
                            const SizedBox(width: 12),
                            ElevatedButton.icon(
                              onPressed: () async {
                                final picker = ImagePicker();
                                final img = await picker.pickImage(
                                  source: ImageSource.gallery,
                                  maxWidth: 1024,
                                  maxHeight: 1024,
                                  imageQuality: 85,
                                );
                                if (img != null) {
                                  setModalState(() => localPickedImage = img);
                                }
                              },
                              icon: const Icon(Icons.photo_library_rounded, size: 16, color: AppColors.backgroundDark),
                              label: const Text('Gallery'),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: AppColors.primary,
                                foregroundColor: AppColors.backgroundDark,
                                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 18),

                  // Name Field
                  TextField(
                    controller: nameCtrl,
                    style: const TextStyle(color: AppColors.textLight),
                    decoration: InputDecoration(
                      labelText: 'Full Name',
                      labelStyle: const TextStyle(color: AppColors.textMuted),
                      prefixIcon: const Icon(Icons.person_outline_rounded, color: AppColors.primary),
                      filled: true,
                      fillColor: AppColors.backgroundDark,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: BorderSide.none),
                      focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.primary)),
                    ),
                  ),
                  const SizedBox(height: 14),

                  // Phone Field
                  TextField(
                    controller: phoneCtrl,
                    keyboardType: TextInputType.phone,
                    style: const TextStyle(color: AppColors.textLight),
                    decoration: InputDecoration(
                      labelText: 'Phone Number',
                      labelStyle: const TextStyle(color: AppColors.textMuted),
                      prefixIcon: const Icon(Icons.phone_outlined, color: AppColors.primary),
                      filled: true,
                      fillColor: AppColors.backgroundDark,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: BorderSide.none),
                      focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.primary)),
                    ),
                  ),
                  const SizedBox(height: 24),

                  // Save Profile & Photo Button
                  ElevatedButton(
                    onPressed: isSaving
                        ? null
                        : () async {
                            final newName = nameCtrl.text.trim();
                            final newPhone = phoneCtrl.text.trim();

                            if (newName.isEmpty) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(content: Text('Please enter your full name')),
                              );
                              return;
                            }

                            setModalState(() => isSaving = true);
                            try {
                              final mapData = <String, dynamic>{
                                'name': newName,
                                'phone': newPhone,
                              };

                              if (localPickedImage != null) {
                                MultipartFile multipartFile;
                                if (kIsWeb) {
                                  final bytes = await localPickedImage!.readAsBytes();
                                  multipartFile = MultipartFile.fromBytes(bytes, filename: localPickedImage!.name);
                                } else {
                                  multipartFile = await MultipartFile.fromFile(
                                    localPickedImage!.path,
                                    filename: localPickedImage!.name,
                                  );
                                }
                                mapData['avatar'] = multipartFile;
                                mapData['photo'] = multipartFile;
                              }

                              final formData = FormData.fromMap(mapData);
                              final res = await _dio.post(ApiConstants.userProfile, data: formData);

                              if (res.statusCode == 200 && res.data['success'] == true) {
                                final updatedUser = res.data['user'] ?? {};
                                final newAvatarUrl = updatedUser['avatar_url'] ?? res.data['avatar_url'];
                                if (newAvatarUrl != null) {
                                  auth.setAvatarUrl(newAvatarUrl.toString());
                                }
                                if (updatedUser['name'] != null) {
                                  auth.setUserName(updatedUser['name'].toString());
                                }
                                if (ctx.mounted) Navigator.pop(ctx);
                                await _fetchProfile();
                                if (mounted) {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(
                                      content: const Row(
                                        children: [
                                          Icon(Icons.check_circle_rounded, color: Colors.white),
                                          SizedBox(width: 8),
                                          Text('Profile & photo saved successfully!'),
                                        ],
                                      ),
                                      backgroundColor: AppColors.success,
                                      behavior: SnackBarBehavior.floating,
                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                    ),
                                  );
                                }
                              } else {
                                throw Exception(res.data['message'] ?? 'Failed to update profile');
                              }
                            } catch (e) {
                              debugPrint('Error updating profile: $e');
                              if (mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text('Error updating profile: $e'),
                                    backgroundColor: AppColors.error,
                                    behavior: SnackBarBehavior.floating,
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                  ),
                                );
                              }
                            } finally {
                              if (mounted) setModalState(() => isSaving = false);
                            }
                          },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      foregroundColor: AppColors.backgroundDark,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    ),
                    child: isSaving
                        ? const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(color: AppColors.backgroundDark, strokeWidth: 2),
                          )
                        : const Text(
                            'Save Profile & Photo',
                            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                          ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final countryProv = Provider.of<CountryProvider>(context);
    final name = _userProfile?['name'] ?? auth.userName ?? 'Rider Account';
    final email = _userProfile?['email'] ?? auth.userEmail ?? '';
    final phone = _userProfile?['phone'] ?? '+1 (555) 019-2834';
    final role = (_userProfile?['role'] ?? 'Customer').toString().toUpperCase();
    final avatarUrl = _userProfile?['avatar_url'] ?? auth.avatarUrl;
    final referralCode = (_userProfile?['referral_code'] ?? auth.referralCode ?? 'RMC000000').toString();
    final referredBy = _userProfile?['referred_by'] ?? auth.referredBy;

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
                  // Profile Avatar Banner with tap to upload photo
                  Center(
                    child: GestureDetector(
                      onTap: () => _showPhotoSourceSheet(auth),
                      child: Stack(
                        children: [
                          CircleAvatar(
                            radius: 48,
                            backgroundColor: AppColors.primary,
                            backgroundImage: (avatarUrl != null && avatarUrl.toString().isNotEmpty)
                                ? NetworkImage(avatarUrl.toString())
                                : null,
                            child: (avatarUrl == null || avatarUrl.toString().isEmpty)
                                ? Text(
                                    name.isNotEmpty ? name[0].toUpperCase() : 'U',
                                    style: const TextStyle(
                                      color: AppColors.backgroundDark,
                                      fontSize: 36,
                                      fontWeight: FontWeight.w900,
                                    ),
                                  )
                                : null,
                          ),
                          Positioned(
                            bottom: 0,
                            right: 0,
                            child: Container(
                              padding: const EdgeInsets.all(8),
                              decoration: BoxDecoration(
                                color: AppColors.primary,
                                shape: BoxShape.circle,
                                border: Border.all(color: AppColors.surfaceDark, width: 2),
                              ),
                              child: const Icon(Icons.camera_alt_rounded, color: AppColors.backgroundDark, size: 16),
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
                        color: AppColors.primary.withOpacity( 0.15),
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
                  const SizedBox(height: 12),
                  Center(
                    child: OutlinedButton.icon(
                      onPressed: () => _showEditProfileModal(auth),
                      icon: const Icon(Icons.edit_rounded, size: 16, color: AppColors.primary),
                      label: const Text('Edit Profile & Photo'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppColors.primary,
                        side: const BorderSide(color: AppColors.primary, width: 1.2),
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),

                  // Referral Program Card
                  Container(
                    margin: const EdgeInsets.only(bottom: 24),
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [
                          Color(0xFF102B54),
                          Color(0xFF1B3D70),
                          AppColors.surfaceDark,
                        ],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(24),
                      border: Border.all(
                        color: AppColors.primary.withOpacity( 0.3),
                        width: 1.5,
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity( 0.25),
                          blurRadius: 15,
                          offset: const Offset(0, 6),
                        ),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                color: AppColors.primary.withOpacity( 0.2),
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(
                                  color: AppColors.primary.withOpacity( 0.4),
                                ),
                              ),
                              child: const Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Text('🎁 ', style: TextStyle(fontSize: 12)),
                                  Text(
                                    'REFERRAL PROGRAM',
                                    style: TextStyle(
                                      color: AppColors.primary,
                                      fontWeight: FontWeight.w900,
                                      fontSize: 11,
                                      letterSpacing: 0.5,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        const Text(
                          'Your Referral Code',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 18,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                        const SizedBox(height: 4),
                        const Text(
                          'Share your code with friends to unlock special booking perks and ride bonuses when they register.',
                          style: TextStyle(
                            color: Colors.white70,
                            fontSize: 12,
                            height: 1.4,
                          ),
                        ),
                        const SizedBox(height: 16),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                          decoration: BoxDecoration(
                            color: Colors.black.withOpacity( 0.4),
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(
                              color: AppColors.primary.withOpacity( 0.4),
                            ),
                          ),
                          child: Row(
                            children: [
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text(
                                      'Unique Referral Code',
                                      style: TextStyle(
                                        color: Colors.white54,
                                        fontSize: 10,
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                    const SizedBox(height: 2),
                                    Text(
                                      referralCode,
                                      style: const TextStyle(
                                        color: AppColors.primary,
                                        fontSize: 20,
                                        fontWeight: FontWeight.w900,
                                        letterSpacing: 2,
                                        fontFamily: 'monospace',
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              ElevatedButton.icon(
                                onPressed: () {
                                  Clipboard.setData(ClipboardData(text: referralCode));
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(
                                      content: Text('Referral Code $referralCode copied to clipboard!'),
                                      backgroundColor: AppColors.success,
                                      behavior: SnackBarBehavior.floating,
                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                    ),
                                  );
                                },
                                icon: const Icon(Icons.copy_rounded, size: 16),
                                label: const Text('Copy'),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: AppColors.primary,
                                  foregroundColor: AppColors.backgroundDark,
                                  elevation: 0,
                                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  textStyle: const TextStyle(
                                    fontSize: 13,
                                    fontWeight: FontWeight.w800,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                        if (referredBy != null && referredBy.toString().isNotEmpty) ...[
                          const SizedBox(height: 12),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Text(
                                'Referred By:',
                                style: TextStyle(color: Colors.white54, fontSize: 11),
                              ),
                              Text(
                                referredBy.toString(),
                                style: const TextStyle(
                                  color: AppColors.primary,
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                        ],
                        const SizedBox(height: 14),
                        SizedBox(
                          width: double.infinity,
                          child: OutlinedButton.icon(
                            onPressed: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(builder: (_) => const ReferralScreen()),
                              );
                            },
                            icon: const Icon(Icons.share_rounded, size: 15, color: AppColors.primary),
                            label: const Text(
                              'View Referral Rewards & Share Code',
                              style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.bold, fontSize: 12),
                            ),
                            style: OutlinedButton.styleFrom(
                              side: BorderSide(color: AppColors.primary.withOpacity(0.4)),
                              padding: const EdgeInsets.symmetric(vertical: 10),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),

                  // Receipts & History Section
                  const Text(
                    'Booking History & Receipts',
                    style: TextStyle(color: AppColors.textLight, fontSize: 16, fontWeight: FontWeight.w800),
                  ),
                  const SizedBox(height: 12),
                  _buildActionTile(
                    Icons.vpn_key_rounded,
                    '🔑 My Rented Fleet',
                    'View your reserved and booked rental vehicles, pickup dates & vouchers',
                    iconColor: const Color(0xFF3B82F6),
                    onTap: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(builder: (_) => const MyRidesScreen(initialCategoryFilter: 'rental')),
                      );
                    },
                  ),
                  const SizedBox(height: 10),
                  _buildActionTile(
                    Icons.directions_car_filled_rounded,
                    '🚗 My Activity & Bookings',
                    'View all rides, car rentals, private chauffeurs & delivery history',
                    iconColor: AppColors.primary,
                    onTap: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(builder: (_) => const MyRidesScreen()),
                      );
                    },
                  ),
                  const SizedBox(height: 10),
                  _buildActionTile(
                    Icons.receipt_long_rounded,
                    'Receipts & Tax Invoices',
                    'View & download PDF receipts for rides, rentals, chauffeurs & deliveries',
                    onTap: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(builder: (_) => const ReceiptsScreen()),
                      );
                    },
                  ),
                  // Country & Currency Preferences
                  const Text(
                    'Country & Currency Preferences',
                    style: TextStyle(color: AppColors.textLight, fontSize: 16, fontWeight: FontWeight.w800),
                  ),
                  const SizedBox(height: 12),
                  _buildActionTile(
                    Icons.public_rounded,
                    '${countryProv.flag} ${countryProv.selectedCountryName}',
                    'Currency: ${countryProv.currencyCode} (${countryProv.currencySymbol}) · Tap to change',
                    badge: countryProv.selectedCountryCode,
                    iconColor: AppColors.primary,
                    onTap: () {
                      showCountryPickerModal(
                        context: context,
                        selectedCountry: CountriesData.findByCode(countryProv.selectedCountryCode),
                        onSelect: (c) async {
                          final messenger = ScaffoldMessenger.of(context);
                          await countryProv.setCountry(c.code, isManual: true);
                          if (mounted) {
                            messenger.showSnackBar(
                              SnackBar(
                                content: Text('Country set to ${c.name} (${countryProv.currencySymbol})'),
                                backgroundColor: AppColors.success,
                              ),
                            );
                          }
                        },
                      );
                    },
                  ),
                  const SizedBox(height: 24),

                  // Personal Information Section
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Personal Information',
                        style: TextStyle(color: AppColors.textLight, fontSize: 16, fontWeight: FontWeight.w800),
                      ),
                      TextButton.icon(
                        onPressed: () => _showEditProfileModal(auth),
                        icon: const Icon(Icons.edit_rounded, size: 14, color: AppColors.primary),
                        label: const Text(
                          'Edit',
                          style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.bold, fontSize: 13),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
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
                  _buildActionTile(
                    Icons.emergency_rounded,
                    'SOS & Emergency Contacts',
                    'In-app emergency contacts & live safety dispatch',
                    badge: 'SOS',
                    iconColor: AppColors.error,
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const SosContactsScreen()),
                      );
                    },
                  ),
                  const SizedBox(height: 10),
                  _buildActionTile(
                    Icons.language_rounded,
                    'Web SOS Safety Portal',
                    'https://www.ridemycars.com/sos',
                    badge: 'WEB',
                    iconColor: AppColors.primary,
                    onTap: () async {
                      final uri = Uri.parse('https://www.ridemycars.com/sos');
                      if (await canLaunchUrl(uri)) {
                        await launchUrl(uri, mode: LaunchMode.externalApplication);
                      }
                    },
                  ),
                  const SizedBox(height: 10),
                  _buildActionTile(
                    Icons.lock_outline_rounded,
                    'Privacy & Permissions',
                    'Location, notifications & data',
                    onTap: () {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Privacy settings are up to date.')),
                      );
                    },
                  ),
                  const SizedBox(height: 10),
                  _buildActionTile(
                    Icons.delete_forever_rounded,
                    'Delete Account',
                    'Permanently delete your profile and all associated data',
                    isDestructive: true,
                    onTap: _showDeleteAccountDialog,
                  ),
                  const SizedBox(height: 32),
                ],
              ),
            ),
    );
  }

  void _showDeleteAccountDialog() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppColors.surfaceDark,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.warning_amber_rounded, color: AppColors.error, size: 28),
            SizedBox(width: 10),
            Text('Delete Account', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 18)),
          ],
        ),
        content: const Text(
          'Are you sure you want to permanently delete your RideMyCars account? All your personal profile information, booking history, and stored preferences will be completely deleted. This action is irreversible.',
          style: TextStyle(color: AppColors.textMuted, fontSize: 13, height: 1.5),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel', style: TextStyle(color: AppColors.textLight)),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(ctx);
              final auth = Provider.of<AuthProvider>(context, listen: false);
              await auth.logout();
              if (mounted) {
                Navigator.of(context).pushNamedAndRemoveUntil('/login', (route) => false);
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Your account deletion request has been processed.'),
                    backgroundColor: AppColors.error,
                  ),
                );
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.error,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            child: const Text('Permanently Delete', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
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
        border: Border.all(color: Colors.white.withOpacity( 0.06)),
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
        border: Border.all(color: Colors.white.withOpacity( 0.06)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: AppColors.info.withOpacity( 0.15),
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

  Widget _buildActionTile(
    IconData icon,
    String title,
    String subtitle, {
    VoidCallback? onTap,
    bool isDestructive = false,
    String? badge,
    Color? iconColor,
  }) {
    final effectiveColor = isDestructive ? AppColors.error : (iconColor ?? AppColors.primary);

    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: AppColors.surfaceDark,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: isDestructive ? AppColors.error.withOpacity(0.3) : Colors.white.withOpacity(0.06),
          ),
        ),
        child: Row(
          children: [
            Icon(icon, color: effectiveColor, size: 22),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Text(
                        title,
                        style: TextStyle(
                          color: isDestructive ? AppColors.error : AppColors.textLight,
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      if (badge != null) ...[
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: effectiveColor.withOpacity(0.18),
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: effectiveColor.withOpacity(0.4)),
                          ),
                          child: Text(
                            badge,
                            style: TextStyle(
                              color: effectiveColor,
                              fontSize: 9,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                  const SizedBox(height: 2),
                  Text(subtitle, style: const TextStyle(color: AppColors.textMuted, fontSize: 12)),
                ],
              ),
            ),
            Icon(
              Icons.arrow_forward_ios_rounded,
              color: isDestructive ? AppColors.error.withOpacity(0.5) : Colors.white24,
              size: 14,
            ),
          ],
        ),
      ),
    );
  }
}
