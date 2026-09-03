import 'package:flutter/material.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/ride_provider.dart';

class RideTrackingScreen extends StatefulWidget {
  final Map<String, dynamic> ride;

  const RideTrackingScreen({super.key, required this.ride});

  @override
  State<RideTrackingScreen> createState() => _RideTrackingScreenState();
}

class _RideTrackingScreenState extends State<RideTrackingScreen> {
  GoogleMapController? _mapController;

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

  Future<void> _callDriver() async {
    final phone = widget.ride['driver']?['phone'];
    if (phone != null && phone.isNotEmpty) {
      final uri = Uri.parse('tel:$phone');
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final rideProv = Provider.of<RideProvider>(context);
    final currentRide = rideProv.activeRide ?? widget.ride;

    final status = currentRide['status'] ?? 'pending';
    final fare = currentRide['fare'] != null ? double.tryParse(currentRide['fare'].toString()) ?? 0.0 : 0.0;
    final driver = currentRide['driver'];
    final pickup = currentRide['pickup_location'] ?? 'Pickup';
    final dropoff = currentRide['dropoff_location'] ?? 'Destination';

    final pickupLat = currentRide['pickup_lat'] != null ? double.tryParse(currentRide['pickup_lat'].toString()) : null;
    final pickupLng = currentRide['pickup_lng'] != null ? double.tryParse(currentRide['pickup_lng'].toString()) : null;
    final driverLat = driver?['current_lat'] != null ? double.tryParse(driver['current_lat'].toString()) : null;
    final driverLng = driver?['current_lng'] != null ? double.tryParse(driver['current_lng'].toString()) : null;

    final initialPos = LatLng(pickupLat ?? 28.6448, pickupLng ?? 77.2167);

    return Scaffold(
      backgroundColor: AppColors.backgroundDark,
      body: Stack(
        children: [
          // Google Map
          GoogleMap(
            initialCameraPosition: CameraPosition(target: initialPos, zoom: 14.0),
            myLocationEnabled: true,
            myLocationButtonEnabled: false,
            zoomControlsEnabled: false,
            onMapCreated: (controller) => _mapController = controller,
            markers: {
              if (pickupLat != null && pickupLng != null)
                Marker(
                  markerId: const MarkerId('pickup'),
                  position: LatLng(pickupLat, pickupLng),
                  infoWindow: InfoWindow(title: 'Pickup', snippet: pickup),
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

          // Header
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
                  ElevatedButton.icon(
                    onPressed: _launchGoogleMaps,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.success,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                      elevation: 6,
                    ),
                    icon: const Icon(Icons.navigation_rounded, size: 18),
                    label: const Text('Live Navigation', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                  ),
                ],
              ),
            ),
          ),

          // Bottom Sheet
          Positioned(
            left: 0,
            right: 0,
            bottom: 0,
            child: Container(
              padding: const EdgeInsets.fromLTRB(24, 20, 24, 28),
              decoration: BoxDecoration(
                color: AppColors.surfaceDark,
                borderRadius: const BorderRadius.vertical(top: Radius.circular(32)),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.55),
                    blurRadius: 30,
                    offset: const Offset(0, -10),
                  ),
                ],
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Status Banner
                  Container(
                    padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 16),
                    decoration: BoxDecoration(
                      color: AppColors.primary.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: AppColors.primary.withOpacity(0.3)),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.radar_rounded, color: AppColors.primary, size: 20),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            _statusDescription(status, driver?['name']),
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

                  // Driver Details (if assigned)
                  if (driver != null) ...[
                    Row(
                      children: [
                        CircleAvatar(
                          radius: 26,
                          backgroundColor: AppColors.purple,
                          child: Text(
                            (driver['name'] ?? 'D')[0].toUpperCase(),
                            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 20),
                          ),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                driver['name'] ?? 'Driver',
                                style: const TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 17),
                              ),
                              Row(
                                children: [
                                  const Icon(Icons.star_rounded, color: AppColors.primary, size: 16),
                                  const SizedBox(width: 4),
                                  Text(
                                    '${driver['rating'] ?? 4.9} · ${driver['total_trips'] ?? 40} trips',
                                    style: const TextStyle(color: AppColors.textMuted, fontSize: 12),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                        if (driver['phone'] != null)
                          IconButton(
                            onPressed: _callDriver,
                            style: IconButton.styleFrom(
                              backgroundColor: AppColors.success.withOpacity(0.2),
                              foregroundColor: AppColors.success,
                            ),
                            icon: const Icon(Icons.phone_rounded),
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
                    const SizedBox(height: 14),
                  ],

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
                  const SizedBox(height: 18),

                  // Cancel Button
                  if (status == 'pending' || status == 'accepted')
                    OutlinedButton(
                      onPressed: () async {
                        final ok = await showDialog<bool>(
                          context: context,
                          builder: (_) => AlertDialog(
                            backgroundColor: AppColors.surfaceDark,
                            title: const Text('Cancel Ride?', style: TextStyle(color: AppColors.textLight)),
                            content: const Text('Are you sure you want to cancel this ride request?', style: TextStyle(color: AppColors.textMuted)),
                            actions: [
                              TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('No')),
                              ElevatedButton(
                                style: ElevatedButton.styleFrom(backgroundColor: AppColors.danger),
                                onPressed: () => Navigator.pop(context, true),
                                child: const Text('Yes, Cancel', style: TextStyle(color: Colors.white)),
                              ),
                            ],
                          ),
                        );

                        if (ok == true && context.mounted) {
                          await rideProv.cancelRide(currentRide['id']);
                          if (context.mounted) Navigator.pop(context);
                        }
                      },
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppColors.danger,
                        side: const BorderSide(color: AppColors.danger),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        padding: const EdgeInsets.symmetric(vertical: 12),
                      ),
                      child: const Text('✕ Cancel Ride Request', style: TextStyle(fontWeight: FontWeight.bold)),
                    ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  String _statusDescription(String status, String? driverName) {
    final d = driverName ?? 'Driver';
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
}
