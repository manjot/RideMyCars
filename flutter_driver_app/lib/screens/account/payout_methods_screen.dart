import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../services/wallet_service.dart';

class PayoutMethodsScreen extends StatefulWidget {
  const PayoutMethodsScreen({super.key});

  @override
  State<PayoutMethodsScreen> createState() => _PayoutMethodsScreenState();
}

class _PayoutMethodsScreenState extends State<PayoutMethodsScreen> {
  bool _isLoading = true;
  bool _isSaving = false;
  List<Map<String, dynamic>> _savedMethods = [];

  // Currently selected option to configure/add: 'bank_account', 'momo', 'stripe', 'digital_wallet'
  String _selectedOption = 'bank_account';
  bool _makeActive = true;

  // Bank Form Controllers
  final _bankNameCtrl = TextEditingController();
  final _bankAccountNumCtrl = TextEditingController();
  final _bankHolderCtrl = TextEditingController();
  final _bankRoutingCtrl = TextEditingController();
  String _bankAccountType = 'Checking'; // Checking or Savings

  // MoMo Form Controllers
  String _momoNetwork = 'MTN'; // MTN, Telecel, AirtelTigo
  final _momoPhoneCtrl = TextEditingController();
  final _momoHolderCtrl = TextEditingController();

  // Stripe / Debit Card Controllers
  final _cardHolderCtrl = TextEditingController();
  final _cardNumberCtrl = TextEditingController();
  final _cardIssuerCtrl = TextEditingController(text: 'Visa / Mastercard Debit');

  // Digital Wallet Controllers
  String _walletProvider = 'PayPal'; // PayPal, Cash App, Zelle
  final _walletIdCtrl = TextEditingController();
  final _walletHolderCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _prefillUserData();
    _loadPayoutMethods();
  }

  void _prefillUserData() {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final userName = auth.userName ?? '';

    _bankHolderCtrl.text = userName;
    _momoHolderCtrl.text = userName;
    _cardHolderCtrl.text = userName;
    _walletHolderCtrl.text = userName;
  }

  @override
  void dispose() {
    _bankNameCtrl.dispose();
    _bankAccountNumCtrl.dispose();
    _bankHolderCtrl.dispose();
    _bankRoutingCtrl.dispose();
    _momoPhoneCtrl.dispose();
    _momoHolderCtrl.dispose();
    _cardHolderCtrl.dispose();
    _cardNumberCtrl.dispose();
    _cardIssuerCtrl.dispose();
    _walletIdCtrl.dispose();
    _walletHolderCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadPayoutMethods() async {
    setState(() => _isLoading = true);
    final methods = await WalletService.getPayoutMethods();
    if (mounted) {
      setState(() {
        _savedMethods = methods;
        _isLoading = false;
      });
    }
  }

  Map<String, dynamic>? get _activeMethod {
    if (_savedMethods.isEmpty) return null;
    return _savedMethods.firstWhere(
      (m) => m['is_default'] == true,
      orElse: () => _savedMethods.first,
    );
  }

  Future<void> _handleSetActive(Map<String, dynamic> method) async {
    final id = method['id'];
    if (id == null) return;

    final success = await WalletService.setDefaultPayoutMethod(id);
    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('${_formatDisplayTitle(method)} set as primary payout account!'),
          backgroundColor: AppColors.success,
        ),
      );
      _loadPayoutMethods();
    }
  }

  Future<void> _handleDelete(Map<String, dynamic> method) async {
    final id = method['id'];
    if (id == null) return;

    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppColors.surfaceDark,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.delete_outline_rounded, color: AppColors.error, size: 24),
            SizedBox(width: 10),
            Text(
              'Remove Payout Method',
              style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 16),
            ),
          ],
        ),
        content: Text(
          'Are you sure you want to remove ${_formatDisplayTitle(method)}? You can add it back anytime.',
          style: const TextStyle(color: AppColors.textMuted, fontSize: 13, height: 1.4),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Cancel', style: TextStyle(color: AppColors.textLight)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.error,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Remove', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );

    if (confirm == true) {
      final ok = await WalletService.deletePayoutMethod(id);
      if (ok && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Payout account removed successfully.'),
            backgroundColor: AppColors.primary,
          ),
        );
        _loadPayoutMethods();
      }
    }
  }

  Future<void> _handleSaveOption() async {
    Map<String, dynamic> payload = {};

    if (_selectedOption == 'bank_account') {
      if (_bankNameCtrl.text.trim().isEmpty) {
        _showError('Please enter your Bank Name (e.g. Chase, Wells Fargo, HDFC).');
        return;
      }
      if (_bankAccountNumCtrl.text.trim().isEmpty) {
        _showError('Please enter your Account Number or IBAN.');
        return;
      }
      if (_bankHolderCtrl.text.trim().isEmpty) {
        _showError('Please enter the Account Holder Full Name.');
        return;
      }

      payload = {
        'type': 'bank_account',
        'bank_name': _bankNameCtrl.text.trim(),
        'account_number': _bankAccountNumCtrl.text.trim(),
        'account_holder_name': _bankHolderCtrl.text.trim(),
        'routing_code': _bankRoutingCtrl.text.trim(),
        'branch_name': _bankAccountType,
        'is_default': _makeActive,
      };
    } else if (_selectedOption == 'momo') {
      if (_momoPhoneCtrl.text.trim().isEmpty) {
        _showError('Please enter your registered MoMo Phone Number.');
        return;
      }
      if (_momoHolderCtrl.text.trim().isEmpty) {
        _showError('Please enter the Account Holder Name on your MoMo SIM.');
        return;
      }

      payload = {
        'type': 'momo',
        'momo_network': _momoNetwork,
        'momo_phone': _momoPhoneCtrl.text.trim(),
        'momo_account_name': _momoHolderCtrl.text.trim(),
        'is_default': _makeActive,
      };
    } else if (_selectedOption == 'stripe') {
      if (_cardNumberCtrl.text.trim().isEmpty) {
        _showError('Please enter your Debit Card or Stripe Account Number.');
        return;
      }
      if (_cardHolderCtrl.text.trim().isEmpty) {
        _showError('Please enter the Cardholder Name.');
        return;
      }

      payload = {
        'type': 'stripe',
        'bank_name': _cardIssuerCtrl.text.trim().isNotEmpty
            ? _cardIssuerCtrl.text.trim()
            : 'Stripe Instant Debit Card',
        'account_number': _cardNumberCtrl.text.trim(),
        'account_holder_name': _cardHolderCtrl.text.trim(),
        'is_default': _makeActive,
      };
    } else if (_selectedOption == 'digital_wallet') {
      if (_walletIdCtrl.text.trim().isEmpty) {
        _showError('Please provide your $_walletProvider email or username/cashtag.');
        return;
      }

      payload = {
        'type': _walletProvider.toLowerCase().replaceAll(' ', ''),
        'bank_name': _walletProvider,
        'account_number': _walletIdCtrl.text.trim(),
        'account_holder_name': _walletHolderCtrl.text.trim(),
        'is_default': _makeActive,
      };
    }

    setState(() => _isSaving = true);
    final ok = await WalletService.savePayoutMethod(payload);
    setState(() => _isSaving = false);

    if (ok && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Payment payout method configured and active!'),
          backgroundColor: AppColors.success,
        ),
      );
      // Reset sensitive fields
      _bankAccountNumCtrl.clear();
      _cardNumberCtrl.clear();
      _walletIdCtrl.clear();
      await _loadPayoutMethods();
    } else if (mounted) {
      _showError('Failed to save payout method. Please try again.');
    }
  }

  void _showError(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(msg), backgroundColor: AppColors.error),
    );
  }

  String _formatDisplayTitle(Map<String, dynamic> method) {
    if (method['display_title'] != null && method['display_title'].toString().isNotEmpty) {
      return method['display_title'].toString();
    }

    final type = method['type']?.toString() ?? 'bank_account';
    if (type == 'momo') {
      final net = method['momo_network'] ?? method['network'] ?? 'MoMo';
      final phone = method['momo_phone'] ?? method['phone_number'] ?? '';
      return '$net: $phone';
    }

    if (type == 'stripe' || type == 'card') {
      final num = method['account_number']?.toString() ?? '';
      final last4 = num.length >= 4 ? num.substring(num.length - 4) : num;
      return '${method['bank_name'] ?? 'Debit Card'} (•••• $last4)';
    }

    if (type == 'paypal' || type == 'cashapp' || type == 'zelle') {
      return '${type.toUpperCase()}: ${method['account_number'] ?? method['account_holder_name'] ?? ''}';
    }

    final bank = method['bank_name'] ?? 'Bank Account';
    final num = method['account_number']?.toString() ?? '';
    final last4 = num.length >= 4 ? num.substring(num.length - 4) : num;
    return '$bank (•••• $last4)';
  }

  IconData _getMethodIcon(String type) {
    switch (type.toLowerCase()) {
      case 'momo':
        return Icons.phone_android_rounded;
      case 'stripe':
      case 'card':
        return Icons.credit_card_rounded;
      case 'paypal':
      case 'cashapp':
      case 'zelle':
        return Icons.account_balance_wallet_rounded;
      case 'bank_account':
      case 'bank':
      default:
        return Icons.account_balance_rounded;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.backgroundDark,
      appBar: AppBar(
        backgroundColor: AppColors.backgroundDark,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: AppColors.textLight, size: 20),
          onPressed: () => Navigator.pop(context, _activeMethod),
        ),
        title: const Text(
          'Payout & Payment Methods',
          style: TextStyle(
            color: AppColors.textLight,
            fontSize: 18,
            fontWeight: FontWeight.w900,
            letterSpacing: -0.3,
          ),
        ),
        centerTitle: false,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : RefreshIndicator(
              color: AppColors.primary,
              backgroundColor: AppColors.surfaceDark,
              onRefresh: _loadPayoutMethods,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Active Method Highlight Card
                    _buildActiveMethodBanner(),
                    const SizedBox(height: 24),

                    // Saved Payout Accounts (if any)
                    if (_savedMethods.isNotEmpty) ...[
                      const Text(
                        'Your Saved Payout Accounts',
                        style: TextStyle(
                          color: AppColors.textLight,
                          fontSize: 16,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      const SizedBox(height: 6),
                      const Text(
                        'Tap any saved account to select it as your active payout destination',
                        style: TextStyle(color: AppColors.textMuted, fontSize: 12),
                      ),
                      const SizedBox(height: 12),
                      ..._savedMethods.map((m) => _buildSavedMethodTile(m)),
                      const SizedBox(height: 28),
                    ],

                    // Payment Options To Be Selected
                    const Text(
                      'Select Payout Payment Option',
                      style: TextStyle(
                        color: AppColors.textLight,
                        fontSize: 16,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(height: 6),
                    const Text(
                      'Choose your preferred payment method below to connect or update:',
                      style: TextStyle(color: AppColors.textMuted, fontSize: 12),
                    ),
                    const SizedBox(height: 16),

                    // 4 Options of Payment Cards
                    _buildOptionSelector(
                      id: 'bank_account',
                      title: 'Direct Bank Deposit',
                      subtitle: 'Direct wire / ACH / IBAN transfer to your bank checking or savings',
                      badge: 'RECOMMENDED',
                      badgeColor: AppColors.primary,
                      icon: Icons.account_balance_rounded,
                    ),
                    const SizedBox(height: 10),

                    _buildOptionSelector(
                      id: 'momo',
                      title: 'Mobile Money (MoMo)',
                      subtitle: 'Instant 24/7 automated cashouts to MTN, Telecel, or AirtelTigo',
                      badge: 'INSTANT 24/7',
                      badgeColor: const Color(0xFFF59E0B),
                      icon: Icons.phone_android_rounded,
                    ),
                    const SizedBox(height: 10),

                    _buildOptionSelector(
                      id: 'stripe',
                      title: 'Stripe Connect / Debit Card',
                      subtitle: 'Instant card payouts to linked Visa or Mastercard debit cards',
                      badge: 'FAST DISPATCH',
                      badgeColor: const Color(0xFF6366F1),
                      icon: Icons.credit_card_rounded,
                    ),
                    const SizedBox(height: 10),

                    _buildOptionSelector(
                      id: 'digital_wallet',
                      title: 'Digital Wallet (PayPal / Cash App)',
                      subtitle: 'Convenient transfer directly to your PayPal, Cash App, or Zelle',
                      badge: 'EASY TRANSFER',
                      badgeColor: const Color(0xFF10B981),
                      icon: Icons.account_balance_wallet_rounded,
                    ),
                    const SizedBox(height: 20),

                    // Configuration Form Container for Selected Payment Option
                    _buildSelectedOptionForm(),
                    const SizedBox(height: 24),

                    // Security Guarantee Footer
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.03),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: Colors.white.withOpacity(0.05)),
                      ),
                      child: Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: AppColors.primary.withOpacity(0.12),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: const Icon(Icons.shield_outlined, color: AppColors.primary, size: 20),
                          ),
                          const SizedBox(width: 12),
                          const Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Bank-Grade 256-bit Security',
                                  style: TextStyle(
                                    color: AppColors.textLight,
                                    fontSize: 12,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                SizedBox(height: 2),
                                Text(
                                  'Your payment credentials are encrypted end-to-end and strictly protected under global financial PCI-DSS standards.',
                                  style: TextStyle(color: AppColors.textMuted, fontSize: 11, height: 1.4),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 32),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildActiveMethodBanner() {
    final active = _activeMethod;
    final hasActive = active != null;

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: hasActive
              ? [
                  AppColors.primary.withOpacity(0.18),
                  AppColors.surfaceDark,
                ]
              : [
                  Colors.white.withOpacity(0.05),
                  AppColors.surfaceDark,
                ],
        ),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: hasActive ? AppColors.primary.withOpacity(0.4) : Colors.white.withOpacity(0.08),
          width: 1.5,
        ),
        boxShadow: hasActive
            ? [
                BoxShadow(
                  color: AppColors.primary.withOpacity(0.1),
                  blurRadius: 20,
                  offset: const Offset(0, 4),
                ),
              ]
            : null,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                decoration: BoxDecoration(
                  color: hasActive ? AppColors.success.withOpacity(0.18) : Colors.amber.withOpacity(0.18),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(
                    color: hasActive ? AppColors.success.withOpacity(0.4) : Colors.amber.withOpacity(0.4),
                  ),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      width: 7,
                      height: 7,
                      decoration: BoxDecoration(
                        color: hasActive ? AppColors.success : Colors.amber,
                        shape: BoxShape.circle,
                      ),
                    ),
                    const SizedBox(width: 6),
                    Text(
                      hasActive ? 'ACTIVE PAYOUT DESTINATION' : 'PAYOUT ACCOUNT SETUP NEEDED',
                      style: TextStyle(
                        color: hasActive ? AppColors.success : Colors.amber,
                        fontSize: 10,
                        fontWeight: FontWeight.w900,
                        letterSpacing: 0.5,
                      ),
                    ),
                  ],
                ),
              ),
              const Spacer(),
              if (hasActive)
                const Icon(Icons.verified_rounded, color: AppColors.success, size: 20),
            ],
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Container(
                width: 46,
                height: 46,
                decoration: BoxDecoration(
                  color: hasActive ? AppColors.primary.withOpacity(0.2) : Colors.white10,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(
                    color: hasActive ? AppColors.primary.withOpacity(0.5) : Colors.white12,
                  ),
                ),
                child: Icon(
                  hasActive ? _getMethodIcon(active['type'] ?? '') : Icons.account_balance_outlined,
                  color: hasActive ? AppColors.primary : AppColors.textMuted,
                  size: 24,
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      hasActive ? _formatDisplayTitle(active) : 'No Payment Option Selected',
                      style: const TextStyle(
                        color: AppColors.textLight,
                        fontSize: 16,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      hasActive
                          ? 'Holder: ${active['account_holder_name'] ?? active['momo_account_name'] ?? 'Driver'}'
                          : 'Select an option below to start receiving weekly earnings',
                      style: const TextStyle(color: AppColors.textMuted, fontSize: 12),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          const Divider(color: Colors.white10, height: 1),
          const SizedBox(height: 10),
          Text(
            hasActive
                ? 'All completed ride earnings, guaranteed incentives, and rider tips are automatically transferred to this verified account.'
                : 'Configure direct bank deposit, mobile money, or debit card to get paid on time.',
            style: const TextStyle(color: AppColors.textMuted, fontSize: 11.5, height: 1.4),
          ),
        ],
      ),
    );
  }

  Widget _buildSavedMethodTile(Map<String, dynamic> method) {
    final isDefault = method['is_default'] == true;
    final type = method['type']?.toString() ?? 'bank_account';

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isDefault ? AppColors.primary.withOpacity(0.5) : Colors.white.withOpacity(0.06),
          width: isDefault ? 1.5 : 1.0,
        ),
      ),
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        leading: Container(
          padding: const EdgeInsets.all(10),
          decoration: BoxDecoration(
            color: isDefault ? AppColors.primary.withOpacity(0.18) : Colors.white.withOpacity(0.05),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Icon(
            _getMethodIcon(type),
            color: isDefault ? AppColors.primary : AppColors.textLight,
            size: 22,
          ),
        ),
        title: Row(
          children: [
            Expanded(
              child: Text(
                _formatDisplayTitle(method),
                style: const TextStyle(
                  color: AppColors.textLight,
                  fontWeight: FontWeight.bold,
                  fontSize: 14,
                ),
              ),
            ),
            if (isDefault)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: AppColors.success.withOpacity(0.2),
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(color: AppColors.success.withOpacity(0.4)),
                ),
                child: const Text(
                  'ACTIVE',
                  style: TextStyle(color: AppColors.success, fontSize: 9.5, fontWeight: FontWeight.w900),
                ),
              ),
          ],
        ),
        subtitle: Padding(
          padding: const EdgeInsets.only(top: 4),
          child: Text(
            'Holder: ${method['account_holder_name'] ?? method['momo_account_name'] ?? 'Driver'}',
            style: const TextStyle(color: AppColors.textMuted, fontSize: 11.5),
          ),
        ),
        trailing: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (!isDefault)
              TextButton(
                onPressed: () => _handleSetActive(method),
                style: TextButton.styleFrom(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  backgroundColor: AppColors.primary.withOpacity(0.12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                ),
                child: const Text(
                  'Make Active',
                  style: TextStyle(color: AppColors.primary, fontSize: 11, fontWeight: FontWeight.w800),
                ),
              ),
            IconButton(
              icon: const Icon(Icons.delete_outline_rounded, color: Colors.white38, size: 20),
              onPressed: () => _handleDelete(method),
              tooltip: 'Remove',
            ),
          ],
        ),
        onTap: () {
          if (!isDefault) _handleSetActive(method);
        },
      ),
    );
  }

  Widget _buildOptionSelector({
    required String id,
    required String title,
    required String subtitle,
    required String badge,
    required Color badgeColor,
    required IconData icon,
  }) {
    final isSelected = _selectedOption == id;

    return InkWell(
      onTap: () => setState(() => _selectedOption = id),
      borderRadius: BorderRadius.circular(16),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.surfaceDark : AppColors.surfaceDark.withOpacity(0.6),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: isSelected ? AppColors.primary : Colors.white.withOpacity(0.06),
            width: isSelected ? 2 : 1,
          ),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                    color: AppColors.primary.withOpacity(0.12),
                    blurRadius: 16,
                    offset: const Offset(0, 4),
                  ),
                ]
              : null,
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(11),
              decoration: BoxDecoration(
                color: isSelected ? AppColors.primary.withOpacity(0.2) : Colors.white.withOpacity(0.05),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(
                  color: isSelected ? AppColors.primary.withOpacity(0.5) : Colors.white10,
                ),
              ),
              child: Icon(
                icon,
                color: isSelected ? AppColors.primary : AppColors.textLight,
                size: 22,
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
                        style: TextStyle(
                          color: isSelected ? AppColors.primary : AppColors.textLight,
                          fontWeight: FontWeight.w800,
                          fontSize: 14.5,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: badgeColor.withOpacity(0.15),
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(color: badgeColor.withOpacity(0.4)),
                        ),
                        child: Text(
                          badge,
                          style: TextStyle(
                            color: badgeColor,
                            fontSize: 9,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 3),
                  Text(
                    subtitle,
                    style: const TextStyle(color: AppColors.textMuted, fontSize: 11.5, height: 1.3),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 8),
            Container(
              width: 22,
              height: 22,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(
                  color: isSelected ? AppColors.primary : Colors.white30,
                  width: 2,
                ),
                color: isSelected ? AppColors.primary : Colors.transparent,
              ),
              child: isSelected
                  ? const Center(
                      child: Icon(Icons.check, size: 14, color: AppColors.backgroundDark),
                    )
                  : null,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSelectedOptionForm() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppColors.primary.withOpacity(0.3), width: 1.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(_getMethodIcon(_selectedOption), color: AppColors.primary, size: 20),
              const SizedBox(width: 10),
              Text(
                _getFormTitle(),
                style: const TextStyle(
                  color: AppColors.textLight,
                  fontSize: 15,
                  fontWeight: FontWeight.w900,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          const Divider(color: Colors.white10, height: 1),
          const SizedBox(height: 16),

          // Dynamic fields depending on selected option
          if (_selectedOption == 'bank_account') ..._buildBankFields(),
          if (_selectedOption == 'momo') ..._buildMomoFields(),
          if (_selectedOption == 'stripe') ..._buildStripeFields(),
          if (_selectedOption == 'digital_wallet') ..._buildWalletFields(),

          const SizedBox(height: 16),
          // Set as active toggle
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.04),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: Colors.white.withOpacity(0.06)),
            ),
            child: Row(
              children: [
                const Icon(Icons.check_circle_outline_rounded, color: AppColors.primary, size: 18),
                const SizedBox(width: 10),
                const Expanded(
                  child: Text(
                    'Set as primary active payout method',
                    style: TextStyle(color: AppColors.textLight, fontSize: 12.5, fontWeight: FontWeight.w600),
                  ),
                ),
                Switch(
                  value: _makeActive,
                  activeColor: AppColors.primary,
                  onChanged: (v) => setState(() => _makeActive = v),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          // Submit Button
          SizedBox(
            width: double.infinity,
            height: 50,
            child: ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: AppColors.backgroundDark,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                elevation: 4,
              ),
              onPressed: _isSaving ? null : _handleSaveOption,
              child: _isSaving
                  ? const SizedBox(
                      width: 22,
                      height: 22,
                      child: CircularProgressIndicator(
                        strokeWidth: 2.5,
                        color: AppColors.backgroundDark,
                      ),
                    )
                  : Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.save_rounded, size: 18),
                        const SizedBox(width: 8),
                        Text(
                          'Save & Activate ${_getOptionName()}',
                          style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 14),
                        ),
                      ],
                    ),
            ),
          ),
        ],
      ),
    );
  }

  String _getFormTitle() {
    switch (_selectedOption) {
      case 'bank_account':
        return 'Configure Bank Direct Deposit';
      case 'momo':
        return 'Configure Mobile Money (MoMo)';
      case 'stripe':
        return 'Configure Stripe / Debit Card';
      case 'digital_wallet':
        return 'Configure Digital Wallet';
      default:
        return 'Configure Payout Account';
    }
  }

  String _getOptionName() {
    switch (_selectedOption) {
      case 'bank_account':
        return 'Bank Account';
      case 'momo':
        return 'MoMo Account';
      case 'stripe':
        return 'Debit Card';
      case 'digital_wallet':
        return 'Digital Wallet';
      default:
        return 'Payment Option';
    }
  }

  List<Widget> _buildBankFields() {
    return [
      _buildTextField(
        label: 'Account Holder Name',
        controller: _bankHolderCtrl,
        hint: 'Full legal name on bank account',
        icon: Icons.person_outline_rounded,
      ),
      const SizedBox(height: 12),
      _buildTextField(
        label: 'Bank Name',
        controller: _bankNameCtrl,
        hint: 'e.g. Chase, Bank of America, HDFC, Barclays',
        icon: Icons.account_balance_outlined,
      ),
      const SizedBox(height: 12),
      _buildTextField(
        label: 'Account Number / IBAN',
        controller: _bankAccountNumCtrl,
        hint: 'e.g. 501002348910 or GB29X...',
        icon: Icons.numbers_rounded,
        keyboardType: TextInputType.text,
      ),
      const SizedBox(height: 12),
      Row(
        children: [
          Expanded(
            child: _buildTextField(
              label: 'Routing / IFSC / Sort Code',
              controller: _bankRoutingCtrl,
              hint: 'e.g. 021000021 / HDFC0001234',
              icon: Icons.merge_type_rounded,
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Account Type',
                  style: TextStyle(color: AppColors.textMuted, fontSize: 11, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 6),
                Container(
                  height: 48,
                  padding: const EdgeInsets.symmetric(horizontal: 12),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.05),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.white12),
                  ),
                  child: DropdownButtonHideUnderline(
                    child: DropdownButton<String>(
                      value: _bankAccountType,
                      dropdownColor: AppColors.surfaceDark,
                      isExpanded: true,
                      style: const TextStyle(color: AppColors.textLight, fontSize: 13, fontWeight: FontWeight.w600),
                      items: const [
                        DropdownMenuItem(value: 'Checking', child: Text('Checking')),
                        DropdownMenuItem(value: 'Savings', child: Text('Savings')),
                      ],
                      onChanged: (v) {
                        if (v != null) setState(() => _bankAccountType = v);
                      },
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    ];
  }

  List<Widget> _buildMomoFields() {
    return [
      const Text(
        'Select Mobile Money Network',
        style: TextStyle(color: AppColors.textMuted, fontSize: 11, fontWeight: FontWeight.bold),
      ),
      const SizedBox(height: 8),
      Row(
        children: [
          _buildNetworkChip('MTN', 'MTN MoMo', Icons.flash_on_rounded),
          const SizedBox(width: 8),
          _buildNetworkChip('Telecel', 'Telecel Cash', Icons.phone_android_rounded),
          const SizedBox(width: 8),
          _buildNetworkChip('AirtelTigo', 'AirtelTigo', Icons.cell_tower_rounded),
        ],
      ),
      const SizedBox(height: 14),
      _buildTextField(
        label: 'Registered MoMo Phone Number',
        controller: _momoPhoneCtrl,
        hint: 'e.g. 0244123456',
        icon: Icons.phone_rounded,
        keyboardType: TextInputType.phone,
      ),
      const SizedBox(height: 12),
      _buildTextField(
        label: 'Account Holder Name',
        controller: _momoHolderCtrl,
        hint: 'Full name registered on this SIM wallet',
        icon: Icons.person_outline_rounded,
      ),
    ];
  }

  Widget _buildNetworkChip(String code, String label, IconData icon) {
    final isSelected = _momoNetwork == code;

    return Expanded(
      child: InkWell(
        onTap: () => setState(() => _momoNetwork = code),
        borderRadius: BorderRadius.circular(10),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
          decoration: BoxDecoration(
            color: isSelected ? AppColors.primary.withOpacity(0.18) : Colors.white.withOpacity(0.04),
            borderRadius: BorderRadius.circular(10),
            border: Border.all(
              color: isSelected ? AppColors.primary : Colors.white10,
              width: isSelected ? 1.5 : 1.0,
            ),
          ),
          child: Column(
            children: [
              Icon(icon, size: 16, color: isSelected ? AppColors.primary : AppColors.textMuted),
              const SizedBox(height: 4),
              Text(
                label,
                style: TextStyle(
                  color: isSelected ? AppColors.primary : AppColors.textLight,
                  fontSize: 10.5,
                  fontWeight: FontWeight.bold,
                ),
                textAlign: TextAlign.center,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ],
          ),
        ),
      ),
    );
  }

  List<Widget> _buildStripeFields() {
    return [
      _buildTextField(
        label: 'Cardholder Name',
        controller: _cardHolderCtrl,
        hint: 'Name as it appears on your debit card',
        icon: Icons.person_outline_rounded,
      ),
      const SizedBox(height: 12),
      _buildTextField(
        label: 'Debit Card Number (Visa / Mastercard)',
        controller: _cardNumberCtrl,
        hint: 'e.g. 4532 0150 9982 4310',
        icon: Icons.credit_card_rounded,
        keyboardType: TextInputType.number,
      ),
      const SizedBox(height: 12),
      _buildTextField(
        label: 'Card Issuer / Financial Institution',
        controller: _cardIssuerCtrl,
        hint: 'e.g. Chase Debit, Wells Fargo, Revolut',
        icon: Icons.business_rounded,
      ),
    ];
  }

  List<Widget> _buildWalletFields() {
    return [
      const Text(
        'Digital Wallet Provider',
        style: TextStyle(color: AppColors.textMuted, fontSize: 11, fontWeight: FontWeight.bold),
      ),
      const SizedBox(height: 8),
      Row(
        children: ['PayPal', 'Cash App', 'Zelle'].map((provider) {
          final isSel = _walletProvider == provider;
          return Expanded(
            child: InkWell(
              onTap: () => setState(() => _walletProvider = provider),
              child: Container(
                margin: const EdgeInsets.symmetric(horizontal: 4),
                padding: const EdgeInsets.symmetric(vertical: 10),
                decoration: BoxDecoration(
                  color: isSel ? AppColors.primary.withOpacity(0.18) : Colors.white.withOpacity(0.04),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(
                    color: isSel ? AppColors.primary : Colors.white10,
                    width: isSel ? 1.5 : 1.0,
                  ),
                ),
                child: Text(
                  provider,
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    color: isSel ? AppColors.primary : AppColors.textLight,
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ),
          );
        }).toList(),
      ),
      const SizedBox(height: 14),
      _buildTextField(
        label: '$_walletProvider ID / Handle / Email',
        controller: _walletIdCtrl,
        hint: _walletProvider == 'Cash App' ? 'e.g. \$yourtag' : 'e.g. driver@email.com',
        icon: Icons.alternate_email_rounded,
      ),
      const SizedBox(height: 12),
      _buildTextField(
        label: 'Account Holder Full Name',
        controller: _walletHolderCtrl,
        hint: 'Full name on account',
        icon: Icons.person_outline_rounded,
      ),
    ];
  }

  Widget _buildTextField({
    required String label,
    required TextEditingController controller,
    required String hint,
    required IconData icon,
    TextInputType keyboardType = TextInputType.text,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(color: AppColors.textMuted, fontSize: 11, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 6),
        Container(
          height: 48,
          decoration: BoxDecoration(
            color: Colors.white.withOpacity(0.05),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.white12),
          ),
          child: TextField(
            controller: controller,
            keyboardType: keyboardType,
            style: const TextStyle(color: AppColors.textLight, fontSize: 13, fontWeight: FontWeight.w600),
            decoration: InputDecoration(
              hintText: hint,
              hintStyle: const TextStyle(color: AppColors.textMuted, fontSize: 12),
              prefixIcon: Icon(icon, color: AppColors.primary.withOpacity(0.7), size: 18),
              border: InputBorder.none,
              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            ),
          ),
        ),
      ],
    );
  }
}
