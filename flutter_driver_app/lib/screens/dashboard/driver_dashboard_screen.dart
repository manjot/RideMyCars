import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../providers/country_provider.dart';
import '../../providers/driver_provider.dart';
import '../../providers/notification_provider.dart';
import '../account/manage_account_screen.dart';
import '../auth/driver_login_screen.dart';
import '../earnings/driver_earnings_screen.dart';
import '../earnings/incentives_screen.dart';
import '../notifications/notifications_screen.dart';
import '../support/help_support_screen.dart';
import '../safety/sos_contacts_screen.dart';
import '../safety/widgets/sos_floating_button.dart';
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

  Future<void> _callPhone(String? phone) async {
    if (phone == null || phone.isEmpty) return;
    final uri = Uri.parse('tel:$phone');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }

  Future<void> _handleToggleOnline(DriverProvider driver) async {
    final success = await driver.toggleOnline();
    if (!mounted) return;
    if (!success && driver.errorMessage != null && driver.errorMessage!.isNotEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Row(
            children: [
              const Icon(Icons.info_outline_rounded, color: Colors.white, size: 20),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  driver.errorMessage!,
                  style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                ),
              ),
            ],
          ),
          backgroundColor: AppColors.danger,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          duration: const Duration(seconds: 4),
          action: SnackBarAction(
            label: 'OK',
            textColor: Colors.white,
            onPressed: () => ScaffoldMessenger.of(context).hideCurrentSnackBar(),
          ),
        ),
      );
    }
  }

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final country = Provider.of<CountryProvider>(context, listen: false);
      country.autoDetectCountry();
      final driver = Provider.of<DriverProvider>(context, listen: false);
      driver.init();
      Provider.of<NotificationProvider>(context, listen: false).startPolling();
    });
  }

  void _openJobDialog(Map<String, dynamic> job, DriverProvider driver) {
    if (_dialogOpen) return;
    _dialogOpen = true;

    final assignmentId = int.tryParse((job['assignment_id'] ?? job['id'] ?? '').toString());
    final rideId = int.tryParse((job['ride_id'] ?? job['ride']?['id'] ?? '').toString());
    final deliveryId = int.tryParse((job['package_delivery_id'] ?? job['delivery_id'] ?? '').toString());
    final bookingId = int.tryParse((job['driver_booking_id'] ?? job['booking_id'] ?? '').toString());
    final isBackup = job['is_backup'] == true || job['assignment_type'] == 'backup';

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (dialogCtx) => IncomingJobDialog(
        request: job,
        onAccept: () async {
          bool ok = false;
          try {
            ok = await driver.respondToRequest(
              assignmentId,
              'accept',
              rideId: rideId,
              deliveryId: deliveryId,
              bookingId: bookingId,
              jobData: job,
            );
          } catch (e) {
            debugPrint('Error responding to assignment: $e');
          } finally {
            if (dialogCtx.mounted) {
              Navigator.of(dialogCtx, rootNavigator: true).pop();
            }
            _dialogOpen = false;
          }

          if (!mounted) return;

          if (ok) {
            if (isBackup || driver.lastAssignmentResponse?['reserved'] == true) {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('✓ Reserved as Backup Chauffeur! Awaiting customer confirmation.'),
                  backgroundColor: Colors.amber,
                  duration: Duration(seconds: 5),
                ),
              );
            } else {
              final acceptedRide = driver.lastAcceptedRide ??
                  driver.lastAssignmentResponse?['ride'] ??
                  driver.lastAssignmentResponse?['delivery'] ??
                  (driver.activeRides.isNotEmpty ? driver.activeRides.first : job);

              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => ActiveTripScreen(ride: Map<String, dynamic>.from(acceptedRide)),
                ),
              );

              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('✓ Ride Accepted! Opening navigation.'),
                  backgroundColor: AppColors.success,
                  duration: Duration(seconds: 3),
                ),
              );
            }
          } else {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(driver.errorMessage ?? 'Could not accept ride. It may have expired or been assigned.'),
                backgroundColor: AppColors.danger,
              ),
            );
          }
        },
        onDecline: () async {
          if (dialogCtx.mounted) {
            Navigator.of(dialogCtx, rootNavigator: true).pop();
          }
          _dialogOpen = false;
          await driver.respondToRequest(
            assignmentId,
            'reject',
            rideId: rideId,
            deliveryId: deliveryId,
            bookingId: bookingId,
          );
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('Job declined.'),
                duration: Duration(seconds: 2),
              ),
            );
          }
        },
      ),
    ).then((_) => _dialogOpen = false);
  }

  void _checkIncomingJobs(DriverProvider driver) {
    if (driver.pendingRequests.isNotEmpty && !_dialogOpen) {
      _openJobDialog(driver.pendingRequests.first, driver);
    }
  }

  void _showCountrySelector(CountryProvider countryProv) {
    showModalBottomSheet(
      context: context,
      backgroundColor: const Color(0xFF0F172A),
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => SafeArea(
        top: false,
        bottom: true,
        child: Padding(
          padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).padding.bottom),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const SizedBox(height: 12),
              Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.white24,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              Padding(
                padding: const EdgeInsets.fromLTRB(20, 16, 20, 10),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Select Currency & Country',
                      style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close, color: Colors.white60, size: 20),
                      onPressed: () => Navigator.pop(ctx),
                    ),
                  ],
                ),
              ),
              Flexible(
                child: ListView.builder(
                  shrinkWrap: true,
                  itemCount: CountryProvider.supportedCountries.length,
                  itemBuilder: (_, idx) {
                    final item = CountryProvider.supportedCountries[idx];
                    final isSelected = item.code == countryProv.selectedCountryCode;
                    return ListTile(
                      leading: Text(item.flag, style: const TextStyle(fontSize: 24)),
                      title: Text(item.name, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13.5)),
                      subtitle: Text('${item.currency} (${item.symbol})', style: const TextStyle(color: Colors.white60, fontSize: 12)),
                      trailing: isSelected
                          ? const Icon(Icons.check_circle_rounded, color: Color(0xFF3B82F6), size: 22)
                          : null,
                      onTap: () async {
                        final code = item.code;
                        Navigator.pop(ctx);
                        final driver = Provider.of<DriverProvider>(context, listen: false);
                        await countryProv.setCountry(code);
                        await driver.fetchEarnings();
                        await driver.fetchActiveRides();
                        await driver.pollPendingRequests();
                      },
                    );
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final driver = Provider.of<DriverProvider>(context);
    final notifs = Provider.of<NotificationProvider>(context);
    final countryProv = Provider.of<CountryProvider>(context);

    // Watch for incoming jobs
    WidgetsBinding.instance.addPostFrameCallback((_) => _checkIncomingJobs(driver));

    return Scaffold(
      backgroundColor: AppColors.backgroundDark,
      drawer: _buildDrawer(context, auth),
      floatingActionButton: Builder(
        builder: (ctx) {
          final activeRide = driver.activeRides.isNotEmpty ? driver.activeRides.first : null;
          final custPhone = activeRide?['customer_phone'] ??
              activeRide?['rider']?['phone'] ??
              activeRide?['rider_phone'] ??
              activeRide?['passenger_phone'];
          final custName = activeRide?['customer_name'] ??
              activeRide?['rider']?['name'] ??
              activeRide?['rider_name'] ??
              activeRide?['passenger_name'] ??
              'Passenger';
          final rideId = activeRide?['id'] as int?;

          return SosFloatingButton(
            role: 'driver',
            rideId: rideId,
            assignedPhone: custPhone?.toString(),
            assignedName: custName.toString(),
          );
        },
      ),
      appBar: AppBar(
        backgroundColor: AppColors.surfaceDark,
        elevation: 0,
        titleSpacing: 10,
        title: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 32,
              height: 32,
              decoration: BoxDecoration(
                color: AppColors.primary,
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.local_taxi_rounded, color: AppColors.backgroundDark, size: 20),
            ),
            const SizedBox(width: 8),
            const Flexible(
              child: Text(
                'Driver Console',
                style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w900, fontSize: 16.5),
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
        actions: [
          // Country & Currency Selector Pill (Matches Rider App UI: 🇮🇳 IND ₹ ▾)
          GestureDetector(
            onTap: () => _showCountrySelector(countryProv),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
              margin: const EdgeInsets.symmetric(vertical: 9),
              decoration: BoxDecoration(
                color: AppColors.surfaceDark,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: AppColors.primary.withOpacity(0.5)),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.35),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    '${countryProv.flag} ${countryProv.selectedCountryCode} ${countryProv.currencySymbol}',
                    style: const TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(width: 2),
                  const Icon(Icons.arrow_drop_down_rounded, color: AppColors.primary, size: 18),
                ],
              ),
            ),
          ),
          const SizedBox(width: 4),
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
          // Driver Avatar in top corner
          Builder(
            builder: (btnCtx) => GestureDetector(
              onTap: () => Scaffold.of(btnCtx).openDrawer(),
              child: Container(
                margin: const EdgeInsets.only(right: 12),
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(color: AppColors.primary, width: 2),
                ),
                child: CircleAvatar(
                  radius: 17,
                  backgroundColor: AppColors.primary,
                  backgroundImage: (auth.avatarUrl != null && auth.avatarUrl!.isNotEmpty)
                      ? NetworkImage(auth.avatarUrl!)
                      : null,
                  child: (auth.avatarUrl == null || auth.avatarUrl!.isEmpty)
                      ? Text(
                          (auth.userName ?? 'D').trim().isNotEmpty
                              ? (auth.userName ?? 'D').trim()[0].toUpperCase()
                              : 'D',
                          style: const TextStyle(
                            color: AppColors.backgroundDark,
                            fontWeight: FontWeight.bold,
                            fontSize: 14,
                          ),
                        )
                      : null,
                ),
              ),
            ),
          ),
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
              // Sticky High-Visibility Banner for Incoming Jobs (always visible when ringtone/order arrives)
              _buildIncomingJobStickyBanner(driver),

              // Online / Offline Toggle Banner
              _buildOnlineStatusCard(driver),
              const SizedBox(height: 14),

              // Incentive Quick Banner
              _buildIncentiveQuickBanner(context),
              const SizedBox(height: 20),

              // Active Rides Section (PRIORITY #1 AT TOP OF CONSOLE)
              if (driver.activeRides.isNotEmpty) ...[
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Row(
                        children: [
                          Container(
                            width: 10,
                            height: 10,
                            decoration: const BoxDecoration(
                              color: AppColors.success,
                              shape: BoxShape.circle,
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              'Active Trip (${driver.activeRides.length})',
                              style: const TextStyle(
                                color: AppColors.textLight,
                                fontSize: 17,
                                fontWeight: FontWeight.w800,
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.refresh_rounded, color: AppColors.success, size: 20),
                      onPressed: () => driver.fetchActiveRides(),
                      tooltip: 'Refresh Active Trips',
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                ...driver.activeRides.map((r) => _buildActiveRideCard(context, r, driver)),
                const SizedBox(height: 20),
              ],

              // Pending Payment & Booking Verification Requests (Parity with Web Dashboard!)
              if (driver.pendingVerifications.isNotEmpty) ...[
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Row(
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
                          Expanded(
                            child: Text(
                              'Pending Verifications (${driver.pendingVerifications.length})',
                              style: const TextStyle(
                                color: AppColors.textLight,
                                fontSize: 17,
                                fontWeight: FontWeight.w800,
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
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
                    Expanded(
                      child: Row(
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
                          Expanded(
                            child: Text(
                              'Available Jobs (${driver.pendingRequests.length})',
                              style: const TextStyle(
                                color: AppColors.textLight,
                                fontSize: 17,
                                fontWeight: FontWeight.w800,
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),
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
              ] else if (driver.isOnline && driver.pendingVerifications.isEmpty && driver.activeRides.isEmpty) ...[
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
                          color: AppColors.primary.withOpacity(0.15),
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
                    child: _buildEarningTile('TODAY', '${countryProv.currencySymbol}${(driver.earnings['today'] as num?)?.toStringAsFixed(2) ?? "0.00"}', AppColors.success),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _buildEarningTile('THIS WEEK', '${countryProv.currencySymbol}${(driver.earnings['week'] as num?)?.toStringAsFixed(2) ?? "0.00"}', AppColors.info),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: _buildEarningTile('THIS MONTH', '${countryProv.currencySymbol}${(driver.earnings['month'] as num?)?.toStringAsFixed(2) ?? "0.00"}', AppColors.purple),
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
                        width: 48,
                        height: 48,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          border: Border.all(color: AppColors.primary, width: 2),
                        ),
                        child: ClipOval(
                          child: (auth.avatarUrl != null && auth.avatarUrl!.isNotEmpty)
                              ? Image.network(
                                  auth.avatarUrl!,
                                  fit: BoxFit.cover,
                                  errorBuilder: (_, __, ___) => Container(
                                    color: AppColors.primary,
                                    alignment: Alignment.center,
                                    child: Text(
                                      (auth.userName ?? 'D').trim().isNotEmpty ? (auth.userName ?? 'D').trim()[0].toUpperCase() : 'D',
                                      style: const TextStyle(color: AppColors.backgroundDark, fontWeight: FontWeight.bold, fontSize: 18),
                                    ),
                                  ),
                                )
                              : Container(
                                  color: AppColors.primary,
                                  alignment: Alignment.center,
                                  child: Text(
                                    (auth.userName ?? 'D').trim().isNotEmpty ? (auth.userName ?? 'D').trim()[0].toUpperCase() : 'D',
                                    style: const TextStyle(color: AppColors.backgroundDark, fontWeight: FontWeight.bold, fontSize: 18),
                                  ),
                                ),
                        ),
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

  Widget _buildIncomingJobStickyBanner(DriverProvider driver) {
    if (driver.pendingRequests.isEmpty) {
      return const SizedBox.shrink();
    }

    final hasRequests = driver.pendingRequests.isNotEmpty;
    final firstJob = hasRequests ? driver.pendingRequests.first : null;
    final rawFare = firstJob?['fare'] ?? firstJob?['total_price'] ?? 0.0;
    final fare = double.tryParse(rawFare.toString()) ?? 0.0;
    final countryProv = Provider.of<CountryProvider>(context, listen: false);
    final bannerCurrSym = (firstJob?['currency_symbol'] ?? countryProv.currencySymbol).toString();
    final pickup = (firstJob?['pickup_location'] ?? 'Pickup available').toString();
    final count = driver.pendingRequests.length;
    final bannerTitle = count == 1 ? '1 INCOMING ORDER' : '$count INCOMING ORDERS';

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF991B1B), Color(0xFFDC2626)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: Colors.amberAccent, width: 2),
        boxShadow: [
          BoxShadow(
            color: Colors.red.withOpacity(0.4),
            blurRadius: 20,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(22),
          onTap: () {
            if (hasRequests && firstJob != null) {
              _openJobDialog(firstJob, driver);
            }
          },
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: const BoxDecoration(
                    color: Colors.white,
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.campaign_rounded, color: Colors.red, size: 24),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              color: Colors.white24,
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              bannerTitle,
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 10,
                                fontWeight: FontWeight.w900,
                                letterSpacing: 0.5,
                              ),
                            ),
                          ),
                          if (fare > 0) ...[
                            const SizedBox(width: 8),
                            Text(
                              '+$bannerCurrSym${fare.toStringAsFixed(2)}',
                              style: const TextStyle(
                                color: Color(0xFFFDE047),
                                fontWeight: FontWeight.w900,
                                fontSize: 15,
                              ),
                            ),
                          ],
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Pickup: $pickup',
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w700,
                          fontSize: 12,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                ElevatedButton(
                  onPressed: () {
                    if (hasRequests && firstJob != null) {
                      _openJobDialog(firstJob, driver);
                    }
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.white,
                    foregroundColor: Colors.red.shade900,
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    elevation: 4,
                  ),
                  child: const Text('VIEW', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 12)),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Future<void> _confirmEndTrip(BuildContext context, int rideId, double fare, DriverProvider driver, {String currencySymbol = '\$'}) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppColors.surfaceDark,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.check_circle_rounded, color: AppColors.success, size: 24),
            SizedBox(width: 10),
            Text('End Ride?', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 18)),
          ],
        ),
        content: Text(
          'Are you sure you want to end this trip and collect the fare of $currencySymbol${fare.toStringAsFixed(2)}?',
          style: const TextStyle(color: AppColors.textLight, fontSize: 14),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Continue Trip', style: TextStyle(color: AppColors.textMuted)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.success,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('✓ End Trip Now', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );

    if (confirmed == true) {
      final ok = await driver.updateRideStatus(rideId, 'completed');
      if (ok && context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('🎉 Trip Completed! Fare added to your earnings.'),
            backgroundColor: AppColors.success,
            duration: Duration(seconds: 4),
          ),
        );
      }
    }
  }

  Future<void> _showCancelTripDialog(BuildContext context, Map<String, dynamic> ride, DriverProvider driver) async {
    final rideId = int.tryParse(ride['id']?.toString() ?? '0') ?? 0;
    if (rideId <= 0) return;

    String selectedReason = 'Passenger no-show';
    final reasons = [
      'Passenger no-show',
      'Vehicle issue / Flat tire',
      'Passenger requested cancellation',
      'Wrong pickup location',
      'Emergency',
      'Other',
    ];

    final confirmed = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (sheetCtx) => StatefulBuilder(
        builder: (ctx, setSheetState) => Container(
          decoration: const BoxDecoration(
            color: AppColors.surfaceDark,
            borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
          ),
          padding: EdgeInsets.fromLTRB(24, 20, 24, MediaQuery.of(sheetCtx).viewInsets.bottom + 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.white24,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 18),
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: AppColors.danger.withOpacity(0.15),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.cancel_rounded, color: AppColors.danger, size: 24),
                  ),
                  const SizedBox(width: 14),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Cancel Active Trip',
                          style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                        ),
                        SizedBox(height: 2),
                        Text(
                          'Select a reason to cancel',
                          style: TextStyle(color: AppColors.textMuted, fontSize: 12),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              ...reasons.map((r) => InkWell(
                    onTap: () => setSheetState(() => selectedReason = r),
                    borderRadius: BorderRadius.circular(12),
                    child: Container(
                      margin: const EdgeInsets.only(bottom: 8),
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      decoration: BoxDecoration(
                        color: selectedReason == r ? AppColors.danger.withOpacity(0.12) : AppColors.backgroundDark,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: selectedReason == r ? AppColors.danger : Colors.white10,
                          width: selectedReason == r ? 1.5 : 1,
                        ),
                      ),
                      child: Row(
                        children: [
                          Icon(
                            selectedReason == r ? Icons.radio_button_checked : Icons.radio_button_off,
                            color: selectedReason == r ? AppColors.danger : AppColors.textMuted,
                            size: 18,
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Text(
                              r,
                              style: TextStyle(
                                color: selectedReason == r ? Colors.white : AppColors.textLight,
                                fontWeight: selectedReason == r ? FontWeight.bold : FontWeight.normal,
                                fontSize: 13,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  )),
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: SizedBox(
                      height: 50,
                      child: OutlinedButton(
                        onPressed: () => Navigator.pop(sheetCtx, false),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: AppColors.textMuted,
                          side: const BorderSide(color: Colors.white24),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        ),
                        child: const Text('Keep Trip', style: TextStyle(fontWeight: FontWeight.bold)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: SizedBox(
                      height: 50,
                      child: ElevatedButton(
                        onPressed: () => Navigator.pop(sheetCtx, true),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.danger,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                          elevation: 4,
                        ),
                        child: const Text('Confirm Cancel', style: TextStyle(fontWeight: FontWeight.bold)),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );

    if (confirmed == true && context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Row(
            children: [
              SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)),
              SizedBox(width: 12),
              Text('Cancelling trip...'),
            ],
          ),
          duration: Duration(seconds: 2),
        ),
      );

      final ok = await driver.cancelRide(rideId, reason: selectedReason);
      if (context.mounted) {
        ScaffoldMessenger.of(context).hideCurrentSnackBar();
        if (ok) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('✓ Trip #$rideId has been cancelled. You are now available for new rides.'),
              backgroundColor: AppColors.success,
            ),
          );
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Failed to cancel trip. Please check your network connection.'),
              backgroundColor: AppColors.danger,
            ),
          );
        }
      }
    }
  }

  Widget _buildOnlineStatusCard(DriverProvider driver) {
    return GestureDetector(
      onTap: driver.isLoading ? null : () => _handleToggleOnline(driver),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: driver.isOnline
                ? [const Color(0xFF064E3B), const Color(0xFF065F46)]
                : [AppColors.surfaceDark, const Color(0xFF1E293B)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(22),
          border: Border.all(
            color: driver.isOnline ? AppColors.success : Colors.white10,
            width: 1.5,
          ),
          boxShadow: [
            BoxShadow(
              color: driver.isOnline ? AppColors.success.withOpacity(0.2) : Colors.black26,
              blurRadius: 18,
              offset: const Offset(0, 5),
            ),
          ],
        ),
        child: Column(
          children: [
            Row(
              children: [
                Container(
                  width: 12,
                  height: 12,
                  decoration: BoxDecoration(
                    color: driver.isOnline ? AppColors.success : AppColors.textMuted,
                    shape: BoxShape.circle,
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    driver.isOnline ? 'ONLINE & ACCEPTING JOBS' : 'YOU ARE OFFLINE',
                    style: TextStyle(
                      color: driver.isOnline ? Colors.white : AppColors.textMuted,
                      fontWeight: FontWeight.w900,
                      fontSize: 12.5,
                      letterSpacing: 0.3,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                const SizedBox(width: 6),
                driver.isLoading
                    ? const SizedBox(
                        width: 24,
                        height: 24,
                        child: CircularProgressIndicator(
                          strokeWidth: 2.5,
                          color: AppColors.primary,
                        ),
                      )
                    : Transform.scale(
                        scale: 0.88,
                        child: Switch(
                          value: driver.isOnline,
                          activeColor: AppColors.success,
                          activeTrackColor: AppColors.success.withOpacity(0.3),
                          inactiveThumbColor: AppColors.textMuted,
                          inactiveTrackColor: Colors.white10,
                          onChanged: driver.isLoading ? null : (_) => _handleToggleOnline(driver),
                        ),
                      ),
              ],
            ),
            const SizedBox(height: 10),
            Text(
              driver.isOnline
                  ? 'Your GPS is transmitting live. Nearby ride & delivery orders will ring with a loud ringtone.'
                  : 'Turn your status online to start receiving ride and delivery orders.',
              style: TextStyle(
                color: Colors.white.withOpacity(0.8),
                fontSize: 12.5,
                height: 1.35,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildIncentiveQuickBanner(BuildContext context) {
    return GestureDetector(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const IncentivesScreen()),
        );
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            colors: [Color(0xFF1E1B4B), Color(0xFF312E81)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: AppColors.primary.withOpacity(0.35), width: 1.2),
          boxShadow: [
            BoxShadow(
              color: AppColors.primary.withOpacity(0.08),
              blurRadius: 12,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: AppColors.primary.withOpacity(0.15),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.card_giftcard_rounded, color: AppColors.primary, size: 22),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: const [
                  Text(
                    "Today's Incentive Program",
                    style: TextStyle(
                      color: AppColors.textLight,
                      fontWeight: FontWeight.w800,
                      fontSize: 13,
                    ),
                  ),
                  SizedBox(height: 2),
                  Text(
                    'Complete ride targets to earn cash bonuses',
                    style: TextStyle(
                      color: AppColors.textMuted,
                      fontSize: 11,
                    ),
                  ),
                ],
              ),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
              decoration: BoxDecoration(
                color: AppColors.primary,
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    'VIEW',
                    style: TextStyle(
                      color: AppColors.backgroundDark,
                      fontSize: 10,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  SizedBox(width: 3),
                  Icon(Icons.arrow_forward_ios_rounded, color: AppColors.backgroundDark, size: 9),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildActiveRideCard(BuildContext context, Map<String, dynamic> ride, DriverProvider driver) {
    final fare = ride['fare'] != null ? double.tryParse(ride['fare'].toString()) ?? 0.0 : 0.0;
    final countryProv = Provider.of<CountryProvider>(context, listen: false);
    final currSym = (ride['currency_symbol'] ?? countryProv.currencySymbol).toString();
    final rawStatus = (ride['status'] ?? 'accepted').toString();
    final status = rawStatus.replaceAll('_', ' ').toUpperCase();
    final riderName = (ride['rider_name'] ?? ride['rider']?['name'] ?? ride['passenger_name'] ?? 'Passenger').toString();
    final rideId = int.tryParse(ride['id']?.toString() ?? '0') ?? 0;

    return Card(
      color: AppColors.surfaceDark,
      margin: const EdgeInsets.only(bottom: 16),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(22),
        side: const BorderSide(color: AppColors.success, width: 2),
      ),
      child: Padding(
        padding: const EdgeInsets.all(18),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: AppColors.success.withOpacity(0.15),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.circle, color: AppColors.success, size: 8),
                          const SizedBox(width: 6),
                          Text(
                            status,
                            style: const TextStyle(
                              color: AppColors.success,
                              fontWeight: FontWeight.w800,
                              fontSize: 11,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),
                    Text(
                      '#RIDE-$rideId',
                      style: const TextStyle(
                        color: AppColors.textMuted,
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
                Text(
                  '$currSym${fare.toStringAsFixed(2)}',
                  style: const TextStyle(
                    color: AppColors.success,
                    fontWeight: FontWeight.w900,
                    fontSize: 22,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                const Icon(Icons.person, color: AppColors.primary, size: 16),
                const SizedBox(width: 6),
                Text(
                  'Rider: $riderName',
                  style: const TextStyle(color: AppColors.textLight, fontSize: 14, fontWeight: FontWeight.bold),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.backgroundDark,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: Colors.white10),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Icon(Icons.circle, color: AppColors.success, size: 12),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          '${ride['pickup_location']}',
                          style: const TextStyle(color: AppColors.textLight, fontSize: 13, fontWeight: FontWeight.w600),
                        ),
                      ),
                    ],
                  ),
                  const Padding(
                    padding: EdgeInsets.only(left: 5),
                    child: SizedBox(height: 12, child: VerticalDivider(color: Colors.white24, width: 2)),
                  ),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Icon(Icons.location_on_rounded, color: AppColors.danger, size: 14),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          '${ride['dropoff_location']}',
                          style: const TextStyle(color: AppColors.textLight, fontSize: 13, fontWeight: FontWeight.w600),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Direct Status Update Buttons right on the card!
            if (rawStatus == 'accepted' || rawStatus == 'driver_assigned' || rawStatus == 'confirmed') ...[
              Row(
                children: [
                  Expanded(
                    child: SizedBox(
                      height: 46,
                      child: ElevatedButton.icon(
                        onPressed: () async {
                          await driver.updateRideStatus(rideId, 'en_route');
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.info,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        icon: const Icon(Icons.directions_car_rounded, size: 18),
                        label: const FittedBox(
                          fit: BoxFit.scaleDown,
                          child: Text('🚗 En Route', style: TextStyle(fontWeight: FontWeight.bold)),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: SizedBox(
                      height: 46,
                      child: ElevatedButton.icon(
                        onPressed: () async {
                          await driver.updateRideStatus(rideId, 'arrived');
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.warning,
                          foregroundColor: Colors.black,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        icon: const Icon(Icons.location_on_rounded, size: 18),
                        label: const FittedBox(
                          fit: BoxFit.scaleDown,
                          child: Text('📍 Arrived', style: TextStyle(fontWeight: FontWeight.bold)),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              SizedBox(
                height: 46,
                child: ElevatedButton.icon(
                  onPressed: () => _confirmEndTrip(context, rideId, fare, driver, currencySymbol: currSym),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.success,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  icon: const Icon(Icons.check_circle_rounded, size: 18),
                  label: const Text('✓ End / Complete Ride Now', style: TextStyle(fontWeight: FontWeight.w900)),
                ),
              ),
            ] else if (rawStatus == 'en_route') ...[
              Row(
                children: [
                  Expanded(
                    flex: 3,
                    child: SizedBox(
                      height: 48,
                      child: ElevatedButton.icon(
                        onPressed: () async {
                          await driver.updateRideStatus(rideId, 'arrived');
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.warning,
                          foregroundColor: Colors.black,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        ),
                        icon: const Icon(Icons.location_on_rounded, size: 18),
                        label: const Text('📍 Arrived at Pickup', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    flex: 2,
                    child: SizedBox(
                      height: 48,
                      child: ElevatedButton.icon(
                        onPressed: () => _confirmEndTrip(context, rideId, fare, driver, currencySymbol: currSym),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.success,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        ),
                        icon: const Icon(Icons.check_circle_rounded, size: 16),
                        label: const Text('✓ End Ride', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                      ),
                    ),
                  ),
                ],
              ),
            ] else if (rawStatus == 'arrived') ...[
              Row(
                children: [
                  Expanded(
                    flex: 3,
                    child: SizedBox(
                      height: 48,
                      child: ElevatedButton.icon(
                        onPressed: () async {
                          await driver.updateRideStatus(rideId, 'in_progress');
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.success,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        ),
                        icon: const Icon(Icons.play_arrow_rounded, size: 20),
                        label: const Text('▶ Start Trip / Ride', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    flex: 2,
                    child: SizedBox(
                      height: 48,
                      child: ElevatedButton.icon(
                        onPressed: () => _confirmEndTrip(context, rideId, fare, driver, currencySymbol: currSym),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.success,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        ),
                        icon: const Icon(Icons.check_circle_rounded, size: 16),
                        label: const Text('✓ End Ride', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                      ),
                    ),
                  ),
                ],
              ),
            ] else ...[
              SizedBox(
                height: 52,
                child: ElevatedButton.icon(
                  onPressed: () => _confirmEndTrip(context, rideId, fare, driver, currencySymbol: currSym),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.success,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    elevation: 4,
                  ),
                  icon: const Icon(Icons.check_circle_rounded, size: 22),
                  label: const Text('✓ Complete Trip & Collect Fare', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 15)),
                ),
              ),
            ],

            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  flex: 3,
                  child: OutlinedButton.icon(
                    onPressed: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => ActiveTripScreen(ride: ride)),
                      );
                    },
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppColors.primary,
                      side: const BorderSide(color: AppColors.primary, width: 1.5),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                    ),
                    icon: const Icon(Icons.navigation_rounded, size: 18),
                    label: const Text('Live Navigation', style: TextStyle(fontWeight: FontWeight.bold)),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  flex: 2,
                  child: OutlinedButton.icon(
                    onPressed: () => _showCancelTripDialog(context, ride, driver),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppColors.danger,
                      side: BorderSide(color: AppColors.danger.withOpacity(0.6), width: 1.5),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                    ),
                    icon: const Icon(Icons.cancel_outlined, size: 16),
                    label: const Text('Cancel', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                  ),
                ),
              ],
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
    final countryProv = Provider.of<CountryProvider>(context, listen: false);
    final currSym = (item['currency_symbol'] ?? countryProv.currencySymbol).toString();

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
        border: Border.all(color: Colors.amber.withOpacity(0.5), width: 1.5),
        boxShadow: [
          BoxShadow(
            color: Colors.amber.withOpacity(0.1),
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
                          color: typeColor.withOpacity(0.2),
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
                      '$currSym${amount.toStringAsFixed(2)} $currency',
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
                      side: BorderSide(color: AppColors.danger.withOpacity(0.5)),
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
    final fare = double.tryParse((job['fare'] ?? job['total_price'] ?? '0').toString()) ?? 0.0;
    final countryProv = Provider.of<CountryProvider>(context, listen: false);
    final currSym = (job['currency_symbol'] ?? countryProv.currencySymbol).toString();
    final pickup = job['pickup_location'] ?? 'Pickup location';
    final dropoff = job['dropoff_location'] ?? 'Destination';
    final customerName = (job['customer_name'] ?? job['rider_name'] ?? job['client_name'] ?? 'Customer').toString();
    final customerPhone = job['customer_phone'] ?? job['rider_phone'] ?? job['client_phone'];
    final pocName = job['poc_name'];
    final pocPhone = job['poc_phone'];
    final bool hasPoc = pocName != null && pocName.toString().isNotEmpty && pocName.toString() != customerName;
    final vehicleType = job['vehicle_type'] ?? 'Standard';
    final isDriverBooking = job['type'] == 'driver_booking';
    final isDelivery = job['type'] == 'package_delivery' || job['package_delivery_id'] != null;
    final isBackup = job['is_backup'] == true || job['assignment_type'] == 'backup';

    Color badgeColor = AppColors.primary;
    IconData badgeIcon = Icons.local_taxi_rounded;
    String badgeLabel = '$vehicleType RIDE';

    if (isBackup) {
      badgeColor = Colors.amber;
      badgeIcon = Icons.shield_rounded;
      badgeLabel = 'PROXIMITY BACKUP';
    } else if (isDriverBooking) {
      badgeColor = AppColors.purple;
      badgeIcon = Icons.airline_seat_recline_extra_rounded;
      badgeLabel = 'DRIVER HIRING';
    } else if (isDelivery) {
      badgeColor = AppColors.info;
      badgeIcon = Icons.local_shipping_rounded;
      badgeLabel = 'PACKAGE DELIVERY';
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: badgeColor.withOpacity(0.4), width: 1.5),
        boxShadow: [
          BoxShadow(
            color: badgeColor.withOpacity(0.12),
            blurRadius: 18,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(22),
          onTap: () => _openJobDialog(job, driver),
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
                    color: badgeColor.withOpacity(0.2),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(
                      color: badgeColor,
                      width: 1,
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        badgeIcon,
                        size: 14,
                        color: badgeColor,
                      ),
                      const SizedBox(width: 6),
                      Text(
                        badgeLabel,
                        style: TextStyle(
                          color: badgeColor,
                          fontSize: 11,
                          fontWeight: FontWeight.w900,
                          letterSpacing: 0.5,
                        ),
                      ),
                    ],
                  ),
                ),
                Text(
                  '$currSym${fare.toStringAsFixed(2)}',
                  style: const TextStyle(
                    color: AppColors.success,
                    fontSize: 22,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),

            // Customer Details Row
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: AppColors.backgroundDark.withOpacity(0.6),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.white10),
              ),
              child: Row(
                children: [
                  const Icon(Icons.person_rounded, color: AppColors.primary, size: 18),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Customer: $customerName',
                          style: const TextStyle(
                            color: AppColors.textLight,
                            fontSize: 14,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        if (customerPhone != null && customerPhone.toString().isNotEmpty)
                          Text(
                            customerPhone.toString(),
                            style: const TextStyle(
                              color: AppColors.textMuted,
                              fontSize: 12,
                            ),
                          ),
                      ],
                    ),
                  ),
                  if (customerPhone != null && customerPhone.toString().isNotEmpty)
                    ElevatedButton.icon(
                      onPressed: () => _callPhone(customerPhone.toString()),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.success,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                        minimumSize: Size.zero,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                      icon: const Icon(Icons.phone_rounded, size: 13),
                      label: const Text('Call', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11)),
                    ),
                ],
              ),
            ),

            // POC Details Row (if available)
            if (hasPoc) ...[
              const SizedBox(height: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(
                  color: Colors.amber.withOpacity(0.08),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.amber.withOpacity(0.25)),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.badge_rounded, color: Colors.amber, size: 18),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'PASSENGER / POC',
                            style: TextStyle(color: Colors.amber, fontSize: 9, fontWeight: FontWeight.w900, letterSpacing: 0.5),
                          ),
                          Text(
                            pocName.toString(),
                            style: const TextStyle(
                              color: AppColors.textLight,
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          if (pocPhone != null && pocPhone.toString().isNotEmpty)
                            Text(
                              pocPhone.toString(),
                              style: const TextStyle(
                                color: AppColors.textMuted,
                                fontSize: 11,
                              ),
                            ),
                        ],
                      ),
                    ),
                    if (pocPhone != null && pocPhone.toString().isNotEmpty)
                      ElevatedButton.icon(
                        onPressed: () => _callPhone(pocPhone.toString()),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.amber.shade700,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                          minimumSize: Size.zero,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                        icon: const Icon(Icons.phone_rounded, size: 13),
                        label: const Text('Call POC', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11)),
                      ),
                  ],
                ),
              ),
            ],
            const SizedBox(height: 10),

            // Request Date & Time Row
            if (job['request_time_formatted'] != null || job['created_at'] != null) ...[
              Container(
                margin: const EdgeInsets.only(bottom: 10),
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                decoration: BoxDecoration(
                  color: AppColors.backgroundDark.withOpacity(0.6),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: Colors.white10),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.schedule_rounded, color: AppColors.primary, size: 14),
                    const SizedBox(width: 6),
                    Expanded(
                      child: Text(
                        'Requested: ${job['request_time_formatted'] ?? job['created_at']}${job['request_time_human'] != null ? ' (${job['request_time_human']})' : ''}',
                        style: const TextStyle(
                          color: AppColors.textLight,
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ),
            ],

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

            // Tap hint
            Container(
              margin: const EdgeInsets.only(bottom: 12),
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: Colors.white.withOpacity(0.04),
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.touch_app_rounded, color: AppColors.textMuted, size: 14),
                  SizedBox(width: 6),
                  Text(
                    'Tap card for full details & route preview',
                    style: TextStyle(color: AppColors.textMuted, fontSize: 11, fontWeight: FontWeight.w600),
                  ),
                ],
              ),
            ),

            // Action Buttons Row: Accept & Decline
            Row(
              children: [
                Expanded(
                  flex: 3,
                  child: ElevatedButton.icon(
                    onPressed: () async {
                      final ok = await driver.respondToRequest(
                        job['assignment_id'] ?? job['id'],
                        'accept',
                        rideId: int.tryParse(job['ride_id']?.toString() ?? job['id']?.toString() ?? '0'),
                        deliveryId: int.tryParse(job['package_delivery_id']?.toString() ?? job['delivery_id']?.toString() ?? '0'),
                        bookingId: int.tryParse(job['driver_booking_id']?.toString() ?? job['booking_id']?.toString() ?? '0'),
                        jobData: job,
                      );
                      if (ok && context.mounted) {
                        if (isBackup || driver.lastAssignmentResponse?['reserved'] == true) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('✓ Reserved as Backup Chauffeur! Awaiting customer confirmation.'),
                              backgroundColor: Colors.amber,
                              duration: Duration(seconds: 5),
                            ),
                          );
                        } else {
                          final acceptedRide = driver.lastAcceptedRide ??
                              driver.lastAssignmentResponse?['ride'] ??
                              driver.lastAssignmentResponse?['delivery'] ??
                              (driver.activeRides.isNotEmpty ? driver.activeRides.first : job);

                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => ActiveTripScreen(ride: Map<String, dynamic>.from(acceptedRide)),
                            ),
                          );

                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('✓ Ride Accepted! Opening navigation.'),
                              backgroundColor: AppColors.success,
                            ),
                          );
                        }
                      } else if (!ok && context.mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(driver.errorMessage ?? 'Could not accept ride. It may have expired or been assigned.'),
                            backgroundColor: AppColors.danger,
                          ),
                        );
                      }
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: isBackup ? Colors.amber.shade700 : AppColors.success,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                    ),
                    icon: Icon(isBackup ? Icons.shield_rounded : Icons.check_circle_rounded, size: 18),
                    label: Text(
                      isBackup ? 'Reserve as Backup ($currSym${fare.toStringAsFixed(2)})' : 'Accept & Earn $currSym${fare.toStringAsFixed(2)}',
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
                        job['assignment_id'] ?? job['id'],
                        'reject',
                        rideId: int.tryParse(job['ride_id']?.toString() ?? job['id']?.toString() ?? '0'),
                        deliveryId: int.tryParse(job['package_delivery_id']?.toString() ?? job['delivery_id']?.toString() ?? '0'),
                        bookingId: int.tryParse(job['driver_booking_id']?.toString() ?? job['booking_id']?.toString() ?? '0'),
                      );
                      if (context.mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text('Job declined.'),
                            duration: Duration(seconds: 2),
                          ),
                        );
                      }
                    },
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppColors.danger,
                      side: BorderSide(color: AppColors.danger.withOpacity(0.4)),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                    ),
                    child: const Text('Decline', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12)),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
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
            // Driver Profile Header
            Padding(
              padding: const EdgeInsets.all(20.0),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 26,
                    backgroundColor: AppColors.primary,
                    backgroundImage: (auth.avatarUrl != null && auth.avatarUrl!.isNotEmpty)
                        ? NetworkImage(auth.avatarUrl!)
                        : null,
                    child: (auth.avatarUrl == null || auth.avatarUrl!.isEmpty)
                        ? Text(
                            (auth.userName ?? 'D').trim().isNotEmpty
                                ? (auth.userName ?? 'D').trim()[0].toUpperCase()
                                : 'D',
                            style: const TextStyle(
                              color: AppColors.backgroundDark,
                              fontWeight: FontWeight.bold,
                              fontSize: 20,
                            ),
                          )
                        : null,
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
                    leading: const Icon(Icons.card_giftcard_rounded, color: AppColors.warning),
                    title: const Text('Incentive Program', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w600)),
                    trailing: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppColors.primary.withOpacity(0.15),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: const Text('BONUS', style: TextStyle(color: AppColors.primary, fontSize: 10, fontWeight: FontWeight.w900)),
                    ),
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(context, MaterialPageRoute(builder: (_) => const IncentivesScreen()));
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
                    leading: const Icon(Icons.emergency_rounded, color: AppColors.danger),
                    title: const Text('SOS (Emergency Contacts)', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w600)),
                    trailing: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppColors.danger.withOpacity(0.18),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: const Text('SOS', style: TextStyle(color: AppColors.danger, fontSize: 10, fontWeight: FontWeight.w900)),
                    ),
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(context, MaterialPageRoute(builder: (_) => const SosContactsScreen()));
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
              onTap: () => _handleLogout(context, auth),
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
          border: Border.all(color: Colors.white.withOpacity(0.06)),
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

  Future<void> _handleLogout(BuildContext drawerContext, AuthProvider auth) async {
    final confirm = await showDialog<bool>(
      context: drawerContext,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppColors.surfaceDark,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.logout_rounded, color: AppColors.danger, size: 24),
            SizedBox(width: 10),
            Text('Log Out', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 18)),
          ],
        ),
        content: const Text(
          'Are you sure you want to log out of your driver account?',
          style: TextStyle(color: AppColors.textMuted, fontSize: 14),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Cancel', style: TextStyle(color: AppColors.textLight)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.danger,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Log Out', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );

    if (confirm != true || !mounted) return;

    try {
      Navigator.of(context).pop();
    } catch (_) {}

    await auth.logout();

    if (!mounted) return;

    Navigator.of(context, rootNavigator: true).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const DriverLoginScreen()),
      (route) => false,
    );
  }
}
