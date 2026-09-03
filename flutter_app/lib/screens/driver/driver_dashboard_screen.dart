import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../providers/driver_provider.dart';
import '../../providers/notification_provider.dart';
import '../notifications/notifications_screen.dart';
import '../rider/rider_home_screen.dart';
import '../auth/login_screen.dart';
import 'active_trip_screen.dart';
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

      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (_) => IncomingJobDialog(
          request: job,
          onAccept: () async {
            Navigator.pop(context);
            _dialogOpen = false;
            final ok = await driver.respondToRequest(job['assignment_id'], 'accept');
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
            await driver.respondToRequest(job['assignment_id'], 'reject');
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

              // Quick Actions
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
                          color: AppColors.primary.withOpacity(0.15),
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
                              'Verified Professional Driver',
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
    return Container(
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
            color: driver.isOnline ? AppColors.success.withOpacity(0.2) : Colors.black26,
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
              Switch(
                value: driver.isOnline,
                activeColor: AppColors.success,
                activeTrackColor: AppColors.success.withOpacity(0.3),
                inactiveThumbColor: AppColors.textMuted,
                inactiveTrackColor: Colors.white10,
                onChanged: (_) => driver.toggleOnline(),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            driver.isOnline
                ? 'Your GPS is transmitting live. Nearby rider requests will ring with an alert sound.'
                : 'Turn your status online to start receiving ride and chauffeur hiring requests.',
            style: TextStyle(
              color: Colors.white.withOpacity(0.8),
              fontSize: 13,
              height: 1.4,
            ),
          ),
        ],
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
                    color: AppColors.success.withOpacity(0.15),
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

  Widget _buildEarningTile(String label, String value, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: Colors.white.withOpacity(0.06)),
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
            Padding(
              padding: const EdgeInsets.all(24.0),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 28,
                    backgroundColor: AppColors.primary,
                    child: Text(
                      (auth.userName ?? 'D')[0].toUpperCase(),
                      style: const TextStyle(
                        color: AppColors.backgroundDark,
                        fontWeight: FontWeight.bold,
                        fontSize: 22,
                      ),
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          auth.userName ?? 'Driver',
                          style: const TextStyle(
                            color: AppColors.textLight,
                            fontWeight: FontWeight.bold,
                            fontSize: 16,
                          ),
                        ),
                        Text(
                          auth.userEmail ?? '',
                          style: const TextStyle(color: AppColors.textMuted, fontSize: 12),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const Divider(color: Colors.white10),
            ListTile(
              leading: const Icon(Icons.swap_horiz_rounded, color: AppColors.primary),
              title: const Text('Switch to Rider Mode', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold)),
              onTap: () {
                Navigator.pop(context);
                auth.switchRole('customer');
                Navigator.pushReplacement(
                  context,
                  MaterialPageRoute(builder: (_) => const RiderHomeScreen()),
                );
              },
            ),
            ListTile(
              leading: const Icon(Icons.notifications_rounded, color: AppColors.info),
              title: const Text('Notifications', style: TextStyle(color: AppColors.textLight)),
              onTap: () {
                Navigator.pop(context);
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const NotificationsScreen()),
                );
              },
            ),
            const Spacer(),
            const Divider(color: Colors.white10),
            ListTile(
              leading: const Icon(Icons.logout_rounded, color: AppColors.danger),
              title: const Text('Log Out', style: TextStyle(color: AppColors.danger, fontWeight: FontWeight.bold)),
              onTap: () async {
                await auth.logout();
                if (context.mounted) {
                  Navigator.pushAndRemoveUntil(
                    context,
                    MaterialPageRoute(builder: (_) => const LoginScreen()),
                    (route) => false,
                  );
                }
              },
            ),
            const SizedBox(height: 12),
          ],
        ),
      ),
    );
  }
}
