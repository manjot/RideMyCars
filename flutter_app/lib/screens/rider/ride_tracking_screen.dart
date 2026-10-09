import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/constants/api_constants.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/country_provider.dart';
import '../../providers/ride_provider.dart';
import '../safety/widgets/sos_floating_button.dart';

class RideTrackingScreen extends StatefulWidget {
  final Map<String, dynamic> ride;

  const RideTrackingScreen({super.key, required this.ride});

  @override
  State<RideTrackingScreen> createState() => _RideTrackingScreenState();
}

class _RideTrackingScreenState extends State<RideTrackingScreen> {
  GoogleMapController? _mapController;
  int? _lastPromptedBackupDriverId;
  bool _isRespondingToBackup = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<RideProvider>(context, listen: false).startActiveRidePolling();
    });
  }

  @override
  void dispose() {
    _mapController?.dispose();
    super.dispose();
  }

  Future<void> _launchGoogleMaps() async {
    final pickup = widget.ride['pickup_location'] ?? '';
    final dropoff = widget.ride['dropoff_location'] ?? '';
    if (dropoff.isEmpty) return;

    final uri = Uri.parse(
      'https://www.google.com/maps/dir/?api=1&origin=${Uri.encodeComponent(pickup)}&destination=${Uri.encodeComponent(dropoff)}&travelmode=driving',
    );

    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  Future<void> _callDriver([String? overridePhone]) async {
    final rideProv = Provider.of<RideProvider>(context, listen: false);
    final currentRide = rideProv.activeRide ?? widget.ride;
    final phone = overridePhone ?? currentRide['driver']?['phone'] ?? widget.ride['driver']?['phone'];
    if (phone != null && phone.toString().isNotEmpty) {
      final uri = Uri.parse('tel:$phone');
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri);
      }
    }
  }

  Widget _buildAvatar(String? photoUrl, String name) {
    if (photoUrl != null && photoUrl.isNotEmpty) {
      final fullUrl = photoUrl.startsWith('http') ? photoUrl : '${ApiConstants.storageBaseUrl}/$photoUrl';
      return ClipRRect(
        borderRadius: BorderRadius.circular(24),
        child: Image.network(
          fullUrl,
          width: 52,
          height: 52,
          fit: BoxFit.cover,
          errorBuilder: (_, __, ___) => _buildLetterAvatar(name),
        ),
      );
    }
    return _buildLetterAvatar(name);
  }

  Widget _buildLetterAvatar(String name) {
    return CircleAvatar(
      radius: 26,
      backgroundColor: AppColors.primary,
      child: Text(
        name.isNotEmpty ? name[0].toUpperCase() : 'D',
        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 22),
      ),
    );
  }

  bool _showingCancelNotif = false;

  void _checkCancellationNotification(RideProvider rideProv) {
    final notif = rideProv.cancellationNotification;
    if (notif != null && !_showingCancelNotif) {
      _showingCancelNotif = true;
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (!mounted) return;
        showDialog(
          context: context,
          barrierDismissible: false,
          builder: (_) => AlertDialog(
            backgroundColor: AppColors.surfaceDark,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            title: Row(
              children: [
                const Icon(Icons.cancel_rounded, color: AppColors.danger, size: 28),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    notif['title']?.toString() ?? 'Ride Notice',
                    style: const TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 17),
                  ),
                ),
              ],
            ),
            content: Text(
              notif['message']?.toString() ?? 'The driver was unable to accept your request or cancelled.',
              style: const TextStyle(color: AppColors.textMuted, fontSize: 14, height: 1.4),
            ),
            actions: [
              ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: Colors.black,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                ),
                onPressed: () {
                  rideProv.clearCancellationNotification();
                  Navigator.pop(context); // dismiss dialog
                  Navigator.pop(context); // back to home screen
                },
                child: const Text('Back to Home', style: TextStyle(fontWeight: FontWeight.w900)),
              ),
            ],
          ),
        );
      });
    }
  }

  Future<void> _confirmAndCancelRide(BuildContext context, dynamic rawRideId) async {
    final rideId = int.tryParse(rawRideId?.toString() ?? '0') ?? 0;
    if (rideId <= 0) return;

    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        backgroundColor: AppColors.surfaceDark,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        title: const Text('Cancel Ride Request?', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold)),
        content: const Text(
          'Are you sure you want to cancel this ride request? Drivers will be notified immediately.',
          style: TextStyle(color: AppColors.textMuted),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Keep Ride', style: TextStyle(color: AppColors.textLight)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.danger,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Yes, Cancel', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );

    if (ok == true && context.mounted) {
      final rideProv = Provider.of<RideProvider>(context, listen: false);
      await rideProv.cancelRide(rideId);
      if (context.mounted) {
        Navigator.pop(context); // back to home
      }
    }
  }

  void _fitRouteBounds(double? pLat, double? pLng, double? dLat, double? dLng, double? drvLat, double? drvLng) {
    if (_mapController == null) return;
    final points = <LatLng>[
      if (pLat != null && pLng != null) LatLng(pLat, pLng),
      if (dLat != null && dLng != null) LatLng(dLat, dLng),
      if (drvLat != null && drvLng != null) LatLng(drvLat, drvLng),
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
  Widget build(BuildContext context) {
    final rideProv = Provider.of<RideProvider>(context);
    final currentRide = rideProv.activeRide ?? widget.ride;

    _checkCancellationNotification(rideProv);
    _checkAndShowBackupModal(currentRide);

    final countryProv = Provider.of<CountryProvider>(context);
    final status = currentRide['status'] ?? 'pending';
    final fare = currentRide['fare'] != null ? double.tryParse(currentRide['fare'].toString()) ?? 0.0 : 0.0;
    final currSym = (currentRide['currency_symbol'] != null && currentRide['currency_symbol'].toString().isNotEmpty)
        ? currentRide['currency_symbol'].toString()
        : (currentRide['currency'] == 'INR' ? '₹' : countryProv.currencySymbol);
    final driver = currentRide['driver'];
    final pickup = currentRide['pickup_location'] ?? 'Pickup';
    final dropoff = currentRide['dropoff_location'] ?? 'Destination';

    final pickupLat = currentRide['pickup_lat'] != null ? double.tryParse(currentRide['pickup_lat'].toString()) : null;
    final pickupLng = currentRide['pickup_lng'] != null ? double.tryParse(currentRide['pickup_lng'].toString()) : null;
    final dropoffLat = currentRide['dropoff_lat'] != null ? double.tryParse(currentRide['dropoff_lat'].toString()) : null;
    final dropoffLng = currentRide['dropoff_lng'] != null ? double.tryParse(currentRide['dropoff_lng'].toString()) : null;
    final driverLat = driver?['current_lat'] != null ? double.tryParse(driver['current_lat'].toString()) : null;
    final driverLng = driver?['current_lng'] != null ? double.tryParse(driver['current_lng'].toString()) : null;

    final initialPos = LatLng(pickupLat ?? 28.6448, pickupLng ?? 77.2167);

    // In-app live connected route polyline
    final polylines = <Polyline>{
      if (driverLat != null && driverLng != null && pickupLat != null && pickupLng != null && (status == 'accepted' || status == 'en_route'))
        Polyline(
          polylineId: const PolylineId('driver_to_pickup'),
          points: [LatLng(driverLat, driverLng), LatLng(pickupLat, pickupLng)],
          color: AppColors.primary,
          width: 5,
          patterns: [PatternItem.dash(20), PatternItem.gap(10)],
        ),
      if (pickupLat != null && pickupLng != null && dropoffLat != null && dropoffLng != null)
        Polyline(
          polylineId: const PolylineId('trip_route'),
          points: [
            if (status == 'in_progress' && driverLat != null && driverLng != null)
              LatLng(driverLat, driverLng)
            else
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
          // Google Map with connected in-app live navigation
          GoogleMap(
            initialCameraPosition: CameraPosition(target: initialPos, zoom: 14.0),
            myLocationEnabled: true,
            myLocationButtonEnabled: false,
            zoomControlsEnabled: false,
            polylines: polylines,
            onMapCreated: (controller) {
              _mapController = controller;
              _fitRouteBounds(pickupLat, pickupLng, dropoffLat, dropoffLng, driverLat, driverLng);
            },
            markers: {
              if (pickupLat != null && pickupLng != null)
                Marker(
                  markerId: const MarkerId('pickup'),
                  position: LatLng(pickupLat, pickupLng),
                  icon: BitmapDescriptor.defaultMarkerWithHue(BitmapDescriptor.hueGreen),
                  infoWindow: InfoWindow(title: 'Pickup Location', snippet: pickup),
                ),
              if (dropoffLat != null && dropoffLng != null)
                Marker(
                  markerId: const MarkerId('dropoff'),
                  position: LatLng(dropoffLat, dropoffLng),
                  icon: BitmapDescriptor.defaultMarkerWithHue(BitmapDescriptor.hueRed),
                  infoWindow: InfoWindow(title: 'Dropoff Destination', snippet: dropoff),
                ),
              if (driverLat != null && driverLng != null)
                Marker(
                  markerId: const MarkerId('driver'),
                  position: LatLng(driverLat, driverLng),
                  icon: BitmapDescriptor.defaultMarkerWithHue(BitmapDescriptor.hueYellow),
                  infoWindow: InfoWindow(title: driver?['name'] ?? 'Driver'),
                ),
            },
          ),

          // Header Overlay with prominent Home Navigation & Quick Cancel
          SafeArea(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
              child: Row(
                children: [
                  // Prominent Home Button
                  InkWell(
                    onTap: () => Navigator.pop(context),
                    borderRadius: BorderRadius.circular(20),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                      decoration: BoxDecoration(
                        color: AppColors.surfaceDark,
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: Colors.white24),
                        boxShadow: const [BoxShadow(color: Colors.black45, blurRadius: 6)],
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.home_rounded, color: AppColors.primary, size: 20),
                          SizedBox(width: 6),
                          Text(
                            'Home',
                            style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 13),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const Spacer(),
                  ElevatedButton.icon(
                    onPressed: _launchGoogleMaps,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.success,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                      elevation: 6,
                    ),
                    icon: const Icon(Icons.navigation_rounded, size: 18),
                    label: const Text('Live Navigation', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                  ),
                ],
              ),
            ),
          ),

          // Map Corner SOS Emergency Button (Positioned safely below top bar)
          Positioned(
            right: 16,
            top: 96,
            child: SosFloatingButton(
              rideId: currentRide['id'] as int?,
              role: 'rider',
              assignedPhone: driver?['phone']?.toString() ?? currentRide['driver_phone']?.toString(),
              assignedName: driver?['name']?.toString() ?? currentRide['driver_name']?.toString() ?? 'Assigned Driver',
            ),
          ),

          // Bottom Sheet
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
                    color: Colors.black.withOpacity( 0.55),
                    blurRadius: 30,
                    offset: const Offset(0, -10),
                  ),
                ],
              ),
              child: SafeArea(
                top: false,
                bottom: true,
                child: SingleChildScrollView(
                  padding: const EdgeInsets.fromLTRB(24, 18, 24, 16),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // Status Banner
                      Container(
                        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 16),
                        decoration: BoxDecoration(
                          color: AppColors.primary.withOpacity( 0.12),
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(color: AppColors.primary.withOpacity( 0.3)),
                        ),
                        child: Row(
                          children: [
                            const Icon(Icons.radar_rounded, color: AppColors.primary, size: 20),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Text(
                                _statusDescription(status, driver?['name'], currentRide),
                                style: const TextStyle(
                                  color: AppColors.primary,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 13,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),

                      // 4-Digit Secure Ride PIN Verification Card
                      _buildSecurePinCard(currentRide, status),

                      // In-App Modal Card: Primary Chauffeur Unavailable
                      if (currentRide['backup_status'] == 'waiting' && currentRide['backup_driver'] != null) ...[
                        _buildBackupConfirmationCard(currentRide, currentRide['backup_driver']),
                        const SizedBox(height: 14),
                      ],

                      // Driver Details (if assigned)
                      if (driver != null) ...[
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                          decoration: BoxDecoration(
                            color: AppColors.backgroundDark,
                            borderRadius: BorderRadius.circular(18),
                            border: Border.all(color: Colors.white10),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.stretch,
                            children: [
                              // Top Row: Avatar + Driver Name & Rating + Prominent Fare
                              Row(
                                children: [
                                  _buildAvatar(driver['photo_url'] ?? driver['image_url'], driver['name'] ?? 'Driver'),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          driver['name'] ?? 'Driver',
                                          style: const TextStyle(
                                            color: AppColors.textLight,
                                            fontWeight: FontWeight.bold,
                                            fontSize: 16,
                                          ),
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                        const SizedBox(height: 2),
                                        Row(
                                          children: [
                                            const Icon(Icons.star_rounded, color: AppColors.primary, size: 15),
                                            const SizedBox(width: 3),
                                            Text(
                                              '${driver['rating'] ?? 4.9} · ${driver['total_trips'] ?? 40} trips',
                                              style: const TextStyle(color: AppColors.textMuted, fontSize: 11),
                                            ),
                                          ],
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
                                      fontSize: 20,
                                    ),
                                  ),
                                ],
                              ),
                              // Bottom Row: Phone number on single line & Call button
                              if (driver['phone'] != null && driver['phone'].toString().isNotEmpty) ...[
                                const SizedBox(height: 10),
                                Container(
                                  padding: const EdgeInsets.only(top: 8),
                                  decoration: const BoxDecoration(
                                    border: Border(top: BorderSide(color: Colors.white10)),
                                  ),
                                  child: Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Row(
                                        children: [
                                          const Icon(Icons.phone_rounded, color: AppColors.primary, size: 14),
                                          const SizedBox(width: 6),
                                          Text(
                                            driver['phone'].toString(),
                                            style: const TextStyle(
                                              color: AppColors.primary,
                                              fontSize: 13,
                                              fontWeight: FontWeight.w700,
                                              letterSpacing: 0.3,
                                            ),
                                          ),
                                        ],
                                      ),
                                      ElevatedButton.icon(
                                        onPressed: () => _callDriver(driver['phone'].toString()),
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: AppColors.success,
                                          foregroundColor: Colors.white,
                                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                          elevation: 2,
                                          minimumSize: Size.zero,
                                          tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                        ),
                                        icon: const Icon(Icons.phone_rounded, size: 14),
                                        label: const Text('Call', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ),
                        const SizedBox(height: 12),
                      ],

                      // Passenger / POC Details (if booked for someone else)
                      if ((currentRide['poc_name'] != null && currentRide['poc_name'].toString().isNotEmpty) ||
                          (currentRide['is_for_someone_else'] == true && currentRide['passenger_name'] != null)) ...[
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                          decoration: BoxDecoration(
                            color: Colors.amber.withOpacity( 0.1),
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: Colors.amber.withOpacity( 0.3)),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.person_pin_rounded, color: Colors.amber, size: 22),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text(
                                      'PASSENGER / POC',
                                      style: TextStyle(color: Colors.amber, fontSize: 10, fontWeight: FontWeight.bold, letterSpacing: 0.5),
                                    ),
                                    Text(
                                      currentRide['poc_name'] ?? currentRide['passenger_name'] ?? 'Passenger',
                                      style: const TextStyle(color: AppColors.textLight, fontSize: 13, fontWeight: FontWeight.bold),
                                    ),
                                    if ((currentRide['poc_phone'] ?? currentRide['passenger_phone']) != null)
                                      Text(
                                        (currentRide['poc_phone'] ?? currentRide['passenger_phone']).toString(),
                                        style: const TextStyle(color: AppColors.textMuted, fontSize: 11),
                                      ),
                                  ],
                                ),
                              ),
                              if ((currentRide['poc_phone'] ?? currentRide['passenger_phone']) != null)
                                IconButton(
                                  onPressed: () => _callDriver((currentRide['poc_phone'] ?? currentRide['passenger_phone']).toString()),
                                  style: IconButton.styleFrom(
                                    backgroundColor: Colors.amber.withOpacity( 0.2),
                                    foregroundColor: Colors.amber,
                                  ),
                                  icon: const Icon(Icons.phone_rounded, size: 18),
                                ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 12),
                      ],

                      // Requested Date & Time Badge
                      Builder(builder: (_) {
                        final rawCreated = currentRide['created_at'];
                        final reqFormatted = currentRide['request_time_formatted'];
                        final reqHuman = currentRide['request_time_human'];
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

                        final pickupDate = currentRide['pickup_date']?.toString();
                        final pickupTime = currentRide['pickup_time']?.toString();
                        final hasScheduled = (pickupDate != null && pickupDate.isNotEmpty && pickupDate != 'null') ||
                            (pickupTime != null && pickupTime.isNotEmpty && pickupTime != 'null');

                        return Container(
                          margin: const EdgeInsets.only(bottom: 12),
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.04),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: Colors.white10),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.schedule_rounded, color: AppColors.primary, size: 15),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  'Requested: $displayTime${reqHuman != null ? ' ($reqHuman)' : ''}',
                                  style: const TextStyle(
                                    color: AppColors.textLight,
                                    fontSize: 12,
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
                                    fontSize: 11,
                                    fontWeight: FontWeight.w700,
                                  ),
                                ),
                              ],
                            ],
                          ),
                        );
                      }),

                      // Route Information
                      Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: AppColors.backgroundDark,
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: Column(
                          children: [
                            Row(
                              children: [
                                const Icon(Icons.circle, color: AppColors.success, size: 12),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: Text(
                                    pickup,
                                    style: const TextStyle(color: AppColors.textLight, fontSize: 13, fontWeight: FontWeight.w600),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                              ],
                            ),
                            const Padding(
                              padding: EdgeInsets.only(left: 5),
                              child: Align(alignment: Alignment.centerLeft, child: SizedBox(height: 14, child: VerticalDivider(color: Colors.white24))),
                            ),
                            Row(
                              children: [
                                const Icon(Icons.location_on_rounded, color: AppColors.danger, size: 14),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: Text(
                                    dropoff,
                                    style: const TextStyle(color: AppColors.textLight, fontSize: 13, fontWeight: FontWeight.w600),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),

                      // Cancel Button
                      if (status == 'pending' || status == 'accepted' || status == 'en_route')
                        ElevatedButton.icon(
                          onPressed: () => _confirmAndCancelRide(context, currentRide['id']),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.danger,
                            foregroundColor: Colors.white,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            elevation: 4,
                          ),
                          icon: const Icon(Icons.cancel_rounded, size: 18),
                          label: const Text('✕ Cancel Ride Request', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
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

  String _statusDescription(String status, String? driverName, [Map<String, dynamic>? currentRide]) {
    final d = driverName ?? 'Driver';
    final backupStatus = currentRide?['backup_status'];
    final assignmentType = currentRide?['driver_assignment_type'];

    if (backupStatus == 'searching') {
      return 'Searching for another chauffeur...';
    }
    if (backupStatus == 'waiting') {
      return 'Driver Found • Waiting for your confirmation';
    }
    if (assignmentType == 'backup' && status == 'accepted') {
      return 'Driver Assigned • $d';
    }
    if (status == 'cancelled' && currentRide?['cancellation_reason'] != null && currentRide!['cancellation_reason'].toString().contains('No nearby')) {
      return 'No nearby chauffeur is currently available.';
    }

    switch (status) {
      case 'pending': return 'Contacting nearby verified drivers...';
      case 'accepted': return '$d has accepted your ride!';
      case 'en_route': return '$d is on the way to your pickup.';
      case 'arrived': return '$d has arrived! Please meet your driver.';
      case 'in_progress': return 'Trip in progress. Enjoy your journey!';
      case 'completed': return 'Trip completed! Safe travels.';
      default: return 'Ride active';
    }
  }

  void _checkAndShowBackupModal(Map<String, dynamic> ride) {
    final backupStatus = ride['backup_status'];
    final backupDriver = ride['backup_driver'];

    if (backupStatus == 'waiting' && backupDriver != null) {
      final driverId = backupDriver['id'];
      if (_lastPromptedBackupDriverId != driverId) {
        _lastPromptedBackupDriverId = driverId;
        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (mounted) {
            _showBackupChauffeurDialog(ride, backupDriver);
          }
        });
      }
    }
  }

  void _showBackupChauffeurDialog(Map<String, dynamic> ride, Map<String, dynamic> backupDriver) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppColors.surfaceDark,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(24),
          side: const BorderSide(color: AppColors.primary, width: 2),
        ),
        title: const Row(
          children: [
            Icon(Icons.shield_outlined, color: AppColors.primary, size: 24),
            SizedBox(width: 8),
            Expanded(
              child: Text(
                'Primary Chauffeur Unavailable',
                style: TextStyle(color: AppColors.textLight, fontSize: 16, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Text(
              'A nearby chauffeur is available to take your ride.\n\nWould you like to continue with this chauffeur?',
              style: TextStyle(color: AppColors.textLight, fontSize: 13.5, height: 1.3),
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.backgroundDark,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: Colors.white10),
              ),
              child: Row(
                children: [
                  _buildAvatar(backupDriver['photo_url'], backupDriver['name'] ?? 'Chauffeur'),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          backupDriver['name'] ?? 'Backup Chauffeur',
                          style: const TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 15),
                        ),
                        Text(
                          backupDriver['vehicle'] ?? 'Executive Sedan',
                          style: const TextStyle(color: AppColors.textMuted, fontSize: 12),
                        ),
                        Row(
                          children: [
                            const Icon(Icons.star_rounded, color: AppColors.primary, size: 14),
                            const SizedBox(width: 4),
                            Text(
                              '${backupDriver['rating'] ?? 4.9} · ${backupDriver['total_trips'] ?? 35} trips',
                              style: const TextStyle(color: AppColors.textMuted, fontSize: 11),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
        actionsPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
        actions: [
          Row(
            children: [
              Expanded(
                child: OutlinedButton(
                  onPressed: _isRespondingToBackup
                      ? null
                      : () async {
                          Navigator.pop(ctx);
                          setState(() => _isRespondingToBackup = true);
                          final rideProv = Provider.of<RideProvider>(context, listen: false);
                          await rideProv.declineBackupDriver(ride['id']);
                          if (mounted) setState(() => _isRespondingToBackup = false);
                        },
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppColors.danger,
                    side: const BorderSide(color: AppColors.danger),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                  child: const Text('Decline', style: TextStyle(fontWeight: FontWeight.bold)),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: ElevatedButton(
                  onPressed: _isRespondingToBackup
                      ? null
                      : () async {
                          Navigator.pop(ctx);
                          setState(() => _isRespondingToBackup = true);
                          final rideProv = Provider.of<RideProvider>(context, listen: false);
                          await rideProv.confirmBackupDriver(ride['id']);
                          if (mounted) setState(() => _isRespondingToBackup = false);
                        },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.success,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                  child: const Text('Confirm', style: TextStyle(fontWeight: FontWeight.bold)),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildBackupConfirmationCard(Map<String, dynamic> ride, Map<String, dynamic> backupDriver) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.primary.withOpacity(0.08),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppColors.primary, width: 1.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const Row(
            children: [
              Icon(Icons.shield_outlined, color: AppColors.primary, size: 20),
              SizedBox(width: 8),
              Expanded(
                child: Text(
                  'Primary Chauffeur Unavailable',
                  style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.bold, fontSize: 14),
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          const Text(
            'A nearby chauffeur is available to take your ride. Would you like to continue with this chauffeur?',
            style: TextStyle(color: AppColors.textLight, fontSize: 12.5, height: 1.3),
          ),
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: AppColors.backgroundDark,
              borderRadius: BorderRadius.circular(14),
            ),
            child: Row(
              children: [
                _buildAvatar(backupDriver['photo_url'], backupDriver['name'] ?? 'Chauffeur'),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        backupDriver['name'] ?? 'Backup Chauffeur',
                        style: const TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 14),
                      ),
                      Text(
                        backupDriver['vehicle'] ?? 'Executive Sedan',
                        style: const TextStyle(color: AppColors.textMuted, fontSize: 11),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: OutlinedButton(
                  onPressed: _isRespondingToBackup
                      ? null
                      : () async {
                          setState(() => _isRespondingToBackup = true);
                          final rideProv = Provider.of<RideProvider>(context, listen: false);
                          await rideProv.declineBackupDriver(ride['id']);
                          if (mounted) setState(() => _isRespondingToBackup = false);
                        },
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppColors.danger,
                    side: const BorderSide(color: AppColors.danger),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    padding: const EdgeInsets.symmetric(vertical: 10),
                  ),
                  child: const Text('Decline', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: ElevatedButton(
                  onPressed: _isRespondingToBackup
                      ? null
                      : () async {
                          setState(() => _isRespondingToBackup = true);
                          final rideProv = Provider.of<RideProvider>(context, listen: false);
                          await rideProv.confirmBackupDriver(ride['id']);
                          if (mounted) setState(() => _isRespondingToBackup = false);
                        },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.success,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    padding: const EdgeInsets.symmetric(vertical: 10),
                  ),
                  child: const Text('Confirm', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildSecurePinCard(Map<String, dynamic> currentRide, String status) {
    final rawPin = currentRide['start_pin'] ?? currentRide['otp'] ?? currentRide['pin'] ?? widget.ride['start_pin'] ?? widget.ride['otp'];
    final pin = rawPin?.toString() ?? '4821';
    final isVerified = ['in_progress', 'completed'].contains(status.toLowerCase());

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF131D33),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: isVerified ? const Color(0xFF10B981).withOpacity(0.4) : const Color(0xFFF59E0B).withOpacity(0.4),
          width: 1.5,
        ),
        boxShadow: [
          BoxShadow(
            color: (isVerified ? const Color(0xFF10B981) : const Color(0xFFF59E0B)).withOpacity(0.08),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: (isVerified ? const Color(0xFF10B981) : const Color(0xFFF59E0B)).withOpacity(0.18),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(
                  isVerified ? Icons.verified_user_rounded : Icons.shield_rounded,
                  color: isVerified ? const Color(0xFF10B981) : const Color(0xFFF59E0B),
                  size: 20,
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'SECURE RIDE VERIFICATION',
                      style: TextStyle(
                        color: isVerified ? const Color(0xFF10B981) : const Color(0xFFF59E0B),
                        fontSize: 9.5,
                        fontWeight: FontWeight.w900,
                        letterSpacing: 0.8,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      isVerified ? 'PIN Verified • Trip in Progress' : '4-Digit Start Ride PIN',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
              ),
              InkWell(
                onTap: () {
                  Clipboard.setData(ClipboardData(text: pin));
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('✓ 4-Digit Ride PIN copied to clipboard!'),
                      duration: Duration(seconds: 2),
                      backgroundColor: Color(0xFF10B981),
                    ),
                  );
                },
                borderRadius: BorderRadius.circular(12),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: Colors.black,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: isVerified ? const Color(0xFF10B981).withOpacity(0.6) : const Color(0xFFF59E0B).withOpacity(0.6),
                      width: 1.5,
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        pin,
                        style: TextStyle(
                          color: isVerified ? const Color(0xFF10B981) : const Color(0xFFF59E0B),
                          fontFamily: 'monospace',
                          fontSize: 18,
                          fontWeight: FontWeight.w900,
                          letterSpacing: 3,
                        ),
                      ),
                      const SizedBox(width: 6),
                      Icon(
                        Icons.copy_rounded,
                        size: 13,
                        color: isVerified ? const Color(0xFF10B981) : const Color(0xFFF59E0B),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            isVerified
                ? 'Your 4-digit PIN was verified with your driver. Sit back, relax, and enjoy your journey!'
                : 'Share this 4-digit PIN with your driver upon arrival. The driver must verify this PIN before starting the ride.',
            style: const TextStyle(color: Colors.white70, fontSize: 11.5, height: 1.35),
          ),
        ],
      ),
    );
  }
}
