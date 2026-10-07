import 'dart:convert';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/api/api_client.dart';
import '../../core/constants/api_constants.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/country_provider.dart';
import '../../providers/driver_provider.dart';

class DriverTripsScreen extends StatefulWidget {
  const DriverTripsScreen({super.key});

  @override
  State<DriverTripsScreen> createState() => _DriverTripsScreenState();
}

class _DriverTripsScreenState extends State<DriverTripsScreen> with SingleTickerProviderStateMixin {
  final Dio _dio = ApiClient().dio;
  late TabController _tabController;
  static List<Map<String, dynamic>> _cachedTrips = [];
  bool _isLoading = true;
  String? _errorMessage;
  List<Map<String, dynamic>> _trips = [];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    if (_cachedTrips.isNotEmpty) {
      _trips = List.from(_cachedTrips);
      _isLoading = false;
    }
    _fetchTrips();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _fetchTrips() async {
    if (!mounted) return;
    if (_trips.isEmpty) {
      setState(() {
        _isLoading = true;
        _errorMessage = null;
      });
    }

    try {
      Response? res;
      try {
        res = await _dio.get('/driver/trips').timeout(const Duration(seconds: 5));
      } catch (e) {
        debugPrint('Primary /driver/trips error: $e');
        try {
          res = await _dio.get(ApiConstants.rides).timeout(const Duration(seconds: 4));
        } catch (_) {}
      }

      if (res != null && res.statusCode == 200 && res.data != null) {
        dynamic data = res.data;
        if (data is String) {
          try {
            data = jsonDecode(data);
          } catch (_) {}
        }

        final dynamic raw = data is Map ? (data['data'] ?? data['rides'] ?? data['items'] ?? data) : data;
        List rawList = [];
        if (raw is Map && raw['data'] is List) {
          rawList = raw['data'];
        } else if (raw is List) {
          rawList = raw;
        }

        final List<Map<String, dynamic>> mapped = [];
        for (final item in rawList) {
          if (item is Map) {
            mapped.add(Map<String, dynamic>.from(item));
          }
        }

        // Also merge any currently active ride from DriverProvider
        try {
          final driverProvider = Provider.of<DriverProvider>(context, listen: false);
          for (final ar in driverProvider.activeRides) {
            if (!mapped.any((m) => m['id']?.toString() == ar['id']?.toString())) {
              mapped.insert(0, Map<String, dynamic>.from(ar));
            }
          }
        } catch (_) {}

        _cachedTrips = List.from(mapped);

        if (mounted) {
          setState(() {
            _trips = mapped;
            _errorMessage = null;
          });
        }
      } else {
        // If API returned without 200 or empty, check active rides fallback
        try {
          final driverProvider = Provider.of<DriverProvider>(context, listen: false);
          if (driverProvider.activeRides.isNotEmpty && mounted) {
            setState(() {
              _trips = List<Map<String, dynamic>>.from(driverProvider.activeRides);
              _errorMessage = null;
            });
          }
        } catch (_) {}
      }
    } catch (e) {
      debugPrint('Error fetching driver trips: $e');
      if (_trips.isEmpty && mounted) {
        setState(() {
          _errorMessage = 'Unable to connect to server. Pull down to refresh.';
        });
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
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

  Future<void> _openReceiptUrl(String url, [Map<String, dynamic>? trip]) async {
    if (trip != null && mounted) {
      _showTripReceiptModal(trip, url);
      return;
    }
    await _launchReceiptExternally(url);
  }

  void _showTripReceiptModal(Map<String, dynamic> t, String receiptUrl) {
    try {
      final currencySymbol = (t['currency_symbol'] ?? '\$').toString();
      final fare = double.tryParse((t['fare'] ?? t['total_amount'] ?? '0').toString()) ?? 0.0;
      final bookingCode = (t['booking_code'] ?? t['digital_receipt_code'] ?? 'REC-${t['id']}').toString();
      final requestedTime = (t['request_time_formatted'] ?? _formatDateTime(t['created_at'])).toString();
      final rider = t['rider'] is Map ? t['rider'] : null;
      final customerName = (t['passenger_name'] ?? rider?['name'] ?? 'Valued Customer').toString();
      final customerPhone = rider?['phone'] ?? t['passenger_phone'] ?? t['customer_phone'];
      final vehicleType = (t['vehicle_type'] ?? t['car_make_model'] ?? 'Standard').toString();
      final pickup = (t['pickup_location'] ?? 'Pickup point').toString();
      final dropoff = (t['dropoff_location'] ?? 'Destination').toString();
      final paymentMethod = (t['payment_method'] ?? 'CARD').toString().toUpperCase();
      final downloadUrl = (t['receipt_download_url'] != null && t['receipt_download_url'].toString().isNotEmpty)
          ? t['receipt_download_url'].toString()
          : '$receiptUrl/download';

      showModalBottomSheet(
        context: context,
        isScrollControlled: true,
        backgroundColor: Colors.transparent,
        builder: (ctx) => DraggableScrollableSheet(
        initialChildSize: 0.82,
        minChildSize: 0.45,
        maxChildSize: 0.95,
        builder: (_, scrollController) => Container(
          decoration: const BoxDecoration(
            color: Color(0xFF1E293B),
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          child: Column(
            children: [
              // Pull Handle
              Container(
                margin: const EdgeInsets.only(top: 12, bottom: 8),
                width: 44,
                height: 5,
                decoration: BoxDecoration(
                  color: Colors.white24,
                  borderRadius: BorderRadius.circular(3),
                ),
              ),
              Expanded(
                child: ListView(
                  controller: scrollController,
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                  children: [
                    // Header Bar
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(6),
                              decoration: BoxDecoration(
                                color: AppColors.primary.withOpacity(0.18),
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.receipt_long_rounded, color: AppColors.primary, size: 18),
                            ),
                            const SizedBox(width: 8),
                            const Text(
                              'DIGITAL RECEIPT',
                              style: TextStyle(
                                color: AppColors.primary,
                                fontSize: 13,
                                fontWeight: FontWeight.w900,
                                letterSpacing: 0.8,
                              ),
                            ),
                          ],
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: AppColors.success.withOpacity(0.15),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: const Text(
                            'COMPLETED',
                            style: TextStyle(color: AppColors.success, fontSize: 10, fontWeight: FontWeight.w900),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 18),

                    // Amount Card
                    Container(
                      padding: const EdgeInsets.all(18),
                      decoration: BoxDecoration(
                        color: const Color(0xFF0F172A),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: Colors.white.withOpacity(0.08)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'TOTAL FARE PAID',
                            style: TextStyle(color: AppColors.textMuted, fontSize: 11, fontWeight: FontWeight.w700),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            '$currencySymbol${fare.toStringAsFixed(2)}',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 32,
                              fontWeight: FontWeight.w900,
                              letterSpacing: -0.5,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Row(
                            children: [
                              const Icon(Icons.credit_card_rounded, color: AppColors.primary, size: 14),
                              const SizedBox(width: 6),
                              Text(
                                'Method: $paymentMethod',
                                style: const TextStyle(color: AppColors.textLight, fontSize: 12, fontWeight: FontWeight.w600),
                              ),
                              const Spacer(),
                              Text(
                                bookingCode,
                                style: const TextStyle(
                                  color: AppColors.primary,
                                  fontFamily: 'monospace',
                                  fontSize: 12,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Trip Details Box
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: const Color(0xFF0F172A),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: Colors.white.withOpacity(0.08)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Trip Information',
                            style: TextStyle(color: Colors.white, fontSize: 14, fontWeight: FontWeight.w800),
                          ),
                          const SizedBox(height: 12),
                          _buildReceiptDetailRow('Date & Time', requestedTime),
                          _buildReceiptDetailRow('Customer', customerName),
                          if (customerPhone != null && customerPhone.toString().isNotEmpty)
                            _buildReceiptDetailRow('Phone', customerPhone.toString()),
                          _buildReceiptDetailRow('Vehicle Service', vehicleType),
                          const Padding(
                            padding: EdgeInsets.symmetric(vertical: 8),
                            child: Divider(color: Colors.white10, height: 1),
                          ),
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
                                  pickup,
                                  style: const TextStyle(color: AppColors.textLight, fontSize: 12, fontWeight: FontWeight.w500),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
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
                                  dropoff,
                                  style: const TextStyle(color: AppColors.textLight, fontSize: 12, fontWeight: FontWeight.w500),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 20),
                  ],
                ),
              ),

              // Bottom Actions
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: const Color(0xFF0F172A),
                  border: Border(top: BorderSide(color: Colors.white.withOpacity(0.08))),
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () async {
                          Navigator.pop(ctx);
                          await _launchReceiptExternally(receiptUrl);
                        },
                        icon: const Icon(Icons.open_in_browser_rounded, size: 16),
                        label: const Text('Open Browser'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: Colors.white,
                          side: const BorderSide(color: Colors.white24),
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () async {
                          Navigator.pop(ctx);
                          await _launchReceiptExternally(downloadUrl);
                        },
                        icon: const Icon(Icons.file_download_rounded, size: 16),
                        label: const Text('Download PDF'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.primary,
                          foregroundColor: AppColors.backgroundDark,
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          textStyle: const TextStyle(fontWeight: FontWeight.w800),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  } catch (e) {
      debugPrint('Error showing receipt modal: $e');
      _launchReceiptExternally(receiptUrl);
    }
  }

  Widget _buildReceiptDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(color: AppColors.textMuted, fontSize: 12)),
          const SizedBox(width: 12),
          Flexible(
            child: Text(
              value,
              style: const TextStyle(color: AppColors.textLight, fontSize: 12, fontWeight: FontWeight.w600),
              textAlign: TextAlign.end,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _launchReceiptExternally(String url) async {
    try {
      final uri = Uri.parse(url);
      final launched = await launchUrl(uri, mode: LaunchMode.externalApplication);
      if (!launched) {
        final fallback = await launchUrl(uri, mode: LaunchMode.platformDefault);
        if (!fallback) {
          await launchUrl(uri, mode: LaunchMode.inAppWebView);
        }
      }
    } catch (e) {
      debugPrint('Error launching receipt url: $e');
      try {
        await launchUrl(Uri.parse(url), mode: LaunchMode.inAppWebView);
      } catch (_) {}
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
          : TabBarView(
              controller: _tabController,
              children: [
                _buildTripsList(_filterTrips('all')),
                _buildTripsList(_filterTrips('completed')),
                _buildTripsList(_filterTrips('active')),
              ],
            ),
    );
  }

  String _formatDateTime(dynamic raw) {
    if (raw == null) return 'Recent';
    final str = raw.toString().trim();
    if (str.isEmpty || str == 'null') return 'Recent';
    try {
      DateTime dt = DateTime.parse(str).toLocal();
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
      final m = months[dt.month - 1];
      final d = dt.day.toString().padLeft(2, '0');
      final y = dt.year;
      final hour = dt.hour % 12 == 0 ? 12 : dt.hour % 12;
      final min = dt.minute.toString().padLeft(2, '0');
      final ampm = dt.hour >= 12 ? 'PM' : 'AM';
      return '$d $m $y • $hour:$min $ampm';
    } catch (_) {
      try {
        final parts = str.split('T');
        if (parts.length >= 2) {
          final timePart = parts[1].split('.').first;
          return '${parts[0]} • $timePart';
        }
      } catch (_) {}
      return str;
    }
  }

  Widget _buildTripsList(List<Map<String, dynamic>> trips) {
    if (trips.isEmpty) {
      return RefreshIndicator(
        color: AppColors.primary,
        onRefresh: _fetchTrips,
        child: ListView(
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
        ),
      );
    }

    return RefreshIndicator(
      color: AppColors.primary,
      onRefresh: _fetchTrips,
      child: ListView.builder(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        itemCount: trips.length,
        itemBuilder: (context, index) {
        final t = trips[index];
        final fare = double.tryParse((t['fare'] ?? t['total_amount'] ?? '0').toString()) ?? 0.0;
        final countryProv = Provider.of<CountryProvider>(context, listen: false);
        final currSym = (t['currency_symbol'] ?? countryProv.currencySymbol).toString();
        final rawStatus = (t['status'] ?? 'completed').toString().toLowerCase();
        final isCompleted = rawStatus == 'completed' || rawStatus == 'paid' || rawStatus == 'finished';
        final isCancelled = rawStatus == 'cancelled';
        final isActive = !isCompleted && !isCancelled;

        final bookingCode = t['booking_code'] ?? 'RIDE-${t['id'] ?? index}';
        final riderName = t['rider'] is Map
            ? (t['rider']['name'] ?? 'Passenger')
            : (t['passenger_name'] ?? t['customer_name'] ?? 'Passenger');
        final vehicleType = (t['vehicle_type'] ?? t['car_make_model'] ?? 'Standard').toString();
        final requestedTime = t['request_time_formatted'] ?? _formatDateTime(t['created_at']);
        final pickupDate = t['pickup_date']?.toString();
        final pickupTime = t['pickup_time']?.toString();
        final hasScheduled = (pickupDate != null && pickupDate.isNotEmpty && pickupDate != 'null') ||
            (pickupTime != null && pickupTime.isNotEmpty && pickupTime != 'null');

        final receiptUrl = (t['receipt_url'] != null && t['receipt_url'].toString().isNotEmpty)
            ? t['receipt_url'].toString()
            : 'https://www.ridemycars.com/receipts/${t['receipt_id'] ?? t['digital_receipt_code'] ?? bookingCode}';

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
                                        vehicleType.toLowerCase().contains('ride') || vehicleType.toLowerCase().contains('delivery') || vehicleType.toLowerCase().contains('chauffeur')
                                            ? vehicleType
                                            : '$vehicleType Ride',
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
                                  '${t['type'] == 'delivery' ? 'Sender' : 'Passenger'}: $riderName',
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
                          fare > 0 ? '+$currSym${fare.toStringAsFixed(2)}' : '$currSym 0.00',
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
                // Dedicated Requested Date & Time Row
                Container(
                  margin: const EdgeInsets.only(top: 10, bottom: 2),
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.04),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.white10),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.schedule_rounded, color: AppColors.primary, size: 13),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          'Requested: $requestedTime',
                          style: const TextStyle(
                            color: AppColors.textLight,
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      if (hasScheduled) ...[
                        const SizedBox(width: 6),
                        Text(
                          '• Pickup: ${pickupDate ?? ''} ${pickupTime ?? ''}'.trim(),
                          style: const TextStyle(
                            color: Colors.amber,
                            fontSize: 10.5,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ],
                    ],
                  ),
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
                if (isCompleted) ...[
                  const SizedBox(height: 10),
                  SizedBox(
                    width: double.infinity,
                    height: 42,
                    child: ElevatedButton(
                      onPressed: () => _openReceiptUrl(receiptUrl, t),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        foregroundColor: AppColors.backgroundDark,
                        padding: const EdgeInsets.symmetric(horizontal: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        elevation: 0,
                      ),
                      child: const Row(
                        children: [
                          Icon(Icons.receipt_long_rounded, color: AppColors.backgroundDark, size: 16),
                          SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              'View Digital Receipt',
                              style: TextStyle(
                                color: AppColors.backgroundDark,
                                fontSize: 13,
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                          ),
                          Icon(Icons.arrow_forward_ios_rounded, color: AppColors.backgroundDark, size: 12),
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
    ),
    );
  }
}
