import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/api/api_client.dart';
import '../../core/constants/api_constants.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/country_provider.dart';

class MyRidesScreen extends StatefulWidget {
  final String? initialCategoryFilter;

  const MyRidesScreen({super.key, this.initialCategoryFilter});

  @override
  State<MyRidesScreen> createState() => _MyRidesScreenState();
}

class _MyRidesScreenState extends State<MyRidesScreen> with SingleTickerProviderStateMixin {
  final Dio _dio = ApiClient().dio;
  late TabController _tabController;
  bool _isLoading = true;
  List<Map<String, dynamic>> _rides = [];
  String _selectedCategory = 'all'; // all, rental, ride, chauffeur, delivery

  @override
  void initState() {
    super.initState();
    if (widget.initialCategoryFilter != null) {
      _selectedCategory = widget.initialCategoryFilter!;
    }
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
      debugPrint('Error fetching activity/rides: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  String _getItemType(Map<String, dynamic> r) {
    final type = (r['type'] ?? 'ride').toString().toLowerCase();
    final rawType = (r['raw_vehicle_type'] ?? r['vehicle_type'] ?? '').toString();
    final dropoff = (r['dropoff_location'] ?? '').toString();
    if (type == 'rental' || rawType.startsWith('RENTAL_') || dropoff.contains('Rental')) {
      return 'rental';
    }
    if (type == 'chauffeur' || type == 'driver_booking' || rawType.startsWith('CHAUFFEUR_') || dropoff.contains('Duty')) {
      return 'chauffeur';
    }
    if (type == 'delivery' || rawType.startsWith('DELIVERY_')) {
      return 'delivery';
    }
    return 'ride';
  }

  List<Map<String, dynamic>> _filterRides(String statusFilter) {
    List<Map<String, dynamic>> list = _rides;

    // Filter by Service Category (Rental, Ride, Chauffeur, Delivery)
    if (_selectedCategory != 'all') {
      list = list.where((r) => _getItemType(r) == _selectedCategory).toList();
    }

    // Filter by Lifecycle Status
    if (statusFilter == 'all') return list;
    if (statusFilter == 'active') {
      return list.where((r) {
        final s = (r['status'] ?? 'pending').toString().toLowerCase();
        return s == 'pending' ||
            s == 'confirmed' ||
            s == 'accepted' ||
            s == 'en_route' ||
            s == 'arrived' ||
            s == 'in_progress' ||
            s == 'dispatched' ||
            s == 'in_transit' ||
            s == 'reserved';
      }).toList();
    }
    if (statusFilter == 'completed') {
      return list.where((r) {
        final s = (r['status'] ?? '').toString().toLowerCase();
        return s == 'completed' || s == 'delivered' || s == 'paid' || s == 'returned';
      }).toList();
    }
    if (statusFilter == 'cancelled') {
      return list.where((r) {
        final s = (r['status'] ?? '').toString().toLowerCase();
        return s == 'cancelled' || s == 'rejected' || s == 'expired';
      }).toList();
    }
    return list;
  }

  Color _getStatusColor(String? status) {
    final s = (status ?? 'pending').toLowerCase();
    switch (s) {
      case 'completed':
      case 'delivered':
      case 'paid':
      case 'confirmed':
      case 'reserved':
        return const Color(0xFF10B981); // Emerald Green
      case 'in_progress':
      case 'en_route':
      case 'arrived':
      case 'accepted':
      case 'dispatched':
      case 'in_transit':
        return const Color(0xFF3B82F6); // Blue
      case 'cancelled':
      case 'rejected':
        return const Color(0xFFEF4444); // Red
      default:
        return const Color(0xFFF59E0B); // Amber / Pending
    }
  }

  String _getStatusDisplay(String? status, String type) {
    final s = (status ?? 'pending').toLowerCase();
    if (type == 'rental') {
      if (s == 'pending') return 'RESERVED (CONFIRMED)';
      if (s == 'completed') return 'RETURNED (PAID)';
      if (s == 'in_progress') return 'ACTIVE ON ROAD';
      return s.toUpperCase();
    }
    if (type == 'delivery') {
      if (s == 'pending') return 'DISPATCHED';
      if (s == 'completed') return 'DELIVERED';
      return s.toUpperCase();
    }
    return s.toUpperCase();
  }

  @override
  Widget build(BuildContext context) {
    final countryProv = Provider.of<CountryProvider>(context);

    return Scaffold(
      backgroundColor: const Color(0xFF0B1120),
      appBar: AppBar(
        backgroundColor: const Color(0xFF111827),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'My Activity & Bookings',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 18),
        ),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(96),
          child: Column(
            children: [
              // Category Filter Pills (All, Rentals, Rides, Chauffeurs, Deliveries)
              SizedBox(
                height: 38,
                child: ListView(
                  scrollDirection: Axis.horizontal,
                  padding: const EdgeInsets.symmetric(horizontal: 14),
                  children: [
                    _buildCategoryPill('all', 'All Activity', Icons.apps_rounded, const Color(0xFF64748B)),
                    _buildCategoryPill('rental', '🔑 Rented Fleet', Icons.vpn_key_rounded, const Color(0xFF3B82F6)),
                    _buildCategoryPill('ride', '🚗 Rides', Icons.directions_car_rounded, AppColors.primary),
                    _buildCategoryPill('chauffeur', '👨‍✈️ Chauffeurs', Icons.person_pin_circle_rounded, const Color(0xFF10B981)),
                    _buildCategoryPill('delivery', '📦 Deliveries', Icons.inventory_2_rounded, const Color(0xFFA855F7)),
                  ],
                ),
              ),
              const SizedBox(height: 6),
              // Status Tabs
              TabBar(
                controller: _tabController,
                indicatorColor: const Color(0xFFF59E0B),
                indicatorWeight: 3,
                labelColor: const Color(0xFFF59E0B),
                unselectedLabelColor: const Color(0xFF94A3B8),
                labelStyle: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13),
                tabs: const [
                  Tab(text: 'All'),
                  Tab(text: 'Active'),
                  Tab(text: 'Completed'),
                  Tab(text: 'Cancelled'),
                ],
              ),
            ],
          ),
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : RefreshIndicator(
              color: AppColors.primary,
              backgroundColor: const Color(0xFF1E293B),
              onRefresh: _fetchRides,
              child: TabBarView(
                controller: _tabController,
                children: [
                  _buildRidesList(_filterRides('all'), countryProv),
                  _buildRidesList(_filterRides('active'), countryProv),
                  _buildRidesList(_filterRides('completed'), countryProv),
                  _buildRidesList(_filterRides('cancelled'), countryProv),
                ],
              ),
            ),
    );
  }

  Widget _buildCategoryPill(String id, String label, IconData icon, Color color) {
    final isSelected = _selectedCategory == id;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: GestureDetector(
        onTap: () => setState(() => _selectedCategory = id),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
          decoration: BoxDecoration(
            color: isSelected ? color.withOpacity(0.25) : const Color(0xFF1E293B),
            borderRadius: BorderRadius.circular(20),
            border: Border.all(
              color: isSelected ? color : Colors.white.withOpacity(0.08),
              width: isSelected ? 1.5 : 1,
            ),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(icon, size: 14, color: isSelected ? Colors.white : const Color(0xFF94A3B8)),
              const SizedBox(width: 6),
              Text(
                label,
                style: TextStyle(
                  color: isSelected ? Colors.white : const Color(0xFF94A3B8),
                  fontSize: 12,
                  fontWeight: isSelected ? FontWeight.w900 : FontWeight.w600,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildRidesList(List<Map<String, dynamic>> rides, CountryProvider countryProv) {
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
                  'No bookings or trips found',
                  style: TextStyle(color: Colors.white, fontSize: 17, fontWeight: FontWeight.w800),
                ),
                SizedBox(height: 6),
                Text(
                  'Your rental reservations and ride history will appear here.',
                  style: TextStyle(color: Color(0xFF94A3B8), fontSize: 12.5),
                ),
              ],
            ),
          ),
        ],
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(14, 14, 14, 28),
      itemCount: rides.length,
      itemBuilder: (context, index) {
        final r = rides[index];
        final type = _getItemType(r);

        if (type == 'rental') {
          return _buildRentalFleetCard(r, countryProv);
        } else if (type == 'chauffeur') {
          return _buildChauffeurCard(r, countryProv);
        } else if (type == 'delivery') {
          return _buildDeliveryCard(r, countryProv);
        } else {
          return _buildStandardRideCard(r, countryProv);
        }
      },
    );
  }

  // --- 1. RENTED FLEET CARD ---
  Widget _buildRentalFleetCard(Map<String, dynamic> r, CountryProvider countryProv) {
    final status = (r['status'] ?? 'pending').toString();
    final statusColor = _getStatusColor(status);
    final statusDisplay = _getStatusDisplay(status, 'rental');
    final totalFare = (r['fare'] ?? r['total_amount'] as num? ?? 0.0).toDouble();
    final paidAmount = (r['paid_amount'] as num? ?? (totalFare * 0.2)).toDouble();
    final remainingBalance = (r['remaining_balance'] as num? ?? (totalFare - paidAmount)).toDouble();
    final vehicleTitle = (r['vehicle_type'] ?? 'Rented Vehicle').toString().replaceAll(RegExp(r'^RENTAL_\d+_'), '');
    final bookingCode = (r['booking_code'] ?? 'RNT-${r['id']}').toString();
    final date = r['created_at'] != null ? r['created_at'].toString().split('T').first : 'Recent';
    final pickupDate = r['pickup_date']?.toString() ?? date;
    final returnDate = r['return_date']?.toString() ?? '3 Days Return';
    final pickupTime = r['pickup_time']?.toString() ?? '10:00 AM';

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: const Color(0xFF131D33),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFF3B82F6).withOpacity(0.3), width: 1.2),
        boxShadow: [
          BoxShadow(color: Colors.black.withOpacity(0.3), blurRadius: 10, offset: const Offset(0, 4)),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Top Row: Icon + Title & Fare + Status
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: const Color(0xFF3B82F6).withOpacity(0.18),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFF3B82F6).withOpacity(0.4)),
                  ),
                  child: const Icon(Icons.vpn_key_rounded, color: Color(0xFF3B82F6), size: 22),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              color: const Color(0xFF3B82F6),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: const Text(
                              'RENTED FLEET',
                              style: TextStyle(color: Colors.white, fontSize: 9.5, fontWeight: FontWeight.w900),
                            ),
                          ),
                          const SizedBox(width: 6),
                          Text(
                            date,
                            style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(
                        vehicleTitle,
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w900,
                          fontSize: 15.5,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      countryProv.formatAmount(totalFare),
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w900,
                        fontSize: 17,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3.5),
                      decoration: BoxDecoration(
                        color: statusColor.withOpacity(0.18),
                        borderRadius: BorderRadius.circular(6),
                        border: Border.all(color: statusColor.withOpacity(0.4)),
                      ),
                      child: Text(
                        statusDisplay,
                        style: TextStyle(
                          color: statusColor,
                          fontSize: 9.5,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),

            const Padding(
              padding: EdgeInsets.symmetric(vertical: 12),
              child: Divider(color: Colors.white12, height: 1),
            ),

            // Rental Schedule & Pickup Location
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFF0F172A),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.white.withOpacity(0.06)),
              ),
              child: Column(
                children: [
                  Row(
                    children: [
                      const Icon(Icons.calendar_today_rounded, size: 14, color: Color(0xFF60A5FA)),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          '📅 $pickupDate ($pickupTime)  ➔  $returnDate',
                          style: const TextStyle(color: Color(0xFFE2E8F0), fontSize: 11.5, fontWeight: FontWeight.w700),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Icon(Icons.location_on_rounded, size: 14, color: Color(0xFFEF4444)),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          r['pickup_location'] ?? 'Pick-up Branch Location',
                          style: const TextStyle(color: Color(0xFFCBD5E1), fontSize: 11.5),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),

            const SizedBox(height: 10),

            // Payment Split Chips (Deposit Paid vs Balance at Pickup)
            Row(
              children: [
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 8),
                    decoration: BoxDecoration(
                      color: const Color(0xFF10B981).withOpacity(0.12),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: const Color(0xFF10B981).withOpacity(0.3)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('DEPOSIT PAID', style: TextStyle(color: Color(0xFF34D399), fontSize: 8.5, fontWeight: FontWeight.bold)),
                        Text(countryProv.formatAmount(paidAmount), style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w900)),
                      ],
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF59E0B).withOpacity(0.12),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: const Color(0xFFF59E0B).withOpacity(0.3)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('AT PICKUP', style: TextStyle(color: Color(0xFFFBBF24), fontSize: 8.5, fontWeight: FontWeight.bold)),
                        Text(countryProv.formatAmount(remainingBalance), style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w900)),
                      ],
                    ),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 12),

            // Voucher Action Button
            SizedBox(
              height: 38,
              child: ElevatedButton.icon(
                onPressed: () => _showRentalVoucherModal(r, countryProv),
                icon: const Icon(Icons.qr_code_rounded, size: 16, color: Colors.white),
                label: Text(
                  'View Digital Voucher ($bookingCode)',
                  style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w800),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF2563EB),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // --- 2. CHAUFFEUR HIRE CARD ---
  Widget _buildChauffeurCard(Map<String, dynamic> r, CountryProvider countryProv) {
    final status = (r['status'] ?? 'pending').toString();
    final statusColor = _getStatusColor(status);
    final fare = (r['fare'] ?? r['total_amount'] as num? ?? 0.0).toDouble();
    final driverName = r['driver'] is Map ? (r['driver']['name'] ?? 'Chauffeur') : (r['driver_name'] ?? 'Chauffeur');
    final vehicleTitle = (r['vehicle_type'] ?? 'Chauffeur Hire').toString().replaceAll(RegExp(r'^CHAUFFEUR_\d+_'), '');
    final date = r['created_at'] != null ? r['created_at'].toString().split('T').first : 'Recent';

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: const Color(0xFF131D33),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFF10B981).withOpacity(0.3), width: 1),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: const Color(0xFF10B981).withOpacity(0.18),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.person_pin_circle_rounded, color: Color(0xFF10B981), size: 22),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              color: const Color(0xFF10B981),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: const Text('CHAUFFEUR', style: TextStyle(color: Colors.white, fontSize: 9.5, fontWeight: FontWeight.w900)),
                          ),
                          const SizedBox(width: 6),
                          Text(date, style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11)),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(
                        vehicleTitle.startsWith('Chauffeur:') ? vehicleTitle : 'Chauffeur: $vehicleTitle',
                        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 15),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      countryProv.formatAmount(fare),
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 17),
                    ),
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: statusColor.withOpacity(0.18),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        status.toUpperCase(),
                        style: TextStyle(color: statusColor, fontSize: 9.5, fontWeight: FontWeight.w800),
                      ),
                    ),
                  ],
                ),
              ],
            ),
            const Padding(padding: EdgeInsets.symmetric(vertical: 10), child: Divider(color: Colors.white12, height: 1)),
            Row(
              children: [
                const Icon(Icons.location_on_rounded, size: 14, color: AppColors.danger),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    r['pickup_location'] ?? 'Pickup location',
                    style: const TextStyle(color: Color(0xFFCBD5E1), fontSize: 11.5),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 4),
            Row(
              children: [
                const Icon(Icons.badge_rounded, size: 14, color: Color(0xFF10B981)),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    'Assigned Chauffeur: $driverName',
                    style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11.5),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  // --- 3. PARCEL DELIVERY CARD ---
  Widget _buildDeliveryCard(Map<String, dynamic> r, CountryProvider countryProv) {
    final status = (r['status'] ?? 'pending').toString();
    final statusColor = _getStatusColor(status);
    final statusDisplay = _getStatusDisplay(status, 'delivery');
    final fare = (r['fare'] ?? r['total_amount'] as num? ?? 0.0).toDouble();
    final vehicleTitle = (r['vehicle_type'] ?? 'Parcel Delivery').toString().replaceAll(RegExp(r'^DELIVERY_'), '');
    final trackingCode = (r['booking_code'] ?? 'DEL-${r['id']}').toString();
    final pin = r['delivery_otp']?.toString() ?? '7682';
    final date = r['created_at'] != null ? r['created_at'].toString().split('T').first : 'Recent';

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: const Color(0xFF131D33),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFA855F7).withOpacity(0.35), width: 1),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: const Color(0xFFA855F7).withOpacity(0.18),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.inventory_2_rounded, color: Color(0xFFA855F7), size: 22),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              color: const Color(0xFFA855F7),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: const Text('DELIVERY', style: TextStyle(color: Colors.white, fontSize: 9.5, fontWeight: FontWeight.w900)),
                          ),
                          const SizedBox(width: 6),
                          Text(date, style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11)),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(
                        vehicleTitle,
                        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 15),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      countryProv.formatAmount(fare),
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 17),
                    ),
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: statusColor.withOpacity(0.18),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        statusDisplay,
                        style: TextStyle(color: statusColor, fontSize: 9.5, fontWeight: FontWeight.w800),
                      ),
                    ),
                  ],
                ),
              ],
            ),
            const Padding(padding: EdgeInsets.symmetric(vertical: 10), child: Divider(color: Colors.white12, height: 1)),
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: const Color(0xFF0F172A),
                    borderRadius: BorderRadius.circular(6),
                    border: Border.all(color: const Color(0xFF38BDF8).withOpacity(0.4)),
                  ),
                  child: Text('TRACK: $trackingCode', style: const TextStyle(color: Color(0xFF38BDF8), fontSize: 10.5, fontWeight: FontWeight.w800)),
                ),
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: const Color(0xFF0F172A),
                    borderRadius: BorderRadius.circular(6),
                    border: Border.all(color: const Color(0xFFF59E0B).withOpacity(0.4)),
                  ),
                  child: Text('🔒 PIN: $pin', style: const TextStyle(color: Color(0xFFFBBF24), fontSize: 10.5, fontWeight: FontWeight.w900)),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                const Icon(Icons.circle, size: 8, color: AppColors.success),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(r['pickup_location'] ?? 'Sender address', style: const TextStyle(color: Color(0xFFCBD5E1), fontSize: 11.5), maxLines: 1, overflow: TextOverflow.ellipsis),
                ),
              ],
            ),
            const SizedBox(height: 4),
            Row(
              children: [
                const Icon(Icons.location_on_rounded, size: 10, color: AppColors.danger),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(r['dropoff_location'] ?? 'Recipient address', style: const TextStyle(color: Color(0xFFCBD5E1), fontSize: 11.5), maxLines: 1, overflow: TextOverflow.ellipsis),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  // --- 4. STANDARD CAB / RIDE CARD ---
  Widget _buildStandardRideCard(Map<String, dynamic> r, CountryProvider countryProv) {
    final status = (r['status'] ?? 'pending').toString();
    final statusColor = _getStatusColor(status);
    final fare = (r['fare'] ?? r['total_amount'] as num? ?? 0.0).toDouble();
    final driverName = r['driver'] is Map ? (r['driver']['name'] ?? 'Assigned Driver') : (r['driver_name'] ?? 'Assigned Driver');
    final vehicleType = (r['vehicle_type'] ?? 'Standard').toString();
    final date = r['created_at'] != null ? r['created_at'].toString().split('T').first : 'Recent';

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: const Color(0xFF131D33),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: Colors.white.withOpacity(0.08)),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: AppColors.primary.withOpacity(0.18),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.local_taxi_rounded, color: AppColors.primary, size: 22),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              color: AppColors.primary,
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: const Text('RIDE', style: TextStyle(color: Colors.black, fontSize: 9.5, fontWeight: FontWeight.w900)),
                          ),
                          const SizedBox(width: 6),
                          Text(date, style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11)),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '$vehicleType Ride',
                        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 15),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      countryProv.formatAmount(fare),
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 17),
                    ),
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: statusColor.withOpacity(0.18),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        status.toUpperCase(),
                        style: TextStyle(color: statusColor, fontSize: 9.5, fontWeight: FontWeight.w800),
                      ),
                    ),
                  ],
                ),
              ],
            ),
            const Padding(padding: EdgeInsets.symmetric(vertical: 10), child: Divider(color: Colors.white12, height: 1)),
            Row(
              children: [
                const Icon(Icons.circle, size: 8, color: AppColors.success),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(r['pickup_location'] ?? 'Pickup location', style: const TextStyle(color: Color(0xFFCBD5E1), fontSize: 11.5), maxLines: 1, overflow: TextOverflow.ellipsis),
                ),
              ],
            ),
            const SizedBox(height: 4),
            Row(
              children: [
                const Icon(Icons.location_on_rounded, size: 10, color: AppColors.danger),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(r['dropoff_location'] ?? 'Drop-off destination', style: const TextStyle(color: Color(0xFFCBD5E1), fontSize: 11.5), maxLines: 1, overflow: TextOverflow.ellipsis),
                ),
              ],
            ),
            if (r['driver'] != null) ...[
              const SizedBox(height: 6),
              Row(
                children: [
                  const Icon(Icons.person_rounded, size: 14, color: Color(0xFF94A3B8)),
                  const SizedBox(width: 6),
                  Text('Driver: $driverName', style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11.5)),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }

  // --- DIGITAL VOUCHER MODAL FOR RENTED FLEET ---
  void _showRentalVoucherModal(Map<String, dynamic> r, CountryProvider countryProv) {
    final vehicleTitle = (r['vehicle_type'] ?? 'Rented Vehicle').toString().replaceAll(RegExp(r'^RENTAL_\d+_'), '');
    final bookingCode = (r['booking_code'] ?? 'RNT-${r['id']}').toString();
    final totalFare = (r['fare'] ?? r['total_amount'] as num? ?? 0.0).toDouble();
    final paidAmount = (r['paid_amount'] as num? ?? (totalFare * 0.2)).toDouble();
    final remainingBalance = (r['remaining_balance'] as num? ?? (totalFare - paidAmount)).toDouble();
    final pickupDate = r['pickup_date']?.toString() ?? 'Today';
    final returnDate = r['return_date']?.toString() ?? 'In 3 Days';
    final pickupTime = r['pickup_time']?.toString() ?? '10:00 AM';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => SafeArea(
        top: false,
        bottom: true,
        child: Container(
          padding: EdgeInsets.fromLTRB(20, 16, 20, 20 + MediaQuery.of(ctx).padding.bottom),
          decoration: const BoxDecoration(
            color: Color(0xFF0F172A),
            borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(width: 40, height: 4, decoration: BoxDecoration(color: Colors.white24, borderRadius: BorderRadius.circular(2))),
              const SizedBox(height: 16),
              const Icon(Icons.vpn_key_rounded, color: Color(0xFF3B82F6), size: 36),
              const SizedBox(height: 8),
              const Text('Digital Rental Voucher', style: TextStyle(color: Colors.white, fontSize: 19, fontWeight: FontWeight.w900)),
              const SizedBox(height: 4),
              Text('Present this code at branch counter upon vehicle pickup.', style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 12), textAlign: TextAlign.center),
              const SizedBox(height: 16),

              // Code Banner
              Container(
                width: double.infinity,
                padding: const EdgeInsets.symmetric(vertical: 14),
                decoration: BoxDecoration(
                  color: const Color(0xFF1E293B),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFF3B82F6).withOpacity(0.5)),
                ),
                child: Column(
                  children: [
                    const Text('RESERVATION CONFIRMATION CODE', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 10, fontWeight: FontWeight.w900, letterSpacing: 0.8)),
                    const SizedBox(height: 6),
                    Text(bookingCode, style: const TextStyle(color: Color(0xFF60A5FA), fontSize: 24, fontWeight: FontWeight.w900, letterSpacing: 2)),
                    const SizedBox(height: 4),
                    const Text('STATUS: CONFIRMED & GUARANTEED', style: TextStyle(color: Color(0xFF34D399), fontSize: 11, fontWeight: FontWeight.w800)),
                  ],
                ),
              ),

              const SizedBox(height: 14),

              // Vehicle & Financial Summary
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: const Color(0xFF1E293B),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Vehicle:', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 12)),
                        Text(vehicleTitle, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12.5)),
                      ],
                    ),
                    const Divider(color: Colors.white10, height: 16),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Rental Period:', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 12)),
                        Text('$pickupDate ($pickupTime)  ➔  $returnDate', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12)),
                      ],
                    ),
                    const Divider(color: Colors.white10, height: 16),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('20% Deposit Paid:', style: TextStyle(color: Color(0xFF34D399), fontSize: 12, fontWeight: FontWeight.bold)),
                        Text(countryProv.formatAmount(paidAmount), style: const TextStyle(color: Color(0xFF34D399), fontWeight: FontWeight.w900, fontSize: 13)),
                      ],
                    ),
                    const SizedBox(height: 6),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('80% Balance Due at Counter:', style: TextStyle(color: Color(0xFFFBBF24), fontSize: 12, fontWeight: FontWeight.bold)),
                        Text(countryProv.formatAmount(remainingBalance), style: const TextStyle(color: Color(0xFFFBBF24), fontWeight: FontWeight.w900, fontSize: 13)),
                      ],
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 18),

              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton(
                  onPressed: () => Navigator.pop(ctx),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF10B981),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  child: const Text('Done & Return', style: TextStyle(color: Colors.black, fontWeight: FontWeight.w900, fontSize: 14)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
