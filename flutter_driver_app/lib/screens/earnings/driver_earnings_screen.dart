import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/driver_provider.dart';
import '../../services/wallet_service.dart';
import 'incentives_screen.dart';

class DriverEarningsScreen extends StatefulWidget {
  const DriverEarningsScreen({super.key});

  @override
  State<DriverEarningsScreen> createState() => _DriverEarningsScreenState();
}

class _DriverEarningsScreenState extends State<DriverEarningsScreen> {
  bool _isLoadingWallet = false;
  double _walletBalance = 215.95;
  double _pendingWithdrawals = 0.0;
  String _currency = '₹';
  double _minWithdrawal = 50.0;
  double _maxWithdrawal = 10000.0;
  String _withdrawalFeeType = 'fixed';
  double _withdrawalFee = 0.0;
  bool _withdrawalsEnabled = true;
  List<String> _allowedPayoutMethods = ['bank', 'momo'];

  List<dynamic> _withdrawals = [];
  List<dynamic> _payoutMethods = [];
  String _selectedStatus = 'all';

  @override
  void initState() {
    super.initState();
    _fetchData();
  }

  Future<void> _fetchData() async {
    setState(() => _isLoadingWallet = true);
    try {
      final driver = Provider.of<DriverProvider>(context, listen: false);
      await driver.fetchEarnings();

      final bal = await WalletService.getBalance();
      if (bal != null) {
        _walletBalance = (bal['wallet_balance'] as num?)?.toDouble() ?? 215.95;
        _pendingWithdrawals = (bal['pending_withdrawals_sum'] as num?)?.toDouble() ?? 0.0;
        _currency = bal['currency']?.toString() ?? '₹';
        final settings = bal['settings'] as Map<String, dynamic>?;
        if (settings != null) {
          _minWithdrawal = (settings['min_withdrawal'] as num?)?.toDouble() ?? 50.0;
          _maxWithdrawal = (settings['max_withdrawal'] as num?)?.toDouble() ?? 10000.0;
          _withdrawalFeeType = settings['withdrawal_fee_type']?.toString() ?? 'fixed';
          _withdrawalFee = (settings['withdrawal_fee'] as num?)?.toDouble() ?? 0.0;
          _withdrawalsEnabled = settings['withdrawals_enabled'] == true || settings['withdrawals_enabled'] == 1 || settings['withdrawals_enabled'] == '1';
          if (settings['allowed_payout_methods'] is List) {
            _allowedPayoutMethods = List<String>.from(settings['allowed_payout_methods']);
          }
        }
      }

      final wData = await WalletService.getWithdrawalHistory(status: _selectedStatus);
      if (wData['success'] == true && wData['data'] is List) {
        _withdrawals = List<dynamic>.from(wData['data']);
      }

      _payoutMethods = await WalletService.getPayoutMethods();
    } catch (e) {
      debugPrint('Error fetching driver earnings: $e');
    } finally {
      if (mounted) setState(() => _isLoadingWallet = false);
    }
  }

  Future<void> _filterWithdrawals(String status) async {
    setState(() {
      _selectedStatus = status;
      _isLoadingWallet = true;
    });
    try {
      final res = await WalletService.getWithdrawalHistory(status: status);
      if (res['success'] == true && res['data'] is List) {
        setState(() {
          _withdrawals = List<dynamic>.from(res['data']);
        });
      }
    } catch (e) {
      debugPrint('Error filtering withdrawals: $e');
    } finally {
      if (mounted) setState(() => _isLoadingWallet = false);
    }
  }

  void _openWithdrawModal() {
    if (!_withdrawalsEnabled) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Withdrawals are temporarily paused by administration.'),
          backgroundColor: AppColors.warning,
        ),
      );
      return;
    }

    if (_walletBalance < _minWithdrawal) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Minimum withdrawal amount is $_currency${_minWithdrawal.toStringAsFixed(2)}. Your balance is $_currency${_walletBalance.toStringAsFixed(2)}.'),
          backgroundColor: AppColors.danger,
        ),
      );
      return;
    }

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _DriverWithdrawModal(
        balance: _walletBalance,
        currency: _currency,
        minWithdrawal: _minWithdrawal,
        maxWithdrawal: _maxWithdrawal,
        withdrawalFee: _withdrawalFee,
        withdrawalFeeType: _withdrawalFeeType,
        allowedMethods: _allowedPayoutMethods,
        savedPayoutMethods: _payoutMethods,
        onSuccess: () {
          _fetchData();
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final driver = Provider.of<DriverProvider>(context);
    final today = (driver.earnings['today'] as num?)?.toDouble() ?? 0.0;
    final week = (driver.earnings['week'] as num?)?.toDouble() ?? 0.0;
    final month = (driver.earnings['month'] as num?)?.toDouble() ?? 0.0;
    final totalTrips = driver.earnings['total_trips'] ?? 0;

    return Scaffold(
      backgroundColor: AppColors.backgroundDark,
      appBar: AppBar(
        backgroundColor: const Color(0xFFF97316),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Wallet & Payouts',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 18),
        ),
        centerTitle: true,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: Colors.white),
            onPressed: _fetchData,
          ),
        ],
      ),
      body: RefreshIndicator(
        color: const Color(0xFFF97316),
        onRefresh: _fetchData,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Orange Hero Balance Card (Matching Reference Screenshot 1 & 2)
              _buildHeroBalanceCard(),

              const SizedBox(height: 18),

              // Incentive Program Navigation Card
              InkWell(
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const IncentivesScreen()),
                  );
                },
                borderRadius: BorderRadius.circular(20),
                child: Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFF1E1B4B), Color(0xFF312E81)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: AppColors.primary.withOpacity(0.4), width: 1.2),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.2),
                        blurRadius: 12,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: AppColors.primary.withOpacity(0.15),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Icon(Icons.card_giftcard_rounded, color: AppColors.primary, size: 24),
                      ),
                      const SizedBox(width: 12),
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Incentive Program',
                              style: TextStyle(
                                color: AppColors.textLight,
                                fontWeight: FontWeight.w900,
                                fontSize: 15,
                              ),
                            ),
                            SizedBox(height: 2),
                            Text(
                              'Daily, Weekly & Monthly Ride Cash Bonuses',
                              style: TextStyle(
                                color: AppColors.textMuted,
                                fontSize: 11,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const Icon(Icons.arrow_forward_ios_rounded, color: AppColors.primary, size: 14),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 24),

              // Earnings Summary Section
              const Text('Earnings Summary', style: TextStyle(color: AppColors.textLight, fontSize: 16, fontWeight: FontWeight.w800)),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(child: _buildPeriodTile('TODAY', '$_currency${today.toStringAsFixed(2)}', AppColors.success)),
                  const SizedBox(width: 12),
                  Expanded(child: _buildPeriodTile('THIS WEEK', '$_currency${week.toStringAsFixed(2)}', AppColors.info)),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(child: _buildPeriodTile('THIS MONTH', '$_currency${month.toStringAsFixed(2)}', AppColors.purple)),
                  const SizedBox(width: 12),
                  Expanded(child: _buildPeriodTile('TOTAL TRIPS', '$totalTrips', AppColors.primary)),
                ],
              ),

              const SizedBox(height: 28),

              // Recent Withdrawals Section (Matching Reference 2)
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Recent Withdrawals',
                    style: TextStyle(color: AppColors.textLight, fontSize: 16, fontWeight: FontWeight.w800),
                  ),
                  TextButton.icon(
                    onPressed: _openWithdrawModal,
                    icon: const Icon(Icons.add_circle_outline_rounded, size: 16, color: Color(0xFFF97316)),
                    label: const Text('New Request', style: TextStyle(color: Color(0xFFF97316), fontWeight: FontWeight.bold, fontSize: 13)),
                  ),
                ],
              ),
              const SizedBox(height: 8),

              // Status filters
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: [
                    _buildFilterChip('all', 'All'),
                    const SizedBox(width: 8),
                    _buildFilterChip('pending', 'Pending'),
                    const SizedBox(width: 8),
                    _buildFilterChip('approved', 'Approved'),
                    const SizedBox(width: 8),
                    _buildFilterChip('rejected', 'Rejected'),
                  ],
                ),
              ),

              const SizedBox(height: 14),

              if (_isLoadingWallet)
                const Center(child: Padding(padding: EdgeInsets.all(30), child: CircularProgressIndicator(color: Color(0xFFF97316))))
              else if (_withdrawals.isEmpty)
                _buildEmptyState()
              else
                ListView.separated(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: _withdrawals.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 10),
                  itemBuilder: (ctx, idx) => _buildWithdrawalTile(_withdrawals[idx]),
                ),

              const SizedBox(height: 20),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeroBalanceCard() {
    return Container(
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFFEA580C), Color(0xFFF97316)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(22),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFFEA580C).withOpacity(0.35),
            blurRadius: 18,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 20),
        child: Column(
          children: [
            const Text(
              'Wallet Balance',
              style: TextStyle(
                color: Colors.white,
                fontSize: 16,
                fontWeight: FontWeight.w600,
                letterSpacing: 0.3,
              ),
            ),
            const SizedBox(height: 8),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.baseline,
              textBaseline: TextBaseline.alphabetic,
              children: [
                Text(
                  _walletBalance.toStringAsFixed(2),
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 38,
                    fontWeight: FontWeight.w900,
                    letterSpacing: -0.5,
                  ),
                ),
                const SizedBox(width: 6),
                Text(
                  _currency,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 26,
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ],
            ),
            if (_pendingWithdrawals > 0) ...[
              const SizedBox(height: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                decoration: BoxDecoration(
                  color: Colors.black.withOpacity(0.2),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  'Pending Review: $_currency${_pendingWithdrawals.toStringAsFixed(2)}',
                  style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w600),
                ),
              ),
            ],
            const SizedBox(height: 18),
            const Divider(color: Colors.white24, height: 1),
            const SizedBox(height: 14),

            // Action Buttons: Add Money (+) | Withdraw (↓)
            Row(
              children: [
                Expanded(
                  child: InkWell(
                    onTap: () {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Instant wallet top-up gateway ready.')),
                      );
                    },
                    borderRadius: BorderRadius.circular(12),
                    child: const Padding(
                      padding: EdgeInsets.symmetric(vertical: 8),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text('Add Money', style: TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.bold)),
                          SizedBox(width: 8),
                          Icon(Icons.add_circle_outline_rounded, color: Colors.white, size: 20),
                        ],
                      ),
                    ),
                  ),
                ),
                Container(height: 24, width: 1, color: Colors.white24),
                Expanded(
                  child: InkWell(
                    onTap: _openWithdrawModal,
                    borderRadius: BorderRadius.circular(12),
                    child: const Padding(
                      padding: EdgeInsets.symmetric(vertical: 8),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text('Withdraw', style: TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.bold)),
                          SizedBox(width: 8),
                          Icon(Icons.arrow_circle_down_rounded, color: Colors.white, size: 22),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFilterChip(String val, String label) {
    final isSelected = _selectedStatus == val;
    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      onSelected: (_) => _filterWithdrawals(val),
      selectedColor: const Color(0xFFF97316),
      backgroundColor: AppColors.surfaceDark,
      labelStyle: TextStyle(
        color: isSelected ? Colors.white : AppColors.textMuted,
        fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
        fontSize: 12,
      ),
      side: BorderSide(color: isSelected ? const Color(0xFFF97316) : Colors.white10),
    );
  }

  Widget _buildEmptyState() {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 36, horizontal: 20),
      alignment: Alignment.center,
      child: Column(
        children: [
          Container(
            width: 120,
            height: 120,
            decoration: BoxDecoration(
              color: const Color(0xFFF97316).withOpacity(0.08),
              shape: BoxShape.circle,
            ),
            child: const Center(
              child: Icon(Icons.credit_card_rounded, size: 60, color: Color(0xFFF97316)),
            ),
          ),
          const SizedBox(height: 18),
          const Text('No payment history yet.', style: TextStyle(color: AppColors.textLight, fontSize: 16, fontWeight: FontWeight.w700)),
          const SizedBox(height: 6),
          const Text('Complete trips and request withdrawals directly to your account.', style: TextStyle(color: AppColors.textMuted, fontSize: 13), textAlign: TextAlign.center),
          const SizedBox(height: 18),
          ElevatedButton(
            onPressed: _openWithdrawModal,
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFF97316),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
            ),
            child: const Text('Request Withdraw', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  Widget _buildWithdrawalTile(dynamic item) {
    final ref = item['reference_id'] ?? 'WTH-${item['id']}';
    final amount = (item['amount'] as num?)?.toDouble() ?? 0.0;
    final status = (item['status']?.toString() ?? 'pending').toLowerCase();
    final method = item['payout_method']?.toString() ?? 'bank';
    final date = item['formatted_created_at'] ?? item['created_at'] ?? '';
    final reason = item['rejection_reason']?.toString();

    Color statusColor = AppColors.warning;
    String statusText = 'Pending';
    IconData icon = Icons.schedule_rounded;

    if (status == 'approved') {
      statusColor = AppColors.success;
      statusText = 'Approved';
      icon = Icons.check_circle_rounded;
    } else if (status == 'rejected') {
      statusColor = AppColors.danger;
      statusText = 'Rejected';
      icon = Icons.cancel_rounded;
    }

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: statusColor.withOpacity(0.3)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(ref, style: const TextStyle(color: AppColors.textMuted, fontSize: 12, fontWeight: FontWeight.bold)),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: statusColor.withOpacity(0.15),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Row(
                  children: [
                    Icon(icon, color: statusColor, size: 12),
                    const SizedBox(width: 4),
                    Text(statusText, style: TextStyle(color: statusColor, fontSize: 11, fontWeight: FontWeight.bold)),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('$_currency${amount.toStringAsFixed(2)}', style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w900)),
              Text(method == 'momo' ? 'Mobile Money' : 'Bank Transfer', style: const TextStyle(color: AppColors.textLight, fontSize: 12, fontWeight: FontWeight.w600)),
            ],
          ),
          const SizedBox(height: 6),
          Text('Requested: $date', style: const TextStyle(color: AppColors.textMuted, fontSize: 11)),
          if (status == 'rejected' && reason != null && reason.isNotEmpty) ...[
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: AppColors.danger.withOpacity(0.1),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: AppColors.danger.withOpacity(0.3)),
              ),
              child: Text('Reason: $reason', style: const TextStyle(color: AppColors.danger, fontSize: 11)),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildPeriodTile(String label, String value, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white.withOpacity(0.06)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: const TextStyle(color: AppColors.textMuted, fontSize: 10, fontWeight: FontWeight.w800, letterSpacing: 0.5)),
          const SizedBox(height: 6),
          Text(value, style: TextStyle(color: color, fontSize: 22, fontWeight: FontWeight.w900)),
        ],
      ),
    );
  }
}

/// Driver Withdrawal Modal matching Reference 3
class _DriverWithdrawModal extends StatefulWidget {
  final double balance;
  final String currency;
  final double minWithdrawal;
  final double maxWithdrawal;
  final double withdrawalFee;
  final String withdrawalFeeType;
  final List<String> allowedMethods;
  final List<dynamic> savedPayoutMethods;
  final VoidCallback onSuccess;

  const _DriverWithdrawModal({
    required this.balance,
    required this.currency,
    required this.minWithdrawal,
    required this.maxWithdrawal,
    required this.withdrawalFee,
    required this.withdrawalFeeType,
    required this.allowedMethods,
    required this.savedPayoutMethods,
    required this.onSuccess,
  });

  @override
  State<_DriverWithdrawModal> createState() => _DriverWithdrawModalState();
}

class _DriverWithdrawModalState extends State<_DriverWithdrawModal> {
  final _amountController = TextEditingController();
  final _bankNameController = TextEditingController();
  final _accountNumberController = TextEditingController();
  final _routingCodeController = TextEditingController();
  final _holderNameController = TextEditingController();

  final _momoNetworkController = TextEditingController(text: 'MTN');
  final _momoPhoneController = TextEditingController();
  final _momoNameController = TextEditingController();

  String _payoutMethod = 'bank';
  bool _savePayoutMethod = true;
  bool _isSubmitting = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    if (!widget.allowedMethods.contains('bank') && widget.allowedMethods.contains('momo')) {
      _payoutMethod = 'momo';
    }

    if (widget.savedPayoutMethods.isNotEmpty) {
      final defaultMethod = widget.savedPayoutMethods.firstWhere(
        (m) => m['is_default'] == true,
        orElse: () => widget.savedPayoutMethods.first,
      );
      if (defaultMethod != null) {
        final type = defaultMethod['type']?.toString() ?? 'bank';
        _payoutMethod = type;
        if (type == 'bank') {
          _bankNameController.text = defaultMethod['bank_name'] ?? '';
          _accountNumberController.text = defaultMethod['account_number'] ?? '';
          _routingCodeController.text = defaultMethod['routing_code'] ?? '';
          _holderNameController.text = defaultMethod['holder_name'] ?? '';
        } else {
          _momoNetworkController.text = defaultMethod['network'] ?? 'MTN';
          _momoPhoneController.text = defaultMethod['phone_number'] ?? '';
          _momoNameController.text = defaultMethod['account_name'] ?? '';
        }
      }
    }
  }

  @override
  void dispose() {
    _amountController.dispose();
    _bankNameController.dispose();
    _accountNumberController.dispose();
    _routingCodeController.dispose();
    _holderNameController.dispose();
    _momoPhoneController.dispose();
    _momoNameController.dispose();
    super.dispose();
  }

  void _setAmount(double val) {
    if (val > widget.balance) val = widget.balance;
    _amountController.text = val.toStringAsFixed(1);
    setState(() => _errorMessage = null);
  }

  double get _enteredAmount => double.tryParse(_amountController.text.trim()) ?? 0.0;

  Future<void> _submit() async {
    setState(() => _errorMessage = null);
    final amt = _enteredAmount;

    if (amt <= 0) {
      setState(() => _errorMessage = 'Please enter a valid withdrawal amount.');
      return;
    }
    if (amt < widget.minWithdrawal) {
      setState(() => _errorMessage = 'Amount must be at least ${widget.currency}${widget.minWithdrawal.toStringAsFixed(2)}.');
      return;
    }
    if (amt > widget.balance) {
      setState(() => _errorMessage = 'Amount cannot exceed available balance (${widget.currency}${widget.balance.toStringAsFixed(2)}).');
      return;
    }

    Map<String, dynamic> payoutDetails = {};
    if (_payoutMethod == 'bank') {
      if (_bankNameController.text.trim().isEmpty || _accountNumberController.text.trim().isEmpty || _holderNameController.text.trim().isEmpty) {
        setState(() => _errorMessage = 'Please complete Bank Name, Account Number, and Account Holder Name.');
        return;
      }
      payoutDetails = {
        'bank_name': _bankNameController.text.trim(),
        'account_number': _accountNumberController.text.trim(),
        'routing_code': _routingCodeController.text.trim(),
        'holder_name': _holderNameController.text.trim(),
      };
    } else {
      if (_momoPhoneController.text.trim().isEmpty || _momoNameController.text.trim().isEmpty) {
        setState(() => _errorMessage = 'Please provide Mobile Money Phone Number and Account Name.');
        return;
      }
      payoutDetails = {
        'network': _momoNetworkController.text.trim(),
        'phone_number': _momoPhoneController.text.trim(),
        'account_name': _momoNameController.text.trim(),
      };
    }

    setState(() => _isSubmitting = true);
    final res = await WalletService.submitWithdrawal(
      amount: amt,
      payoutMethod: _payoutMethod,
      payoutDetails: payoutDetails,
      savePayoutMethod: _savePayoutMethod,
    );
    setState(() => _isSubmitting = false);

    if (res['success'] == true) {
      if (!mounted) return;
      Navigator.pop(context);
      widget.onSuccess();

      showDialog(
        context: context,
        builder: (ctx) => AlertDialog(
          backgroundColor: AppColors.surfaceDark,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: const Row(
            children: [
              Icon(Icons.check_circle_rounded, color: AppColors.success, size: 28),
              SizedBox(width: 10),
              Text('Request Submitted', style: TextStyle(color: AppColors.textLight, fontSize: 18, fontWeight: FontWeight.bold)),
            ],
          ),
          content: Text(
            'Your withdrawal request of ${widget.currency}${amt.toStringAsFixed(2)} has been submitted for approval.\n\nYour wallet balance remains unchanged until admin approval.',
            style: const TextStyle(color: AppColors.textMuted, fontSize: 13, height: 1.5),
          ),
          actions: [
            ElevatedButton(
              onPressed: () => Navigator.pop(ctx),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFFF97316),
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              child: const Text('OK', style: TextStyle(fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      );
    } else {
      setState(() {
        _errorMessage = res['message'] ?? 'Failed to submit withdrawal request.';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
      ),
      padding: EdgeInsets.only(
        top: 14,
        left: 20,
        right: 20,
        bottom: MediaQuery.of(context).viewInsets.bottom + 20,
      ),
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Center(
              child: Container(
                width: 44,
                height: 5,
                decoration: BoxDecoration(color: Colors.white24, borderRadius: BorderRadius.circular(10)),
              ),
            ),
            const SizedBox(height: 18),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Request Withdrawal', style: TextStyle(color: AppColors.textLight, fontSize: 18, fontWeight: FontWeight.w800)),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF97316).withOpacity(0.15),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    'Available: ${widget.currency}${widget.balance.toStringAsFixed(2)}',
                    style: const TextStyle(color: Color(0xFFF97316), fontSize: 12, fontWeight: FontWeight.bold),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 20),

            // Amount Input
            Container(
              decoration: BoxDecoration(
                color: AppColors.backgroundDark,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: Colors.white24, width: 1.2),
              ),
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
              child: Row(
                children: [
                  Text(widget.currency, style: const TextStyle(color: AppColors.textLight, fontSize: 20, fontWeight: FontWeight.bold)),
                  const SizedBox(width: 12),
                  Expanded(
                    child: TextField(
                      controller: _amountController,
                      keyboardType: const TextInputType.numberWithOptions(decimal: true),
                      style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w800),
                      decoration: const InputDecoration(
                        hintText: 'Enter Amount Here',
                        hintStyle: TextStyle(color: AppColors.textMuted, fontSize: 16, fontWeight: FontWeight.w500),
                        border: InputBorder.none,
                      ),
                      onChanged: (_) => setState(() => _errorMessage = null),
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 14),

            // Preset Quick Amount Chips (Reference Screenshot 3: ₹ 50.0, ₹ 100.0, ₹ 150.0)
            Row(
              children: [
                Expanded(child: _buildChip(50.0)),
                const SizedBox(width: 8),
                Expanded(child: _buildChip(100.0)),
                const SizedBox(width: 8),
                Expanded(child: _buildChip(150.0)),
                const SizedBox(width: 8),
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => _setAmount(widget.balance),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: const Color(0xFFF97316),
                      side: const BorderSide(color: Color(0xFFF97316)),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      padding: const EdgeInsets.symmetric(vertical: 10),
                    ),
                    child: const Text('Max', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 20),

            // Payout Method Selector
            Row(
              children: [
                if (widget.allowedMethods.contains('bank'))
                  Expanded(
                    child: _buildMethodRadio('bank', 'Bank Account', Icons.account_balance_rounded),
                  ),
                if (widget.allowedMethods.contains('bank') && widget.allowedMethods.contains('momo'))
                  const SizedBox(width: 10),
                if (widget.allowedMethods.contains('momo'))
                  Expanded(
                    child: _buildMethodRadio('momo', 'Mobile Money', Icons.phone_android_rounded),
                  ),
              ],
            ),

            const SizedBox(height: 16),

            if (_payoutMethod == 'bank') ...[
              _buildField('Bank Name', _bankNameController, 'e.g. HDFC / Chase'),
              const SizedBox(height: 10),
              _buildField('Account Number / IBAN', _accountNumberController, 'e.g. 501002348910', isNumber: true),
              const SizedBox(height: 10),
              Row(
                children: [
                  Expanded(child: _buildField('Routing / IFSC Code', _routingCodeController, 'e.g. HDFC0001234')),
                  const SizedBox(width: 10),
                  Expanded(child: _buildField('Account Holder Name', _holderNameController, 'e.g. John Doe')),
                ],
              ),
            ] else ...[
              _buildField('Network Provider', _momoNetworkController, 'e.g. MTN / Airtel / Vodafone'),
              const SizedBox(height: 10),
              _buildField('MoMo Mobile Number', _momoPhoneController, 'e.g. +91 9876543210', isNumber: true),
              const SizedBox(height: 10),
              _buildField('Registered Account Name', _momoNameController, 'e.g. John Doe'),
            ],

            const SizedBox(height: 12),

            Row(
              children: [
                Checkbox(
                  value: _savePayoutMethod,
                  activeColor: const Color(0xFFF97316),
                  onChanged: (v) => setState(() => _savePayoutMethod = v ?? false),
                ),
                const Expanded(
                  child: Text('Save payout details for future withdrawals', style: TextStyle(color: AppColors.textMuted, fontSize: 12)),
                ),
              ],
            ),

            if (_errorMessage != null) ...[
              const SizedBox(height: 10),
              Text(_errorMessage!, style: const TextStyle(color: AppColors.danger, fontSize: 12, fontWeight: FontWeight.bold)),
            ],

            const SizedBox(height: 18),

            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: _isSubmitting ? null : () => Navigator.pop(context),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppColors.textLight,
                      side: const BorderSide(color: Colors.white24),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                    child: const Text('Cancel', style: TextStyle(fontWeight: FontWeight.w700)),
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: ElevatedButton(
                    onPressed: _isSubmitting ? null : _submit,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFF97316),
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                    child: _isSubmitting
                        ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                        : const Text('Withdraw', style: TextStyle(fontWeight: FontWeight.w800)),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildChip(double val) {
    return OutlinedButton(
      onPressed: () => _setAmount(val),
      style: OutlinedButton.styleFrom(
        foregroundColor: AppColors.textLight,
        side: const BorderSide(color: Colors.white24),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        padding: const EdgeInsets.symmetric(vertical: 10),
      ),
      child: Text('${widget.currency} ${val.toStringAsFixed(1)}', style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 12)),
    );
  }

  Widget _buildMethodRadio(String value, String label, IconData icon) {
    final isSelected = _payoutMethod == value;
    return InkWell(
      onTap: () => setState(() => _payoutMethod = value),
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 10),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFFF97316).withOpacity(0.15) : AppColors.backgroundDark,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: isSelected ? const Color(0xFFF97316) : Colors.white12, width: isSelected ? 1.5 : 1.0),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, color: isSelected ? const Color(0xFFF97316) : AppColors.textMuted, size: 18),
            const SizedBox(width: 8),
            Text(label, style: TextStyle(color: isSelected ? Colors.white : AppColors.textMuted, fontSize: 12, fontWeight: isSelected ? FontWeight.bold : FontWeight.w500)),
          ],
        ),
      ),
    );
  }

  Widget _buildField(String label, TextEditingController controller, String hint, {bool isNumber = false}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(color: AppColors.textMuted, fontSize: 11, fontWeight: FontWeight.w600)),
        const SizedBox(height: 4),
        Container(
          decoration: BoxDecoration(
            color: AppColors.backgroundDark,
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: Colors.white12),
          ),
          padding: const EdgeInsets.symmetric(horizontal: 12),
          child: TextField(
            controller: controller,
            keyboardType: isNumber ? TextInputType.number : TextInputType.text,
            style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.w600),
            decoration: InputDecoration(
              hintText: hint,
              hintStyle: const TextStyle(color: AppColors.textMuted, fontSize: 12),
              border: InputBorder.none,
              isDense: true,
              contentPadding: const EdgeInsets.symmetric(vertical: 10),
            ),
          ),
        ),
      ],
    );
  }
}
