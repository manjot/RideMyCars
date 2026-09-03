import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../../core/api/api_client.dart';
import '../../core/constants/api_constants.dart';
import '../../core/constants/app_colors.dart';

class MyRidesScreen extends StatefulWidget {
  const MyRidesScreen({super.key});

  @override
  State<MyRidesScreen> createState() => _MyRidesScreenState();
}

class _MyRidesScreenState extends State<MyRidesScreen> with SingleTickerProviderStateMixin {
  final Dio _dio = ApiClient().dio;
  late TabController _tabController;
  bool _isLoading = true;
  List<Map<String, dynamic>> _rides = [];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    _fetchRides();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _fetchRides() async {
    setState(() => _isLoading = true);
    try {
      final res = await _dio.get(ApiConstants.rides);
      if (res.statusCode == 200) {
        final dynamic raw = res.data is Map ? (res.data['data'] ?? res.data['rides'] ?? res.data) : res.data;
        List rawList = [];
        if (raw is Map && raw['data'] is List) {
          rawList = raw['data'];
        } else if (raw is List) {
          rawList = raw;
        }
        setState(() {
          _rides = rawList.map((e) => Map<String, dynamic>.from(e)).toList();
        });
      }
    } catch (e) {
      debugPrint('Error fetching my rides: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  List<Map<String, dynamic>> _filterRides(String status) {
    if (status == 'all') return _rides;
    if (status == 'active') {
      return _rides.where((r) {
        final s = r['status'];
        return s == 'pending' || s == 'accepted' || s == 'en_route' || s == 'arrived' || s == 'in_progress';
      }).toList();
    }
    return _rides.where((r) => r['status'] == status).toList();
  }

  Color _getStatusColor(String? status) {
    switch (status) {
      case 'completed':
        return AppColors.success;
      case 'in_progress':
      case 'en_route':
      case 'arrived':
      case 'accepted':
        return AppColors.info;
      case 'cancelled':
        return AppColors.danger;
      default:
        return AppColors.primary;
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
          'My Rides',
          style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w800, fontSize: 18),
        ),
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: AppColors.primary,
          indicatorWeight: 3,
          labelColor: AppColors.primary,
          unselectedLabelColor: AppColors.textMuted,
          labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
          tabs: const [
            Tab(text: 'All'),
            Tab(text: 'Active'),
            Tab(text: 'Completed'),
            Tab(text: 'Cancelled'),
          ],
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : RefreshIndicator(
              color: AppColors.primary,
              onRefresh: _fetchRides,
              child: TabBarView(
                controller: _tabController,
                children: [
                  _buildRidesList(_filterRides('all')),
                  _buildRidesList(_filterRides('active')),
                  _buildRidesList(_filterRides('completed')),
                  _buildRidesList(_filterRides('cancelled')),
                ],
              ),
            ),
    );
  }

  Widget _buildRidesList(List<Map<String, dynamic>> rides) {
    if (rides.isEmpty) {
      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: const [
          SizedBox(height: 120),
          Center(
            child: Column(
              children: [
                Icon(Icons.directions_car_outlined, size: 64, color: Colors.white24),
                SizedBox(height: 16),
                Text(
                  'No trips found',
                  style: TextStyle(color: AppColors.textLight, fontSize: 18, fontWeight: FontWeight.bold),
                ),
                SizedBox(height: 6),
                Text(
                  'Your trip history will appear here.',
                  style: TextStyle(color: AppColors.textMuted, fontSize: 13),
                ),
              ],
            ),
          ),
        ],
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: rides.length,
      itemBuilder: (context, index) {
        final r = rides[index];
        final status = (r['status'] ?? 'pending').toString();
        final fare = double.tryParse((r['fare'] ?? r['total_amount'] ?? '0').toString()) ?? 0.0;
        final driverName = r['driver'] is Map ? (r['driver']['name'] ?? 'Assigned Driver') : (r['driver_name'] ?? 'Assigned Driver');
        final vehicleType = (r['vehicle_type'] ?? r['car_make_model'] ?? 'Standard').toString();
        final date = r['created_at'] != null
            ? r['created_at'].toString().split('T').first
            : 'Recent';

        return Container(
          margin: const EdgeInsets.only(bottom: 16),
          decoration: BoxDecoration(
            color: AppColors.surfaceDark,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: Colors.white.withValues(alpha: 0.08)),
          ),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: AppColors.primary.withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.local_taxi_rounded, color: AppColors.primary, size: 20),
                        ),
                        const SizedBox(width: 10),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              '$vehicleType Ride',
                              style: const TextStyle(
                                color: AppColors.textLight,
                                fontWeight: FontWeight.bold,
                                fontSize: 15,
                              ),
                            ),
                            Text(
                              date,
                              style: const TextStyle(color: AppColors.textMuted, fontSize: 11),
                            ),
                          ],
                        ),
                      ],
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text(
                          '\$${fare.toStringAsFixed(2)}',
                          style: const TextStyle(
                            color: AppColors.textLight,
                            fontWeight: FontWeight.w900,
                            fontSize: 18,
                          ),
                        ),
                        Container(
                          margin: const EdgeInsets.only(top: 4),
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: _getStatusColor(status).withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            status.toUpperCase(),
                            style: TextStyle(
                              color: _getStatusColor(status),
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
                  padding: EdgeInsets.symmetric(vertical: 12),
                  child: Divider(color: Colors.white10, height: 1),
                ),
                // Route Locations
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Icon(Icons.circle, color: AppColors.success, size: 10),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        r['pickup_location'] ?? 'Pickup location',
                        style: const TextStyle(color: AppColors.textLight, fontSize: 12, fontWeight: FontWeight.w500),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
                const Padding(
                  padding: EdgeInsets.only(left: 4),
                  child: Align(
                    alignment: Alignment.centerLeft,
                    child: SizedBox(height: 8, child: VerticalDivider(color: Colors.white24)),
                  ),
                ),
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Icon(Icons.location_on_rounded, color: AppColors.danger, size: 12),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        r['dropoff_location'] ?? 'Destination',
                        style: const TextStyle(color: AppColors.textLight, fontSize: 12, fontWeight: FontWeight.w500),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
                if (r['driver'] != null) ...[
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      const Icon(Icons.person_rounded, size: 14, color: AppColors.textMuted),
                      const SizedBox(width: 6),
                      Text(
                        'Driver: $driverName',
                        style: const TextStyle(color: AppColors.textMuted, fontSize: 12),
                      ),
                    ],
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
