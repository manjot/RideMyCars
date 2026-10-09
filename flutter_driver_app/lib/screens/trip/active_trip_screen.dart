import 'package:flutter/material.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/country_provider.dart';
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

  Future<void> _sharePrescription(Map<String, dynamic> rx) async {
    final downloadUrl = rx['download_url'] ?? rx['view_url'] ?? '';
    final fileName = rx['file_name'] ?? 'Prescription';
    final shareText = 'Doctor Prescription for Medicine Pickup ($fileName): $downloadUrl';

    final uri = Uri.parse('whatsapp://send?text=${Uri.encodeComponent(shareText)}');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    } else {
      final mailUri = Uri.parse('mailto:?subject=${Uri.encodeComponent("Doctor Prescription - $fileName")}&body=${Uri.encodeComponent(shareText)}');
      if (await canLaunchUrl(mailUri)) {
        await launchUrl(mailUri);
      } else if (downloadUrl.isNotEmpty) {
        await launchUrl(Uri.parse(downloadUrl.toString()), mode: LaunchMode.externalApplication);
      }
    }
  }

  void _showPrescriptionModal(Map<String, dynamic> rx) {
    final fileName = rx['file_name']?.toString() ?? 'Doctor Prescription';
    final fileType = rx['file_type']?.toString().toUpperCase() ?? 'DOC';
    final isPdf = fileType == 'PDF';
    final downloadUrl = rx['download_url']?.toString();

    showDialog(
      context: context,
      builder: (ctx) {
        TransformationController transformCtrl = TransformationController();
        return Dialog(
          backgroundColor: const Color(0xFF0F172A),
          insetPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          child: Container(
            padding: const EdgeInsets.all(16),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Row(
                  children: [
                    Icon(
                      isPdf ? Icons.picture_as_pdf_rounded : Icons.medical_services_rounded,
                      color: isPdf ? const Color(0xFFEF4444) : const Color(0xFF10B981),
                      size: 22,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            fileName,
                            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                          Text(
                            '$fileType Prescription • Pinch or use buttons to zoom',
                            style: const TextStyle(color: Colors.white54, fontSize: 10.5),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close, color: Colors.white70),
                      onPressed: () => Navigator.pop(ctx),
                    ),
                  ],
                ),
                const Divider(color: Colors.white12, height: 16),
                Container(
                  height: 320,
                  width: double.infinity,
                  decoration: BoxDecoration(
                    color: const Color(0xFF1E293B),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: Colors.white12),
                  ),
                  clipBehavior: Clip.antiAlias,
                  child: InteractiveViewer(
                    transformationController: transformCtrl,
                    minScale: 0.8,
                    maxScale: 5.0,
                    child: Center(
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Container(
                              padding: const EdgeInsets.all(20),
                              decoration: BoxDecoration(
                                color: const Color(0xFF10B981).withOpacity(0.15),
                                shape: BoxShape.circle,
                              ),
                              child: Icon(
                                isPdf ? Icons.picture_as_pdf_rounded : Icons.receipt_long_rounded,
                                color: isPdf ? const Color(0xFFEF4444) : const Color(0xFF10B981),
                                size: 54,
                              ),
                            ),
                            const SizedBox(height: 14),
                            Text(
                              fileName,
                              textAlign: TextAlign.center,
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                            ),
                            const SizedBox(height: 6),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                color: const Color(0xFF10B981).withOpacity(0.2),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: const Text(
                                '✓ VALID PRESCRIPTION FOR PHARMACY',
                                style: TextStyle(color: Color(0xFF10B981), fontSize: 10, fontWeight: FontWeight.w900),
                              ),
                            ),
                            const SizedBox(height: 10),
                            const Text(
                              'Present this screen to the pharmacist / medicine counter.\nPinch with 2 fingers to zoom into dosage details.',
                              textAlign: TextAlign.center,
                              style: TextStyle(color: Colors.white60, fontSize: 11),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        IconButton(
                          icon: const Icon(Icons.zoom_in, color: Color(0xFFF59E0B)),
                          tooltip: 'Zoom In',
                          onPressed: () {
                            transformCtrl.value = transformCtrl.value.scaled(1.25);
                          },
                        ),
                        IconButton(
                          icon: const Icon(Icons.zoom_out, color: Color(0xFFF59E0B)),
                          tooltip: 'Zoom Out',
                          onPressed: () {
                            transformCtrl.value = transformCtrl.value.scaled(0.8);
                          },
                        ),
                        IconButton(
                          icon: const Icon(Icons.restart_alt_rounded, color: Colors.white54),
                          tooltip: 'Reset Zoom',
                          onPressed: () {
                            transformCtrl.value = Matrix4.identity();
                          },
                        ),
                      ],
                    ),
                    Row(
                      children: [
                        if (downloadUrl != null && downloadUrl.isNotEmpty)
                          IconButton(
                            icon: const Icon(Icons.download_rounded, color: Color(0xFF10B981)),
                            tooltip: 'Download Prescription',
                            onPressed: () async {
                              final uri = Uri.parse(downloadUrl);
                              if (await canLaunchUrl(uri)) {
                                await launchUrl(uri, mode: LaunchMode.externalApplication);
                              }
                            },
                          ),
                        IconButton(
                          icon: const Icon(Icons.share_rounded, color: Color(0xFF3B82F6)),
                          tooltip: 'Share Prescription',
                          onPressed: () => _sharePrescription(rx),
                        ),
                      ],
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Future<void> _advanceStatus(String newStatus) async {
    final rideId = int.tryParse(_ride['id']?.toString() ?? '0') ?? 0;
    if (rideId <= 0) return;

    if (newStatus == 'completed') {
      final fareAmount = double.tryParse((_ride['fare'] ?? _ride['total_price'] ?? '0').toString()) ?? 0.0;
      final countryProv = Provider.of<CountryProvider>(context, listen: false);
      final currSym = (_ride['currency_symbol'] ?? countryProv.currencySymbol).toString();
      final rawMethod = (_ride['payment_method'] ?? 'cash').toString().toLowerCase();
      final isCash = rawMethod.contains('cash');
      final confirmMsg = isCash
          ? 'Are you sure you want to end this trip and collect $currSym${fareAmount.toStringAsFixed(2)} cash from rider?'
          : 'Are you sure you want to end this trip? Fare of $currSym${fareAmount.toStringAsFixed(2)} was prepaid digitally and will be credited to your earnings.';

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
            confirmMsg,
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
    final success = await driver.updateRideStatus(rideId, newStatus, type: _ride['type']?.toString());

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
    final rawId = _ride['id'] ?? _ride['ride_id'] ?? _ride['booking_id'] ?? _ride['package_delivery_id'] ?? _ride['code'] ?? '';
    final cleanIdStr = rawId.toString().replaceAll(RegExp(r'[^0-9]'), '');
    final rideId = int.tryParse(cleanIdStr) ?? (int.tryParse(rawId.toString()) ?? 0);

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
      final ok = await driver.cancelRide(rideId > 0 ? rideId : rawId, reason: selectedReason, fallbackRide: _ride);

      if (!mounted) return;
      setState(() => _isUpdating = false);

      if (ok) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('✓ Trip ${rideId > 0 ? "#$rideId " : ""}has been cancelled.'),
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
    final countryProv = Provider.of<CountryProvider>(context, listen: false);
    final currSym = (_ride['currency_symbol'] ?? countryProv.currencySymbol).toString();
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
                ],
              ),
            ),
          ),

          // SOS Map Corner Floating Button (Driver, positioned safely below top bar)
          Positioned(
            right: 16,
            top: 96,
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
                      // Customer Info (Clean 2-row Layout)
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          // Row 1: Avatar + Name & Status Badge + Fare
                          Row(
                            children: [
                              CircleAvatar(
                                radius: 22,
                                backgroundColor: AppColors.purple,
                                child: Text(
                                  customerName.isNotEmpty ? customerName[0].toUpperCase() : 'C',
                                  style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 17),
                                ),
                              ),
                              const SizedBox(width: 12),
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
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                    const SizedBox(height: 3),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                      decoration: BoxDecoration(
                                        color: AppColors.primary.withOpacity(0.15),
                                        borderRadius: BorderRadius.circular(6),
                                        border: Border.all(color: AppColors.primary.withOpacity(0.3)),
                                      ),
                                      child: Text(
                                        status.replaceAll('_', ' ').toUpperCase(),
                                        style: const TextStyle(
                                          color: AppColors.primary,
                                          fontWeight: FontWeight.w800,
                                          fontSize: 9.5,
                                          letterSpacing: 0.5,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              const SizedBox(width: 10),
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
                          // Row 2: Customer Phone Number (Single clean line) & Call button
                          if (customerPhone != null && customerPhone.toString().isNotEmpty) ...[
                            const SizedBox(height: 10),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                              decoration: BoxDecoration(
                                color: Colors.white.withOpacity(0.04),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Row(
                                    children: [
                                      const Icon(Icons.phone_rounded, color: AppColors.primary, size: 14),
                                      const SizedBox(width: 6),
                                      Text(
                                        customerPhone.toString(),
                                        style: const TextStyle(
                                          color: AppColors.primary,
                                          fontWeight: FontWeight.w700,
                                          fontSize: 13,
                                          letterSpacing: 0.3,
                                        ),
                                      ),
                                    ],
                                  ),
                                  ElevatedButton.icon(
                                    onPressed: () => _callRider(customerPhone.toString()),
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: AppColors.success,
                                      foregroundColor: Colors.white,
                                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                      elevation: 2,
                                      minimumSize: Size.zero,
                                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                    ),
                                    icon: const Icon(Icons.phone_rounded, size: 13),
                                    label: const Text('Call', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ],
                      ),

                      // Unified Payment Indicator
                      Builder(builder: (_) {
                        final rawMethod = (_ride['payment_method'] ?? _ride['payment']?['method'] ?? 'cash').toString().toLowerCase();
                        String payTitle = 'Cash Direct Pay';
                        String payDesc = 'Collect physical cash upon destination';
                        Color payColor = const Color(0xFF10B981);
                        IconData payIcon = Icons.payments_rounded;

                        if (rawMethod.contains('stripe') || rawMethod.contains('card')) {
                          payTitle = 'Stripe (Cards & Apple Pay)';
                          payDesc = 'Prepaid online • Do NOT collect cash';
                          payColor = const Color(0xFF6366F1);
                          payIcon = Icons.credit_card_rounded;
                        } else if (rawMethod.contains('momo')) {
                          payTitle = 'MoMo Pay';
                          payDesc = 'Prepaid via Mobile Money • Do NOT collect cash';
                          payColor = const Color(0xFFFFCC00);
                          payIcon = Icons.phone_android_rounded;
                        } else if (rawMethod.contains('wallet')) {
                          payTitle = 'RideMyCars Wallet';
                          payDesc = 'Prepaid via In-App Wallet • Do NOT collect cash';
                          payColor = const Color(0xFFF59E0B);
                          payIcon = Icons.account_balance_wallet_rounded;
                        }

                        return Container(
                          margin: const EdgeInsets.only(top: 10),
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          decoration: BoxDecoration(
                            color: payColor.withOpacity(0.12),
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: payColor.withOpacity(0.35)),
                          ),
                          child: Row(
                            children: [
                              Icon(payIcon, size: 18, color: payColor),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      payTitle,
                                      style: TextStyle(
                                        color: payColor,
                                        fontSize: 12,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                    Text(
                                      payDesc,
                                      style: TextStyle(
                                        color: payColor.withOpacity(0.85),
                                        fontSize: 10.5,
                                      ),
                                      maxLines: 2,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        );
                      }),

                      // Requested Date & Time Badge
                      Builder(builder: (_) {
                        final rawCreated = _ride['created_at'];
                        final reqFormatted = _ride['request_time_formatted'];
                        final reqHuman = _ride['request_time_human'];
                        String displayTime = 'Recent';
                        if (reqFormatted != null && reqFormatted.toString().isNotEmpty) {
                          displayTime = reqFormatted.toString();
                        } else if (rawCreated != null && rawCreated.toString().isNotEmpty) {
                          try {
                            final dt = DateTime.parse(rawCreated.toString()).toLocal();
                            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                            final m = months[dt.month - 1];
                            final d = dt.day.toString().padLeft(2, '0');
                            final y = dt.year;
                            final hour = dt.hour % 12 == 0 ? 12 : dt.hour % 12;
                            final min = dt.minute.toString().padLeft(2, '0');
                            final ampm = dt.hour >= 12 ? 'PM' : 'AM';
                            displayTime = '$d $m $y • $hour:$min $ampm';
                          } catch (_) {
                            displayTime = rawCreated.toString();
                          }
                        }

                        final pickupDate = _ride['pickup_date']?.toString();
                        final pickupTime = _ride['pickup_time']?.toString();
                        final hasScheduled = (pickupDate != null && pickupDate.isNotEmpty && pickupDate != 'null') ||
                            (pickupTime != null && pickupTime.isNotEmpty && pickupTime != 'null');

                        return Container(
                          margin: const EdgeInsets.only(top: 8),
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.04),
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: Colors.white10),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.schedule_rounded, color: AppColors.primary, size: 14),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  'Requested: $displayTime${reqHuman != null ? ' ($reqHuman)' : ''}',
                                  style: const TextStyle(
                                    color: AppColors.textLight,
                                    fontSize: 11.5,
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
                        );
                      }),

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
                      // Doctor Prescription Section (Pharmeasy Medicine Delivery)
                      Builder(builder: (_) {
                        final category = (_ride['package_category'] ?? _ride['category'] ?? '').toString();
                        final hasRx = _ride['has_prescription'] == true ||
                            (_ride['prescriptions'] is List && (_ride['prescriptions'] as List).isNotEmpty);
                        final isPharmeasy = category.toLowerCase() == 'pharmeasy' || hasRx;

                        if (!isPharmeasy) return const SizedBox.shrink();

                        final List prescriptions = (_ride['prescriptions'] as List?) ?? [];

                        return Container(
                          margin: const EdgeInsets.only(bottom: 16),
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: const Color(0xFF10B981).withOpacity(0.12),
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: const Color(0xFF10B981).withOpacity(0.4)),
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xFF10B981).withOpacity(0.1),
                                blurRadius: 12,
                                offset: const Offset(0, 3),
                              ),
                            ],
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Row(
                                    children: const [
                                      Icon(Icons.local_pharmacy_rounded, color: Color(0xFF10B981), size: 20),
                                      SizedBox(width: 8),
                                      Text(
                                        'Doctor Prescription (Rx)',
                                        style: TextStyle(
                                          color: Color(0xFF10B981),
                                          fontSize: 13,
                                          fontWeight: FontWeight.w900,
                                        ),
                                      ),
                                    ],
                                  ),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFF10B981),
                                      borderRadius: BorderRadius.circular(6),
                                    ),
                                    child: const Text(
                                      'SHOW AT PHARMACY',
                                      style: TextStyle(color: Colors.black, fontSize: 9.5, fontWeight: FontWeight.w900),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 6),
                              const Text(
                                'Present to pharmacy storekeeper to inspect dosage and verify medicine pickup.',
                                style: TextStyle(color: Colors.white70, fontSize: 11),
                              ),
                              const SizedBox(height: 10),

                              if (prescriptions.isNotEmpty) ...[
                                ...prescriptions.map((rx) {
                                  final rxMap = rx is Map ? Map<String, dynamic>.from(rx) : <String, dynamic>{};
                                  final fileName = rxMap['file_name']?.toString() ?? 'Doctor Prescription';
                                  final fileType = rxMap['file_type']?.toString().toUpperCase() ?? 'DOC';
                                  final isPdf = fileType == 'PDF';

                                  return Container(
                                    margin: const EdgeInsets.only(bottom: 8),
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFF1E293B),
                                      borderRadius: BorderRadius.circular(12),
                                      border: Border.all(color: Colors.white10),
                                    ),
                                    child: Row(
                                      children: [
                                        Icon(
                                          isPdf ? Icons.picture_as_pdf_rounded : Icons.medical_services_rounded,
                                          color: isPdf ? const Color(0xFFEF4444) : const Color(0xFF10B981),
                                          size: 20,
                                        ),
                                        const SizedBox(width: 8),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                fileName,
                                                style: const TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.bold),
                                                maxLines: 1,
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                              Text(
                                                '$fileType Document • ${rxMap['file_size'] ?? 'Attached'}',
                                                style: const TextStyle(color: Colors.white54, fontSize: 9.5),
                                              ),
                                            ],
                                          ),
                                        ),
                                        IconButton(
                                          icon: const Icon(Icons.zoom_in_rounded, color: Color(0xFFF59E0B), size: 20),
                                          tooltip: 'View & Zoom Full Screen',
                                          onPressed: () => _showPrescriptionModal(rxMap),
                                        ),
                                        IconButton(
                                          icon: const Icon(Icons.share_rounded, color: Color(0xFF3B82F6), size: 18),
                                          tooltip: 'Share with Pharmacy',
                                          onPressed: () => _sharePrescription(rxMap),
                                        ),
                                      ],
                                    ),
                                  );
                                }).toList(),
                              ] else ...[
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFF1E293B),
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: Row(
                                    children: [
                                      const Icon(Icons.check_circle_rounded, color: Color(0xFF10B981), size: 18),
                                      const SizedBox(width: 8),
                                      const Expanded(
                                        child: Text(
                                          'Prescription approved on order dispatch.',
                                          style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ],
                          ),
                        );
                      }),
                      const SizedBox(height: 8),

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
                  child: Text('En Route to Pickup', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
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
                  child: Text('End Ride', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 13)),
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
                  child: Text('Arrived at Pickup', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
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
                  child: Text('End Ride', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 13)),
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
                  child: Text('Start Trip', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
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
                  child: Text('End Ride', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 13)),
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
          label: const Text('Complete & End Trip', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 16)),
        ),
      );
    }
  }
}
