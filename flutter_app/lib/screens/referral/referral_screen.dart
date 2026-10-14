import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/api/api_client.dart';
import '../../core/constants/api_constants.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/auth_provider.dart';

class ReferralScreen extends StatefulWidget {
  const ReferralScreen({super.key});

  @override
  State<ReferralScreen> createState() => _ReferralScreenState();
}

class _ReferralScreenState extends State<ReferralScreen> {
  final _redeemCodeController = TextEditingController();
  bool _isRedeeming = false;
  String? _redeemMessage;
  bool _redeemSuccess = false;
  String? _userReferralCode;
  String? _referredByCode;

  @override
  void initState() {
    super.initState();
    _fetchReferralStatus();
  }

  @override
  void dispose() {
    _redeemCodeController.dispose();
    super.dispose();
  }

  Future<void> _fetchReferralStatus() async {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    setState(() {
      _userReferralCode = auth.referralCode;
    });

    try {
      final dio = ApiClient().dio;
      final res = await dio.get(ApiConstants.me);
      if (res.statusCode == 200 && res.data['success'] == true) {
        final u = res.data['user'];
        if (mounted) {
          setState(() {
            _userReferralCode = u['referral_code']?.toString() ?? _userReferralCode;
            _referredByCode = u['referred_by']?.toString();
          });
        }
      }
    } catch (_) {
      // Fallback to auth provider code
    }
  }

  Future<void> _applyReferralCode() async {
    final code = _redeemCodeController.text.trim().toUpperCase();
    if (code.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please enter a referral code'),
          backgroundColor: AppColors.danger,
        ),
      );
      return;
    }

    setState(() {
      _isRedeeming = true;
      _redeemMessage = null;
    });

    try {
      final dio = ApiClient().dio;
      // Post referral code redemption
      final res = await dio.post('/api/user/apply-referral', data: {
        'referral_code': code,
      });

      if (mounted) {
        if (res.statusCode == 200 && (res.data['success'] == true || res.data['status'] == 'success')) {
          setState(() {
            _redeemSuccess = true;
            _redeemMessage = res.data['message']?.toString() ?? 'Referral code applied successfully!';
            _referredByCode = code;
          });
          _redeemCodeController.clear();
        } else {
          setState(() {
            _redeemSuccess = false;
            _redeemMessage = res.data['message']?.toString() ?? 'Invalid referral code. Please check and try again.';
          });
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _redeemSuccess = false;
          _redeemMessage = 'Invalid referral code or it could not be applied.';
        });
      }
    } finally {
      if (mounted) {
        setState(() => _isRedeeming = false);
      }
    }
  }

  void _copyToClipboard(String code) {
    Clipboard.setData(ClipboardData(text: code));
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.check_circle_rounded, color: AppColors.backgroundDark, size: 20),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                'Referral code "$code" copied to clipboard!',
                style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.backgroundDark),
              ),
            ),
          ],
        ),
        backgroundColor: AppColors.primary,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        duration: const Duration(seconds: 3),
      ),
    );
  }

  String _getShareMessage(String code) {
    return 'Join RideMyCars using my referral code $code to get exclusive discounts on executive rides and premium car rentals! Download the app: https://www.ridemycars.com';
  }

  Future<void> _shareViaWhatsApp(String code) async {
    final msg = Uri.encodeComponent(_getShareMessage(code));
    final url = Uri.parse('whatsapp://send?text=$msg');
    try {
      if (await canLaunchUrl(url)) {
        await launchUrl(url);
      } else {
        _copyToClipboard(code);
      }
    } catch (_) {
      _copyToClipboard(code);
    }
  }

  Future<void> _shareViaSms(String code) async {
    final msg = Uri.encodeComponent(_getShareMessage(code));
    final url = Uri.parse('sms:?body=$msg');
    try {
      if (await canLaunchUrl(url)) {
        await launchUrl(url);
      } else {
        _copyToClipboard(code);
      }
    } catch (_) {
      _copyToClipboard(code);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final referralCode = _userReferralCode ?? auth.referralCode ?? 'RMCVIP01';

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
          'Referral Program & Rewards',
          style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w800, fontSize: 18),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Hero Banner
            Container(
              padding: const EdgeInsets.all(24),
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
                  color: AppColors.primary.withOpacity(0.35),
                  width: 1.5,
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.3),
                    blurRadius: 16,
                    offset: const Offset(0, 6),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: AppColors.primary.withOpacity(0.18),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: AppColors.primary.withOpacity(0.4)),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.card_giftcard_rounded, color: AppColors.primary, size: 16),
                        SizedBox(width: 6),
                        Text(
                          'RIDE REWARDS',
                          style: TextStyle(
                            color: AppColors.primary,
                            fontWeight: FontWeight.w900,
                            fontSize: 11,
                            letterSpacing: 0.8,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),
                  const Text(
                    'Invite Friends,\nEarn Ride Credits',
                    style: TextStyle(
                      color: AppColors.textLight,
                      fontSize: 24,
                      fontWeight: FontWeight.w900,
                      height: 1.2,
                    ),
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'Share your unique referral code with family and friends. When they complete their first ride or rental, you both receive bonus wallet credits.',
                    style: TextStyle(
                      color: AppColors.textMuted,
                      fontSize: 13,
                      height: 1.45,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // Referral Code Display Card
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppColors.surfaceDark,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: Colors.white.withOpacity(0.08)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const Text(
                    'YOUR UNIQUE REFERRAL CODE',
                    style: TextStyle(
                      color: AppColors.textMuted,
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      letterSpacing: 1.0,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
                    decoration: BoxDecoration(
                      color: AppColors.backgroundDark,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: AppColors.primary.withOpacity(0.4)),
                    ),
                    child: Row(
                      children: [
                        Expanded(
                          child: SelectableText(
                            referralCode,
                            style: const TextStyle(
                              color: AppColors.primary,
                              fontSize: 24,
                              fontWeight: FontWeight.w900,
                              letterSpacing: 3,
                              fontFamily: 'monospace',
                            ),
                          ),
                        ),
                        IconButton(
                          tooltip: 'Copy Code',
                          icon: const Icon(Icons.copy_rounded, color: AppColors.primary),
                          onPressed: () => _copyToClipboard(referralCode),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Action Buttons
                  Row(
                    children: [
                      Expanded(
                        child: ElevatedButton.icon(
                          onPressed: () => _copyToClipboard(referralCode),
                          icon: const Icon(Icons.copy_rounded, size: 16),
                          label: const Text('Copy Code', style: TextStyle(fontWeight: FontWeight.bold)),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.primary,
                            foregroundColor: AppColors.backgroundDark,
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                            elevation: 0,
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: () => _shareViaWhatsApp(referralCode),
                          icon: const Icon(Icons.chat_bubble_outline_rounded, size: 16),
                          label: const Text('WhatsApp', style: TextStyle(fontWeight: FontWeight.bold)),
                          style: OutlinedButton.styleFrom(
                            foregroundColor: const Color(0xFF25D366),
                            side: const BorderSide(color: Color(0xFF25D366)),
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      OutlinedButton(
                        onPressed: () => _shareViaSms(referralCode),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: AppColors.textLight,
                          side: const BorderSide(color: Colors.white24),
                          padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        ),
                        child: const Icon(Icons.sms_outlined, size: 18),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // How It Works Section
            const Text(
              'How It Works',
              style: TextStyle(
                color: AppColors.textLight,
                fontSize: 18,
                fontWeight: FontWeight.w800,
              ),
            ),
            const SizedBox(height: 14),

            _buildStepTile(
              step: '1',
              title: 'Share Your Code',
              description: 'Send your personalized referral code to friends, colleagues, and family.',
              icon: Icons.send_rounded,
            ),
            const SizedBox(height: 10),
            _buildStepTile(
              step: '2',
              title: 'Friend Registers & Books',
              description: 'Your friend enters your code during sign-up and books their first ride or rental.',
              icon: Icons.app_registration_rounded,
            ),
            const SizedBox(height: 10),
            _buildStepTile(
              step: '3',
              title: 'Both Get Rewarded',
              description: 'Instant bonus credits are deposited directly into both your RideMyCars wallets.',
              icon: Icons.savings_rounded,
            ),
            const SizedBox(height: 24),

            // Claim a Code Section (if unreferred)
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppColors.surfaceDark,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: Colors.white.withOpacity(0.08)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.redeem_rounded, color: AppColors.primary, size: 20),
                      const SizedBox(width: 10),
                      const Text(
                        'Have a Referral Code?',
                        style: TextStyle(
                          color: AppColors.textLight,
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      if (_referredByCode != null && _referredByCode!.isNotEmpty) ...[
                        const Spacer(),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: AppColors.success.withOpacity(0.15),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Text(
                            'CLAIMED',
                            style: TextStyle(color: AppColors.success, fontSize: 10, fontWeight: FontWeight.bold),
                          ),
                        ),
                      ],
                    ],
                  ),
                  const SizedBox(height: 8),
                  if (_referredByCode != null && _referredByCode!.isNotEmpty) ...[
                    Text(
                      'You were referred by: $_referredByCode',
                      style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.w700, fontSize: 13),
                    ),
                  ] else ...[
                    const Text(
                      'Enter a friend\'s referral code below to claim welcome bonuses.',
                      style: TextStyle(color: AppColors.textMuted, fontSize: 12),
                    ),
                    const SizedBox(height: 14),
                    Row(
                      children: [
                        Expanded(
                          child: TextFormField(
                            controller: _redeemCodeController,
                            textCapitalization: TextCapitalization.characters,
                            style: const TextStyle(
                              color: AppColors.textLight,
                              fontWeight: FontWeight.bold,
                              letterSpacing: 2,
                            ),
                            decoration: InputDecoration(
                              hintText: 'e.g. RMC7K9A2',
                              hintStyle: const TextStyle(color: AppColors.textMuted, letterSpacing: 1),
                              filled: true,
                              fillColor: AppColors.backgroundDark,
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(14),
                                borderSide: BorderSide.none,
                              ),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        ElevatedButton(
                          onPressed: _isRedeeming ? null : _applyReferralCode,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.primary,
                            foregroundColor: AppColors.backgroundDark,
                            padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                          ),
                          child: _isRedeeming
                              ? const SizedBox(
                                  width: 18,
                                  height: 18,
                                  child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.backgroundDark),
                                )
                              : const Text('Apply', style: TextStyle(fontWeight: FontWeight.bold)),
                        ),
                      ],
                    ),
                    if (_redeemMessage != null) ...[
                      const SizedBox(height: 10),
                      Text(
                        _redeemMessage!,
                        style: TextStyle(
                          color: _redeemSuccess ? AppColors.success : AppColors.danger,
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ],
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Terms note
            Center(
              child: Text(
                'Ride credits are non-transferable and subject to RideMyCars Referral Terms.',
                textAlign: TextAlign.center,
                style: TextStyle(color: AppColors.textMuted.withOpacity(0.6), fontSize: 11),
              ),
            ),
            const SizedBox(height: 10),
          ],
        ),
      ),
    );
  }

  Widget _buildStepTile({
    required String step,
    required String title,
    required String description,
    required IconData icon,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white.withOpacity(0.05)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 38,
            height: 38,
            decoration: BoxDecoration(
              color: AppColors.primary.withOpacity(0.15),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.primary.withOpacity(0.3)),
            ),
            child: Center(
              child: Text(
                step,
                style: const TextStyle(
                  color: AppColors.primary,
                  fontWeight: FontWeight.w900,
                  fontSize: 16,
                ),
              ),
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Text(
                      title,
                      style: const TextStyle(
                        color: AppColors.textLight,
                        fontWeight: FontWeight.bold,
                        fontSize: 14,
                      ),
                    ),
                    const Spacer(),
                    Icon(icon, size: 16, color: AppColors.primary.withOpacity(0.7)),
                  ],
                ),
                const SizedBox(height: 4),
                Text(
                  description,
                  style: const TextStyle(
                    color: AppColors.textMuted,
                    fontSize: 12,
                    height: 1.4,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
