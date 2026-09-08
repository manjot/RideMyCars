import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/countries_data.dart';
import '../../core/storage/token_storage.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/country_picker_modal.dart';
import '../rider/rider_home_screen.dart';
import 'register_screen.dart';

enum RiderAuthMethod { phone, emailOtp, password }

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController(text: 'customer@ridemycars.com');
  final _otpEmailController = TextEditingController();
  final _passwordController = TextEditingController(text: '123456');
  final _mobileNumberController = TextEditingController();
  final _phoneOtpController = TextEditingController();
  final _emailOtpCodeController = TextEditingController();
  Country _selectedCountry = CountriesData.defaultCountry;

  RiderAuthMethod _authMethod = RiderAuthMethod.phone;
  bool _phoneOtpSent = false;
  bool _emailOtpSent = false;
  bool _obscurePassword = true;

  int _phoneCountdown = 120;
  Timer? _phoneTimer;

  int _emailCountdown = 300;
  Timer? _emailTimer;

  @override
  void initState() {
    super.initState();
    _loadSavedCredentials();
  }

  Future<void> _loadSavedCredentials() async {
    final savedEmail = await TokenStorage.getUserEmail();
    final savedPass = await TokenStorage.getSavedPassword();
    if (savedEmail != null && savedEmail.isNotEmpty) {
      _emailController.text = savedEmail;
      _otpEmailController.text = savedEmail;
    }
    if (savedPass != null && savedPass.isNotEmpty) {
      _passwordController.text = savedPass;
    }
    if (mounted) setState(() {});
  }

  @override
  void dispose() {
    _phoneTimer?.cancel();
    _emailTimer?.cancel();
    _emailController.dispose();
    _otpEmailController.dispose();
    _passwordController.dispose();
    _mobileNumberController.dispose();
    _phoneOtpController.dispose();
    _emailOtpCodeController.dispose();
    super.dispose();
  }

  void _startPhoneCountdown() {
    _phoneCountdown = 120;
    _phoneTimer?.cancel();
    _phoneTimer = Timer.periodic(const Duration(seconds: 1), (t) {
      if (_phoneCountdown > 0) {
        if (mounted) setState(() => _phoneCountdown--);
      } else {
        _phoneTimer?.cancel();
      }
    });
  }

  void _startEmailCountdown() {
    _emailCountdown = 300;
    _emailTimer?.cancel();
    _emailTimer = Timer.periodic(const Duration(seconds: 1), (t) {
      if (_emailCountdown > 0) {
        if (mounted) setState(() => _emailCountdown--);
      } else {
        _emailTimer?.cancel();
      }
    });
  }

  String _formatTimer(int seconds) {
    final m = seconds ~/ 60;
    final s = seconds % 60;
    return '${m.toString().padLeft(2, '0')}:${s.toString().padLeft(2, '0')}';
  }

  // --- Phone SMS OTP Handlers ---
  Future<void> _handleSendPhoneOtp() async {
    final localNum = _mobileNumberController.text.trim();
    if (localNum.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please enter your mobile phone number'),
          backgroundColor: AppColors.danger,
        ),
      );
      return;
    }
    final phone = '${_selectedCountry.dial} $localNum';

    final auth = Provider.of<AuthProvider>(context, listen: false);
    final res = await auth.sendPhoneOtp(phone: phone, action: 'login');

    if (!mounted) return;

    if (res['not_found'] == true || res['user_exists'] == false) {
      _showRegisterPrompt(
        title: 'Phone Not Registered',
        message: 'No RideMyCars account found for $phone. Would you like to create an account now?',
        onRegister: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => RegisterScreen(initialPhone: phone)),
          );
        },
      );
    } else if (res['success'] == true) {
      setState(() => _phoneOtpSent = true);
      _startPhoneCountdown();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Verification code sent to $phone (valid for 2 min)'),
          backgroundColor: AppColors.success,
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(auth.errorMessage ?? 'Failed to send SMS OTP code.'),
          backgroundColor: AppColors.danger,
        ),
      );
    }
  }

  Future<void> _handleVerifyPhoneOtp() async {
    final phone = '${_selectedCountry.dial} ${_mobileNumberController.text.trim()}';
    final otp = _phoneOtpController.text.trim();
    if (otp.length < 4) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please enter the 4-digit verification code'),
          backgroundColor: AppColors.danger,
        ),
      );
      return;
    }

    final auth = Provider.of<AuthProvider>(context, listen: false);
    final success = await auth.verifyPhoneOtp(phone: phone, otp: otp, role: 'customer');

    if (!mounted) return;

    if (success) {
      _phoneTimer?.cancel();
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const RiderHomeScreen()),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(auth.errorMessage ?? 'Invalid verification code'),
          backgroundColor: AppColors.danger,
        ),
      );
    }
  }

  // --- Email OTP Handlers ---
  Future<void> _handleSendEmailOtp() async {
    final email = _otpEmailController.text.trim();
    if (email.isEmpty || !email.contains('@')) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please enter a valid email address'),
          backgroundColor: AppColors.danger,
        ),
      );
      return;
    }

    final auth = Provider.of<AuthProvider>(context, listen: false);
    final res = await auth.sendEmailOtp(email: email, action: 'login');

    if (!mounted) return;

    if (res['not_found'] == true || res['user_exists'] == false) {
      _showRegisterPrompt(
        title: 'Account Not Found',
        message: 'No RideMyCars account found with $email. Would you like to create an account now?',
        onRegister: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const RegisterScreen()),
          );
        },
      );
    } else if (res['success'] == true) {
      setState(() => _emailOtpSent = true);
      _startEmailCountdown();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Verification code sent to $email (valid for 5 min)'),
          backgroundColor: AppColors.success,
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(auth.errorMessage ?? 'Failed to send email verification code.'),
          backgroundColor: AppColors.danger,
        ),
      );
    }
  }

  Future<void> _handleVerifyEmailOtp() async {
    final email = _otpEmailController.text.trim();
    final otp = _emailOtpCodeController.text.trim();
    if (otp.length < 4) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please enter the 4-digit code sent to your email'),
          backgroundColor: AppColors.danger,
        ),
      );
      return;
    }

    final auth = Provider.of<AuthProvider>(context, listen: false);
    final success = await auth.verifyEmailOtp(email: email, otp: otp, role: 'customer');

    if (!mounted) return;

    if (success) {
      _emailTimer?.cancel();
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const RiderHomeScreen()),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(auth.errorMessage ?? 'Invalid email verification code'),
          backgroundColor: AppColors.danger,
        ),
      );
    }
  }

  // --- Password Login Handler ---
  Future<void> _handleLogin() async {
    if (!_formKey.currentState!.validate()) return;

    final auth = Provider.of<AuthProvider>(context, listen: false);
    final success = await auth.login(
      _emailController.text,
      _passwordController.text,
    );

    if (!mounted) return;

    if (success) {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const RiderHomeScreen()),
      );
    } else if (auth.errorMessage != null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(auth.errorMessage!),
          backgroundColor: AppColors.danger,
        ),
      );
    }
  }

  // --- Social Logins (Google & Apple) ---
  Future<void> _handleGoogleLogin() async {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    
    final emailInput = TextEditingController();
    final nameInput = TextEditingController();

    final proceed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppColors.surfaceDark,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(10)),
              child: Image.network('https://www.google.com/favicon.ico', width: 20, height: 20, errorBuilder: (_, __, ___) => const Icon(Icons.g_mobiledata, color: Colors.blue)),
            ),
            const SizedBox(width: 12),
            const Text('Google Sign-In', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 18)),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Text('Connect your Google Account to sign in to RideMyCars instantly:', style: TextStyle(color: AppColors.textMuted, fontSize: 13)),
            const SizedBox(height: 16),
            TextField(
              controller: emailInput,
              keyboardType: TextInputType.emailAddress,
              style: const TextStyle(color: AppColors.textLight),
              decoration: InputDecoration(
                labelText: 'Google Email Address',
                labelStyle: const TextStyle(color: AppColors.textMuted),
                filled: true,
                fillColor: AppColors.backgroundDark,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: nameInput,
              style: const TextStyle(color: AppColors.textLight),
              decoration: InputDecoration(
                labelText: 'Full Name (optional)',
                labelStyle: const TextStyle(color: AppColors.textMuted),
                filled: true,
                fillColor: AppColors.backgroundDark,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Cancel', style: TextStyle(color: AppColors.textMuted)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primary,
              foregroundColor: AppColors.backgroundDark,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Continue with Google', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );

    if (proceed != true || !mounted) return;

    final email = emailInput.text.trim();
    if (email.isEmpty || !email.contains('@')) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter a valid Google email address'), backgroundColor: AppColors.danger),
      );
      return;
    }

    final success = await auth.loginWithGoogle(
      email: email,
      name: nameInput.text.trim().isNotEmpty ? nameInput.text.trim() : 'Google Rider',
      googleId: 'g_${email.hashCode.abs()}',
      role: 'customer',
    );

    if (!mounted) return;

    if (success) {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const RiderHomeScreen()),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(auth.errorMessage ?? 'Google authentication failed.'), backgroundColor: AppColors.danger),
      );
    }
  }

  Future<void> _handleAppleLogin() async {
    final auth = Provider.of<AuthProvider>(context, listen: false);

    final emailInput = TextEditingController();
    final nameInput = TextEditingController();

    final proceed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppColors.surfaceDark,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.apple, color: Colors.white, size: 28),
            SizedBox(width: 12),
            Text('Sign in with Apple', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 18)),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Text('Sign in with your Apple ID to access RideMyCars:', style: TextStyle(color: AppColors.textMuted, fontSize: 13)),
            const SizedBox(height: 16),
            TextField(
              controller: emailInput,
              keyboardType: TextInputType.emailAddress,
              style: const TextStyle(color: AppColors.textLight),
              decoration: InputDecoration(
                labelText: 'Apple ID Email',
                labelStyle: const TextStyle(color: AppColors.textMuted),
                filled: true,
                fillColor: AppColors.backgroundDark,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: nameInput,
              style: const TextStyle(color: AppColors.textLight),
              decoration: InputDecoration(
                labelText: 'Full Name (optional)',
                labelStyle: const TextStyle(color: AppColors.textMuted),
                filled: true,
                fillColor: AppColors.backgroundDark,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Cancel', style: TextStyle(color: AppColors.textMuted)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.white,
              foregroundColor: Colors.black,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Sign in with Apple', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );

    if (proceed != true || !mounted) return;

    final email = emailInput.text.trim();
    if (email.isEmpty || !email.contains('@')) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter a valid Apple ID email'), backgroundColor: AppColors.danger),
      );
      return;
    }

    final success = await auth.loginWithApple(
      email: email,
      appleId: 'apple_${email.hashCode.abs()}',
      name: nameInput.text.trim().isNotEmpty ? nameInput.text.trim() : 'Apple Rider',
      role: 'customer',
    );

    if (!mounted) return;

    if (success) {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const RiderHomeScreen()),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(auth.errorMessage ?? 'Apple authentication failed.'), backgroundColor: AppColors.danger),
      );
    }
  }

  void _showRegisterPrompt({required String title, required String message, required VoidCallback onRegister}) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppColors.surfaceDark,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text(title, style: const TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold)),
        content: Text(message, style: const TextStyle(color: AppColors.textMuted)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel', style: TextStyle(color: AppColors.textMuted)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primary,
              foregroundColor: AppColors.backgroundDark,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            onPressed: () {
              Navigator.pop(ctx);
              onRegister();
            },
            child: const Text('Create Account', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);

    return Scaffold(
      backgroundColor: AppColors.backgroundDark,
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 16.0),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Official Website Logo
                  Center(
                    child: Container(
                      width: 140,
                      height: 90,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(20),
                        boxShadow: [
                          BoxShadow(
                            color: AppColors.primary.withOpacity(0.25),
                            blurRadius: 20,
                            offset: const Offset(0, 6),
                          ),
                        ],
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(20),
                        child: Image.asset(
                          'assets/logo.png',
                          fit: BoxFit.contain,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                  const Text(
                    'RideMyCars',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: AppColors.textLight,
                      fontSize: 24,
                      fontWeight: FontWeight.w900,
                      letterSpacing: -0.5,
                    ),
                  ),
                  const SizedBox(height: 4),
                  const Text(
                    'Executive Mobility & Logistics',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: AppColors.textMuted,
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 20),

                  // 3-Option Authentication Tabs
                  Container(
                    margin: const EdgeInsets.only(bottom: 20),
                    padding: const EdgeInsets.all(4),
                    decoration: BoxDecoration(
                      color: AppColors.surfaceDark,
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: Row(
                      children: [
                        _buildTabButton(
                          title: '📱 Phone OTP',
                          isSelected: _authMethod == RiderAuthMethod.phone,
                          onTap: () => setState(() => _authMethod = RiderAuthMethod.phone),
                        ),
                        _buildTabButton(
                          title: '✉️ Email OTP',
                          isSelected: _authMethod == RiderAuthMethod.emailOtp,
                          onTap: () => setState(() => _authMethod = RiderAuthMethod.emailOtp),
                        ),
                        _buildTabButton(
                          title: '🔑 Password',
                          isSelected: _authMethod == RiderAuthMethod.password,
                          onTap: () => setState(() => _authMethod = RiderAuthMethod.password),
                        ),
                      ],
                    ),
                  ),

                  // --- 1. Phone SMS OTP Section ---
                  if (_authMethod == RiderAuthMethod.phone) ...[
                    Container(
                      decoration: BoxDecoration(
                        color: AppColors.surfaceDark,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(
                          color: _phoneOtpSent ? Colors.white12 : AppColors.primary.withOpacity(0.3),
                          width: 1.5,
                        ),
                      ),
                      child: Row(
                        children: [
                          InkWell(
                            onTap: _phoneOtpSent
                                ? null
                                : () {
                                    showCountryPickerModal(
                                      context: context,
                                      selectedCountry: _selectedCountry,
                                      onSelect: (c) => setState(() => _selectedCountry = c),
                                    );
                                  },
                            borderRadius: const BorderRadius.horizontal(left: Radius.circular(16)),
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 15),
                              decoration: BoxDecoration(
                                color: Colors.white.withOpacity(0.03),
                                border: Border(right: BorderSide(color: Colors.white.withOpacity(0.1), width: 1)),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Text(_selectedCountry.flag, style: const TextStyle(fontSize: 22)),
                                  const SizedBox(width: 8),
                                  Text(
                                    _selectedCountry.dial,
                                    style: const TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 15),
                                  ),
                                  const SizedBox(width: 4),
                                  const Icon(Icons.arrow_drop_down_rounded, color: AppColors.textMuted, size: 20),
                                ],
                              ),
                            ),
                          ),
                          Expanded(
                            child: TextFormField(
                              controller: _mobileNumberController,
                              keyboardType: TextInputType.phone,
                              enabled: !_phoneOtpSent,
                              style: const TextStyle(color: AppColors.textLight, fontSize: 16),
                              decoration: const InputDecoration(
                                hintText: 'Mobile number',
                                hintStyle: TextStyle(color: AppColors.textMuted, fontSize: 15),
                                contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 15),
                                border: InputBorder.none,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    if (!_phoneOtpSent) ...[
                      SizedBox(
                        height: 52,
                        child: ElevatedButton(
                          onPressed: auth.isLoading ? null : _handleSendPhoneOtp,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.primary,
                            foregroundColor: AppColors.backgroundDark,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                            elevation: 4,
                          ),
                          child: auth.isLoading
                              ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.5, color: AppColors.backgroundDark))
                              : const Text('Send SMS OTP Code', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900)),
                        ),
                      ),
                    ] else ...[
                      _buildOtpEntryCard(
                        title: 'Enter SMS 4-Digit Code',
                        countdown: _phoneCountdown,
                        formattedTimer: _formatTimer(_phoneCountdown),
                        controller: _phoneOtpController,
                        onReset: () => setState(() {
                          _phoneOtpSent = false;
                          _phoneOtpController.clear();
                        }),
                        onResend: _phoneCountdown == 0 ? _handleSendPhoneOtp : null,
                        onVerify: auth.isLoading ? null : _handleVerifyPhoneOtp,
                        isLoading: auth.isLoading,
                      ),
                    ],
                  ],

                  // --- 2. Email OTP Section ---
                  if (_authMethod == RiderAuthMethod.emailOtp) ...[
                    TextFormField(
                      controller: _otpEmailController,
                      keyboardType: TextInputType.emailAddress,
                      enabled: !_emailOtpSent,
                      style: const TextStyle(color: AppColors.textLight),
                      decoration: InputDecoration(
                        labelText: 'Email Address',
                        labelStyle: const TextStyle(color: AppColors.textMuted),
                        prefixIcon: const Icon(Icons.email_outlined, color: AppColors.textMuted),
                        filled: true,
                        fillColor: AppColors.surfaceDark,
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: const BorderSide(color: AppColors.primary, width: 1.5),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),

                    if (!_emailOtpSent) ...[
                      SizedBox(
                        height: 52,
                        child: ElevatedButton(
                          onPressed: auth.isLoading ? null : _handleSendEmailOtp,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.primary,
                            foregroundColor: AppColors.backgroundDark,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                            elevation: 4,
                          ),
                          child: auth.isLoading
                              ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.5, color: AppColors.backgroundDark))
                              : const Text('Send Email OTP Code', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900)),
                        ),
                      ),
                    ] else ...[
                      _buildOtpEntryCard(
                        title: 'Enter Email 4-Digit Code',
                        countdown: _emailCountdown,
                        formattedTimer: _formatTimer(_emailCountdown),
                        controller: _emailOtpCodeController,
                        onReset: () => setState(() {
                          _emailOtpSent = false;
                          _emailOtpCodeController.clear();
                        }),
                        onResend: _emailCountdown == 0 ? _handleSendEmailOtp : null,
                        onVerify: auth.isLoading ? null : _handleVerifyEmailOtp,
                        isLoading: auth.isLoading,
                      ),
                    ],
                  ],

                  // --- 3. Password Section ---
                  if (_authMethod == RiderAuthMethod.password) ...[
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
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
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
                    const SizedBox(height: 14),

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
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: const BorderSide(color: AppColors.primary, width: 1.5),
                        ),
                      ),
                      validator: (val) => (val == null || val.isEmpty) ? 'Please enter your password' : null,
                    ),
                    const SizedBox(height: 20),

                    SizedBox(
                      height: 52,
                      child: ElevatedButton(
                        onPressed: auth.isLoading ? null : _handleLogin,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.primary,
                          foregroundColor: AppColors.backgroundDark,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                          elevation: 4,
                        ),
                        child: auth.isLoading
                            ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.5, color: AppColors.backgroundDark))
                            : const Text('Sign In', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900)),
                      ),
                    ),
                  ],

                  const SizedBox(height: 24),

                  // Social Sign-In Divider
                  Row(
                    children: [
                      Expanded(child: Container(height: 1, color: Colors.white12)),
                      const Padding(
                        padding: EdgeInsets.symmetric(horizontal: 14),
                        child: Text(
                          'OR CONTINUE WITH',
                          style: TextStyle(color: AppColors.textMuted, fontSize: 11, fontWeight: FontWeight.bold, letterSpacing: 1),
                        ),
                      ),
                      Expanded(child: Container(height: 1, color: Colors.white12)),
                    ],
                  ),
                  const SizedBox(height: 18),

                  // Google & Apple Buttons
                  Row(
                    children: [
                      // Google Button
                      Expanded(
                        child: OutlinedButton(
                          onPressed: auth.isLoading ? null : _handleGoogleLogin,
                          style: OutlinedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            side: const BorderSide(color: Colors.white12),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                            backgroundColor: AppColors.surfaceDark,
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Container(
                                width: 22,
                                height: 22,
                                decoration: const BoxDecoration(color: Colors.white, shape: BoxShape.circle),
                                child: const Center(
                                  child: Text('G', style: TextStyle(color: Color(0xFF4285F4), fontWeight: FontWeight.bold, fontSize: 14)),
                                ),
                              ),
                              const SizedBox(width: 10),
                              const Text(
                                'Google',
                                style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 14),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),

                      // Apple Button
                      Expanded(
                        child: OutlinedButton(
                          onPressed: auth.isLoading ? null : _handleAppleLogin,
                          style: OutlinedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            side: const BorderSide(color: Colors.white12),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                            backgroundColor: AppColors.surfaceDark,
                          ),
                          child: const Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.apple, color: Colors.white, size: 22),
                              SizedBox(width: 10),
                              Text(
                                'Apple',
                                style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 14),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 24),

                  // Customer Register Link
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Text("Don't have an account? ", style: TextStyle(color: AppColors.textMuted, fontSize: 13)),
                      GestureDetector(
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(builder: (_) => const RegisterScreen()),
                          );
                        },
                        child: const Text(
                          'Register now',
                          style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.bold, fontSize: 13),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildTabButton({required String title, required bool isSelected, required VoidCallback onTap}) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 9),
          decoration: BoxDecoration(
            color: isSelected ? AppColors.primary : Colors.transparent,
            borderRadius: BorderRadius.circular(10),
          ),
          child: Text(
            title,
            textAlign: TextAlign.center,
            style: TextStyle(
              color: isSelected ? AppColors.backgroundDark : AppColors.textMuted,
              fontWeight: FontWeight.bold,
              fontSize: 12,
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildOtpEntryCard({
    required String title,
    required int countdown,
    required String formattedTimer,
    required TextEditingController controller,
    required VoidCallback onReset,
    required VoidCallback? onResend,
    required VoidCallback? onVerify,
    required bool isLoading,
  }) {
    return Column(
      children: [
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: AppColors.surfaceDark,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppColors.primary.withOpacity(0.3)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(title, style: const TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 13)),
                  Text(
                    countdown > 0 ? formattedTimer : 'Expired',
                    style: TextStyle(
                      color: countdown > 0 ? AppColors.primary : AppColors.danger,
                      fontWeight: FontWeight.bold,
                      fontSize: 12,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: controller,
                keyboardType: TextInputType.number,
                maxLength: 4,
                textAlign: TextAlign.center,
                style: const TextStyle(color: AppColors.textLight, fontSize: 24, letterSpacing: 12, fontWeight: FontWeight.bold),
                decoration: InputDecoration(
                  counterText: '',
                  hintText: '••••',
                  hintStyle: const TextStyle(color: AppColors.textMuted, fontSize: 24, letterSpacing: 12),
                  filled: true,
                  fillColor: AppColors.backgroundDark,
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                ),
              ),
              const SizedBox(height: 10),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  TextButton(
                    onPressed: onReset,
                    child: const Text('Change', style: TextStyle(color: AppColors.textMuted, fontSize: 12)),
                  ),
                  TextButton(
                    onPressed: onResend,
                    child: Text(
                      countdown == 0 ? 'Resend Code' : 'Resend in $formattedTimer',
                      style: TextStyle(
                        color: countdown == 0 ? AppColors.primary : AppColors.textMuted,
                        fontWeight: FontWeight.bold,
                        fontSize: 12,
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 14),
        SizedBox(
          height: 52,
          width: double.infinity,
          child: ElevatedButton(
            onPressed: onVerify,
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primary,
              foregroundColor: AppColors.backgroundDark,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              elevation: 4,
            ),
            child: isLoading
                ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.5, color: AppColors.backgroundDark))
                : const Text('Verify & Open App', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900)),
          ),
        ),
      ],
    );
  }
}
