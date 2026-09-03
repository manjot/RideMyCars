import 'dart:async';
import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../providers/notification_provider.dart';
import '../../providers/ride_provider.dart';
import '../../services/places_service.dart';
import '../auth/login_screen.dart';
import '../notifications/notifications_screen.dart';
import 'ride_tracking_screen.dart';
import 'widgets/floating_ride_widget.dart';

class RiderHomeScreen extends StatefulWidget {
  const RiderHomeScreen({super.key});

  @override
  State<RiderHomeScreen> createState() => _RiderHomeScreenState();
}

class _RiderHomeScreenState extends State<RiderHomeScreen> {
  GoogleMapController? _mapController;
  final _pickupController = TextEditingController();
  final _dropoffController = TextEditingController();

  double? _userLat;
  double? _userLng;
  double? _dropoffLat;
  double? _dropoffLng;

  List<PlacePrediction> _predictions = [];
  bool _isSearchingPlaces = false;
  bool _isSearchingPickup = true;
  Timer? _debounceTimer;
  Set<Marker> _markers = {};

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final rideProv = Provider.of<RideProvider>(context, listen: false);
      rideProv.startActiveRidePolling();
      Provider.of<NotificationProvider>(context, listen: false).startPolling();
      _getCurrentLocation();
    });
  }

  @override
  void dispose() {
    _debounceTimer?.cancel();
    _pickupController.dispose();
    _dropoffController.dispose();
    super.dispose();
  }

  Future<void> _getCurrentLocation() async {
    try {
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }

      if (permission == LocationPermission.always || permission == LocationPermission.whileInUse) {
        final pos = await Geolocator.getCurrentPosition();
        setState(() {
          _userLat = pos.latitude;
          _userLng = pos.longitude;
          if (_pickupController.text.isEmpty) {
            _pickupController.text = 'Current Location';
          }
          _updateMapMarkers();
        });

        _mapController?.animateCamera(
          CameraUpdate.newLatLng(LatLng(pos.latitude, pos.longitude)),
        );
      }
    } catch (_) {}
  }

  void _onQueryChanged(String query, {required bool isPickup}) {
    _debounceTimer?.cancel();
    _debounceTimer = Timer(const Duration(milliseconds: 300), () async {
      if (query.trim().isEmpty) {
        setState(() {
          _predictions = [];
          _isSearchingPlaces = false;
        });
        return;
      }

      setState(() {
        _isSearchingPlaces = true;
        _isSearchingPickup = isPickup;
      });

      final results = await PlacesService.getAutocomplete(
        query,
        lat: _userLat,
        lng: _userLng,
      );

      if (mounted) {
        setState(() {
          _predictions = results;
          _isSearchingPlaces = false;
        });
      }
    });
  }

  Future<void> _selectPlace(PlacePrediction prediction) async {
    final details = await PlacesService.getPlaceDetails(prediction.placeId);

    setState(() {
      if (_isSearchingPickup) {
        _pickupController.text = prediction.description;
        if (details != null) {
          _userLat = details.lat;
          _userLng = details.lng;
        }
      } else {
        _dropoffController.text = prediction.description;
        if (details != null) {
          _dropoffLat = details.lat;
          _dropoffLng = details.lng;
        }
      }
      _predictions = [];
      _updateMapMarkers();
    });

    FocusScope.of(context).unfocus();
  }

  void _updateMapMarkers() {
    final markers = <Marker>{};
    if (_userLat != null && _userLng != null) {
      markers.add(
        Marker(
          markerId: const MarkerId('pickup'),
          position: LatLng(_userLat!, _userLng!),
          infoWindow: InfoWindow(title: 'Pickup', snippet: _pickupController.text),
          icon: BitmapDescriptor.defaultMarkerWithHue(BitmapDescriptor.hueGreen),
        ),
      );
    }
    if (_dropoffLat != null && _dropoffLng != null) {
      markers.add(
        Marker(
          markerId: const MarkerId('dropoff'),
          position: LatLng(_dropoffLat!, _dropoffLng!),
          infoWindow: InfoWindow(title: 'Destination', snippet: _dropoffController.text),
          icon: BitmapDescriptor.defaultMarkerWithHue(BitmapDescriptor.hueRed),
        ),
      );
    }
    setState(() {
      _markers = markers;
    });

    if (_userLat != null && _userLng != null) {
      if (_dropoffLat != null && _dropoffLng != null) {
        final bounds = LatLngBounds(
          southwest: LatLng(
            _userLat! < _dropoffLat! ? _userLat! : _dropoffLat!,
            _userLng! < _dropoffLng! ? _userLng! : _dropoffLng!,
          ),
          northeast: LatLng(
            _userLat! > _dropoffLat! ? _userLat! : _dropoffLat!,
            _userLng! > _dropoffLng! ? _userLng! : _dropoffLng!,
          ),
        );
        _mapController?.animateCamera(CameraUpdate.newLatLngBounds(bounds, 60));
      } else {
        _mapController?.animateCamera(CameraUpdate.newLatLng(LatLng(_userLat!, _userLng!)));
      }
    }
  }

  Future<void> _handleBookRide() async {
    if (_pickupController.text.isEmpty || _dropoffController.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter both pickup and destination.')),
      );
      return;
    }

    final rideProv = Provider.of<RideProvider>(context, listen: false);
    final success = await rideProv.bookRide(
      pickupLocation: _pickupController.text,
      dropoffLocation: _dropoffController.text,
      pickupLat: _userLat,
      pickupLng: _userLng,
      dropoffLat: _dropoffLat,
      dropoffLng: _dropoffLng,
      distanceKm: 15.0,
      durationMinutes: 25,
    );

    if (!mounted) return;

    if (success && rideProv.activeRide != null) {
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => RideTrackingScreen(ride: rideProv.activeRide!),
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(rideProv.errorMessage ?? 'Failed to request ride. Please try again.'),
          backgroundColor: AppColors.danger,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final rideProv = Provider.of<RideProvider>(context);
    final notifs = Provider.of<NotificationProvider>(context);

    final initialCenter = LatLng(_userLat ?? 28.6448, _userLng ?? 77.2167);

    return Scaffold(
      backgroundColor: AppColors.backgroundDark,
      drawer: _buildDrawer(context, auth),
      body: Stack(
        children: [
          // Google Map Background
          GoogleMap(
            initialCameraPosition: CameraPosition(target: initialCenter, zoom: 14.0),
            markers: _markers,
            myLocationEnabled: true,
            myLocationButtonEnabled: false,
            zoomControlsEnabled: false,
            onMapCreated: (c) => _mapController = c,
          ),

          // Top App Bar Floating Header
          SafeArea(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
              child: Row(
                children: [
                  Builder(
                    builder: (ctx) => CircleAvatar(
                      backgroundColor: AppColors.surfaceDark,
                      child: IconButton(
                        icon: const Icon(Icons.menu_rounded, color: AppColors.textLight),
                        onPressed: () => Scaffold.of(ctx).openDrawer(),
                      ),
                    ),
                  ),
                  const Spacer(),
                  // Notification Bell with Badge
                  Stack(
                    alignment: Alignment.center,
                    children: [
                      CircleAvatar(
                        backgroundColor: AppColors.surfaceDark,
                        child: IconButton(
                          icon: const Icon(Icons.notifications_none_rounded, color: AppColors.textLight),
                          onPressed: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(builder: (_) => const NotificationsScreen()),
                            );
                          },
                        ),
                      ),
                      if (notifs.unreadCount > 0)
                        Positioned(
                          top: 2,
                          right: 2,
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
                                fontSize: 9,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ),
                    ],
                  ),
                ],
              ),
            ),
          ),

          // Booking Card & Live Autocomplete Suggestions Bottom Sheet
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
                    blurRadius: 28,
                    offset: const Offset(0, -8),
                  ),
                ],
              ),
              child: SafeArea(
                top: false,
                bottom: true,
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(20, 16, 20, 16),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // Inputs
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                        decoration: BoxDecoration(
                          color: AppColors.backgroundDark,
                          borderRadius: BorderRadius.circular(18),
                        ),
                        child: Column(
                          children: [
                            // Pickup Field
                            TextField(
                              controller: _pickupController,
                              onChanged: (val) => _onQueryChanged(val, isPickup: true),
                              style: const TextStyle(color: AppColors.textLight, fontSize: 13),
                              decoration: InputDecoration(
                                prefixIcon: const Icon(Icons.circle, color: AppColors.success, size: 14),
                                hintText: 'Pickup location (e.g. Karol Bagh)',
                                hintStyle: const TextStyle(color: AppColors.textMuted, fontSize: 13),
                                border: InputBorder.none,
                                suffixIcon: _pickupController.text.isNotEmpty
                                    ? IconButton(
                                        icon: const Icon(Icons.close_rounded, color: AppColors.textMuted, size: 16),
                                        onPressed: () {
                                          _pickupController.clear();
                                          setState(() => _predictions = []);
                                        },
                                      )
                                    : null,
                              ),
                            ),
                            const Divider(color: Colors.white10, height: 1),
                            // Dropoff Field
                            TextField(
                              controller: _dropoffController,
                              onChanged: (val) => _onQueryChanged(val, isPickup: false),
                              style: const TextStyle(color: AppColors.textLight, fontSize: 13),
                              decoration: InputDecoration(
                                prefixIcon: const Icon(Icons.location_on_rounded, color: AppColors.danger, size: 16),
                                hintText: 'Where to? (e.g. Airport, Market)',
                                hintStyle: const TextStyle(color: AppColors.textMuted, fontSize: 13),
                                border: InputBorder.none,
                                suffixIcon: _dropoffController.text.isNotEmpty
                                    ? IconButton(
                                        icon: const Icon(Icons.close_rounded, color: AppColors.textMuted, size: 16),
                                        onPressed: () {
                                          _dropoffController.clear();
                                          setState(() => _predictions = []);
                                        },
                                      )
                                    : null,
                              ),
                            ),
                          ],
                        ),
                      ),

                      // Google Places Autocomplete Suggestions List
                      if (_isSearchingPlaces || _predictions.isNotEmpty)
                        Container(
                          margin: const EdgeInsets.only(top: 8),
                          constraints: const BoxConstraints(maxHeight: 220),
                          decoration: BoxDecoration(
                            color: AppColors.backgroundDark,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: AppColors.primary.withValues(alpha: 0.3)),
                          ),
                          child: _isSearchingPlaces
                              ? const Padding(
                                  padding: EdgeInsets.all(16.0),
                                  child: Center(
                                    child: SizedBox(
                                      width: 22,
                                      height: 22,
                                      child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.primary),
                                    ),
                                  ),
                                )
                              : ListView.separated(
                                  shrinkWrap: true,
                                  padding: const EdgeInsets.symmetric(vertical: 4),
                                  itemCount: _predictions.length,
                                  separatorBuilder: (_, __) => const Divider(color: Colors.white10, height: 1),
                                  itemBuilder: (context, index) {
                                    final p = _predictions[index];
                                    return ListTile(
                                      dense: true,
                                      leading: const Icon(Icons.location_on_outlined, color: AppColors.primary, size: 20),
                                      title: Text(
                                        p.mainText,
                                        style: const TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 13),
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                      subtitle: p.secondaryText.isNotEmpty
                                          ? Text(
                                              p.secondaryText,
                                              style: const TextStyle(color: AppColors.textMuted, fontSize: 11),
                                              maxLines: 1,
                                              overflow: TextOverflow.ellipsis,
                                            )
                                          : null,
                                      onTap: () => _selectPlace(p),
                                    );
                                  },
                                ),
                        ),

                      const SizedBox(height: 14),

                      // Vehicle Selector
                      Row(
                        children: [
                          _buildVehicleOption('Standard', 'Sedan', '4 min', '\$35.00', rideProv),
                          const SizedBox(width: 8),
                          _buildVehicleOption('Executive', 'SUV', '6 min', '\$55.00', rideProv),
                          const SizedBox(width: 8),
                          _buildVehicleOption('Luxury', 'VIP', '10 min', '\$95.00', rideProv),
                        ],
                      ),
                      const SizedBox(height: 16),

                      // Request Ride Button
                      SizedBox(
                        height: 52,
                        child: ElevatedButton(
                          onPressed: rideProv.isBooking ? null : _handleBookRide,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.primary,
                            foregroundColor: AppColors.backgroundDark,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(16),
                            ),
                            elevation: 4,
                          ),
                          child: rideProv.isBooking
                              ? const SizedBox(
                                  width: 22,
                                  height: 22,
                                  child: CircularProgressIndicator(strokeWidth: 2.5, color: AppColors.backgroundDark),
                                )
                              : const Text(
                                  'Request Ride Now →',
                                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
                                ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),

          // Uber-Style Floating Trip Widget (Shows when an active ride exists!)
          if (rideProv.activeRide != null)
            FloatingRideWidget(
              ride: rideProv.activeRide!,
              onTap: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => RideTrackingScreen(ride: rideProv.activeRide!),
                  ),
                );
              },
            ),
        ],
      ),
    );
  }

  Widget _buildVehicleOption(String id, String label, String eta, String price, RideProvider rideProv) {
    final isSelected = rideProv.selectedVehicle == id;
    return Expanded(
      child: GestureDetector(
        onTap: () => rideProv.setSelectedVehicle(id),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
          decoration: BoxDecoration(
            color: isSelected ? AppColors.primary.withOpacity(0.15) : AppColors.backgroundDark,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(
              color: isSelected ? AppColors.primary : Colors.transparent,
              width: 1.5,
            ),
          ),
          child: Column(
            children: [
              Icon(
                id == 'Executive' ? Icons.directions_bus_rounded : Icons.directions_car_rounded,
                color: isSelected ? AppColors.primary : AppColors.textMuted,
                size: 24,
              ),
              const SizedBox(height: 4),
              Text(
                label,
                style: TextStyle(
                  color: isSelected ? AppColors.textLight : AppColors.textMuted,
                  fontWeight: FontWeight.bold,
                  fontSize: 12,
                ),
              ),
              Text(
                price,
                style: const TextStyle(
                  color: AppColors.success,
                  fontWeight: FontWeight.w900,
                  fontSize: 12,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDrawer(BuildContext context, AuthProvider auth) {
    return Drawer(
      backgroundColor: AppColors.surfaceDark,
      child: SafeArea(
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.all(24.0),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 28,
                    backgroundColor: AppColors.info,
                    child: Text(
                      (auth.userName ?? 'R')[0].toUpperCase(),
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 22),
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          auth.userName ?? 'Rider',
                          style: const TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 16),
                        ),
                        Text(
                          auth.userEmail ?? '',
                          style: const TextStyle(color: AppColors.textMuted, fontSize: 12),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const Divider(color: Colors.white10),
            ListTile(
              leading: const Icon(Icons.notifications_rounded, color: AppColors.info),
              title: const Text('Notifications', style: TextStyle(color: AppColors.textLight)),
              onTap: () {
                Navigator.pop(context);
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const NotificationsScreen()),
                );
              },
            ),
            const Spacer(),
            const Divider(color: Colors.white10),
            ListTile(
              leading: const Icon(Icons.logout_rounded, color: AppColors.danger),
              title: const Text('Log Out', style: TextStyle(color: AppColors.danger, fontWeight: FontWeight.bold)),
              onTap: () async {
                await auth.logout();
                if (context.mounted) {
                  Navigator.pushAndRemoveUntil(
                    context,
                    MaterialPageRoute(builder: (_) => const LoginScreen()),
                    (route) => false,
                  );
                }
              },
            ),
            const SizedBox(height: 12),
          ],
        ),
      ),
    );
  }
}
