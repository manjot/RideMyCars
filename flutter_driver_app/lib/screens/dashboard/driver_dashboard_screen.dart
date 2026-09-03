import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../providers/driver_provider.dart';
import '../../providers/notification_provider.dart';
import '../account/manage_account_screen.dart';
import '../auth/driver_login_screen.dart';
import '../earnings/driver_earnings_screen.dart';
import '../notifications/notifications_screen.dart';
import '../support/help_support_screen.dart';
import '../trip/active_trip_screen.dart';
import '../trips/driver_trips_screen.dart';
import 'incoming_job_dialog.dart';

class DriverDashboardScreen extends StatefulWidget {
  const DriverDashboardScreen({super.key});

  @override
  State<DriverDashboardScreen> createState() => _DriverDashboardScreenState();
}

class _DriverDashboardScreenState extends State<DriverDashboardScreen> {
  bool _dialogOpen = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final driver = Provider.of<DriverProvider>(context, listen: false);
      driver.init();
      Provider.of<NotificationProvider>(context, listen: false).startPolling();
    });
  }

  void _checkIncomingJobs(DriverProvider driver) {
    if (driver.pendingRequests.isNotEmpty && !_dialogOpen) {
      _dialogOpen = true;
      final job = driver.pendingRequests.first;
      final assignmentId = (job['assignment_id'] ?? job['id']) as int?;
      final rideId = (job['ride_id'] ?? job['ride']?['id']) as int?;

      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (_) => IncomingJobDialog(
          request: job,
          onAccept: () async {
            Navigator.pop(context);
            _dialogOpen = false;
            final ok = await driver.respondToRequest(assignmentId, 'accept', rideId: rideId);
            if (ok && mounted && driver.activeRides.isNotEmpty) {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => ActiveTripScreen(ride: driver.activeRides.first),
                ),
              );
            }
          },
          onDecline: () async {
            Navigator.pop(context);
            _dialogOpen = false;
            await driver.respondToRequest(assignmentId, 'reject', rideId: rideId);
          },
        ),
      ).then((_) => _dialogOpen = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final driver = Provider.of<DriverProvider>(context);
    final notifs = Provider.of<NotificationProvider>(context);

    // Watch for incoming jobs
    WidgetsBinding.instance.addPostFrameCallback((_) => _checkIncomingJobs(driver));

    return Scaffold(
      backgroundColor: AppColors.backgroundDark,
      drawer: _buildDrawer(context, auth),
      appBar: AppBar(
        backgroundColor: AppColors.surfaceDark,
        elevation: 0,
        title: Row(
          children: [
            Container(
              width: 34,
              height: 34,
              decoration: BoxDecoration(
                color: AppColors.primary,
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.local_taxi_rounded, color: AppColors.backgroundDark, size: 22),
            ),
            const SizedBox(width: 10),
            const Text(
              'Driver Console',
              style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w900, fontSize: 18),
            ),
          ],
        ),
        actions: [
          // Notification Bell with Badge
          Stack(
            alignment: Alignment.center,
            children: [
              IconButton(
                icon: const Icon(Icons.notifications_none_rounded, color: AppColors.textLight),
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const NotificationsScreen()),
                  );
                },
              ),
              if (notifs.unreadCount > 0)
                Positioned(
                  top: 10,
                  right: 10,
                  child: Container(
                    padding: const EdgeInsets.all(4),
                    decoration: const BoxDecoration(
                      color: AppColors.danger,
                      shape: BoxShape.circle,
                    ),
                    constraints: const BoxConstraints(minWidth: 16, minHeight: 16),
                    child: Text(
                      '${notifs.unreadCount}',
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ),
            ],
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: RefreshIndicator(
        color: AppColors.primary,
        onRefresh: () async {
          await driver.pollPendingRequests();
          await driver.fetchActiveRides();
          await driver.fetchEarnings();
        },
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(20.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Online / Offline Toggle Banner
              _buildOnlineStatusCard(driver),
              const SizedBox(height: 24),

              // Pending Payment & Booking Verification Requests (Parity with Web Dashboard!)
              if (driver.pendingVerifications.isNotEmpty) ...[
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        Container(
                          width: 10,
                          height: 10,
                          decoration: const BoxDecoration(
                            color: Colors.amber,
                            shape: BoxShape.circle,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Text(
                          'Pending Verifications (${driver.pendingVerifications.length})',
                          style: const TextStyle(
                            color: AppColors.textLight,
                            fontSize: 18,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ],
                    ),
                    IconButton(
                      icon: const Icon(Icons.refresh_rounded, color: Colors.amber, size: 20),
                      onPressed: () => driver.pollPendingRequests(),
                      tooltip: 'Refresh Verifications',
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                ...driver.pendingVerifications.map((item) => _buildPendingVerificationCard(context, item, driver)),
                const SizedBox(height: 20),
              ],

              // Available Ride Requests Section (Like Web Version!)
              if (driver.pendingRequests.isNotEmpty) ...[
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        Container(
                          width: 10,
                          height: 10,
                          decoration: const BoxDecoration(
                            color: AppColors.primary,
                            shape: BoxShape.circle,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Text(
                          'Available Jobs (${driver.pendingRequests.length})',
                          style: const TextStyle(
                            color: AppColors.textLight,
                            fontSize: 18,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ],
                    ),
                    const Text(
                      'Live Dispatch',
                      style: TextStyle(
                        color: AppColors.primary,
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                ...driver.pendingRequests.map((job) => _buildAvailableJobCard(context, job, driver)),
                const SizedBox(height: 20),
              ] else if (driver.isOnline && driver.pendingVerifications.isEmpty) ...[
                Container(
                  margin: const EdgeInsets.only(bottom: 24),
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppColors.surfaceDark,
                    borderRadius: BorderRadius.circular(18),
                    border: Border.all(color: Colors.white10),
                  ),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: AppColors.primary.withValues(alpha: 0.15),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(Icons.radar_rounded, color: AppColors.primary, size: 22),
                      ),
                      const SizedBox(width: 14),
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Scanning for nearby ride requests...',
                              style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 14),
                            ),
                            SizedBox(height: 2),
                            Text(
                              'New rider jobs will pop up here in real time.',
                              style: TextStyle(color: AppColors.textMuted, fontSize: 12),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ],

              // Active Rides Section
              if (driver.activeRides.isNotEmpty) ...[
                Row(
                  children: [
                    const Icon(Icons.near_me_rounded, color: AppColors.success, size: 20),
                    const SizedBox(width: 8),
                    Text(
                      'Active Trip (${driver.activeRides.length})',
                      style: const TextStyle(
                        color: AppColors.textLight,
                        fontSize: 18,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                ...driver.activeRides.map((r) => _buildActiveRideCard(context, r)),
                const SizedBox(height: 24),
              ],

              // Earnings Overview
              const Text(
                'Earnings Summary',
                style: TextStyle(
                  color: AppColors.textLight,
                  fontSize: 18,
                  fontWeight: FontWeight.w800,
                ),
              ),
              const SizedBox(height: 14),
              Row(
                children: [
                  Expanded(
                    child: _buildEarningTile('TODAY', '\$${(driver.earnings['today'] as num?)?.toStringAsFixed(2) ?? "0.00"}', AppColors.success),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _buildEarningTile('THIS WEEK', '\$${(driver.earnings['week'] as num?)?.toStringAsFixed(2) ?? "0.00"}', AppColors.info),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: _buildEarningTile('THIS MONTH', '\$${(driver.earnings['month'] as num?)?.toStringAsFixed(2) ?? "0.00"}', AppColors.purple),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _buildEarningTile('TOTAL TRIPS', '${driver.earnings['total_trips'] ?? 0}', AppColors.primary),
                  ),
                ],
              ),
              const SizedBox(height: 28),

              // Driver Profile Card
              Card(
                color: AppColors.surfaceDark,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                child: Padding(
                  padding: const EdgeInsets.all(18),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: AppColors.primary.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: const Icon(Icons.person_pin_rounded, color: AppColors.primary),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              auth.userName ?? 'Driver Partner',
                              style: const TextStyle(
                                color: AppColors.textLight,
                                fontWeight: FontWeight.bold,
                                fontSize: 16,
                              ),
                            ),
                            const Text(
                              'Verified Professional Chauffeur',
                              style: TextStyle(color: AppColors.textMuted, fontSize: 12),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildOnlineStatusCard(DriverProvider driver) {
    return GestureDetector(
      onTap: driver.isLoading ? null : () => driver.toggleOnline(),
      child: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: driver.isOnline
                ? [const Color(0xFF064E3B), const Color(0xFF065F46)]
                : [AppColors.surfaceDark, const Color(0xFF1E293B)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(24),
          border: Border.all(
            color: driver.isOnline ? AppColors.success : Colors.white10,
            width: 1.5,
          ),
          boxShadow: [
            BoxShadow(
              color: driver.isOnline ? AppColors.success.withValues(alpha: 0.2) : Colors.black26,
              blurRadius: 20,
              offset: const Offset(0, 6),
            ),
          ],
        ),
        child: Column(
          children: [
            Row(
              children: [
                Container(
                  width: 14,
                  height: 14,
                  decoration: BoxDecoration(
                    color: driver.isOnline ? AppColors.success : AppColors.textMuted,
                    shape: BoxShape.circle,
                  ),
                ),
                const SizedBox(width: 10),
                Text(
                  driver.isOnline ? 'ONLINE & ACCEPTING JOBS' : 'YOU ARE OFFLINE',
                  style: TextStyle(
                    color: driver.isOnline ? Colors.white : AppColors.textMuted,
                    fontWeight: FontWeight.w900,
                    fontSize: 13,
                    letterSpacing: 0.5,
                  ),
                ),
                const Spacer(),
                driver.isLoading
                    ? const SizedBox(
                        width: 24,
                        height: 24,
                        child: CircularProgressIndicator(
                          strokeWidth: 2.5,
                          color: AppColors.primary,
                        ),
                      )
                    : Switch(
                        value: driver.isOnline,
                        activeThumbColor: AppColors.success,
                        activeTrackColor: AppColors.success.withValues(alpha: 0.3),
                        inactiveThumbColor: AppColors.textMuted,
                        inactiveTrackColor: Colors.white10,
                        onChanged: (val) => driver.toggleOnline(),
                      ),
              ],
            ),
            const SizedBox(height: 12),
            Text(
              driver.isOnline
                  ? 'Your GPS is transmitting live. Nearby rider requests will ring with an alert sound.'
                  : 'Turn your status online to start receiving ride and chauffeur hiring requests.',
              style: TextStyle(
                color: Colors.white.withValues(alpha: 0.8),
                fontSize: 13,
                height: 1.4,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildActiveRideCard(BuildContext context, Map<String, dynamic> ride) {
    final fare = ride['fare'] != null ? double.tryParse(ride['fare'].toString()) ?? 0.0 : 0.0;
    final status = (ride['status'] ?? 'accepted').toString().replaceAll('_', ' ').toUpperCase();

    return Card(
      color: AppColors.surfaceDark,
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(20),
        side: const BorderSide(color: AppColors.success, width: 1.5),
      ),
      child: Padding(
        padding: const EdgeInsets.all(18),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppColors.success.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    status,
                    style: const TextStyle(
                      color: AppColors.success,
                      fontWeight: FontWeight.w800,
                      fontSize: 11,
                    ),
                  ),
                ),
                Text(
                  '\$${fare.toStringAsFixed(2)}',
                  style: const TextStyle(
                    color: AppColors.success,
                    fontWeight: FontWeight.w900,
                    fontSize: 20,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 14),
            Text(
              '📍 Pickup: ${ride['pickup_location']}',
              style: const TextStyle(color: AppColors.textLight, fontSize: 13, fontWeight: FontWeight.w600),
            ),
            const SizedBox(height: 4),
            Text(
              '🏁 Destination: ${ride['dropoff_location']}',
              style: const TextStyle(color: AppColors.textMuted, fontSize: 13),
            ),
            const SizedBox(height: 16),
            ElevatedButton.icon(
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => ActiveTripScreen(ride: ride)),
                );
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: AppColors.backgroundDark,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                padding: const EdgeInsets.symmetric(vertical: 12),
              ),
              icon: const Icon(Icons.navigation_rounded),
              label: const Text('Open Trip Navigation & Controls', style: TextStyle(fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPendingVerificationCard(BuildContext context, Map<String, dynamic> item, DriverProvider driver) {
    final type = item['type'] ?? 'ride';
    final typeLabel = (item['type_label'] ?? (type == 'driver_booking' ? 'Chauffeur Booking' : 'Ride Service')).toString().toUpperCase();
    final code = item['code'] ?? item['id']?.toString() ?? '';
    final customer = item['customer_name'] ?? 'Customer';
    final amount = (item['amount'] as num?)?.toDouble() ?? 0.0;
    final currency = item['currency'] ?? 'USD';
    final pickup = item['pickup'] ?? 'N/A';
    final dropoff = item['dropoff'] ?? 'N/A';
    final schedule = item['schedule'] ?? 'Immediate';
    final vehicle = item['vehicle'] ?? 'Standard';

    Color typeColor = Colors.amber;
    if (type == 'driver_booking') {
      typeColor = AppColors.purple;
    } else if (type == 'package_delivery') {
      typeColor = AppColors.info;
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: Colors.amber.withValues(alpha: 0.5), width: 1.5),
        boxShadow: [
          BoxShadow(
            color: Colors.amber.withValues(alpha: 0.1),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(18),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Top Header: Type badge and Amount
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: typeColor.withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: typeColor, width: 1),
                        ),
                        child: Text(
                          typeLabel,
                          style: TextStyle(
                            color: typeColor,
                            fontSize: 10,
                            fontWeight: FontWeight.w900,
                            letterSpacing: 0.5,
                          ),
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        'Booking ID: #$code',
                        style: const TextStyle(
                          color: AppColors.textLight,
                          fontWeight: FontWeight.bold,
                          fontSize: 15,
                        ),
                      ),
                      Text(
                        'Customer: $customer',
                        style: const TextStyle(color: AppColors.textMuted, fontSize: 12),
                      ),
                    ],
                  ),
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      '\$${amount.toStringAsFixed(2)} $currency',
                      style: const TextStyle(
                        color: AppColors.success,
                        fontWeight: FontWeight.w900,
                        fontSize: 18,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Row(
                      mainAxisSize: MainAxisSize.min,
                      children: const [
                        Icon(Icons.credit_card_rounded, color: Colors.amber, size: 12),
                        SizedBox(width: 4),
                        Text(
                          'Stripe Verification Pending',
                          style: TextStyle(color: Colors.amber, fontSize: 10, fontWeight: FontWeight.bold),
                        ),
                      ],
                    ),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 12),

            // Details Container
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.backgroundDark,
                borderRadius: BorderRadius.circular(14),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('📍 ', style: TextStyle(fontSize: 12)),
                      const Text('Pickup: ', style: TextStyle(color: AppColors.textMuted, fontSize: 12, fontWeight: FontWeight.bold)),
                      Expanded(
                        child: Text(
                          pickup,
                          style: const TextStyle(color: AppColors.textLight, fontSize: 12),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('🏁 ', style: TextStyle(fontSize: 12)),
                      const Text('Dropoff: ', style: TextStyle(color: AppColors.textMuted, fontSize: 12, fontWeight: FontWeight.bold)),
                      Expanded(
                        child: Text(
                          dropoff,
                          style: const TextStyle(color: AppColors.textLight, fontSize: 12),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      const Text('📅 ', style: TextStyle(fontSize: 12)),
                      const Text('Date & Time: ', style: TextStyle(color: AppColors.textMuted, fontSize: 12, fontWeight: FontWeight.bold)),
                      Expanded(
                        child: Text(
                          schedule,
                          style: const TextStyle(color: AppColors.textLight, fontSize: 12),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      const Text('🚘 ', style: TextStyle(fontSize: 12)),
                      const Text('Vehicle Info: ', style: TextStyle(color: AppColors.textMuted, fontSize: 12, fontWeight: FontWeight.bold)),
                      Expanded(
                        child: Text(
                          vehicle,
                          style: const TextStyle(color: AppColors.textLight, fontSize: 12),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 14),

            // Action Buttons
            Row(
              children: [
                Expanded(
                  flex: 2,
                  child: OutlinedButton(
                    onPressed: () async {
                      final confirmed = await showDialog<bool>(
                        context: context,
                        builder: (ctx) => AlertDialog(
                          backgroundColor: AppColors.surfaceDark,
                          title: const Text('Reject Verification', style: TextStyle(color: Colors.white)),
                          content: const Text(
                            'Are you sure you want to reject this booking verification request?',
                            style: TextStyle(color: AppColors.textMuted),
                          ),
                          actions: [
                            TextButton(
                              onPressed: () => Navigator.pop(ctx, false),
                              child: const Text('Cancel', style: TextStyle(color: AppColors.textMuted)),
                            ),
                            ElevatedButton(
                              style: ElevatedButton.styleFrom(backgroundColor: AppColors.danger),
                              onPressed: () => Navigator.pop(ctx, true),
                              child: const Text('Reject', style: TextStyle(color: Colors.white)),
                            ),
                          ],
                        ),
                      );

                      if (confirmed == true) {
                        final ok = await driver.verifyBooking(
                          serviceType: type,
                          serviceId: item['id'],
                          action: 'reject',
                          rejectionReason: 'Driver schedule or vehicle mismatch',
                        );
                        if (context.mounted && ok) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(content: Text('Verification rejected.')),
                          );
                        }
                      }
                    },
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppColors.danger,
                      side: BorderSide(color: AppColors.danger.withValues(alpha: 0.5)),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                    ),
                    child: const Text('✕ Reject Verification', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11)),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  flex: 3,
                  child: ElevatedButton(
                    onPressed: () async {
                      final ok = await driver.verifyBooking(
                        serviceType: type,
                        serviceId: item['id'],
                        action: 'approve',
                      );
                      if (context.mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(ok ? '✓ Booking details verified and approved!' : 'Failed to approve verification.'),
                            backgroundColor: ok ? AppColors.success : AppColors.danger,
                          ),
                        );
                      }
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.success,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                    ),
                    child: const Text('✓ Approve & Verify Details', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildAvailableJobCard(BuildContext context, Map<String, dynamic> job, DriverProvider driver) {
    final fare = (job['fare'] ?? job['total_price'] ?? 0.0) as num;
    final pickup = job['pickup_location'] ?? 'Pickup location';
    final dropoff = job['dropoff_location'] ?? 'Destination';
    final riderName = job['rider_name'] ?? job['client_name'] ?? 'Rider';
    final vehicleType = job['vehicle_type'] ?? 'Standard';
    final isDriverBooking = job['type'] == 'driver_booking';

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: AppColors.primary.withValues(alpha: 0.4), width: 1.5),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withValues(alpha: 0.12),
            blurRadius: 18,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(18),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Header Row: Type Badge & Fare
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                  decoration: BoxDecoration(
                    color: isDriverBooking
                        ? AppColors.purple.withValues(alpha: 0.2)
                        : AppColors.primary.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(
                      color: isDriverBooking ? AppColors.purple : AppColors.primary,
                      width: 1,
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        isDriverBooking ? Icons.badge_rounded : Icons.local_taxi_rounded,
                        size: 14,
                        color: isDriverBooking ? AppColors.purple : AppColors.primary,
                      ),
                      const SizedBox(width: 6),
                      Text(
                        isDriverBooking ? 'DRIVER HIRING' : '$vehicleType RIDE',
                        style: TextStyle(
                          color: isDriverBooking ? AppColors.purple : AppColors.primary,
                          fontSize: 11,
                          fontWeight: FontWeight.w900,
                          letterSpacing: 0.5,
                        ),
                      ),
                    ],
                  ),
                ),
                Text(
                  '\$${fare.toStringAsFixed(2)}',
                  style: const TextStyle(
                    color: AppColors.success,
                    fontSize: 22,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),

            // Rider Name
            Text(
              'Rider: $riderName',
              style: const TextStyle(
                color: AppColors.textLight,
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 10),

            // Locations Card
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.backgroundDark,
                borderRadius: BorderRadius.circular(14),
              ),
              child: Column(
                children: [
                  Row(
                    children: [
                      const Icon(Icons.circle, color: AppColors.success, size: 10),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          pickup,
                          style: const TextStyle(color: AppColors.textLight, fontSize: 12, fontWeight: FontWeight.w600),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  if (dropoff.isNotEmpty) ...[
                    const Padding(
                      padding: EdgeInsets.only(left: 4),
                      child: Align(
                        alignment: Alignment.centerLeft,
                        child: SizedBox(height: 10, child: VerticalDivider(color: Colors.white24)),
                      ),
                    ),
                    Row(
                      children: [
                        const Icon(Icons.location_on_rounded, color: AppColors.danger, size: 12),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            dropoff,
                            style: const TextStyle(color: AppColors.textLight, fontSize: 12, fontWeight: FontWeight.w600),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                  ],
                ],
              ),
            ),
            const SizedBox(height: 14),

            // Action Buttons Row: Accept & Decline
            Row(
              children: [
                Expanded(
                  flex: 3,
                  child: ElevatedButton.icon(
                    onPressed: () async {
                      final ok = await driver.respondToRequest(
                        job['assignment_id'],
                        'accept',
                        rideId: job['ride_id'],
                      );
                      if (ok && context.mounted && driver.activeRides.isNotEmpty) {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => ActiveTripScreen(ride: driver.activeRides.first),
                          ),
                        );
                      }
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.success,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                    ),
                    icon: const Icon(Icons.check_circle_rounded, size: 18),
                    label: Text(
                      'Accept & Earn \$${fare.toStringAsFixed(2)}',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  flex: 1,
                  child: OutlinedButton(
                    onPressed: () async {
                      await driver.respondToRequest(
                        job['assignment_id'],
                        'reject',
                        rideId: job['ride_id'],
                      );
                    },
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppColors.textMuted,
                      side: const BorderSide(color: Colors.white24),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                    ),
                    child: const Text('Decline', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 12)),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEarningTile(String label, String value, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: Colors.white.withValues(alpha: 0.06)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            style: const TextStyle(
              color: AppColors.textMuted,
              fontSize: 10,
              fontWeight: FontWeight.w800,
              letterSpacing: 0.5,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            value,
            style: TextStyle(
              color: color,
              fontSize: 22,
              fontWeight: FontWeight.w900,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDrawer(BuildContext context, AuthProvider auth) {
    return Drawer(
      backgroundColor: AppColors.surfaceDark,
      child: SafeArea(
        child: Column(
          children: [
            // Driver Profile Header
            Padding(
              padding: const EdgeInsets.all(20.0),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 26,
                    backgroundColor: AppColors.primary,
                    child: Text(
                      (auth.userName ?? 'D')[0].toUpperCase(),
                      style: const TextStyle(
                        color: AppColors.backgroundDark,
                        fontWeight: FontWeight.bold,
                        fontSize: 20,
                      ),
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          auth.userName ?? 'Sipho Ndlovu',
                          style: const TextStyle(
                            color: AppColors.textLight,
                            fontWeight: FontWeight.bold,
                            fontSize: 16,
                          ),
                        ),
                        Text(
                          auth.userEmail ?? '',
                          style: const TextStyle(color: AppColors.textMuted, fontSize: 12),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const Divider(color: Colors.white10, height: 1),

            // Quick Action Buttons (Dashboard, Earnings, Help)
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 14, 16, 10),
              child: Row(
                children: [
                  Expanded(
                    child: _buildDrawerActionBtn(
                      icon: Icons.dashboard_rounded,
                      label: 'Dashboard',
                      onTap: () => Navigator.pop(context),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _buildDrawerActionBtn(
                      icon: Icons.monetization_on_outlined,
                      label: 'Earnings',
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.push(context, MaterialPageRoute(builder: (_) => const DriverEarningsScreen()));
                      },
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _buildDrawerActionBtn(
                      icon: Icons.help_outline_rounded,
                      label: 'Help',
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.push(context, MaterialPageRoute(builder: (_) => const HelpSupportScreen()));
                      },
                    ),
                  ),
                ],
              ),
            ),

            const Divider(color: Colors.white10, height: 1),

            // Navigation List
            Expanded(
              child: ListView(
                padding: const EdgeInsets.symmetric(vertical: 6),
                children: [
                  ListTile(
                    leading: const Icon(Icons.dashboard_rounded, color: AppColors.primary),
                    title: const Text('Driver Dashboard', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w600)),
                    onTap: () => Navigator.pop(context),
                  ),
                  ListTile(
                    leading: const Icon(Icons.directions_car_filled_rounded, color: AppColors.info),
                    title: const Text('Ride History / My Trips', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w600)),
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(context, MaterialPageRoute(builder: (_) => const DriverTripsScreen()));
                    },
                  ),
                  ListTile(
                    leading: const Icon(Icons.person_outline_rounded, color: AppColors.purple),
                    title: const Text('Manage Account & Profile', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w600)),
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(context, MaterialPageRoute(builder: (_) => const ManageAccountScreen()));
                    },
                  ),
                  ListTile(
                    leading: const Icon(Icons.account_balance_wallet_rounded, color: AppColors.success),
                    title: const Text('Earnings & Payouts', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w600)),
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(context, MaterialPageRoute(builder: (_) => const DriverEarningsScreen()));
                    },
                  ),
                  ListTile(
                    leading: const Icon(Icons.notifications_rounded, color: AppColors.primary),
                    title: const Text('Notifications', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w600)),
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(context, MaterialPageRoute(builder: (_) => const NotificationsScreen()));
                    },
                  ),
                  ListTile(
                    leading: const Icon(Icons.headset_mic_rounded, color: AppColors.info),
                    title: const Text('Driver Support & Safety', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w600)),
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(context, MaterialPageRoute(builder: (_) => const HelpSupportScreen()));
                    },
                  ),
                ],
              ),
            ),

            const Divider(color: Colors.white10, height: 1),
            ListTile(
              leading: const Icon(Icons.logout_rounded, color: AppColors.danger),
              title: const Text('Log Out', style: TextStyle(color: AppColors.danger, fontWeight: FontWeight.bold)),
              onTap: () async {
                await auth.logout();
                if (context.mounted) {
                  Navigator.pushAndRemoveUntil(
                    context,
                    MaterialPageRoute(builder: (_) => const DriverLoginScreen()),
                    (route) => false,
                  );
                }
              },
            ),
            const SizedBox(height: 8),
          ],
        ),
      ),
    );
  }

  Widget _buildDrawerActionBtn({required IconData icon, required String label, required VoidCallback onTap}) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10),
        decoration: BoxDecoration(
          color: AppColors.backgroundDark,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.white.withValues(alpha: 0.06)),
        ),
        child: Column(
          children: [
            Icon(icon, color: AppColors.textLight, size: 20),
            const SizedBox(height: 4),
            Text(label, style: const TextStyle(color: AppColors.textLight, fontSize: 11, fontWeight: FontWeight.bold)),
          ],
        ),
      ),
    );
  }
}
