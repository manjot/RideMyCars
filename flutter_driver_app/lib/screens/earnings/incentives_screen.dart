import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import '../../core/api/api_client.dart';
import '../../core/constants/api_constants.dart';
import '../../core/constants/app_colors.dart';

class IncentivesScreen extends StatefulWidget {
  const IncentivesScreen({super.key});

  @override
  State<IncentivesScreen> createState() => _IncentivesScreenState();
}

class _IncentivesScreenState extends State<IncentivesScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final Dio _dio = ApiClient().dio;

  bool _isLoading = true;
  String? _errorMessage;
  Map<String, dynamic> _data = {};

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _fetchIncentives();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _fetchIncentives() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final res = await _dio.get(ApiConstants.driverIncentives);
      if (res.statusCode == 200 && res.data is Map<String, dynamic>) {
        setState(() {
          _data = Map<String, dynamic>.from(res.data);
          _isLoading = false;
        });
      } else {
        setState(() {
          _errorMessage = 'Failed to load incentives.';
          _isLoading = false;
        });
      }
    } catch (e) {
      debugPrint('Error fetching incentives: $e');
      setState(() {
        _errorMessage = 'Unable to connect to server. Please try again.';
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final locationData = (_data['driver_location'] as Map?) ?? {};
    final displayLocation = locationData['display_location']?.toString() ?? 'India';
    final vehicleType = locationData['vehicle_type']?.toString() ?? 'Car';

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
          'Incentive Program',
          style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w800, fontSize: 18),
        ),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(48),
          child: Container(
            margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
            decoration: BoxDecoration(
              color: AppColors.backgroundDark,
              borderRadius: BorderRadius.circular(14),
            ),
            child: TabBar(
              controller: _tabController,
              indicator: BoxDecoration(
                color: AppColors.primary,
                borderRadius: BorderRadius.circular(12),
              ),
              labelColor: AppColors.backgroundDark,
              unselectedLabelColor: AppColors.textMuted,
              labelStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
              indicatorSize: TabBarIndicatorSize.tab,
              dividerColor: Colors.transparent,
              tabs: const [
                Tab(text: 'Daily'),
                Tab(text: 'Weekly'),
                Tab(text: 'Monthly'),
              ],
            ),
          ),
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : (_errorMessage != null && _data.isEmpty)
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24.0),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.cloud_off_rounded, size: 48, color: AppColors.danger),
                        const SizedBox(height: 16),
                        Text(
                          _errorMessage!,
                          style: const TextStyle(color: AppColors.textLight, fontSize: 15, fontWeight: FontWeight.w600),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 16),
                        ElevatedButton.icon(
                          onPressed: _fetchIncentives,
                          icon: const Icon(Icons.refresh_rounded, size: 18),
                          label: const Text('Try Again'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.primary,
                            foregroundColor: AppColors.backgroundDark,
                          ),
                        ),
                      ],
                    ),
                  ),
                )
              : RefreshIndicator(
              color: AppColors.primary,
              onRefresh: _fetchIncentives,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(18),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Location & Vehicle Filter Chip
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                      decoration: BoxDecoration(
                        color: AppColors.surfaceDark,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: Colors.white.withOpacity(0.06)),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.location_on_rounded, color: AppColors.primary, size: 16),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              '$displayLocation • $vehicleType',
                              style: const TextStyle(
                                color: AppColors.textLight,
                                fontWeight: FontWeight.bold,
                                fontSize: 12,
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: AppColors.success.withOpacity(0.15),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: const Text(
                              'AUTOMATIC ASSIGNMENT',
                              style: TextStyle(
                                color: AppColors.success,
                                fontSize: 9,
                                fontWeight: FontWeight.w900,
                                letterSpacing: 0.4,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 18),

                    // Tab View Content for active tab
                    AnimatedBuilder(
                      animation: _tabController,
                      builder: (context, _) {
                        final tabIndex = _tabController.index;
                        final tabKey = tabIndex == 0 ? 'daily' : (tabIndex == 1 ? 'weekly' : 'monthly');
                        final tabs = (_data['tabs'] as Map?) ?? {};
                        final currentTab = (tabs[tabKey] as Map?) ?? {};

                        return _buildTabContent(tabKey, currentTab);
                      },
                    ),

                    const SizedBox(height: 26),

                    // Reward History Section
                    _buildRewardHistorySection(),

                    const SizedBox(height: 20),

                    // Wallet Summary Card
                    _buildWalletSummaryCard(),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildTabContent(String tabKey, Map currentTab) {
    final bool hasIncentive = currentTab['has_incentive'] == true;
    final bool isMissed = currentTab['is_missed'] == true;

    if (!hasIncentive) {
      if (isMissed) {
        return _buildMissedCard(currentTab['missed_message']?.toString() ?? "Today's incentive expired. Better luck tomorrow.");
      }
      return Container(
        padding: const EdgeInsets.all(28),
        decoration: BoxDecoration(
          color: AppColors.surfaceDark,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: Colors.white.withOpacity(0.06)),
        ),
        child: Column(
          children: [
            const Icon(Icons.flag_outlined, color: AppColors.textMuted, size: 40),
            const SizedBox(height: 12),
            Text(
              'No Active ${tabKey.toUpperCase()} Incentive',
              style: const TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w800, fontSize: 15),
            ),
            const SizedBox(height: 6),
            const Text(
              'Check other tabs or check back tomorrow for new ride milestone rewards.',
              textAlign: TextAlign.center,
              style: TextStyle(color: AppColors.textMuted, fontSize: 12, height: 1.4),
            ),
          ],
        ),
      );
    }

    final title = currentTab['title']?.toString() ?? "Today's Incentive";
    final heroReward = currentTab['hero_reward_text']?.toString() ?? 'Earn ₹500';
    final heroTarget = currentTab['hero_target_text']?.toString() ?? 'Complete 20 Rides';
    final progressFraction = currentTab['progress_fraction']?.toString() ?? '0 / 20';
    final remainingText = currentTab['remaining_text']?.toString() ?? 'Rides Remaining';
    final int progressPercentage = (currentTab['progress_percentage'] as num?)?.toInt() ?? 0;
    final bool isCompleted = currentTab['is_completed'] == true;
    final milestones = (currentTab['milestones'] as List?) ?? [];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        // Hero Incentive Card
        Container(
          padding: const EdgeInsets.all(22),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFF0F172A), Color(0xFF1E293B)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(24),
            border: Border.all(color: AppColors.primary.withOpacity(0.4), width: 1.5),
            boxShadow: [
              BoxShadow(
                color: AppColors.primary.withOpacity(0.12),
                blurRadius: 24,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: AppColors.primary.withOpacity(0.15),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: AppColors.primary.withOpacity(0.3)),
                    ),
                    child: Text(
                      title.toUpperCase(),
                      style: const TextStyle(
                        color: AppColors.primary,
                        fontSize: 10,
                        fontWeight: FontWeight.w900,
                        letterSpacing: 0.5,
                      ),
                    ),
                  ),
                  Text(
                    remainingText,
                    style: TextStyle(
                      color: isCompleted ? AppColors.success : AppColors.primary,
                      fontSize: 12,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 14),

              Text(
                heroReward,
                style: const TextStyle(
                  color: AppColors.textLight,
                  fontSize: 32,
                  fontWeight: FontWeight.w900,
                  letterSpacing: -0.5,
                ),
              ),
              const SizedBox(height: 4),

              Text(
                heroTarget,
                style: const TextStyle(
                  color: AppColors.textMuted,
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                ),
              ),
              const SizedBox(height: 20),

              // Progress row
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'PROGRESS',
                    style: TextStyle(
                      color: AppColors.textMuted,
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                      letterSpacing: 0.5,
                    ),
                  ),
                  Text(
                    progressFraction,
                    style: const TextStyle(
                      color: AppColors.primary,
                      fontSize: 18,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),

              // Progress Bar
              ClipRRect(
                borderRadius: BorderRadius.circular(10),
                child: LinearProgressIndicator(
                  value: progressPercentage / 100.0,
                  minHeight: 12,
                  backgroundColor: Colors.white.withOpacity(0.08),
                  valueColor: const AlwaysStoppedAnimation<Color>(AppColors.primary),
                ),
              ),
              const SizedBox(height: 8),

              Align(
                alignment: Alignment.centerRight,
                child: Text(
                  '$progressPercentage% achieved',
                  style: const TextStyle(color: AppColors.textMuted, fontSize: 11),
                ),
              ),
            ],
          ),
        ),

        // Completed Celebration Green Card
        if (isCompleted) ...[
          const SizedBox(height: 14),
          _buildCompletedCard(currentTab['completed_card'] as Map?),
        ],

        const SizedBox(height: 20),

        // Milestone Cards Header
        const Text(
          'Milestone Targets',
          style: TextStyle(color: AppColors.textLight, fontSize: 15, fontWeight: FontWeight.w800),
        ),
        const SizedBox(height: 12),

        // Milestone Cards List
        ...milestones.map((m) => _buildMilestoneCard(Map<String, dynamic>.from(m))),
      ],
    );
  }

  Widget _buildCompletedCard(Map? cardData) {
    final title = cardData?['title']?.toString() ?? 'Congratulations!';
    final message = cardData?['message']?.toString() ?? 'Bonus Credited';

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: AppColors.success.withOpacity(0.15),
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.success.withOpacity(0.4)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: AppColors.success,
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(Icons.celebration_rounded, color: Colors.white, size: 24),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(color: AppColors.success, fontWeight: FontWeight.w900, fontSize: 16),
                ),
                const SizedBox(height: 2),
                Text(
                  message,
                  style: const TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w700, fontSize: 13),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMissedCard(String message) {
    return Container(
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: AppColors.danger.withOpacity(0.15),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppColors.danger.withOpacity(0.35)),
      ),
      child: Column(
        children: [
          const Icon(Icons.alarm_off_rounded, color: AppColors.danger, size: 36),
          const SizedBox(height: 10),
          const Text(
            "Today's incentive expired.",
            style: TextStyle(color: AppColors.danger, fontWeight: FontWeight.w900, fontSize: 15),
          ),
          const SizedBox(height: 4),
          Text(
            message,
            textAlign: TextAlign.center,
            style: const TextStyle(color: AppColors.textMuted, fontSize: 12),
          ),
        ],
      ),
    );
  }

  Widget _buildMilestoneCard(Map<String, dynamic> milestone) {
    final bool isCompleted = milestone['is_completed'] == true;
    final bool isCurrent = milestone['is_current'] == true;
    final title = milestone['title']?.toString() ?? 'Milestone';
    final reward = milestone['reward_formatted']?.toString() ?? '₹0';
    final progressText = milestone['progress_text']?.toString() ?? '';

    Color borderColor = Colors.white.withOpacity(0.06);
    Color bgColor = AppColors.surfaceDark;

    if (isCompleted) {
      borderColor = AppColors.success.withOpacity(0.4);
      bgColor = AppColors.success.withOpacity(0.08);
    } else if (isCurrent) {
      borderColor = AppColors.primary.withOpacity(0.4);
      bgColor = AppColors.surfaceDark;
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: borderColor),
      ),
      child: Row(
        children: [
          // Status Icon
          Container(
            width: 28,
            height: 28,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: isCompleted ? AppColors.success : (isCurrent ? AppColors.primary.withOpacity(0.15) : Colors.white.withOpacity(0.06)),
            ),
            child: Icon(
              isCompleted ? Icons.check_rounded : Icons.radio_button_unchecked_rounded,
              color: isCompleted ? Colors.white : (isCurrent ? AppColors.primary : AppColors.textMuted),
              size: 16,
            ),
          ),
          const SizedBox(width: 14),

          // Title & Progress
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    color: AppColors.textLight,
                    fontWeight: isCompleted || isCurrent ? FontWeight.w800 : FontWeight.w600,
                    fontSize: 13,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  'Reward $reward',
                  style: const TextStyle(
                    color: AppColors.success,
                    fontWeight: FontWeight.w700,
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ),

          // Progress badge
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: isCompleted ? AppColors.success.withOpacity(0.15) : (isCurrent ? AppColors.primary.withOpacity(0.15) : Colors.white.withOpacity(0.05)),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Text(
              progressText,
              style: TextStyle(
                color: isCompleted ? AppColors.success : (isCurrent ? AppColors.primary : AppColors.textMuted),
                fontWeight: FontWeight.w800,
                fontSize: 11,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRewardHistorySection() {
    final List history = (_data['reward_history'] as List?) ?? [];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Reward History',
              style: TextStyle(color: AppColors.textLight, fontSize: 16, fontWeight: FontWeight.w800),
            ),
            Text(
              '${history.length} Credited',
              style: const TextStyle(color: AppColors.textMuted, fontSize: 12, fontWeight: FontWeight.w600),
            ),
          ],
        ),
        const SizedBox(height: 12),

        if (history.isEmpty)
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: AppColors.surfaceDark,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: Colors.white.withOpacity(0.06)),
            ),
            child: const Center(
              child: Text(
                'No incentive bonuses credited yet.\nComplete your first ride milestone to earn!',
                textAlign: TextAlign.center,
                style: TextStyle(color: AppColors.textMuted, fontSize: 12, height: 1.4),
              ),
            ),
          )
        else
          ...history.take(10).map((item) {
            final date = item['date']?.toString() ?? '';
            final incentive = item['incentive']?.toString() ?? 'Incentive';
            final reward = item['reward']?.toString() ?? '₹0';
            final trips = item['trips']?.toString() ?? '';
            final status = item['status']?.toString() ?? 'Credited';

            return Container(
              margin: const EdgeInsets.only(bottom: 8),
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: AppColors.surfaceDark,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: Colors.white.withOpacity(0.06)),
              ),
              child: Row(
                children: [
                  Container(
                    width: 38,
                    height: 38,
                    decoration: BoxDecoration(
                      color: AppColors.success.withOpacity(0.15),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.card_giftcard_rounded, color: AppColors.success, size: 20),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          incentive,
                          style: const TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w800, fontSize: 13),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          '$date • $trips',
                          style: const TextStyle(color: AppColors.textMuted, fontSize: 11),
                        ),
                      ],
                    ),
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(
                        '+$reward',
                        style: const TextStyle(color: AppColors.success, fontWeight: FontWeight.w900, fontSize: 15),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        status,
                        style: const TextStyle(color: AppColors.success, fontSize: 10, fontWeight: FontWeight.w800),
                      ),
                    ],
                  ),
                ],
              ),
            );
          }),
      ],
    );
  }

  Widget _buildWalletSummaryCard() {
    final wallet = (_data['wallet_summary'] as Map?) ?? {};
    final totalEarned = (wallet['total_incentive_earned'] as num?)?.toDouble() ?? 0.0;
    final balance = (wallet['balance'] as num?)?.toDouble() ?? 0.0;
    final currency = wallet['currency']?.toString() ?? '₹';

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.primary.withOpacity(0.25)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.account_balance_wallet_rounded, color: AppColors.primary, size: 20),
              const SizedBox(width: 8),
              const Text(
                'Wallet Incentive Bonuses',
                style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 14),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('TOTAL INCENTIVES EARNED', style: TextStyle(color: AppColors.textMuted, fontSize: 10, fontWeight: FontWeight.w800)),
                  const SizedBox(height: 4),
                  Text(
                    '$currency${totalEarned.toStringAsFixed(0)}',
                    style: const TextStyle(color: AppColors.success, fontWeight: FontWeight.w900, fontSize: 20),
                  ),
                ],
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  const Text('PAYOUT BALANCE', style: TextStyle(color: AppColors.textMuted, fontSize: 10, fontWeight: FontWeight.w800)),
                  const SizedBox(height: 4),
                  Text(
                    '$currency${balance.toStringAsFixed(2)}',
                    style: const TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w900, fontSize: 20),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 10),
          const Text(
            'All bonus rewards are instantly credited to your wallet once targets are reached.',
            style: TextStyle(color: AppColors.textMuted, fontSize: 11),
          ),
        ],
      ),
    );
  }
}
