import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../services/wallet_service.dart';

class WalletScreen extends StatefulWidget {
  const WalletScreen({super.key});

  @override
  State<WalletScreen> createState() => _WalletScreenState();
}

class _WalletScreenState extends State<WalletScreen> with SingleTickerProviderStateMixin {
  bool _isLoading = true;
  double _balance = 215.95;
  double _pendingWithdrawals = 0.0;
  String _currency = '₹';
  double _minWithdrawal = 50.0;
  double _maxWithdrawal = 10000.0;
  String _withdrawalFeeType = 'fixed';
  double _withdrawalFee = 0.0;
  bool _withdrawalsEnabled = true;
  List<String> _allowedPayoutMethods = ['bank', 'momo'];

  List<dynamic> _transactions = [];
  List<dynamic> _withdrawals = [];
  List<dynamic> _payoutMethods = [];

  int _selectedTabIndex = 0; // 0: Transactions, 1: Withdrawals
  String _withdrawalFilter = 'all';

  @override
  void initState() {
    super.initState();
    _loadWalletData();
  }

  Future<void> _loadWalletData() async {
    setState(() => _isLoading = true);
    try {
      final balanceData = await WalletService.getBalance();
      if (balanceData != null) {
        setState(() {
          _balance = (balanceData['wallet_balance'] as num?)?.toDouble() ?? 215.95;
          _pendingWithdrawals = (balanceData['pending_withdrawals_sum'] as num?)?.toDouble() ?? 0.0;
          _currency = balanceData['currency']?.toString() ?? '₹';
          final settings = balanceData['settings'] as Map<String, dynamic>?;
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
        });
      }

      final txList = await WalletService.getTransactions();
      final wthData = await WalletService.getWithdrawalHistory(status: _withdrawalFilter);
      final pMethods = await WalletService.getPayoutMethods();

      setState(() {
        _transactions = txList;
        if (wthData['success'] == true && wthData['data'] is List) {
          _withdrawals = List<dynamic>.from(wthData['data']);
        }
        _payoutMethods = pMethods;
      });
    } catch (e) {
      debugPrint('Error loading wallet data: $e');
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _filterWithdrawals(String status) async {
    setState(() {
      _withdrawalFilter = status;
      _isLoading = true;
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
      if (mounted) setState(() => _isLoading = false);
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

    if (_balance < _minWithdrawal) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Minimum withdrawal amount is $_currency${_minWithdrawal.toStringAsFixed(2)}. Your balance is $_currency${_balance.toStringAsFixed(2)}.'),
          backgroundColor: AppColors.error,
        ),
      );
      return;
    }

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _WithdrawBottomSheet(
        balance: _balance,
        currency: _currency,
        minWithdrawal: _minWithdrawal,
        maxWithdrawal: _maxWithdrawal,
        withdrawalFee: _withdrawalFee,
        withdrawalFeeType: _withdrawalFeeType,
        allowedMethods: _allowedPayoutMethods,
        savedPayoutMethods: _payoutMethods,
        onSuccess: () {
          _loadWalletData();
          setState(() {
            _selectedTabIndex = 1; // Switch to withdrawals tab
          });
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.backgroundDark,
      appBar: AppBar(
        backgroundColor: const Color(0xFFF97316), // Warm Orange brand accent
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          _selectedTabIndex == 1 ? 'Recent Withdrawal' : 'Wallet',
          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 19),
        ),
        centerTitle: true,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: Colors.white),
            onPressed: _loadWalletData,
            tooltip: 'Refresh',
          ),
        ],
      ),
      body: RefreshIndicator(
        color: const Color(0xFFF97316),
        onRefresh: _loadWalletData,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Hero Balance Card (Matching Reference 1 & 2)
              _buildHeroBalanceCard(),

              const SizedBox(height: 20),

              // Segmented Tab Selector
              _buildTabSelector(),

              const SizedBox(height: 16),

              // Tab View Content
              if (_selectedTabIndex == 0)
                _buildTransactionsTab()
              else
                _buildWithdrawalsTab(),
            ],
          ),
        ),
      ),
      bottomNavigationBar: _selectedTabIndex == 1 && _withdrawals.isEmpty
          ? Container(
              padding: const EdgeInsets.all(16),
              decoration: const BoxDecoration(
                color: AppColors.surfaceDark,
                border: Border(top: BorderSide(color: Colors.white10)),
              ),
              child: SafeArea(
                child: ElevatedButton(
                  onPressed: _openWithdrawModal,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFF97316),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    elevation: 3,
                  ),
                  child: const Text(
                    'Request Withdraw',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800),
                  ),
                ),
              ),
            )
          : null,
    );
  }

  /// Orange Hero Balance Card with Add Money & Withdraw buttons
  Widget _buildHeroBalanceCard() {
    return Container(
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFFEA580C), Color(0xFFF97316)], // Vibrant Orange
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
          crossAxisAlignment: CrossAxisAlignment.center,
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
                  _balance.toStringAsFixed(2),
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
                // Add Money
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
                          Text(
                            'Add Money',
                            style: TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.bold),
                          ),
                          SizedBox(width: 8),
                          Icon(Icons.add_circle_outline_rounded, color: Colors.white, size: 20),
                        ],
                      ),
                    ),
                  ),
                ),
                Container(height: 24, width: 1, color: Colors.white24),
                // Withdraw
                Expanded(
                  child: InkWell(
                    onTap: _openWithdrawModal,
                    borderRadius: BorderRadius.circular(12),
                    child: const Padding(
                      padding: EdgeInsets.symmetric(vertical: 8),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            'Withdraw',
                            style: TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.bold),
                          ),
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

  /// Segmented Tab Bar between Transactions and Withdrawals
  Widget _buildTabSelector() {
    return Container(
      padding: const EdgeInsets.all(4),
      decoration: BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.white10),
      ),
      child: Row(
        children: [
          Expanded(
            child: InkWell(
              onTap: () => setState(() => _selectedTabIndex = 0),
              borderRadius: BorderRadius.circular(10),
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 10),
                decoration: BoxDecoration(
                  color: _selectedTabIndex == 0 ? const Color(0xFFF97316) : Colors.transparent,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Center(
                  child: Text(
                    'Recent Transactions',
                    style: TextStyle(
                      color: _selectedTabIndex == 0 ? Colors.white : AppColors.textMuted,
                      fontWeight: FontWeight.w700,
                      fontSize: 13,
                    ),
                  ),
                ),
              ),
            ),
          ),
          Expanded(
            child: InkWell(
              onTap: () => setState(() => _selectedTabIndex = 1),
              borderRadius: BorderRadius.circular(10),
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 10),
                decoration: BoxDecoration(
                  color: _selectedTabIndex == 1 ? const Color(0xFFF97316) : Colors.transparent,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Center(
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        'Withdrawals',
                        style: TextStyle(
                          color: _selectedTabIndex == 1 ? Colors.white : AppColors.textMuted,
                          fontWeight: FontWeight.w700,
                          fontSize: 13,
                        ),
                      ),
                      if (_withdrawals.isNotEmpty) ...[
                        const SizedBox(width: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: _selectedTabIndex == 1 ? Colors.white24 : AppColors.primary.withOpacity(0.2),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Text(
                            '${_withdrawals.length}',
                            style: TextStyle(
                              color: _selectedTabIndex == 1 ? Colors.white : AppColors.primary,
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  /// Recent Transactions Tab Content
  Widget _buildTransactionsTab() {
    if (_isLoading) {
      return const Center(child: Padding(padding: EdgeInsets.all(40), child: CircularProgressIndicator(color: Color(0xFFF97316))));
    }

    if (_transactions.isEmpty) {
      // Demo/Fallback transactions matching Reference 1 screenshot
      return Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Recent Transactions', style: TextStyle(color: AppColors.textLight, fontSize: 16, fontWeight: FontWeight.w800)),
          const SizedBox(height: 12),
          _buildTransactionItem('Daily Incentive Amount', '23rd Sep 10:42 PM', 50.0, true),
          _buildTransactionItem('Admin Commission For Trip', '23rd Sep 10:42 PM', 11.2, false),
          _buildTransactionItem('Daily Incentive Amount', '2nd Sep 10:18 PM', 50.0, true),
          _buildTransactionItem('Admin Commission For Trip', '2nd Sep 10:18 PM', 0.0, false),
          _buildTransactionItem('Daily Incentive Amount', '15th Jul 10:21 PM', 50.0, true),
          _buildTransactionItem('Trip Commission', '10th Jul 09:30 PM', 106.4, true),
        ],
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Recent Transactions', style: TextStyle(color: AppColors.textLight, fontSize: 16, fontWeight: FontWeight.w800)),
        const SizedBox(height: 12),
        ListView.separated(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: _transactions.length,
          separatorBuilder: (_, __) => const SizedBox(height: 10),
          itemBuilder: (ctx, idx) {
            final tx = _transactions[idx];
            final type = tx['type']?.toString() ?? 'credit';
            final title = tx['description'] ?? (type == 'credit' ? 'Wallet Credit' : 'Wallet Debit');
            final date = tx['formatted_created_at'] ?? tx['created_at'] ?? '';
            final amount = (tx['amount'] as num?)?.toDouble() ?? 0.0;
            final isCredit = type == 'credit' || (tx['direction'] == 'in');

            return _buildTransactionItem(title, date, amount, isCredit);
          },
        ),
      ],
    );
  }

  Widget _buildTransactionItem(String title, String subtitle, double amount, bool isPositive) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      decoration: BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white.withOpacity(0.06)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.06),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(
              Icons.swap_horiz_rounded,
              color: isPositive ? AppColors.success : AppColors.textMuted,
              size: 24,
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(color: AppColors.textLight, fontSize: 14, fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 4),
                Text(
                  subtitle,
                  style: const TextStyle(color: AppColors.textMuted, fontSize: 12),
                ),
              ],
            ),
          ),
          Text(
            '${isPositive ? '+' : '-'} ${amount.toStringAsFixed(1)}',
            style: TextStyle(
              color: isPositive ? AppColors.success : AppColors.danger,
              fontSize: 15,
              fontWeight: FontWeight.w800,
            ),
          ),
        ],
      ),
    );
  }

  /// Recent Withdrawals Tab Content (Matching Reference 2)
  Widget _buildWithdrawalsTab() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Status Filter Chips
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

        const SizedBox(height: 18),

        if (_isLoading)
          const Center(child: Padding(padding: EdgeInsets.all(40), child: CircularProgressIndicator(color: Color(0xFFF97316))))
        else if (_withdrawals.isEmpty)
          _buildEmptyWithdrawalsView()
        else
          ListView.separated(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: _withdrawals.length,
            separatorBuilder: (_, __) => const SizedBox(height: 12),
            itemBuilder: (ctx, idx) {
              final item = _withdrawals[idx];
              return _buildWithdrawalCard(item);
            },
          ),

        if (_withdrawals.isNotEmpty) ...[
          const SizedBox(height: 24),
          ElevatedButton(
            onPressed: _openWithdrawModal,
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFF97316),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              padding: const EdgeInsets.symmetric(vertical: 16),
              minimumSize: const Size.fromHeight(50),
            ),
            child: const Text('Request Withdraw', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
          ),
        ],
      ],
    );
  }

  Widget _buildFilterChip(String value, String label) {
    final isSelected = _withdrawalFilter == value;
    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      onSelected: (_) => _filterWithdrawals(value),
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

  /// Empty state matching Reference Screenshot 2
  Widget _buildEmptyWithdrawalsView() {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 40, horizontal: 20),
      alignment: Alignment.center,
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            width: 140,
            height: 140,
            decoration: BoxDecoration(
              color: const Color(0xFFF97316).withOpacity(0.08),
              shape: BoxShape.circle,
            ),
            child: const Center(
              child: Icon(
                Icons.credit_card_rounded,
                size: 70,
                color: Color(0xFFF97316),
              ),
            ),
          ),
          const SizedBox(height: 24),
          const Text(
            'No payment history yet.',
            style: TextStyle(
              color: AppColors.textLight,
              fontSize: 16,
              fontWeight: FontWeight.w700,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 6),
          const Text(
            'Start your journey by requesting a withdrawal or booking a ride today!',
            style: TextStyle(
              color: AppColors.textMuted,
              fontSize: 13,
              height: 1.4,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  /// Individual Withdrawal Request Card
  Widget _buildWithdrawalCard(dynamic item) {
    final ref = item['reference_id'] ?? 'WTH-${item['id']}';
    final amount = (item['amount'] as num?)?.toDouble() ?? 0.0;
    final status = (item['status']?.toString() ?? 'pending').toLowerCase();
    final method = item['payout_method']?.toString() ?? 'bank';
    final date = item['formatted_created_at'] ?? item['created_at'] ?? '';
    final rejectionReason = item['rejection_reason']?.toString();
    final txRef = item['transaction_reference']?.toString();

    Color statusColor;
    String statusLabel;
    IconData statusIcon;

    if (status == 'approved') {
      statusColor = AppColors.success;
      statusLabel = 'Approved';
      statusIcon = Icons.check_circle_rounded;
    } else if (status == 'rejected') {
      statusColor = AppColors.danger;
      statusLabel = 'Rejected';
      statusIcon = Icons.cancel_rounded;
    } else {
      statusColor = AppColors.warning;
      statusLabel = 'Pending Review';
      statusIcon = Icons.schedule_rounded;
    }

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: statusColor.withOpacity(0.3), width: 1.2),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                ref,
                style: const TextStyle(
                  color: AppColors.textMuted,
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                  letterSpacing: 0.5,
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: statusColor.withOpacity(0.15),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(statusIcon, color: statusColor, size: 14),
                    const SizedBox(width: 4),
                    Text(
                      statusLabel,
                      style: TextStyle(color: statusColor, fontSize: 11, fontWeight: FontWeight.bold),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                '$_currency${amount.toStringAsFixed(2)}',
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 22,
                  fontWeight: FontWeight.w900,
                ),
              ),
              Row(
                children: [
                  Icon(
                    method == 'momo' ? Icons.phone_android_rounded : Icons.account_balance_rounded,
                    color: const Color(0xFFF97316),
                    size: 16,
                  ),
                  const SizedBox(width: 6),
                  Text(
                    method == 'momo' ? 'Mobile Money' : 'Bank Account',
                    style: const TextStyle(color: AppColors.textLight, fontSize: 13, fontWeight: FontWeight.w600),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 10),
          const Divider(color: Colors.white10, height: 1),
          const SizedBox(height: 10),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Requested: $date',
                style: const TextStyle(color: AppColors.textMuted, fontSize: 11),
              ),
              if (txRef != null && txRef.isNotEmpty)
                Text(
                  'Ref: $txRef',
                  style: const TextStyle(color: AppColors.info, fontSize: 11, fontWeight: FontWeight.w600),
                ),
            ],
          ),
          if (status == 'rejected' && rejectionReason != null && rejectionReason.isNotEmpty) ...[
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: AppColors.danger.withOpacity(0.1),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: AppColors.danger.withOpacity(0.3)),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Icon(Icons.info_outline_rounded, color: AppColors.danger, size: 16),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Reason: $rejectionReason',
                      style: const TextStyle(color: AppColors.danger, fontSize: 12, height: 1.3),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }
}

/// Withdrawal Bottom Sheet / Modal (Matching Reference 3)
class _WithdrawBottomSheet extends StatefulWidget {
  final double balance;
  final String currency;
  final double minWithdrawal;
  final double maxWithdrawal;
  final double withdrawalFee;
  final String withdrawalFeeType;
  final List<String> allowedMethods;
  final List<dynamic> savedPayoutMethods;
  final VoidCallback onSuccess;

  const _WithdrawBottomSheet({
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
  State<_WithdrawBottomSheet> createState() => _WithdrawBottomSheetState();
}

class _WithdrawBottomSheetState extends State<_WithdrawBottomSheet> {
  final _amountController = TextEditingController();
  final _bankNameController = TextEditingController();
  final _accountNumberController = TextEditingController();
  final _routingCodeController = TextEditingController();
  final _holderNameController = TextEditingController();

  final _momoNetworkController = TextEditingController(text: 'MTN');
  final _momoPhoneController = TextEditingController();
  final _momoNameController = TextEditingController();

  String _payoutMethod = 'bank'; // 'bank' or 'momo'
  bool _savePayoutMethod = true;
  bool _isSubmitting = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    if (!widget.allowedMethods.contains('bank') && widget.allowedMethods.contains('momo')) {
      _payoutMethod = 'momo';
    }

    // Auto-fill from saved methods if available
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
    if (val > widget.balance) {
      val = widget.balance;
    }
    _amountController.text = val.toStringAsFixed(1);
    setState(() => _errorMessage = null);
  }

  double get _enteredAmount {
    return double.tryParse(_amountController.text.trim()) ?? 0.0;
  }

  double get _calculatedFee {
    final amt = _enteredAmount;
    if (amt <= 0) return 0.0;
    if (widget.withdrawalFeeType == 'percent') {
      return (amt * widget.withdrawalFee) / 100.0;
    }
    return widget.withdrawalFee;
  }

  double get _netPayout {
    final amt = _enteredAmount;
    final fee = _calculatedFee;
    return (amt - fee) > 0 ? (amt - fee) : 0.0;
  }

  Future<void> _submit() async {
    setState(() => _errorMessage = null);
    final amt = _enteredAmount;

    // Validations matching prompt requirements
    if (amt <= 0) {
      setState(() => _errorMessage = 'Please enter a valid withdrawal amount.');
      return;
    }
    if (amt < widget.minWithdrawal) {
      setState(() => _errorMessage = 'Amount must be at least ${widget.currency}${widget.minWithdrawal.toStringAsFixed(2)}.');
      return;
    }
    if (amt > widget.balance) {
      setState(() => _errorMessage = 'Withdrawal amount cannot exceed available balance (${widget.currency}${widget.balance.toStringAsFixed(2)}).');
      return;
    }
    if (amt > widget.maxWithdrawal) {
      setState(() => _errorMessage = 'Amount exceeds maximum withdrawal limit of ${widget.currency}${widget.maxWithdrawal.toStringAsFixed(2)}.');
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
      Navigator.pop(context); // Close sheet
      widget.onSuccess();

      // Show confirmation dialog matching specification
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
            'Your withdrawal request of ${widget.currency}${amt.toStringAsFixed(2)} has been submitted for pending approval.\n\nNote: Your wallet balance remains intact and will only be deducted once approved by admin.',
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
              child: const Text('View History', style: TextStyle(fontWeight: FontWeight.bold)),
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
            // Grabber handle
            Center(
              child: Container(
                width: 44,
                height: 5,
                decoration: BoxDecoration(
                  color: Colors.white24,
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
            ),
            const SizedBox(height: 18),

            // Header with Available Balance
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Request Withdrawal',
                  style: TextStyle(color: AppColors.textLight, fontSize: 18, fontWeight: FontWeight.w800),
                ),
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

            // Amount Input (Reference Screenshot 3: ₹ Enter Amount Here)
            Container(
              decoration: BoxDecoration(
                color: AppColors.backgroundDark,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: Colors.white24, width: 1.2),
              ),
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
              child: Row(
                children: [
                  Text(
                    widget.currency,
                    style: const TextStyle(color: AppColors.textLight, fontSize: 20, fontWeight: FontWeight.bold),
                  ),
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
                Expanded(child: _buildQuickChip(50.0)),
                const SizedBox(width: 8),
                Expanded(child: _buildQuickChip(100.0)),
                const SizedBox(width: 8),
                Expanded(child: _buildQuickChip(150.0)),
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
            const Text(
              'Payout Method',
              style: TextStyle(color: AppColors.textLight, fontSize: 14, fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 10),
            Row(
              children: [
                if (widget.allowedMethods.contains('bank'))
                  Expanded(
                    child: _buildMethodRadio(
                      'bank',
                      'Bank Account',
                      Icons.account_balance_rounded,
                    ),
                  ),
                if (widget.allowedMethods.contains('bank') && widget.allowedMethods.contains('momo'))
                  const SizedBox(width: 10),
                if (widget.allowedMethods.contains('momo'))
                  Expanded(
                    child: _buildMethodRadio(
                      'momo',
                      'Mobile Money (MoMo)',
                      Icons.phone_android_rounded,
                    ),
                  ),
              ],
            ),

            const SizedBox(height: 16),

            // Method-specific input fields
            if (_payoutMethod == 'bank') ...[
              _buildTextField('Bank Name', _bankNameController, 'e.g. HDFC Bank / Chase'),
              const SizedBox(height: 10),
              _buildTextField('Account Number / IBAN', _accountNumberController, 'e.g. 501002348910', isNumber: true),
              const SizedBox(height: 10),
              Row(
                children: [
                  Expanded(child: _buildTextField('Routing / IFSC Code', _routingCodeController, 'e.g. HDFC0001234')),
                  const SizedBox(width: 10),
                  Expanded(child: _buildTextField('Account Holder Name', _holderNameController, 'e.g. John Doe')),
                ],
              ),
            ] else ...[
              _buildTextField('Network Provider', _momoNetworkController, 'e.g. MTN / Airtel / Vodafone'),
              const SizedBox(height: 10),
              _buildTextField('MoMo Mobile Number', _momoPhoneController, 'e.g. +91 9876543210', isNumber: true),
              const SizedBox(height: 10),
              _buildTextField('Registered Account Name', _momoNameController, 'e.g. John Doe'),
            ],

            const SizedBox(height: 14),

            // Save details checkbox
            Row(
              children: [
                Checkbox(
                  value: _savePayoutMethod,
                  activeColor: const Color(0xFFF97316),
                  onChanged: (v) => setState(() => _savePayoutMethod = v ?? false),
                ),
                const Expanded(
                  child: Text(
                    'Save payout account details for fast withdrawals',
                    style: TextStyle(color: AppColors.textMuted, fontSize: 12),
                  ),
                ),
              ],
            ),

            // Payout calculation preview
            if (_enteredAmount > 0) ...[
              const SizedBox(height: 8),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.backgroundDark,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.white10),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Fee: ${widget.currency}${_calculatedFee.toStringAsFixed(2)}',
                      style: const TextStyle(color: AppColors.textMuted, fontSize: 12),
                    ),
                    Text(
                      'Net Payout: ${widget.currency}${_netPayout.toStringAsFixed(2)}',
                      style: const TextStyle(color: AppColors.success, fontSize: 13, fontWeight: FontWeight.bold),
                    ),
                  ],
                ),
              ),
            ],

            if (_errorMessage != null) ...[
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: AppColors.danger.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  _errorMessage!,
                  style: const TextStyle(color: AppColors.danger, fontSize: 12, fontWeight: FontWeight.w600),
                ),
              ),
            ],

            const SizedBox(height: 20),

            // Action Buttons: Cancel and Withdraw (Matching Reference 3)
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
                    child: const Text('Cancel', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
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
                        : const Text('Withdraw', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 15)),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildQuickChip(double val) {
    return OutlinedButton(
      onPressed: () => _setAmount(val),
      style: OutlinedButton.styleFrom(
        foregroundColor: AppColors.textLight,
        side: const BorderSide(color: Colors.white24),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        padding: const EdgeInsets.symmetric(vertical: 10),
      ),
      child: Text(
        '${widget.currency} ${val.toStringAsFixed(1)}',
        style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 12),
      ),
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
          border: Border.all(
            color: isSelected ? const Color(0xFFF97316) : Colors.white12,
            width: isSelected ? 1.5 : 1.0,
          ),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, color: isSelected ? const Color(0xFFF97316) : AppColors.textMuted, size: 18),
            const SizedBox(width: 8),
            Flexible(
              child: Text(
                label,
                style: TextStyle(
                  color: isSelected ? Colors.white : AppColors.textMuted,
                  fontSize: 12,
                  fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                ),
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTextField(String label, TextEditingController controller, String hint, {bool isNumber = false}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(color: AppColors.textMuted, fontSize: 11, fontWeight: FontWeight.w600),
        ),
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
