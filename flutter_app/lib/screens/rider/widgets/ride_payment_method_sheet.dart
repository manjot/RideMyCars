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

  const RidePaymentMethodSheet({
    super.key,
    required this.currentMethod,
    this.currentMomoPhone,
    this.currentMomoNetwork,
  });

  static Future<RidePaymentSelection?> show(
    BuildContext context, {
    String currentMethod = 'stripe',
    String? currentMomoPhone,
    String? currentMomoNetwork,
  }) {
    return showModalBottomSheet<RidePaymentSelection>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => RidePaymentMethodSheet(
        currentMethod: currentMethod,
        currentMomoPhone: currentMomoPhone,
        currentMomoNetwork: currentMomoNetwork,
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
    _momoPhoneController = TextEditingController(text: widget.currentMomoPhone ?? '');
    _momoNetwork = widget.currentMomoNetwork ?? 'MTN';
  }

  @override
  void dispose() {
    _momoPhoneController.dispose();
    super.dispose();
  }

  Widget _buildBrandChip(String text, {Color? bg, Color? fg}) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
      decoration: BoxDecoration(
        color: bg ?? Colors.white.withOpacity(0.08),
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: Colors.white12),
      ),
      child: Text(
        text,
        style: TextStyle(
          color: fg ?? Colors.white,
          fontSize: 10,
          fontWeight: FontWeight.w900,
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
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

            // 1. Stripe (Cards & Apple Pay) - Screenshot 3 Style
            GestureDetector(
              onTap: () => setState(() => _selectedMethod = 'stripe'),
              child: Container(
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
                          width: 40,
                          height: 40,
                          decoration: BoxDecoration(
                            color: const Color(0xFF6366F1),
                            borderRadius: BorderRadius.circular(12),
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
                          child: Row(
                            children: [
                              const Text(
                                'Stripe',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w900,
                                  fontSize: 15,
                                ),
                              ),
                              const SizedBox(width: 8),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                decoration: BoxDecoration(
                                  color: const Color(0xFF6366F1).withOpacity(0.2),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: const Text(
                                  '💳 CARDS & APPLE PAY',
                                  style: TextStyle(
                                    color: Color(0xFF818CF8),
                                    fontSize: 9.5,
                                    fontWeight: FontWeight.w900,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                        if (_selectedMethod == 'stripe')
                          const Icon(Icons.check_circle_rounded, color: Color(0xFF10B981), size: 22),
                      ],
                    ),
                    const SizedBox(height: 10),
                    // Badges row: VISA, Mastercard, Discover, Amex, Apple Pay
                    Wrap(
                      spacing: 6,
                      runSpacing: 4,
                      children: [
                        _buildBrandChip('VISA', bg: const Color(0xFF1E3A8A), fg: Colors.white),
                        _buildBrandChip('Mastercard', bg: const Color(0xFFDC2626).withOpacity(0.3), fg: const Color(0xFFF97316)),
                        _buildBrandChip('DISCOVER', bg: const Color(0xFFEA580C).withOpacity(0.3), fg: const Color(0xFFFB923C)),
                        _buildBrandChip('AMEX', bg: const Color(0xFF0284C7).withOpacity(0.3), fg: const Color(0xFF38BDF8)),
                        _buildBrandChip(' Pay', bg: Colors.black45, fg: Colors.white),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 12),

            // 2. MoMo Pay (Mobile Money) - Screenshot 3 Style
            GestureDetector(
              onTap: () => setState(() => _selectedMethod = 'momo'),
              child: Container(
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
                          width: 40,
                          height: 40,
                          decoration: BoxDecoration(
                            color: const Color(0xFFFFDC00),
                            borderRadius: BorderRadius.circular(12),
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
                          child: Row(
                            children: [
                              const Text(
                                'MoMo Pay',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w900,
                                  fontSize: 15,
                                ),
                              ),
                              const SizedBox(width: 8),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFFFDC00).withOpacity(0.2),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: const Text(
                                  '📱 MOBILE MONEY',
                                  style: TextStyle(
                                    color: Color(0xFFFFDC00),
                                    fontSize: 9.5,
                                    fontWeight: FontWeight.w900,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                        if (_selectedMethod == 'momo')
                          const Icon(Icons.check_circle_rounded, color: Color(0xFFFFDC00), size: 22)
                        else
                          const Icon(Icons.chevron_right, color: Colors.white38, size: 20),
                      ],
                    ),
                    const SizedBox(height: 10),
                    // Badges: MTN MoMo, Telecel, AirtelTigo
                    Wrap(
                      spacing: 6,
                      runSpacing: 4,
                      children: [
                        _buildBrandChip('⚡ MTN MoMo', bg: const Color(0xFFFFDC00).withOpacity(0.2), fg: const Color(0xFFFFDC00)),
                        _buildBrandChip('🔴 Telecel Cash', bg: const Color(0xFFEF4444).withOpacity(0.2), fg: const Color(0xFFFCA5A5)),
                        _buildBrandChip('🔵 AirtelTigo', bg: const Color(0xFF3B82F6).withOpacity(0.2), fg: const Color(0xFF93C5FD)),
                      ],
                    ),

                    if (_selectedMethod == 'momo') ...[
                      const SizedBox(height: 12),
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
            const SizedBox(height: 12),

            // 3. Cash Direct Pay - Screenshot 3 Style
            GestureDetector(
              onTap: () => setState(() => _selectedMethod = 'cash'),
              child: Container(
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
                      width: 40,
                      height: 40,
                      decoration: BoxDecoration(
                        color: const Color(0xFF10B981).withOpacity(0.2),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Center(
                        child: Text('💵', style: TextStyle(fontSize: 20)),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Text(
                                'Cash Direct Pay',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w900,
                                  fontSize: 15,
                                ),
                              ),
                              const SizedBox(width: 8),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                decoration: BoxDecoration(
                                  color: const Color(0xFF10B981).withOpacity(0.2),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: const Text(
                                  'PAY ON DROP-OFF',
                                  style: TextStyle(
                                    color: Color(0xFF34D399),
                                    fontSize: 9.5,
                                    fontWeight: FontWeight.w900,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 3),
                          const Text(
                            'Pay driver physical cash upon reaching destination (No upfront hold)',
                            style: TextStyle(
                              color: AppColors.textMuted,
                              fontSize: 11.5,
                            ),
                          ),
                        ],
                      ),
                    ),
                    if (_selectedMethod == 'cash')
                      const Icon(Icons.check_circle_rounded, color: Color(0xFF10B981), size: 22),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 18),

            // Done Button: Yellow with Bold Black Text
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                onPressed: () {
                  Navigator.pop(
                    context,
                    RidePaymentSelection(
                      method: _selectedMethod,
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
