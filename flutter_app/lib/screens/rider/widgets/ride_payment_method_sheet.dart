import 'package:flutter/material.dart';
import '../../../core/constants/app_colors.dart';

class RidePaymentSelection {
  final String method; // 'stripe', 'momo', 'cash', 'wallet'
  final String? momoPhone;
  final String? momoNetwork;

  RidePaymentSelection({
    required this.method,
    this.momoPhone,
    this.momoNetwork,
  });

  String get displayName {
    switch (method) {
      case 'stripe':
        return 'Stripe (Cards & Apple Pay)';
      case 'momo':
        return 'MoMo Pay (${momoNetwork ?? 'MTN'})';
      case 'cash':
        return 'Cash Direct Pay';
      case 'wallet':
        return 'RideMyCars Wallet';
      default:
        return 'Stripe (Cards & Apple Pay)';
    }
  }

  String get subtitle {
    switch (method) {
      case 'stripe':
        return 'Pre-authorization hold secured by Stripe';
      case 'momo':
        return 'Prompt to ${momoPhone ?? 'mobile money'}';
      case 'cash':
        return 'Pay driver physical cash upon reaching destination';
      case 'wallet':
        return 'Pay directly with in-app balance';
      default:
        return 'Pre-authorization hold secured by Stripe';
    }
  }
}

class RidePaymentMethodSheet extends StatefulWidget {
  final String currentMethod;
  final String? currentMomoPhone;
  final String? currentMomoNetwork;
  final String serviceType; // 'ride', 'rental', 'driver', 'delivery'
  final bool? showCash;
  final bool showWallet;

  const RidePaymentMethodSheet({
    super.key,
    required this.currentMethod,
    this.currentMomoPhone,
    this.currentMomoNetwork,
    this.serviceType = 'ride',
    this.showCash,
    this.showWallet = false,
  });

  bool get isCashAllowed {
    if (showCash == false) return false;
    final st = serviceType.toLowerCase().trim();
    if (st == 'rental' || st == 'rent' || st == 'driver' || st == 'driver_booking') {
      return false;
    }
    return showCash ?? true;
  }

  static Future<RidePaymentSelection?> show(
    BuildContext context, {
    String currentMethod = 'stripe',
    String? currentMomoPhone,
    String? currentMomoNetwork,
    String serviceType = 'ride',
    bool? showCash,
    bool showWallet = false,
  }) {
    return showModalBottomSheet<RidePaymentSelection>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => RidePaymentMethodSheet(
        currentMethod: currentMethod,
        currentMomoPhone: currentMomoPhone,
        currentMomoNetwork: currentMomoNetwork,
        serviceType: serviceType,
        showCash: showCash,
        showWallet: showWallet,
      ),
    );
  }

  @override
  State<RidePaymentMethodSheet> createState() => _RidePaymentMethodSheetState();
}

class _RidePaymentMethodSheetState extends State<RidePaymentMethodSheet> {
  late String _selectedMethod;
  late TextEditingController _momoPhoneController;
  late String _momoNetwork;

  @override
  void initState() {
    super.initState();
    _selectedMethod = widget.currentMethod;
    if (!widget.isCashAllowed && _selectedMethod == 'cash') {
      _selectedMethod = 'stripe';
    }
    _momoPhoneController = TextEditingController(text: widget.currentMomoPhone ?? '');
    _momoNetwork = widget.currentMomoNetwork ?? 'MTN';
  }

  @override
  void dispose() {
    _momoPhoneController.dispose();
    super.dispose();
  }

  Widget _buildBrandIcon(String assetName, {double height = 23}) {
    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(4),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.25),
            blurRadius: 3,
            offset: const Offset(0, 1),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(4),
        child: Image.asset(
          'assets/images/payment-icons/$assetName',
          height: height,
          fit: BoxFit.contain,
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (!widget.isCashAllowed && _selectedMethod == 'cash') {
      _selectedMethod = 'stripe';
    }
    if (!widget.showWallet && _selectedMethod == 'wallet') {
      _selectedMethod = 'stripe';
    }
    return Container(
      padding: EdgeInsets.only(
        bottom: MediaQuery.of(context).viewInsets.bottom + 20,
        top: 18,
        left: 20,
        right: 20,
      ),
      decoration: const BoxDecoration(
        color: Color(0xFF0F172A),
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
        boxShadow: [
          BoxShadow(
            color: Colors.black54,
            blurRadius: 30,
            offset: Offset(0, -6),
          ),
        ],
      ),
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Handle
            Center(
              child: Container(
                width: 44,
                height: 4,
                margin: const EdgeInsets.only(bottom: 14),
                decoration: BoxDecoration(
                  color: Colors.white24,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),

            // Header
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'SELECT PAYMENT METHOD',
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w900,
                    fontSize: 13,
                    letterSpacing: 0.5,
                  ),
                ),
                GestureDetector(
                  onTap: () => Navigator.pop(context),
                  child: Container(
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.08),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.close, color: Colors.white70, size: 16),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // 1. Stripe (Cards & Apple Pay)
            GestureDetector(
              onTap: () => setState(() => _selectedMethod = 'stripe'),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: _selectedMethod == 'stripe'
                      ? const Color(0xFF6366F1).withOpacity(0.12)
                      : const Color(0xFF1E293B),
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(
                    color: _selectedMethod == 'stripe'
                        ? const Color(0xFF6366F1)
                        : Colors.white.withOpacity(0.08),
                    width: _selectedMethod == 'stripe' ? 2 : 1,
                  ),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          width: 42,
                          height: 42,
                          decoration: BoxDecoration(
                            color: const Color(0xFF6366F1),
                            borderRadius: BorderRadius.circular(12),
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xFF6366F1).withOpacity(0.35),
                                blurRadius: 8,
                                offset: const Offset(0, 3),
                              ),
                            ],
                          ),
                          child: const Center(
                            child: Text(
                              'S',
                              style: TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.w900,
                                fontSize: 24,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Wrap(
                            crossAxisAlignment: WrapCrossAlignment.center,
                            spacing: 8,
                            runSpacing: 4,
                            children: [
                              const Text(
                                'Stripe',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w900,
                                  fontSize: 15,
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                                decoration: BoxDecoration(
                                  color: const Color(0xFF6366F1).withOpacity(0.2),
                                  borderRadius: BorderRadius.circular(6),
                                  border: Border.all(color: const Color(0xFF6366F1).withOpacity(0.35)),
                                ),
                                child: const Text(
                                  '💳 CARDS & APPLE PAY',
                                  style: TextStyle(
                                    color: Color(0xFF818CF8),
                                    fontSize: 9.5,
                                    fontWeight: FontWeight.w900,
                                    letterSpacing: 0.3,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                        if (_selectedMethod == 'stripe')
                          const Icon(Icons.check_circle_rounded, color: Color(0xFF10B981), size: 22)
                        else
                          const Icon(Icons.radio_button_unchecked_rounded, color: Colors.white24, size: 20),
                      ],
                    ),
                    const SizedBox(height: 12),
                    // Crisp Brand Icons: VISA, Mastercard, Discover, Amex, Apple Pay
                    Wrap(
                      spacing: 7,
                      runSpacing: 6,
                      crossAxisAlignment: WrapCrossAlignment.center,
                      children: [
                        _buildBrandIcon('visa.png'),
                        _buildBrandIcon('mastercard.png'),
                        _buildBrandIcon('discover.png'),
                        _buildBrandIcon('amex.png'),
                        _buildBrandIcon('apple_pay.png'),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 12),

            // 2. MoMo Pay (Mobile Money)
            GestureDetector(
              onTap: () => setState(() => _selectedMethod = 'momo'),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: _selectedMethod == 'momo'
                      ? const Color(0xFFFFDC00).withOpacity(0.12)
                      : const Color(0xFF1E293B),
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(
                    color: _selectedMethod == 'momo'
                        ? const Color(0xFFFFDC00)
                        : Colors.white.withOpacity(0.08),
                    width: _selectedMethod == 'momo' ? 2 : 1,
                  ),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          width: 42,
                          height: 42,
                          decoration: BoxDecoration(
                            color: const Color(0xFFFFDC00),
                            shape: BoxShape.circle,
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xFFFFDC00).withOpacity(0.35),
                                blurRadius: 8,
                                offset: const Offset(0, 3),
                              ),
                            ],
                          ),
                          child: const Center(
                            child: Text(
                              'MoMo',
                              style: TextStyle(
                                color: Colors.black,
                                fontWeight: FontWeight.w900,
                                fontSize: 11,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Wrap(
                            crossAxisAlignment: WrapCrossAlignment.center,
                            spacing: 8,
                            runSpacing: 4,
                            children: [
                              const Text(
                                'MoMo Pay',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w900,
                                  fontSize: 15,
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFFFDC00).withOpacity(0.2),
                                  borderRadius: BorderRadius.circular(6),
                                  border: Border.all(color: const Color(0xFFFFDC00).withOpacity(0.35)),
                                ),
                                child: const Text(
                                  '📱 MOBILE MONEY',
                                  style: TextStyle(
                                    color: Color(0xFFFFDC00),
                                    fontSize: 9.5,
                                    fontWeight: FontWeight.w900,
                                    letterSpacing: 0.3,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                        if (_selectedMethod == 'momo')
                          const Icon(Icons.check_circle_rounded, color: Color(0xFFFFDC00), size: 22)
                        else
                          const Icon(Icons.chevron_right_rounded, color: Colors.white38, size: 20),
                      ],
                    ),
                    const SizedBox(height: 12),
                    // Crisp Brand Icons: MTN MoMo, Telecel, AirtelTigo
                    Wrap(
                      spacing: 7,
                      runSpacing: 6,
                      crossAxisAlignment: WrapCrossAlignment.center,
                      children: [
                        _buildBrandIcon('mtn_momo.png'),
                        _buildBrandIcon('telecel.png'),
                        _buildBrandIcon('airteltigo.png'),
                      ],
                    ),

                    if (_selectedMethod == 'momo') ...[
                      const SizedBox(height: 14),
                      Row(
                        children: [
                          Expanded(
                            flex: 2,
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10),
                              decoration: BoxDecoration(
                                color: const Color(0xFF0F172A),
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(color: Colors.white24),
                              ),
                              child: DropdownButtonHideUnderline(
                                child: DropdownButton<String>(
                                  value: _momoNetwork,
                                  dropdownColor: const Color(0xFF1E293B),
                                  style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                                  items: ['MTN', 'Telecel', 'AirtelTigo'].map((n) {
                                    return DropdownMenuItem(value: n, child: Text(n));
                                  }).toList(),
                                  onChanged: (val) {
                                    if (val != null) setState(() => _momoNetwork = val);
                                  },
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            flex: 3,
                            child: TextField(
                              controller: _momoPhoneController,
                              keyboardType: TextInputType.phone,
                              style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold),
                              decoration: InputDecoration(
                                hintText: 'MoMo Number',
                                hintStyle: const TextStyle(color: Colors.white38, fontSize: 11),
                                filled: true,
                                fillColor: const Color(0xFF0F172A),
                                isDense: true,
                                contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 11),
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(10),
                                  borderSide: const BorderSide(color: Colors.white24),
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ],
                ),
              ),
            ),
            if (widget.isCashAllowed) ...[
              const SizedBox(height: 12),
              // 3. Cash Direct Pay - Clean design, zero overflow
              GestureDetector(
                onTap: () => setState(() => _selectedMethod = 'cash'),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: _selectedMethod == 'cash'
                        ? const Color(0xFF10B981).withOpacity(0.12)
                        : const Color(0xFF1E293B),
                    borderRadius: BorderRadius.circular(18),
                    border: Border.all(
                      color: _selectedMethod == 'cash'
                          ? const Color(0xFF10B981)
                          : Colors.white.withOpacity(0.08),
                      width: _selectedMethod == 'cash' ? 2 : 1,
                    ),
                  ),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(
                        width: 42,
                        height: 42,
                        decoration: BoxDecoration(
                          color: const Color(0xFF10B981).withOpacity(0.18),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: const Color(0xFF10B981).withOpacity(0.35)),
                        ),
                        child: const Center(
                          child: Icon(Icons.payments_rounded, color: Color(0xFF10B981), size: 24),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Wrap(
                              crossAxisAlignment: WrapCrossAlignment.center,
                              spacing: 8,
                              runSpacing: 4,
                              children: [
                                const Text(
                                  'Cash Direct Pay',
                                  style: TextStyle(
                                    color: Colors.white,
                                    fontWeight: FontWeight.w900,
                                    fontSize: 15,
                                  ),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFF10B981).withOpacity(0.18),
                                    borderRadius: BorderRadius.circular(6),
                                    border: Border.all(color: const Color(0xFF10B981).withOpacity(0.3)),
                                  ),
                                  child: Text(
                                    widget.serviceType == 'rental'
                                        ? 'PAY ON PICK-UP'
                                        : (widget.serviceType == 'delivery'
                                            ? 'PAY ON DELIVERY'
                                            : 'PAY ON DROP-OFF'),
                                    style: const TextStyle(
                                      color: Color(0xFF34D399),
                                      fontSize: 9.5,
                                      fontWeight: FontWeight.w900,
                                      letterSpacing: 0.3,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 4),
                            Text(
                              widget.serviceType == 'rental'
                                  ? 'Pay rental partner physical cash upon picking up vehicle'
                                  : (widget.serviceType == 'delivery'
                                      ? 'Pay courier physical cash upon package drop-off'
                                      : (widget.serviceType == 'driver'
                                          ? 'Pay personal chauffeur physical cash upon trip completion'
                                          : 'Pay driver physical cash upon reaching destination (No upfront hold)')),
                              style: const TextStyle(
                                color: AppColors.textMuted,
                                fontSize: 11.5,
                                height: 1.3,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 8),
                      if (_selectedMethod == 'cash')
                        const Icon(Icons.check_circle_rounded, color: Color(0xFF10B981), size: 22)
                      else
                        const Icon(Icons.radio_button_unchecked_rounded, color: Colors.white24, size: 20),
                    ],
                  ),
                ),
              ),
            ],

            if (widget.showWallet) ...[
              const SizedBox(height: 12),
              // 4. RideMyCars In-App Wallet
              GestureDetector(
                onTap: () => setState(() => _selectedMethod = 'wallet'),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: _selectedMethod == 'wallet'
                        ? const Color(0xFFF59E0B).withOpacity(0.12)
                        : const Color(0xFF1E293B),
                    borderRadius: BorderRadius.circular(18),
                    border: Border.all(
                      color: _selectedMethod == 'wallet'
                          ? const Color(0xFFF59E0B)
                          : Colors.white.withOpacity(0.08),
                      width: _selectedMethod == 'wallet' ? 2 : 1,
                    ),
                  ),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(
                        width: 42,
                        height: 42,
                        decoration: BoxDecoration(
                          color: const Color(0xFFF59E0B).withOpacity(0.18),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: const Color(0xFFF59E0B).withOpacity(0.35)),
                        ),
                        child: const Center(
                          child: Icon(Icons.account_balance_wallet_rounded, color: Color(0xFFF59E0B), size: 24),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Wrap(
                              crossAxisAlignment: WrapCrossAlignment.center,
                              spacing: 8,
                              runSpacing: 4,
                              children: [
                                const Text(
                                  'RideMyCars Wallet',
                                  style: TextStyle(
                                    color: Colors.white,
                                    fontWeight: FontWeight.w900,
                                    fontSize: 15,
                                  ),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFF59E0B).withOpacity(0.18),
                                    borderRadius: BorderRadius.circular(6),
                                    border: Border.all(color: const Color(0xFFF59E0B).withOpacity(0.3)),
                                  ),
                                  child: const Text(
                                    'IN-APP BALANCE',
                                    style: TextStyle(
                                      color: Color(0xFFFBBF24),
                                      fontSize: 9.5,
                                      fontWeight: FontWeight.w900,
                                      letterSpacing: 0.3,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 4),
                            const Text(
                              'Instantly debit from your verified RideMyCars in-app wallet balance',
                              style: TextStyle(
                                color: AppColors.textMuted,
                                fontSize: 11.5,
                                height: 1.3,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 8),
                      if (_selectedMethod == 'wallet')
                        const Icon(Icons.check_circle_rounded, color: Color(0xFFF59E0B), size: 22)
                      else
                        const Icon(Icons.radio_button_unchecked_rounded, color: Colors.white24, size: 20),
                    ],
                  ),
                ),
              ),
            ],
            const SizedBox(height: 18),

            // Done Button: Yellow with Bold Black Text
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                onPressed: () {
                  String finalMethod = _selectedMethod;
                  if (!widget.isCashAllowed && finalMethod == 'cash') {
                    finalMethod = 'stripe';
                  }
                  if (!widget.showWallet && finalMethod == 'wallet') {
                    finalMethod = 'stripe';
                  }
                  Navigator.pop(
                    context,
                    RidePaymentSelection(
                      method: finalMethod,
                      momoPhone: _momoPhoneController.text.trim().isNotEmpty
                          ? _momoPhoneController.text.trim()
                          : null,
                      momoNetwork: _momoNetwork,
                    ),
                  );
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFFFFDC00),
                  foregroundColor: Colors.black,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                  ),
                  elevation: 4,
                ),
                child: const Text(
                  'Done',
                  style: TextStyle(
                    color: Colors.black,
                    fontWeight: FontWeight.w900,
                    fontSize: 16,
                    letterSpacing: 0.3,
                  ),
                ),
              ),
            ),
            const SizedBox(height: 10),
          ],
        ),
      ),
    );
  }
}
