import 'package:flutter/material.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/driver_provider.dart';
import '../safety/widgets/sos_floating_button.dart';

class ActiveTripScreen extends StatefulWidget {
  final Map<String, dynamic> ride;

  const ActiveTripScreen({super.key, required this.ride});

  @override
  State<ActiveTripScreen> createState() => _ActiveTripScreenState();
}

class _ActiveTripScreenState extends State<ActiveTripScreen> {
  late Map<String, dynamic> _ride;
  bool _isUpdating = false;
  GoogleMapController? _mapController;

  @override
  void dispose() {
    _mapController?.dispose();
    super.dispose();
  }

  void _fitRouteBounds(double? pLat, double? pLng, double? dLat, double? dLng) {
    if (_mapController == null) return;
    final points = <LatLng>[
      if (pLat != null && pLng != null) LatLng(pLat, pLng),
      if (dLat != null && dLng != null) LatLng(dLat, dLng),
    ];
    if (points.isEmpty) return;
    if (points.length == 1) {
      _mapController?.animateCamera(CameraUpdate.newLatLngZoom(points.first, 15));
      return;
    }

    double minLat = points.first.latitude;
    double maxLat = points.first.latitude;
    double minLng = points.first.longitude;
    double maxLng = points.first.longitude;

    for (final p in points) {
      if (p.latitude < minLat) minLat = p.latitude;
      if (p.latitude > maxLat) maxLat = p.latitude;
      if (p.longitude < minLng) minLng = p.longitude;
      if (p.longitude > maxLng) maxLng = p.longitude;
    }

    _mapController?.animateCamera(
      CameraUpdate.newLatLngBounds(
        LatLngBounds(
          southwest: LatLng(minLat, minLng),
          northeast: LatLng(maxLat, maxLng),
        ),
        80.0,
      ),
    );
  }

  @override
  void initState() {
    super.initState();
    _ride = widget.ride;
  }

  String get _currentDestination {
    final status = _ride['status'];
    if (status == 'in_progress') {
      return _ride['dropoff_location'] ?? '';
    }
    return _ride['pickup_location'] ?? '';
  }

  Future<void> _launchGoogleMapsNavigation() async {
    final destination = _currentDestination;
    if (destination.isEmpty) return;

    final uri = Uri.parse(
      'https://www.google.com/maps/dir/?api=1&destination=${Uri.encodeComponent(destination)}&travelmode=driving',
    );

    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Could not open Google Maps.')),
        );
      }
    }
  }

  Future<void> _callRider([String? overridePhone]) async {
    final phone = overridePhone ?? _ride['customer_phone'] ?? _ride['rider']?['phone'] ?? _ride['rider_phone'] ?? _ride['passenger_phone'];
    if (phone != null && phone.toString().isNotEmpty) {
      final uri = Uri.parse('tel:$phone');
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri);
      }
    }
  }

  Future<void> _advanceStatus(String newStatus) async {
    final rideId = int.tryParse(_ride['id']?.toString() ?? '0') ?? 0;
    if (rideId <= 0) return;

    if (newStatus == 'completed') {
      final fareAmount = double.tryParse((_ride['fare'] ?? _ride['total_price'] ?? '0').toString()) ?? 0.0;
      final confirmed = await showDialog<bool>(
        context: context,
        builder: (ctx) => AlertDialog(
          backgroundColor: AppColors.surfaceDark,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: const Row(
            children: [
              Icon(Icons.check_circle_rounded, color: AppColors.success, size: 24),
              SizedBox(width: 10),
              Text('End Trip?', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 18)),
            ],
          ),
          content: Text(
            'Are you sure you want to end this trip and collect your fare of \$${fareAmount.toStringAsFixed(2)}?',
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
      if (confirmed != true) return;
    }

    setState(() => _isUpdating = true);
    final driver = Provider.of<DriverProvider>(context, listen: false);
    final success = await driver.updateRideStatus(rideId, newStatus);

    if (!mounted) return;
    setState(() => _isUpdating = false);

    if (success) {
      setState(() {
        _ride['status'] = newStatus;
      });

      if (newStatus == 'completed') {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('🎉 Trip Completed! Fare has been added to your earnings.'),
            backgroundColor: AppColors.success,
            duration: Duration(seconds: 4),
          ),
        );
        Navigator.pop(context);
      }
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Could not update trip status. Please check your connection.'),
          backgroundColor: AppColors.danger,
        ),
      );
    }
  }

  Future<void> _showCancelDialog() async {
    final rideId = int.tryParse(_ride['id']?.toString() ?? '0') ?? 0;
    if (rideId <= 0) return;

    String selectedReason = 'Passenger no-show';
    final reasons = [
      'Passenger no-show',
      'Vehicle breakdown / Flat tire',
      'Passenger requested cancellation',
      'Wrong pickup location / Unreachable',
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
                          'Cancel This Trip',
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
              const SizedBox(height: 18),
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

    if (confirmed == true && mounted) {
      setState(() => _isUpdating = true);
      final driver = Provider.of<DriverProvider>(context, listen: false);
      final ok = await driver.cancelRide(rideId, reason: selectedReason);

      if (!mounted) return;
      setState(() => _isUpdating = false);

      if (ok) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('✓ Trip #$rideId has been cancelled.'),
            backgroundColor: AppColors.success,
          ),
        );
        Navigator.pop(context);
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

  @override
  Widget build(BuildContext context) {
    final status = _ride['status'] ?? 'accepted';
    final fare = _ride['fare'] != null ? double.tryParse(_ride['fare'].toString()) ?? 0.0 : 0.0;
    final customerName = (_ride['customer_name'] ?? _ride['rider']?['name'] ?? _ride['rider_name'] ?? _ride['passenger_name'] ?? 'Customer').toString();
    final customerPhone = _ride['customer_phone'] ?? _ride['rider']?['phone'] ?? _ride['rider_phone'] ?? _ride['passenger_phone'];
    final pocName = _ride['poc_name'];
    final pocPhone = _ride['poc_phone'];
    final bool hasPoc = pocName != null && pocName.toString().isNotEmpty && pocName.toString() != customerName;

    final pickupLat = _ride['pickup_lat'] != null ? double.tryParse(_ride['pickup_lat'].toString()) : null;
    final pickupLng = _ride['pickup_lng'] != null ? double.tryParse(_ride['pickup_lng'].toString()) : null;
    final dropoffLat = _ride['dropoff_lat'] != null ? double.tryParse(_ride['dropoff_lat'].toString()) : null;
    final dropoffLng = _ride['dropoff_lng'] != null ? double.tryParse(_ride['dropoff_lng'].toString()) : null;

    final initialPos = LatLng(pickupLat ?? 28.6448, pickupLng ?? 77.2167);

    // In-app live connected route polyline
    final polylines = <Polyline>{
      if (pickupLat != null && pickupLng != null && dropoffLat != null && dropoffLng != null)
        Polyline(
          polylineId: const PolylineId('driver_trip_route'),
          points: [
            LatLng(pickupLat, pickupLng),
            LatLng(dropoffLat, dropoffLng),
          ],
          color: AppColors.primary,
          width: 5,
        ),
    };

    return Scaffold(
      backgroundColor: AppColors.backgroundDark,
      body: Stack(
        children: [
          // Google Map Background with connected in-app route polyline
          GoogleMap(
            initialCameraPosition: CameraPosition(
              target: initialPos,
              zoom: 14.0,
            ),
            myLocationEnabled: true,
            myLocationButtonEnabled: false,
            zoomControlsEnabled: false,
            polylines: polylines,
            onMapCreated: (controller) {
              _mapController = controller;
              _fitRouteBounds(pickupLat, pickupLng, dropoffLat, dropoffLng);
            },
            markers: {
              if (pickupLat != null && pickupLng != null)
                Marker(
                  markerId: const MarkerId('pickup'),
                  position: initialPos,
                  icon: BitmapDescriptor.defaultMarkerWithHue(BitmapDescriptor.hueGreen),
                  infoWindow: InfoWindow(title: 'Pickup: ${_ride['pickup_location']}'),
                ),
              if (dropoffLat != null && dropoffLng != null)
                Marker(
                  markerId: const MarkerId('dropoff'),
                  position: LatLng(dropoffLat, dropoffLng),
                  icon: BitmapDescriptor.defaultMarkerWithHue(BitmapDescriptor.hueRed),
                  infoWindow: InfoWindow(title: 'Destination: ${_ride['dropoff_location']}'),
                ),
            },
          ),

          // Top Header Overlay
          SafeArea(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
              child: Row(
                children: [
                  CircleAvatar(
                    backgroundColor: AppColors.surfaceDark,
                    child: IconButton(
                      icon: const Icon(Icons.arrow_back_ios_new_rounded, color: AppColors.textLight, size: 18),
                      onPressed: () => Navigator.pop(context),
                    ),
                  ),
                  const Spacer(),
                  // Turn-by-Turn Navigation Button
                  ElevatedButton.icon(
                    onPressed: _launchGoogleMapsNavigation,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.success,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(20),
                      ),
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                      elevation: 6,
                    ),
                    icon: const Icon(Icons.navigation_rounded, size: 18),
                    label: const Text(
                      'Maps',
                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                    ),
                  ),
                  const SizedBox(width: 8),
                  // Top Quick Cancel Button
                  ElevatedButton.icon(
                    onPressed: _isUpdating ? null : _showCancelDialog,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.surfaceDark,
                      foregroundColor: AppColors.danger,
                      side: BorderSide(color: AppColors.danger.withOpacity(0.5)),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(20),
                      ),
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      elevation: 4,
                    ),
                    icon: const Icon(Icons.cancel_outlined, size: 16),
                    label: const Text(
                      'Cancel',
                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                    ),
                  ),
                ],
              ),
            ),
          ),

          // SOS Map Corner Floating Button (Driver)
          Positioned(
            right: 16,
            top: 76,
            child: SosFloatingButton(
              rideId: int.tryParse(_ride['id']?.toString() ?? '0'),
              role: 'driver',
              assignedPhone: (hasPoc && pocPhone != null ? pocPhone : customerPhone)?.toString(),
              assignedName: (hasPoc && pocName != null ? pocName : customerName)?.toString() ?? 'Passenger',
            ),
          ),

          // Bottom Sheet with Ride Details & Lifecycle Action
          Positioned(
            left: 0,
            right: 0,
            bottom: 0,
            child: Container(
              decoration: BoxDecoration(
                color: AppColors.surfaceDark,
                borderRadius: const BorderRadius.vertical(top: Radius.circular(32)),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.5),
                    blurRadius: 30,
                    offset: const Offset(0, -10),
                  ),
                ],
              ),
              child: SafeArea(
                top: false,
                bottom: true,
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(24, 20, 24, 16),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // Customer Info Row
                      Row(
                        children: [
                          CircleAvatar(
                            radius: 24,
                            backgroundColor: AppColors.purple,
                            child: Text(
                              customerName.isNotEmpty ? customerName[0].toUpperCase() : 'C',
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 18),
                            ),
                          ),
                          const SizedBox(width: 14),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  customerName,
                                  style: const TextStyle(
                                    color: AppColors.textLight,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 16,
                                  ),
                                ),
                                if (customerPhone != null && customerPhone.toString().isNotEmpty)
                                  Text(
                                    customerPhone.toString(),
                                    style: const TextStyle(
                                      color: AppColors.primary,
                                      fontWeight: FontWeight.w700,
                                      fontSize: 12,
                                    ),
                                  ),
                                Text(
                                  status.replaceAll('_', ' ').toUpperCase(),
                                  style: const TextStyle(
                                    color: AppColors.textMuted,
                                    fontWeight: FontWeight.w800,
                                    fontSize: 10,
                                    letterSpacing: 0.5,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          if (customerPhone != null && customerPhone.toString().isNotEmpty)
                            ElevatedButton.icon(
                              onPressed: () => _callRider(customerPhone.toString()),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: AppColors.success,
                                foregroundColor: Colors.white,
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              ),
                              icon: const Icon(Icons.phone_rounded, size: 15),
                              label: const Text('Call', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                            ),
                          const SizedBox(width: 8),
                          Text(
                            '\$${fare.toStringAsFixed(2)}',
                            style: const TextStyle(
                              color: AppColors.success,
                              fontWeight: FontWeight.w900,
                              fontSize: 22,
                            ),
                          ),
                        ],
                      ),

                      if (hasPoc) ...[
                        const SizedBox(height: 10),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          decoration: BoxDecoration(
                            color: Colors.amber.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: Colors.amber.withOpacity(0.3)),
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
                                  onPressed: () => _callRider(pocPhone.toString()),
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
                      const SizedBox(height: 18),
                      const Divider(color: Colors.white10),
                      const SizedBox(height: 12),

                      // Route display
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Icon(Icons.pin_drop_rounded, color: AppColors.primary, size: 20),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  status == 'in_progress' ? 'DESTINATION' : 'PICKUP LOCATION',
                                  style: const TextStyle(
                                    color: AppColors.textMuted,
                                    fontSize: 10,
                                    fontWeight: FontWeight.w800,
                                    letterSpacing: 1,
                                  ),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  _currentDestination,
                                  style: const TextStyle(
                                    color: AppColors.textLight,
                                    fontSize: 13,
                                    fontWeight: FontWeight.w600,
                                  ),
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 22),

                      // Lifecycle Step Button
                      SizedBox(
                        height: 54,
                        child: _buildActionButton(status),
                      ),
                      const SizedBox(height: 10),

                      // Driver Cancel Button
                      SizedBox(
                        height: 42,
                        child: TextButton.icon(
                          onPressed: _isUpdating ? null : _showCancelDialog,
                          style: TextButton.styleFrom(
                            foregroundColor: AppColors.danger,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                          ),
                          icon: const Icon(Icons.cancel_outlined, size: 16),
                          label: const Text(
                            'Cancel This Ride',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                          ),
                        ),
                      ),
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

  Widget _buildActionButton(String status) {
    if (_isUpdating) {
      return const Center(child: CircularProgressIndicator(color: AppColors.primary));
    }

    final normalized = status.toLowerCase();

    if (normalized == 'accepted' || normalized == 'driver_assigned' || normalized == 'confirmed') {
      return Row(
        children: [
          Expanded(
            flex: 3,
            child: SizedBox(
              height: 52,
              child: ElevatedButton.icon(
                onPressed: () => _advanceStatus('en_route'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.info,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                ),
                icon: const Icon(Icons.directions_car_rounded, size: 18),
                label: const FittedBox(
                  fit: BoxFit.scaleDown,
                  child: Text('🚗 En Route to Pickup', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                ),
              ),
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            flex: 2,
            child: SizedBox(
              height: 52,
              child: ElevatedButton.icon(
                onPressed: () => _advanceStatus('completed'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.success,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                ),
                icon: const Icon(Icons.check_circle_rounded, size: 16),
                label: const FittedBox(
                  fit: BoxFit.scaleDown,
                  child: Text('✓ End Ride', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 13)),
                ),
              ),
            ),
          ),
        ],
      );
    } else if (normalized == 'en_route') {
      return Row(
        children: [
          Expanded(
            flex: 3,
            child: SizedBox(
              height: 52,
              child: ElevatedButton.icon(
                onPressed: () => _advanceStatus('arrived'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.warning,
                  foregroundColor: Colors.black,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                ),
                icon: const Icon(Icons.location_on_rounded, size: 18),
                label: const FittedBox(
                  fit: BoxFit.scaleDown,
                  child: Text('📍 Arrived at Pickup', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                ),
              ),
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            flex: 2,
            child: SizedBox(
              height: 52,
              child: ElevatedButton.icon(
                onPressed: () => _advanceStatus('completed'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.success,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                ),
                icon: const Icon(Icons.check_circle_rounded, size: 16),
                label: const FittedBox(
                  fit: BoxFit.scaleDown,
                  child: Text('✓ End Ride', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 13)),
                ),
              ),
            ),
          ),
        ],
      );
    } else if (normalized == 'arrived') {
      return Row(
        children: [
          Expanded(
            flex: 3,
            child: SizedBox(
              height: 52,
              child: ElevatedButton.icon(
                onPressed: () => _advanceStatus('in_progress'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.success,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                ),
                icon: const Icon(Icons.play_arrow_rounded, size: 20),
                label: const FittedBox(
                  fit: BoxFit.scaleDown,
                  child: Text('▶ Start Trip', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                ),
              ),
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            flex: 2,
            child: SizedBox(
              height: 52,
              child: ElevatedButton.icon(
                onPressed: () => _advanceStatus('completed'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.success,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                ),
                icon: const Icon(Icons.check_circle_rounded, size: 16),
                label: const FittedBox(
                  fit: BoxFit.scaleDown,
                  child: Text('✓ End Ride', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 13)),
                ),
              ),
            ),
          ),
        ],
      );
    } else {
      // in_progress / started / or any other active status
      return SizedBox(
        height: 54,
        child: ElevatedButton.icon(
          onPressed: () => _advanceStatus('completed'),
          style: ElevatedButton.styleFrom(
            backgroundColor: AppColors.success,
            foregroundColor: Colors.white,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            elevation: 4,
          ),
          icon: const Icon(Icons.check_circle_rounded, size: 22),
          label: const Text('✓ Complete & End Trip', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 16)),
        ),
      );
    }
  }
}
