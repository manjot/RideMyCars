import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/countries_data.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/country_picker_modal.dart';
import '../dashboard/driver_dashboard_screen.dart';

class DriverRegisterScreen extends StatefulWidget {
  final String? initialPhone;
  const DriverRegisterScreen({super.key, this.initialPhone});

  @override
  State<DriverRegisterScreen> createState() => _DriverRegisterScreenState();
}

class _DriverRegisterScreenState extends State<DriverRegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _photoController = TextEditingController();
  late final TextEditingController _mobileNumberController;
  late Country _selectedCountry;
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  bool _obscurePassword = true;

  @override
  void initState() {
    super.initState();
    final initial = widget.initialPhone?.trim() ?? '';
    if (initial.isNotEmpty) {
      _selectedCountry = CountriesData.findByPhone(initial);
      final raw = initial.startsWith(_selectedCountry.dial)
          ? initial.substring(_selectedCountry.dial.length).trim()
          : initial;
      _mobileNumberController = TextEditingController(text: raw);
    } else {
      _selectedCountry = CountriesData.defaultCountry;
      _mobileNumberController = TextEditingController();
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _photoController.dispose();
    _mobileNumberController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _handleRegister() async {
    if (!_formKey.currentState!.validate()) return;

    final localNum = _mobileNumberController.text.trim();
    if (localNum.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please enter your mobile phone number for OTP verification'),
          backgroundColor: AppColors.danger,
        ),
      );
      return;
    }

    final phone = '${_selectedCountry.dial} $localNum';

    final auth = Provider.of<AuthProvider>(context, listen: false);

    // 1. Send SMS OTP for driver registration
    final res = await auth.sendPhoneOtp(phone: phone, action: 'register');

    if (!mounted) return;

    if (res['success'] == true) {
      _showOtpVerificationDialog(phone);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(auth.errorMessage ?? 'Failed to send SMS verification code.'),
          backgroundColor: AppColors.danger,
        ),
      );
    }
  }

  void _showOtpVerificationDialog(String phone) {
    final otpController = TextEditingController();
    int countdown = 120;
    Timer? timer;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.surfaceDark,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
      ),
      builder: (sheetContext) {
        return StatefulBuilder(
          builder: (context, setSheetState) {
            timer ??= Timer.periodic(const Duration(seconds: 1), (t) {
              if (countdown > 0) {
                setSheetState(() => countdown--);
              } else {
                t.cancel();
              }
            });

            final m = countdown ~/ 60;
            final s = countdown % 60;
            final timerStr = '${m.toString().padLeft(2, '0')}:${s.toString().padLeft(2, '0')}';

            return Padding(
              padding: EdgeInsets.only(
                left: 24,
                right: 24,
                top: 24,
                bottom: MediaQuery.of(sheetContext).viewInsets.bottom + 24,
              ),
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
                  const SizedBox(height: 20),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Verify Driver Phone',
                        style: TextStyle(
                          color: AppColors.textLight,
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: AppColors.primary.withOpacity(0.15),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: AppColors.primary.withOpacity(0.3)),
                        ),
                        child: Text(
                          countdown > 0 ? timerStr : 'Expired',
                          style: TextStyle(
                            color: countdown > 0 ? AppColors.primary : AppColors.danger,
                            fontWeight: FontWeight.bold,
                            fontSize: 12,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'We sent a 4-digit verification code to $phone. Valid for 2 minutes.',
                    style: const TextStyle(color: AppColors.textMuted, fontSize: 13),
                  ),
                  const SizedBox(height: 20),

                  TextFormField(
                    controller: otpController,
                    keyboardType: TextInputType.number,
                    maxLength: 4,
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                      color: AppColors.textLight,
                      fontSize: 26,
                      letterSpacing: 14,
                      fontWeight: FontWeight.bold,
                    ),
                    decoration: InputDecoration(
                      counterText: '',
                      hintText: '••••',
                      hintStyle: const TextStyle(color: AppColors.textMuted, fontSize: 26, letterSpacing: 14),
                      filled: true,
                      fillColor: AppColors.backgroundDark,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(16),
                        borderSide: BorderSide.none,
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(16),
                        borderSide: const BorderSide(color: AppColors.primary, width: 1.5),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),

                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      TextButton(
                        onPressed: () {
                          timer?.cancel();
                          Navigator.pop(sheetContext);
                        },
                        child: const Text('Cancel', style: TextStyle(color: AppColors.textMuted)),
                      ),
                      TextButton(
                        onPressed: countdown == 0
                            ? () async {
                                final auth = Provider.of<AuthProvider>(context, listen: false);
                                final res = await auth.sendPhoneOtp(phone: phone, action: 'register');
                                if (res['success'] == true) {
                                  setSheetState(() {
                                    countdown = 120;
                                  });
                                }
                              }
                            : null,
                        child: Text(
                          countdown == 0 ? 'Resend SMS OTP' : 'Resend in $timerStr',
                          style: TextStyle(
                            color: countdown == 0 ? AppColors.primary : AppColors.textMuted,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  Consumer<AuthProvider>(
                    builder: (context, auth, _) {
                      return SizedBox(
                        height: 52,
                        child: ElevatedButton(
                          onPressed: auth.isLoading
                              ? null
                              : () async {
                                  final code = otpController.text.trim();
                                  if (code.length < 4) {
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      const SnackBar(
                                        content: Text('Please enter 4 digits'),
                                        backgroundColor: AppColors.danger,
                                      ),
                                    );
                                    return;
                                  }

                                  final success = await auth.verifyPhoneOtp(
                                    phone: phone,
                                    otp: code,
                                    name: _nameController.text.trim(),
                                    email: _emailController.text.trim(),
                                    password: _passwordController.text,
                                    role: 'driver',
                                    driverDetails: _photoController.text.trim().isNotEmpty
                                        ? {
                                            'driver_photo': _photoController.text.trim(),
                                            'photo_url': _photoController.text.trim(),
                                          }
                                        : null,
                                  );

                                  if (!mounted) return;

                                  if (success) {
                                    timer?.cancel();
                                    Navigator.pop(sheetContext);
                                    Navigator.pushAndRemoveUntil(
                                      context,
                                      MaterialPageRoute(builder: (_) => const DriverDashboardScreen()),
                                      (route) => false,
                                    );
                                  } else {
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      SnackBar(
                                        content: Text(auth.errorMessage ?? 'Invalid OTP code.'),
                                        backgroundColor: AppColors.danger,
                                      ),
                                    );
                                  }
                                },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.primary,
                            foregroundColor: AppColors.backgroundDark,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                            elevation: 4,
                          ),
                          child: auth.isLoading
                              ? const SizedBox(
                                  width: 22,
                                  height: 22,
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2.5,
                                    color: AppColors.backgroundDark,
                                  ),
                                )
                              : const Text(
                                  'Verify & Complete Registration',
                                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                                ),
                        ),
                      );
                    },
                  ),
                ],
              ),
            );
          },
        );
      },
    ).whenComplete(() {
      timer?.cancel();
    });
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);

    return Scaffold(
      backgroundColor: AppColors.backgroundDark,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: AppColors.textLight),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 28.0, vertical: 10.0),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Center(
                    child: Container(
                      width: 130,
                      height: 75,
                      margin: const EdgeInsets.only(bottom: 16),
                      child: Image.asset('assets/logo.png', fit: BoxFit.contain),
                    ),
                  ),
                  const Text(
                    'Driver Partner Registration',
                    style: TextStyle(
                      color: AppColors.textLight,
                      fontSize: 26,
                      fontWeight: FontWeight.w900,
                      letterSpacing: -0.5,
                    ),
                  ),
                  const SizedBox(height: 6),
                  const Text(
                    'Earn money on your schedule with RideMyCars',
                    style: TextStyle(
                      color: AppColors.textMuted,
                      fontSize: 14,
                    ),
                  ),
                  const SizedBox(height: 28),

                  // Full Name
                  TextFormField(
                    controller: _nameController,
                    style: const TextStyle(color: AppColors.textLight),
                    decoration: InputDecoration(
                      labelText: 'Full Name',
                      labelStyle: const TextStyle(color: AppColors.textMuted),
                      prefixIcon: const Icon(Icons.person_outline_rounded, color: AppColors.textMuted),
                      filled: true,
                      fillColor: AppColors.surfaceDark,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(16),
                        borderSide: BorderSide.none,
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(16),
                        borderSide: const BorderSide(color: AppColors.primary, width: 1.5),
                      ),
                    ),
                    validator: (val) => (val == null || val.isEmpty) ? 'Please enter your name' : null,
                  ),
                  const SizedBox(height: 16),

                  // Mobile Phone Field (With Country Flag Dropdown & Verified Badge)
                  Container(
                    decoration: BoxDecoration(
                      color: AppColors.surfaceDark,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: AppColors.primary.withOpacity(0.3), width: 1),
                    ),
                    child: Row(
                      children: [
                        // Country Dropdown Trigger
                        InkWell(
                          onTap: () {
                            showCountryPickerModal(
                              context: context,
                              selectedCountry: _selectedCountry,
                              onSelect: (c) {
                                setState(() => _selectedCountry = c);
                              },
                            );
                          },
                          borderRadius: const BorderRadius.horizontal(left: Radius.circular(16)),
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 15),
                            decoration: BoxDecoration(
                              color: Colors.white.withOpacity(0.03),
                              border: Border(
                                right: BorderSide(
                                  color: Colors.white.withOpacity(0.1),
                                  width: 1,
                                ),
                              ),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text(_selectedCountry.flag, style: const TextStyle(fontSize: 22)),
                                const SizedBox(width: 8),
                                Text(
                                  _selectedCountry.dial,
                                  style: const TextStyle(
                                    color: AppColors.textLight,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 15,
                                  ),
                                ),
                                const SizedBox(width: 4),
                                const Icon(Icons.arrow_drop_down_rounded, color: AppColors.textMuted, size: 20),
                              ],
                            ),
                          ),
                        ),

                        // Mobile Number Input Field
                        Expanded(
                          child: TextFormField(
                            controller: _mobileNumberController,
                            keyboardType: TextInputType.phone,
                            style: const TextStyle(color: AppColors.textLight, fontSize: 16),
                            decoration: const InputDecoration(
                              hintText: 'Mobile number *',
                              hintStyle: TextStyle(color: AppColors.textMuted, fontSize: 15),
                              contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 15),
                              border: InputBorder.none,
                            ),
                          ),
                        ),

                        const Padding(
                          padding: EdgeInsets.only(right: 14.0),
                          child: Tooltip(
                            message: 'Verified via Twilio SMS OTP',
                            child: Icon(Icons.verified_outlined, color: AppColors.primary, size: 20),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Email
                  TextFormField(
                    controller: _emailController,
                    keyboardType: TextInputType.emailAddress,
                    style: const TextStyle(color: AppColors.textLight),
                    decoration: InputDecoration(
                      labelText: 'Email Address',
                      labelStyle: const TextStyle(color: AppColors.textMuted),
                      prefixIcon: const Icon(Icons.email_outlined, color: AppColors.textMuted),
                      filled: true,
                      fillColor: AppColors.surfaceDark,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(16),
                        borderSide: BorderSide.none,
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(16),
                        borderSide: const BorderSide(color: AppColors.primary, width: 1.5),
                      ),
                    ),
                    validator: (val) {
                      if (val == null || val.isEmpty) return 'Please enter your email';
                      if (!val.contains('@')) return 'Please enter a valid email';
                      return null;
                    },
                  ),
                  const SizedBox(height: 16),

                  // Password
                  TextFormField(
                    controller: _passwordController,
                    obscureText: _obscurePassword,
                    style: const TextStyle(color: AppColors.textLight),
                    decoration: InputDecoration(
                      labelText: 'Password',
                      labelStyle: const TextStyle(color: AppColors.textMuted),
                      prefixIcon: const Icon(Icons.lock_outline_rounded, color: AppColors.textMuted),
                      suffixIcon: IconButton(
                        icon: Icon(
                          _obscurePassword ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                          color: AppColors.textMuted,
                        ),
                        onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                      ),
                      filled: true,
                      fillColor: AppColors.surfaceDark,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(16),
                        borderSide: BorderSide.none,
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(16),
                        borderSide: const BorderSide(color: AppColors.primary, width: 1.5),
                      ),
                    ),
                    validator: (val) => (val == null || val.length < 6) ? 'Password must be at least 6 characters' : null,
                  ),
                  const SizedBox(height: 16),

                  // Confirm Password
                  TextFormField(
                    controller: _confirmPasswordController,
                    obscureText: _obscurePassword,
                    style: const TextStyle(color: AppColors.textLight),
                    decoration: InputDecoration(
                      labelText: 'Confirm Password',
                      labelStyle: const TextStyle(color: AppColors.textMuted),
                      prefixIcon: const Icon(Icons.lock_clock_outlined, color: AppColors.textMuted),
                      filled: true,
                      fillColor: AppColors.surfaceDark,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(16),
                        borderSide: BorderSide.none,
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(16),
                        borderSide: const BorderSide(color: AppColors.primary, width: 1.5),
                      ),
                    ),
                    validator: (val) => (val != _passwordController.text) ? 'Passwords do not match' : null,
                  ),
                  const SizedBox(height: 16),

                  // Driver Profile Photo Section
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: AppColors.surfaceDark,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: Colors.white10),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Container(
                              width: 48,
                              height: 48,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                color: AppColors.backgroundDark,
                                border: Border.all(color: AppColors.primary, width: 1.5),
                              ),
                              child: ClipOval(
                                child: _photoController.text.isNotEmpty
                                    ? Image.network(
                                        _photoController.text,
                                        fit: BoxFit.cover,
                                        errorBuilder: (_, __, ___) => const Icon(Icons.person, color: AppColors.primary),
                                      )
                                    : const Icon(Icons.camera_alt_rounded, color: AppColors.primary, size: 22),
                              ),
                            ),
                            const SizedBox(width: 14),
                            const Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Profile Picture (Recommended)',
                                    style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 14),
                                  ),
                                  Text(
                                    'Formal suit or shirt photo for verification',
                                    style: TextStyle(color: AppColors.textMuted, fontSize: 11),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        TextFormField(
                          controller: _photoController,
                          onChanged: (_) => setState(() {}),
                          style: const TextStyle(color: AppColors.textLight, fontSize: 13),
                          decoration: InputDecoration(
                            hintText: 'Photo URL or select a preset below',
                            hintStyle: const TextStyle(color: AppColors.textMuted, fontSize: 12),
                            filled: true,
                            fillColor: AppColors.backgroundDark,
                            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                          ),
                        ),
                        const SizedBox(height: 8),
                        const Text('Quick Select Preset Avatar:', style: TextStyle(color: AppColors.textMuted, fontSize: 11)),
                        const SizedBox(height: 6),
                        Row(
                          children: [
                            'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400',
                            'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400',
                            'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400',
                            'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400',
                          ].map((url) => Padding(
                            padding: const EdgeInsets.only(right: 8.0),
                            child: GestureDetector(
                              onTap: () => setState(() => _photoController.text = url),
                              child: Container(
                                decoration: BoxDecoration(
                                  border: Border.all(
                                    color: _photoController.text == url ? AppColors.primary : Colors.transparent,
                                    width: 2,
                                  ),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: ClipRRect(
                                  borderRadius: BorderRadius.circular(8),
                                  child: Image.network(url, width: 38, height: 38, fit: BoxFit.cover),
                                ),
                              ),
                            ),
                          )).toList(),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 28),

                  // Submit
                  SizedBox(
                    height: 54,
                    child: ElevatedButton(
                      onPressed: auth.isLoading ? null : _handleRegister,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        foregroundColor: AppColors.backgroundDark,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                        ),
                        elevation: 4,
                      ),
                      child: auth.isLoading
                          ? const SizedBox(
                              width: 22,
                              height: 22,
                              child: CircularProgressIndicator(
                                strokeWidth: 2.5,
                                color: AppColors.backgroundDark,
                              ),
                            )
                          : const Text(
                              'Verify Phone & Complete Registration',
                              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
                            ),
                    ),
                  ),
                  const SizedBox(height: 20),

                  const Text(
                    'By signing up as a driver, you agree to the RideMyCars Driver Terms & Conditions and Safety Guidelines.',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: AppColors.textMuted,
                      fontSize: 12,
                      height: 1.4,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
