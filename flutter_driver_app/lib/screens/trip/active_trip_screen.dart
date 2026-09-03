import 'package:flutter/material.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/driver_provider.dart';

class ActiveTripScreen extends StatefulWidget {
  final Map<String, dynamic> ride;

  const ActiveTripScreen({super.key, required this.ride});

  @override
  State<ActiveTripScreen> createState() => _ActiveTripScreenState();
}

class _ActiveTripScreenState extends State<ActiveTripScreen> {
  late Map<String, dynamic> _ride;
  bool _isUpdating = false;

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

  Future<void> _callRider() async {
    final phone = _ride['rider']?['phone'] ?? _ride['passenger_phone'];
    if (phone != null && phone.isNotEmpty) {
      final uri = Uri.parse('tel:$phone');
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri);
      }
    }
  }

  Future<void> _advanceStatus(String newStatus) async {
    setState(() => _isUpdating = true);
    final driver = Provider.of<DriverProvider>(context, listen: false);
    final success = await driver.updateRideStatus(_ride['id'], newStatus);

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
          ),
        );
        Navigator.pop(context);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final status = _ride['status'] ?? 'accepted';
    final fare = _ride['fare'] != null ? double.tryParse(_ride['fare'].toString()) ?? 0.0 : 0.0;
    final riderName = _ride['rider']?['name'] ?? _ride['passenger_name'] ?? 'Passenger';

    final pickupLat = _ride['pickup_lat'] != null ? double.tryParse(_ride['pickup_lat'].toString()) : null;
    final pickupLng = _ride['pickup_lng'] != null ? double.tryParse(_ride['pickup_lng'].toString()) : null;

    final initialPos = LatLng(pickupLat ?? 28.6448, pickupLng ?? 77.2167);

    return Scaffold(
      backgroundColor: AppColors.backgroundDark,
      body: Stack(
        children: [
          // Google Map Background
          GoogleMap(
            initialCameraPosition: CameraPosition(
              target: initialPos,
              zoom: 14.0,
            ),
            myLocationEnabled: true,
            myLocationButtonEnabled: false,
            zoomControlsEnabled: false,
            markers: {
              if (pickupLat != null && pickupLng != null)
                Marker(
                  markerId: const MarkerId('pickup'),
                  position: initialPos,
                  infoWindow: InfoWindow(title: 'Pickup: ${_ride['pickup_location']}'),
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
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                      elevation: 6,
                    ),
                    icon: const Icon(Icons.navigation_rounded, size: 18),
                    label: const Text(
                      'Navigate in Maps',
                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                    ),
                  ),
                ],
              ),
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
                    color: Colors.black.withValues(alpha: 0.5),
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
                      // Rider Info Row
                      Row(
                        children: [
                          CircleAvatar(
                            radius: 24,
                            backgroundColor: AppColors.purple,
                            child: Text(
                              riderName.isNotEmpty ? riderName[0].toUpperCase() : 'R',
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 18),
                            ),
                          ),
                          const SizedBox(width: 14),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  riderName,
                                  style: const TextStyle(
                                    color: AppColors.textLight,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 16,
                                  ),
                                ),
                                Text(
                                  status.replaceAll('_', ' ').toUpperCase(),
                                  style: const TextStyle(
                                    color: AppColors.primary,
                                    fontWeight: FontWeight.w800,
                                    fontSize: 11,
                                    letterSpacing: 0.5,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          IconButton(
                            onPressed: _callRider,
                            style: IconButton.styleFrom(
                              backgroundColor: AppColors.success.withValues(alpha: 0.2),
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

    if (status == 'accepted') {
      return ElevatedButton.icon(
        onPressed: () => _advanceStatus('en_route'),
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.info,
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        ),
        icon: const Icon(Icons.directions_car_rounded),
        label: const Text('🚗 En Route to Pickup', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
      );
    } else if (status == 'en_route') {
      return ElevatedButton.icon(
        onPressed: () => _advanceStatus('arrived'),
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.warning,
          foregroundColor: Colors.black,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        ),
        icon: const Icon(Icons.location_on_rounded),
        label: const Text('📍 Arrived at Pickup', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
      );
    } else if (status == 'arrived') {
      return ElevatedButton.icon(
        onPressed: () => _advanceStatus('in_progress'),
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.success,
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        ),
        icon: const Icon(Icons.play_arrow_rounded),
        label: const Text('▶ Start Trip', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
      );
    } else if (status == 'in_progress') {
      return ElevatedButton.icon(
        onPressed: () => _advanceStatus('completed'),
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.success,
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        ),
        icon: const Icon(Icons.check_circle_rounded),
        label: const Text('✓ Complete Trip', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
      );
    }

    return const SizedBox.shrink();
  }
}
