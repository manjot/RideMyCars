import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/api/api_client.dart';
import '../../core/constants/api_constants.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/driver_provider.dart';

class DriverTripsScreen extends StatefulWidget {
  const DriverTripsScreen({super.key});

  @override
  State<DriverTripsScreen> createState() => _DriverTripsScreenState();
}

class _DriverTripsScreenState extends State<DriverTripsScreen> with SingleTickerProviderStateMixin {
  final Dio _dio = ApiClient().dio;
  late TabController _tabController;
  bool _isLoading = true;
  String? _errorMessage;
  List<Map<String, dynamic>> _trips = [];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _fetchTrips();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _fetchTrips() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });
    try {
      Response? res;
      try {
        res = await _dio.get(
          ApiConstants.rides,
          options: Options(
            sendTimeout: const Duration(seconds: 12),
            receiveTimeout: const Duration(seconds: 12),
          ),
        );
      } catch (e) {
        debugPrint('Primary rides endpoint failed, trying fallback: $e');
        try {
          res = await _dio.get(
            '/driver/trips',
            options: Options(
              sendTimeout: const Duration(seconds: 10),
              receiveTimeout: const Duration(seconds: 10),
            ),
          );
        } catch (_) {}
      }

      if (res != null && res.statusCode == 200) {
        final dynamic raw = res.data is Map ? (res.data['data'] ?? res.data['rides'] ?? res.data) : res.data;
        List rawList = [];
        if (raw is Map && raw['data'] is List) {
          rawList = raw['data'];
        } else if (raw is List) {
          rawList = raw;
        }
        setState(() {
          _trips = rawList.map((e) => Map<String, dynamic>.from(e)).toList();
        });
      } else {
        if (_trips.isEmpty) {
          _errorMessage = 'Unable to load trips at this time.';
        }
      }
    } catch (e) {
      debugPrint('Error fetching driver trips: $e');
      if (_trips.isEmpty) {
        _errorMessage = 'Network connection issue. Pull down to refresh.';
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  List<Map<String, dynamic>> _filterTrips(String status) {
    if (status == 'all') return _trips;
    if (status == 'completed') {
      return _trips.where((r) {
        final s = (r['status'] ?? '').toString().toLowerCase();
        return s == 'completed' || s == 'paid' || s == 'finished';
      }).toList();
    }
    if (status == 'active') {
      final activeList = _trips.where((r) {
        final s = (r['status'] ?? '').toString().toLowerCase();
        return s == 'accepted' ||
            s == 'en_route' ||
            s == 'arrived' ||
            s == 'in_progress' ||
            s == 'started' ||
            s == 'driver_assigned' ||
            s == 'confirmed';
      }).toList();

      if (activeList.isEmpty) {
        try {
          final driverProvider = Provider.of<DriverProvider>(context, listen: false);
          return driverProvider.activeRides;
        } catch (_) {}
      }
      return activeList;
    }
    return _trips;
  }

  Future<void> _openReceiptUrl(String url) async {
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  @override
  Widget build(BuildContext context) {
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
          'Ride History',
          style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w800, fontSize: 18),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: AppColors.primary, size: 22),
            onPressed: _fetchTrips,
            tooltip: 'Refresh',
          ),
        ],
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: AppColors.primary,
          indicatorWeight: 3,
          labelColor: AppColors.primary,
          unselectedLabelColor: AppColors.textMuted,
          labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
          tabs: const [
            Tab(text: 'All Trips'),
            Tab(text: 'Completed'),
            Tab(text: 'Active'),
          ],
        ),
      ),
      body: _isLoading
          ? const Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  CircularProgressIndicator(color: AppColors.primary),
                  SizedBox(height: 16),
                  Text(
                    'Loading your trip history...',
                    style: TextStyle(color: AppColors.textMuted, fontSize: 13),
                  ),
                ],
              ),
            )
          : RefreshIndicator(
              color: AppColors.primary,
              onRefresh: _fetchTrips,
              child: TabBarView(
                controller: _tabController,
                children: [
                  _buildTripsList(_filterTrips('all')),
                  _buildTripsList(_filterTrips('completed')),
                  _buildTripsList(_filterTrips('active')),
                ],
              ),
            ),
    );
  }

  Widget _buildTripsList(List<Map<String, dynamic>> trips) {
    if (trips.isEmpty) {
      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: [
          const SizedBox(height: 100),
          Center(
            child: Column(
              children: [
                Icon(
                  _errorMessage != null ? Icons.wifi_off_rounded : Icons.directions_car_outlined,
                  size: 64,
                  color: Colors.white24,
                ),
                const SizedBox(height: 16),
                Text(
                  _errorMessage ?? 'No trips found',
                  textAlign: TextAlign.center,
                  style: const TextStyle(color: AppColors.textLight, fontSize: 17, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 8),
                Text(
                  _errorMessage != null
                      ? 'Please check your connection and tap below to try again.'
                      : 'Your trip history will appear here once you take rides.',
                  textAlign: TextAlign.center,
                  style: const TextStyle(color: AppColors.textMuted, fontSize: 13),
                ),
                if (_errorMessage != null) ...[
                  const SizedBox(height: 20),
                  ElevatedButton.icon(
                    onPressed: _fetchTrips,
                    icon: const Icon(Icons.refresh_rounded, size: 18),
                    label: const Text('Try Again'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      foregroundColor: AppColors.backgroundDark,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: trips.length,
      itemBuilder: (context, index) {
        final t = trips[index];
        final fare = double.tryParse((t['fare'] ?? t['total_amount'] ?? '0').toString()) ?? 0.0;
        final rawStatus = (t['status'] ?? 'completed').toString().toLowerCase();
        final isCompleted = rawStatus == 'completed' || rawStatus == 'paid' || rawStatus == 'finished';
        final isCancelled = rawStatus == 'cancelled';
        final isActive = !isCompleted && !isCancelled;

        final bookingCode = t['booking_code'] ?? 'RIDE-${t['id'] ?? index}';
        final riderName = t['rider'] is Map
            ? (t['rider']['name'] ?? 'Passenger')
            : (t['passenger_name'] ?? t['customer_name'] ?? 'Passenger');
        final vehicleType = (t['vehicle_type'] ?? t['car_make_model'] ?? 'Standard').toString();
        final rawDate = (t['created_at'] ?? t['pickup_date'])?.toString();
        final date = rawDate != null ? rawDate.split('T').first : 'Recent';

        final receiptUrl = t['receipt_url'] ?? (t['receipt_id'] != null ? 'https://www.ridemycars.com/receipts/${t['receipt_id']}' : null);

        Color statusColor = AppColors.success;
        if (isCancelled) {
          statusColor = AppColors.danger;
        } else if (isActive) {
          statusColor = AppColors.primary;
        }

        return Container(
          margin: const EdgeInsets.only(bottom: 14),
          decoration: BoxDecoration(
            color: AppColors.surfaceDark,
            borderRadius: BorderRadius.circular(18),
            border: Border.all(
              color: isActive ? AppColors.primary.withOpacity(0.3) : Colors.white.withOpacity(0.08),
            ),
          ),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: statusColor.withOpacity(0.15),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Icon(
                              t['type'] == 'delivery' ? Icons.local_shipping_rounded : Icons.local_taxi_rounded,
                              color: statusColor,
                              size: 20,
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Expanded(
                                      child: Text(
                                        '$vehicleType Ride',
                                        overflow: TextOverflow.ellipsis,
                                        style: const TextStyle(
                                          color: AppColors.textLight,
                                          fontWeight: FontWeight.bold,
                                          fontSize: 15,
                                        ),
                                      ),
                                    ),
                                    Text(
                                      bookingCode,
                                      style: const TextStyle(
                                        color: AppColors.textMuted,
                                        fontWeight: FontWeight.w600,
                                        fontSize: 11,
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  'Passenger: $riderName · $date',
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(color: AppColors.textMuted, fontSize: 11),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 10),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text(
                          fare > 0 ? '+\$${fare.toStringAsFixed(2)}' : '\$0.00',
                          style: TextStyle(
                            color: isCancelled ? AppColors.textMuted : AppColors.success,
                            fontWeight: FontWeight.w900,
                            fontSize: 17,
                          ),
                        ),
                        Container(
                          margin: const EdgeInsets.only(top: 4),
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: statusColor.withOpacity(0.15),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            rawStatus.toUpperCase(),
                            style: TextStyle(
                              color: statusColor,
                              fontSize: 10,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 10),
                  child: Divider(color: Colors.white10, height: 1),
                ),
                // Route Locations
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Padding(
                      padding: EdgeInsets.only(top: 2),
                      child: Icon(Icons.circle, color: AppColors.success, size: 8),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        t['pickup_location'] ?? 'Pickup location',
                        style: const TextStyle(color: AppColors.textLight, fontSize: 12, fontWeight: FontWeight.w500),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
                const Padding(
                  padding: EdgeInsets.only(left: 3),
                  child: Align(
                    alignment: Alignment.centerLeft,
                    child: SizedBox(height: 6, child: VerticalDivider(color: Colors.white24)),
                  ),
                ),
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Padding(
                      padding: EdgeInsets.only(top: 2),
                      child: Icon(Icons.location_on_rounded, color: AppColors.danger, size: 10),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        t['dropoff_location'] ?? 'Destination',
                        style: const TextStyle(color: AppColors.textLight, fontSize: 12, fontWeight: FontWeight.w500),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
                if (receiptUrl != null && isCompleted) ...[
                  const SizedBox(height: 10),
                  InkWell(
                    onTap: () => _openReceiptUrl(receiptUrl),
                    borderRadius: BorderRadius.circular(8),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.04),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.white10),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.receipt_long_rounded, color: AppColors.primary, size: 14),
                          SizedBox(width: 6),
                          Text(
                            'View Digital Receipt',
                            style: TextStyle(
                              color: AppColors.primary,
                              fontSize: 11,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          Spacer(),
                          Icon(Icons.arrow_forward_ios_rounded, color: AppColors.textMuted, size: 10),
                        ],
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ),
        );
      },
    );
  }
}
