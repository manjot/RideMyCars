import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../providers/country_provider.dart';
import '../../services/wallet_service.dart';

class PaymentSelectionResult {
  final String paymentMethod; // 'stripe', 'momo', 'wallet', 'cash'
  final String? momoPhone;
  final String? momoNetwork;
  final String? cardLast4;
  final String? cardHolder;
  final bool saveCard;

  PaymentSelectionResult({
    required this.paymentMethod,
    this.momoPhone,
    this.momoNetwork,
    this.cardLast4,
    this.cardHolder,
    this.saveCard = false,
  });
}

class PaymentGatewaySheet extends StatefulWidget {
  final String serviceType; // 'ride', 'rental', 'driver_booking', 'package_delivery'
  final double totalAmount;
  final double payNowAmount;
  final String currencySymbol;
  final String currencyCode;
  final String? initialMethod;

  const PaymentGatewaySheet({
    super.key,
    required this.serviceType,
    required this.totalAmount,
    required this.payNowAmount,
    this.currencySymbol = '\$',
    this.currencyCode = 'USD',
    this.initialMethod,
  });

  static Future<PaymentSelectionResult?> show(
    BuildContext context, {
    required String serviceType,
    required double totalAmount,
    required double payNowAmount,
    String currencySymbol = '\$',
    String currencyCode = 'USD',
    String? initialMethod,
  }) {
    final country = Provider.of<CountryProvider>(context, listen: false);
    final resolvedSymbol = (currencySymbol != '\$') ? currencySymbol : country.currencySymbol;
    final resolvedCode = (currencyCode != 'USD') ? currencyCode : country.currencyCode;

    return showModalBottomSheet<PaymentSelectionResult>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => PaymentGatewaySheet(
        serviceType: serviceType,
        totalAmount: totalAmount,
        payNowAmount: payNowAmount,
        currencySymbol: resolvedSymbol,
        currencyCode: resolvedCode,
        initialMethod: initialMethod,
      ),
    );
  }

  @override
  State<PaymentGatewaySheet> createState() => _PaymentGatewaySheetState();
}

class _PaymentGatewaySheetState extends State<PaymentGatewaySheet> {
  late String _selectedMethod;
  final _cardNumberController = TextEditingController();
  final _cardExpiryController = TextEditingController();
  final _cardCvvController = TextEditingController();
  final _cardHolderController = TextEditingController();
  final _momoPhoneController = TextEditingController();
  String _momoNetwork = 'MTN';
  bool _saveCard = true;

  double _walletBalance = 0.0;
  bool _isLoadingWallet = false;

  bool get _isCashAllowed =>
      widget.serviceType == 'ride' || widget.serviceType == 'package_delivery';

  @override
  void initState() {
    super.initState();
    _selectedMethod = widget.initialMethod ?? 'stripe';
    if (!_isCashAllowed && _selectedMethod == 'cash') {
      _selectedMethod = 'stripe';
    }

    final auth = Provider.of<AuthProvider>(context, listen: false);
    _cardHolderController.text = auth.userName ?? 'Cardholder Name';
    _momoPhoneController.text = '';

    _loadWallet();
  }

  @override
  void dispose() {
    _cardNumberController.dispose();
    _cardExpiryController.dispose();
    _cardCvvController.dispose();
    _cardHolderController.dispose();
    _momoPhoneController.dispose();
    super.dispose();
  }

  Future<void> _loadWallet() async {
    setState(() => _isLoadingWallet = true);
    try {
      final res = await WalletService.getBalance();
      if (mounted && res != null) {
        setState(() {
          _walletBalance = (res['balance'] as num?)?.toDouble() ?? 0.0;
        });
      }
    } catch (_) {} finally {
      if (mounted) setState(() => _isLoadingWallet = false);
    }
  }

  void _confirmSelection() {
    if (_selectedMethod == 'stripe') {
      final num = _cardNumberController.text.replaceAll(' ', '');
      if (num.isNotEmpty && num.length < 13) {
        _showError('Please enter a valid card number');
        return;
      }
      final last4 = num.length >= 4 ? num.substring(num.length - 4) : '4242';
      Navigator.pop(
        context,
        PaymentSelectionResult(
          paymentMethod: 'stripe',
          cardLast4: last4,
          cardHolder: _cardHolderController.text.trim(),
          saveCard: _saveCard,
        ),
      );
    } else if (_selectedMethod == 'momo') {
      final phone = _momoPhoneController.text.trim();
      if (phone.isEmpty) {
        _showError('Please enter your Mobile Money phone number');
        return;
      }
      Navigator.pop(
        context,
        PaymentSelectionResult(
          paymentMethod: 'momo',
          momoPhone: phone,
          momoNetwork: _momoNetwork,
        ),
      );
    } else if (_selectedMethod == 'wallet') {
      if (_walletBalance < widget.payNowAmount) {
        _showError('Insufficient wallet balance. Please choose another payment method or top up.');
        return;
      }
      Navigator.pop(
        context,
        PaymentSelectionResult(paymentMethod: 'wallet'),
      );
    } else {
      Navigator.pop(
        context,
        PaymentSelectionResult(paymentMethod: 'cash'),
      );
    }
  }

  void _showError(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(msg), backgroundColor: AppColors.error),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Container(
      padding: EdgeInsets.only(
        bottom: MediaQuery.of(context).viewInsets.bottom + 20,
        top: 16,
        left: 20,
        right: 20,
      ),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF161922) : Colors.white,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
        boxShadow: const [
          BoxShadow(color: Colors.black26, blurRadius: 30, offset: Offset(0, -6)),
        ],
      ),
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Drag handle
            Center(
              child: Container(
                width: 44,
                height: 4,
                margin: const EdgeInsets.only(bottom: 16),
                decoration: BoxDecoration(
                  color: isDark ? Colors.white24 : Colors.grey[300],
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),

            // Header & Amount Banner
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Payment Method',
                      style: TextStyle(fontSize: 20, fontWeight: FontWeight.w900),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Secure 256-Bit SSL Encrypted Checkout',
                      style: TextStyle(
                        fontSize: 11,
                        color: isDark ? Colors.white54 : Colors.black45,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: AppColors.primary.withOpacity(0.12),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppColors.primary.withOpacity(0.3)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(
                        'Total Due',
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w700,
                          color: AppColors.primary,
                        ),
                      ),
                      Text(
                        '${widget.currencySymbol}${widget.payNowAmount.toStringAsFixed(2)}',
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 18),

            // Payment Methods Tabs
            _buildMethodTile(
              id: 'stripe',
              title: 'Credit / Debit Card',
              subtitle: 'Visa, Mastercard, Amex via Stripe',
              icon: Icons.credit_card_rounded,
              isDark: isDark,
            ),
            const SizedBox(height: 8),

            _buildMethodTile(
              id: 'momo',
              title: 'Mobile Money (Ghana)',
              subtitle: 'MTN MoMo, Vodafone Cash, AirtelTigo',
              icon: Icons.phone_android_rounded,
              isDark: isDark,
            ),
            const SizedBox(height: 8),

            _buildMethodTile(
              id: 'wallet',
              title: 'RideMyCars Wallet',
              subtitle: _isLoadingWallet
                  ? 'Loading balance...'
                  : 'Balance: ${widget.currencySymbol}${_walletBalance.toStringAsFixed(2)}',
              icon: Icons.account_balance_wallet_rounded,
              isDark: isDark,
            ),
            const SizedBox(height: 8),

            if (_isCashAllowed)
              _buildMethodTile(
                id: 'cash',
                title: 'Cash on Service / Drop-off',
                subtitle: 'Pay directly to your driver/courier',
                icon: Icons.payments_rounded,
                isDark: isDark,
              )
            else
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                decoration: BoxDecoration(
                  color: Colors.red.withOpacity(0.06),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: Colors.red.withOpacity(0.2)),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.info_outline, size: 16, color: Colors.red),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Cash payment is not permitted for ${widget.serviceType.replaceAll('_', ' ')}. Digital authorization required.',
                        style: const TextStyle(fontSize: 11, color: Colors.red, fontWeight: FontWeight.w600),
                      ),
                    ),
                  ],
                ),
              ),

            const SizedBox(height: 16),

            // Dynamic Inputs based on selected method
            if (_selectedMethod == 'stripe') _buildStripeForm(isDark),
            if (_selectedMethod == 'momo') _buildMomoForm(isDark),
            if (_selectedMethod == 'wallet') _buildWalletInfo(isDark),
            if (_selectedMethod == 'cash') _buildCashInfo(isDark),

            const SizedBox(height: 20),

            // Submit Button
            ElevatedButton(
              onPressed: _confirmSelection,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                elevation: 4,
              ),
              child: Text(
                'Authorize & Pay ${widget.currencySymbol}${widget.payNowAmount.toStringAsFixed(2)} →',
                style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w900),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMethodTile({
    required String id,
    required String title,
    required String subtitle,
    required IconData icon,
    required bool isDark,
  }) {
    final isSelected = _selectedMethod == id;
    return GestureDetector(
      onTap: () => setState(() => _selectedMethod = id),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        decoration: BoxDecoration(
          color: isSelected
              ? AppColors.primary.withOpacity(0.10)
              : (isDark ? Colors.white.withOpacity(0.04) : Colors.grey[100]),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: isSelected ? AppColors.primary : (isDark ? Colors.white12 : Colors.grey[300]!),
            width: isSelected ? 2 : 1,
          ),
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: isSelected ? AppColors.primary : (isDark ? Colors.white10 : Colors.white),
                shape: BoxShape.circle,
              ),
              child: Icon(
                icon,
                size: 20,
                color: isSelected ? Colors.white : (isDark ? Colors.white70 : Colors.black87),
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w800,
                      color: isSelected ? AppColors.primary : null,
                    ),
                  ),
                  Text(
                    subtitle,
                    style: TextStyle(
                      fontSize: 11,
                      color: isDark ? Colors.white54 : Colors.black54,
                    ),
                  ),
                ],
              ),
            ),
            Radio<String>(
              value: id,
              groupValue: _selectedMethod,
              activeColor: AppColors.primary,
              onChanged: (val) {
                if (val != null) setState(() => _selectedMethod = val);
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStripeForm(bool isDark) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: isDark ? Colors.black26 : Colors.grey[50],
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: isDark ? Colors.white10 : Colors.grey[200]!),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          TextField(
            controller: _cardNumberController,
            keyboardType: TextInputType.number,
            decoration: InputDecoration(
              labelText: 'Card Number',
              hintText: '4242 •••• •••• 4242',
              prefixIcon: const Icon(Icons.credit_card, size: 20),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              isDense: true,
            ),
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              Expanded(
                child: TextField(
                  controller: _cardExpiryController,
                  decoration: InputDecoration(
                    labelText: 'Expiry (MM/YY)',
                    hintText: '12/28',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    isDense: true,
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: TextField(
                  controller: _cardCvvController,
                  keyboardType: TextInputType.number,
                  obscureText: true,
                  decoration: InputDecoration(
                    labelText: 'CVV',
                    hintText: '123',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    isDense: true,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          TextField(
            controller: _cardHolderController,
            decoration: InputDecoration(
              labelText: 'Cardholder Name',
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              isDense: true,
            ),
          ),
          const SizedBox(height: 6),
          Row(
            children: [
              Checkbox(
                value: _saveCard,
                activeColor: AppColors.primary,
                onChanged: (v) => setState(() => _saveCard = v ?? true),
              ),
              const Expanded(
                child: Text(
                  'Save card securely for 1-click future rides and rentals',
                  style: TextStyle(fontSize: 11),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildMomoForm(bool isDark) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: isDark ? Colors.black26 : Colors.grey[50],
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: isDark ? Colors.white10 : Colors.grey[200]!),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Select Mobile Network',
            style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 8),
          Row(
            children: ['MTN', 'Vodafone', 'AirtelTigo'].map((net) {
              final isSel = _momoNetwork == net;
              return Expanded(
                child: GestureDetector(
                  onTap: () => setState(() => _momoNetwork = net),
                  child: Container(
                    margin: const EdgeInsets.symmetric(horizontal: 3),
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    decoration: BoxDecoration(
                      color: isSel ? AppColors.primary : (isDark ? Colors.white10 : Colors.white),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: isSel ? AppColors.primary : (isDark ? Colors.white12 : Colors.grey[300]!),
                      ),
                    ),
                    alignment: Alignment.center,
                    child: Text(
                      net,
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        color: isSel ? Colors.white : null,
                      ),
                    ),
                  ),
                ),
              );
            }).toList(),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _momoPhoneController,
            keyboardType: TextInputType.phone,
            decoration: InputDecoration(
              labelText: 'Mobile Money Number',
              hintText: '024 123 4567',
              prefixIcon: const Icon(Icons.phone_iphone, size: 20),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              isDense: true,
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            '⚠️ An authorization prompt will be pushed directly to your phone. Approve it with your MoMo PIN to complete the booking.',
            style: TextStyle(fontSize: 11, color: Colors.amber, fontWeight: FontWeight.w600),
          ),
        ],
      ),
    );
  }

  Widget _buildWalletInfo(bool isDark) {
    final hasEnough = _walletBalance >= widget.payNowAmount;
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: hasEnough
            ? Colors.green.withOpacity(0.08)
            : Colors.amber.withOpacity(0.08),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: hasEnough ? Colors.green.withOpacity(0.3) : Colors.amber.withOpacity(0.3),
        ),
      ),
      child: Row(
        children: [
          Icon(
            hasEnough ? Icons.check_circle : Icons.warning_amber_rounded,
            color: hasEnough ? Colors.green : Colors.amber,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  hasEnough ? 'Sufficient Balance' : 'Insufficient Wallet Balance',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w800,
                    color: hasEnough ? Colors.green : Colors.amber[900],
                  ),
                ),
                Text(
                  hasEnough
                      ? 'Funds will be immediately deducted from your in-app wallet balance.'
                      : 'You need ${widget.currencySymbol}${(widget.payNowAmount - _walletBalance).toStringAsFixed(2)} more. Please top up your wallet.',
                  style: const TextStyle(fontSize: 11),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCashInfo(bool isDark) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.primary.withOpacity(0.08),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.primary.withOpacity(0.2)),
      ),
      child: const Row(
        children: [
          Icon(Icons.info_outline, color: AppColors.primary),
          SizedBox(width: 12),
          Expanded(
            child: Text(
              'Please have the exact cash ready to hand over to your driver upon arrival or delivery completion.',
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
            ),
          ),
        ],
      ),
    );
  }
}
