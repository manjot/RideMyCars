import 'dart:async';
import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../models/driver_model.dart';
import '../../models/vehicle_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/country_provider.dart';
import '../../providers/notification_provider.dart';
import '../../providers/ride_provider.dart';
import '../../services/driver_service.dart';
import '../../services/places_service.dart';
import '../../services/rental_service.dart';
import '../account/manage_account_screen.dart';
import '../auth/login_screen.dart';
import '../delivery/delivery_booking_screen.dart';
import '../driver/driver_detail_screen.dart';
import '../notifications/notifications_screen.dart';
import '../rent/rental_detail_screen.dart';
import '../rides/my_rides_screen.dart';
import '../support/help_support_screen.dart';
import '../wallet/wallet_screen.dart';
import 'ride_tracking_screen.dart';
import 'widgets/floating_ride_widget.dart';

enum ServiceType {
  ride,
  rent,
  driver,
  deliver,
}

class RiderHomeScreen extends StatefulWidget {
  const RiderHomeScreen({super.key});

  @override
  State<RiderHomeScreen> createState() => _RiderHomeScreenState();
}

class _RiderHomeScreenState extends State<RiderHomeScreen> {
  GoogleMapController? _mapController;
  final _pickupController = TextEditingController();
  final _dropoffController = TextEditingController();

  ServiceType _selectedService = ServiceType.ride;
  String _selectedTierId = 'Standard';

  // Rental Vehicles API Data
  List<VehicleModel> _rentalVehicles = [];
  VehicleModel? _selectedRentalVehicle;
  bool _isLoadingVehicles = false;
  String _selectedRentalCategory = 'All';

  // Registered Drivers API Data
  List<DriverModel> _drivers = [];
  DriverModel? _selectedDriver;
  bool _isLoadingDrivers = false;
  String _selectedDriverFilter = 'All';

  double? _userLat;
  double? _userLng;
  double? _dropoffLat;
  double? _dropoffLng;

  List<PlacePrediction> _predictions = [];
  bool _isSearchingPlaces = false;
  bool _isSearchingPickup = true;
  Timer? _debounceTimer;
  Set<Marker> _markers = {};
  Set<Polyline> _polylines = {};

  final List<String> _rentalCategories = ['All', 'Economy', 'Compact', 'Sedan', 'SUV', 'Luxury', 'Van'];
  final List<String> _driverFilters = ['All', 'Top Rated', 'City Duty', 'Executive'];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final rideProv = Provider.of<RideProvider>(context, listen: false);
      rideProv.startActiveRidePolling();
      Provider.of<NotificationProvider>(context, listen: false).startPolling();
      _getCurrentLocation();
      _fetchRentalVehicles();
      _fetchDrivers();
    });
  }

  @override
  void dispose() {
    _debounceTimer?.cancel();
    _pickupController.dispose();
    _dropoffController.dispose();
    super.dispose();
  }

  Future<void> _fetchRentalVehicles({String? category, String? country}) async {
    setState(() => _isLoadingVehicles = true);
    final countryProv = Provider.of<CountryProvider>(context, listen: false);
    final vehicles = await RentalService.getAvailableVehicles(
      category: category ?? (_selectedRentalCategory == 'All' ? null : _selectedRentalCategory),
      country: country ?? countryProv.selectedCountryCode,
    );
    if (mounted) {
      setState(() {
        _rentalVehicles = vehicles;
        if (_rentalVehicles.isNotEmpty) {
          _selectedRentalVehicle = _rentalVehicles.first;
        }
        _isLoadingVehicles = false;
      });
    }
  }

  Future<void> _fetchDrivers({String? filter, String? country}) async {
    setState(() => _isLoadingDrivers = true);
    final countryProv = Provider.of<CountryProvider>(context, listen: false);
    double? minRating;
    if (filter == 'Top Rated') minRating = 4.8;
    final list = await DriverService.getDrivers(
      minRating: minRating,
      country: country ?? countryProv.selectedCountryCode,
    );
    if (mounted) {
      setState(() {
        _drivers = list;
        if (_drivers.isNotEmpty) {
          _selectedDriver = _drivers.first;
        }
        _isLoadingDrivers = false;
      });
    }
  }

  Color get _serviceColor {
    switch (_selectedService) {
      case ServiceType.ride:
        return AppColors.primary;
      case ServiceType.rent:
        return const Color(0xFF3B82F6); // Royal Blue
      case ServiceType.driver:
        return const Color(0xFF10B981); // Emerald Green
      case ServiceType.deliver:
        return const Color(0xFFA855F7); // Purple Violet
    }
  }

  String get _serviceBadge {
    switch (_selectedService) {
      case ServiceType.ride:
        return '⚡ INSTANT ON-DEMAND';
      case ServiceType.rent:
        return '🔑 LUXURY FLEET';
      case ServiceType.driver:
        return '🛡️ VETTED CHAUFFEUR';
      case ServiceType.deliver:
        return '📦 EXPRESS COURIER';
    }
  }

  String get _pickupHint {
    switch (_selectedService) {
      case ServiceType.ride:
        return 'Pickup location (e.g. Karol Bagh)';
      case ServiceType.rent:
        return 'Pick-up location / hub (e.g. Airport, T3)';
      case ServiceType.driver:
        return 'Reporting location (e.g. Home / Office)';
      case ServiceType.deliver:
        return 'Sender address (Pickup parcel)';
    }
  }

  String get _dropoffHint {
    switch (_selectedService) {
      case ServiceType.ride:
        return 'Where to? (e.g. Airport, Market)';
      case ServiceType.rent:
        return 'Rental duration (e.g. 1 Day, 3 Days, 1 Week)';
      case ServiceType.driver:
        return 'Duty hours (e.g. 4 Hours, 8 Hours, Outstation)';
      case ServiceType.deliver:
        return 'Recipient address (Dropoff parcel)';
    }
  }

  String get _ctaButtonText {
    switch (_selectedService) {
      case ServiceType.ride:
        return 'Request Ride Now →';
      case ServiceType.rent:
        return _selectedRentalVehicle != null
            ? 'Rent ${_selectedRentalVehicle!.fullName} • ${_selectedRentalVehicle!.currencySymbol}${_selectedRentalVehicle!.dailyRate.toStringAsFixed(0)}/day →'
            : 'Search & Rent Car →';
      case ServiceType.driver:
        return _selectedDriver != null
            ? 'Hire ${_selectedDriver!.name} • ${_selectedDriver!.currencySymbol}${_selectedDriver!.hourlyRate.toStringAsFixed(0)}/hr →'
            : 'Hire Chauffeur Now →';
      case ServiceType.deliver:
        return 'Send Delivery Now →';
    }
  }

  List<Map<String, dynamic>> get _rideTierOptions {
    final countryProv = Provider.of<CountryProvider>(context, listen: false);
    return [
      {'id': 'Standard', 'label': 'Sedan', 'eta': '4 min', 'price': countryProv.formatAmount(35.0), 'icon': Icons.directions_car_rounded},
      {'id': 'Executive', 'label': 'SUV', 'eta': '6 min', 'price': countryProv.formatAmount(55.0), 'icon': Icons.directions_bus_rounded},
      {'id': 'Luxury', 'label': 'VIP', 'eta': '10 min', 'price': countryProv.formatAmount(95.0), 'icon': Icons.local_taxi_rounded},
    ];
  }

  List<Map<String, dynamic>> get _deliverTierOptions {
    final countryProv = Provider.of<CountryProvider>(context, listen: false);
    return [
      {'id': 'Hyperlocal', 'label': 'Hyperlocal', 'eta': '< 5 kg', 'price': countryProv.formatAmount(15.0), 'icon': Icons.two_wheeler_rounded},
      {'id': 'Same Day', 'label': 'Same Day', 'eta': 'Today', 'price': countryProv.formatAmount(19.0), 'icon': Icons.local_shipping_rounded},
      {'id': 'Instant', 'label': 'Instant', 'eta': '< 30 min', 'price': countryProv.formatAmount(25.0), 'icon': Icons.electric_bolt_rounded},
    ];
  }

  void _onServiceTabChanged(ServiceType service) {
    setState(() {
      _selectedService = service;
      if (service == ServiceType.ride) {
        _selectedTierId = 'Standard';
      } else if (service == ServiceType.deliver) {
        _selectedTierId = 'Hyperlocal';
      }
    });

    if (service == ServiceType.rent && _rentalVehicles.isEmpty) {
      _fetchRentalVehicles();
    } else if (service == ServiceType.driver && _drivers.isEmpty) {
      _fetchDrivers();
    }
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
            _pickupController.text = 'Locating pickup address...';
          }
        });
        _updateMapMarkers();

        final address = await PlacesService.getAddressFromCoordinates(pos.latitude, pos.longitude);
        if (mounted) {
          setState(() {
            if (address != null && address.isNotEmpty) {
              _pickupController.text = address;
            } else if (_pickupController.text == 'Locating pickup address...') {
              _pickupController.text = 'Current Location';
            }
          });
          _updateMapMarkers();
        }
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
        _pickupController.text = prediction.mainText.isNotEmpty ? prediction.mainText : prediction.description;
        if (details != null) {
          _userLat = details.lat;
          _userLng = details.lng;
        }
      } else {
        _dropoffController.text = prediction.mainText.isNotEmpty ? prediction.mainText : prediction.description;
        if (details != null) {
          _dropoffLat = details.lat;
          _dropoffLng = details.lng;
        }
      }
      _predictions = [];
    });

    _updateMapMarkers();
    if (!mounted) return;
    FocusScope.of(context).unfocus();
  }

  void _updateMapMarkers() {
    final markers = <Marker>{};
    final polylines = <Polyline>{};

    if (_userLat != null && _userLng != null) {
      markers.add(
        Marker(
          markerId: const MarkerId('pickup'),
          position: LatLng(_userLat!, _userLng!),
          infoWindow: InfoWindow(title: 'Pickup / Location', snippet: _pickupController.text),
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

    if (_userLat != null && _userLng != null && _dropoffLat != null && _dropoffLng != null) {
      polylines.add(
        Polyline(
          polylineId: const PolylineId('route_preview'),
          color: _serviceColor,
          width: 5,
          points: [
            LatLng(_userLat!, _userLng!),
            LatLng(_dropoffLat!, _dropoffLng!),
          ],
        ),
      );
    }

    setState(() {
      _markers = markers;
      _polylines = polylines;
    });

    if (_userLat != null && _userLng != null) {
      if (_dropoffLat != null && _dropoffLng != null) {
        double south = _userLat! < _dropoffLat! ? _userLat! : _dropoffLat!;
        double north = _userLat! > _dropoffLat! ? _userLat! : _dropoffLat!;
        double west = _userLng! < _dropoffLng! ? _userLng! : _dropoffLng!;
        double east = _userLng! > _dropoffLng! ? _userLng! : _dropoffLng!;

        if (north - south < 0.01) {
          north += 0.005;
          south -= 0.005;
        }
        if (east - west < 0.01) {
          east += 0.005;
          west -= 0.005;
        }

        final bounds = LatLngBounds(
          southwest: LatLng(south, west),
          northeast: LatLng(north, east),
        );

        Future.delayed(const Duration(milliseconds: 100), () {
          _mapController?.animateCamera(
            CameraUpdate.newLatLngBounds(bounds, 50),
          );
        });
      } else {
        _mapController?.animateCamera(CameraUpdate.newLatLng(LatLng(_userLat!, _userLng!)));
      }
    }
  }

  Future<void> _handleBookService() async {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    if (!auth.isAuthenticated || auth.token == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Please log in to confirm your ${_selectedService.name.toUpperCase()} request.'),
          backgroundColor: _serviceColor,
        ),
      );
      Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => const LoginScreen()),
      );
      return;
    }

    String pickup = _pickupController.text.trim();
    String dropoff = _dropoffController.text.trim();

    if (_selectedService == ServiceType.rent) {
      if (_selectedRentalVehicle != null) {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => RentalDetailScreen(
              vehicle: _selectedRentalVehicle!,
              initialPickupLocation: pickup.isNotEmpty ? pickup : null,
              initialDropoffLocation: dropoff.isNotEmpty ? dropoff : null,
            ),
          ),
        );
        return;
      }
    } else if (_selectedService == ServiceType.driver) {
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => DriverDetailScreen(
            driver: _selectedDriver,
            initialPickupLocation: pickup.isNotEmpty ? pickup : null,
            initialDropoffLocation: dropoff.isNotEmpty ? dropoff : null,
          ),
        ),
      );
    } else if (_selectedService == ServiceType.deliver) {
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => DeliveryBookingScreen(
            initialPickup: pickup.isNotEmpty ? pickup : null,
            initialDropoff: dropoff.isNotEmpty ? dropoff : null,
            initialTier: _selectedTierId,
          ),
        ),
      );
      return;
    }

    if (pickup.isEmpty || dropoff.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Please fill in required fields for ${_selectedService.name.toUpperCase()}.'),
          backgroundColor: AppColors.warning,
        ),
      );
      return;
    }

    final rideProv = Provider.of<RideProvider>(context, listen: false);

    String vehicleTypeTag;
    if (_selectedService == ServiceType.rent && _selectedRentalVehicle != null) {
      vehicleTypeTag = 'RENTAL_${_selectedRentalVehicle!.id}_${_selectedRentalVehicle!.fullName}';
    } else if (_selectedService == ServiceType.driver && _selectedDriver != null) {
      vehicleTypeTag = 'CHAUFFEUR_${_selectedDriver!.id}_${_selectedDriver!.name}';
    } else if (_selectedService == ServiceType.deliver) {
      vehicleTypeTag = 'DELIVERY_$_selectedTierId';
    } else {
      vehicleTypeTag = _selectedTierId;
    }

    rideProv.setSelectedVehicle(vehicleTypeTag);

    final success = await rideProv.bookRide(
      pickupLocation: pickup,
      dropoffLocation: dropoff,
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
      if (rideProv.errorMessage?.contains('Unauthenticated') == true ||
          rideProv.errorMessage?.contains('sign in') == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Session expired. Please sign in to request a service.'),
            backgroundColor: AppColors.warning,
          ),
        );
        Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const LoginScreen()),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(rideProv.errorMessage ?? 'Failed to submit request. Please try again.'),
            backgroundColor: AppColors.danger,
          ),
        );
      }
    }
  }

  void _showCountrySelector(CountryProvider countryProv) {
    showModalBottomSheet(
      context: context,
      backgroundColor: const Color(0xFF0F172A),
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => SafeArea(
        top: false,
        bottom: true,
        child: Padding(
          padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).padding.bottom),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const SizedBox(height: 12),
              Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.white24,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              Padding(
                padding: const EdgeInsets.fromLTRB(20, 16, 20, 10),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Select Currency & Country',
                      style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close, color: Colors.white60, size: 20),
                      onPressed: () => Navigator.pop(ctx),
                    ),
                  ],
                ),
              ),
              Flexible(
                child: ListView.builder(
                  shrinkWrap: true,
                  itemCount: CountryProvider.supportedCountries.length,
                  itemBuilder: (_, idx) {
                    final item = CountryProvider.supportedCountries[idx];
                    final isSelected = item.code == countryProv.selectedCountryCode;
                    return ListTile(
                      leading: Text(item.flag, style: const TextStyle(fontSize: 24)),
                      title: Text(item.name, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13.5)),
                      subtitle: Text('${item.currency} (${item.symbol})', style: const TextStyle(color: Colors.white60, fontSize: 12)),
                      trailing: isSelected
                          ? const Icon(Icons.check_circle_rounded, color: Color(0xFF3B82F6), size: 22)
                          : null,
                      onTap: () {
                        countryProv.setCountry(item.code);
                        _fetchRentalVehicles(country: item.code);
                        _fetchDrivers(country: item.code);
                        Navigator.pop(ctx);
                      },
                    );
                  },
                ),
              ),
              const SizedBox(height: 16),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final rideProv = Provider.of<RideProvider>(context);
    final notifs = Provider.of<NotificationProvider>(context);
    final countryProv = Provider.of<CountryProvider>(context);

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
            polylines: _polylines,
            padding: const EdgeInsets.only(top: 80, bottom: 360),
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
                    builder: (btnCtx) => GestureDetector(
                      onTap: () => Scaffold.of(btnCtx).openDrawer(),
                      child: Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: AppColors.surfaceDark,
                          shape: BoxShape.circle,
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(0.35),
                              blurRadius: 10,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: const Icon(Icons.menu_rounded, color: AppColors.textLight, size: 22),
                      ),
                    ),
                  ),
                  const Spacer(),
                  // Country & Currency Selector Pill (Matches Web Header 🌐 USA $)
                  GestureDetector(
                    onTap: () => _showCountrySelector(countryProv),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
                      decoration: BoxDecoration(
                        color: AppColors.surfaceDark,
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: _serviceColor.withOpacity(0.5)),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.35),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            '${countryProv.flag} ${countryProv.selectedCountryCode} ${countryProv.currencySymbol}',
                            style: const TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(width: 2),
                          Icon(Icons.arrow_drop_down_rounded, color: _serviceColor, size: 18),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  // Notification Button
                  GestureDetector(
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const NotificationsScreen()),
                      );
                    },
                    child: Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: AppColors.surfaceDark,
                        shape: BoxShape.circle,
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.35),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: Stack(
                        clipBehavior: Clip.none,
                        children: [
                          const Icon(Icons.notifications_rounded, color: AppColors.textLight, size: 22),
                          if (notifs.unreadCount > 0)
                            Positioned(
                              top: -2,
                              right: -2,
                              child: Container(
                                padding: const EdgeInsets.all(4),
                                decoration: const BoxDecoration(
                                  color: AppColors.danger,
                                  shape: BoxShape.circle,
                                ),
                                child: Text(
                                  notifs.unreadCount > 9 ? '9+' : notifs.unreadCount.toString(),
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
                    ),
                  ),
                  const SizedBox(width: 10),
                  // User Profile Avatar
                  Builder(
                    builder: (btnCtx) => GestureDetector(
                      onTap: () => Scaffold.of(btnCtx).openDrawer(),
                      child: Container(
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          border: Border.all(color: _serviceColor, width: 2),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(0.35),
                              blurRadius: 10,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: CircleAvatar(
                          radius: 19,
                          backgroundColor: _serviceColor,
                          backgroundImage: (auth.avatarUrl != null && auth.avatarUrl!.isNotEmpty)
                              ? NetworkImage(auth.avatarUrl!)
                              : null,
                          child: (auth.avatarUrl == null || auth.avatarUrl!.isEmpty)
                              ? Text(
                                  (auth.userName ?? 'U').trim().isNotEmpty
                                      ? (auth.userName ?? 'U').trim()[0].toUpperCase()
                                      : 'U',
                                  style: const TextStyle(
                                    color: AppColors.backgroundDark,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 16,
                                  ),
                                )
                              : null,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),

          // Main Service Interactive Sheet + Bottom Tab Bar
          if (rideProv.activeRide == null)
            Positioned(
              left: 0,
              right: 0,
              bottom: 0,
              child: Container(
                decoration: BoxDecoration(
                  color: AppColors.surfaceDark,
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.55),
                      blurRadius: 28,
                      offset: const Offset(0, -8),
                    ),
                  ],
                ),
                child: SafeArea(
                  top: false,
                  bottom: true,
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      // Header Badge Chip & Assurance
                      Padding(
                        padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3.5),
                              decoration: BoxDecoration(
                                color: _serviceColor.withOpacity(0.16),
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(color: _serviceColor.withOpacity(0.4), width: 1),
                              ),
                              child: Text(
                                _serviceBadge,
                                style: TextStyle(
                                  color: _serviceColor,
                                  fontWeight: FontWeight.w900,
                                  fontSize: 10,
                                  letterSpacing: 0.5,
                                ),
                              ),
                            ),
                            Row(
                              children: [
                                const Icon(Icons.verified_user_rounded, color: AppColors.success, size: 14),
                                const SizedBox(width: 4),
                                Text(
                                  'Guaranteed & Insured',
                                  style: TextStyle(
                                    color: Colors.white.withOpacity(0.65),
                                    fontSize: 11,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),

                      // Mode-Specific Body (Rent Catalog, Driver Catalog, Ride, Deliver)
                      if (_selectedService == ServiceType.rent)
                        _buildRentSection()
                      else if (_selectedService == ServiceType.driver)
                        _buildDriverSection()
                      else
                        _buildStandardRideOrDeliverSection(),

                      // CTA Action Button
                      Padding(
                        padding: const EdgeInsets.fromLTRB(16, 4, 16, 8),
                        child: SizedBox(
                          width: double.infinity,
                          height: 48,
                          child: ElevatedButton(
                            onPressed: rideProv.isBooking ? null : _handleBookService,
                            style: ElevatedButton.styleFrom(
                              backgroundColor: _serviceColor,
                              foregroundColor: AppColors.backgroundDark,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(14),
                              ),
                              elevation: 4,
                            ),
                            child: rideProv.isBooking
                                ? const SizedBox(
                                    width: 20,
                                    height: 20,
                                    child: CircularProgressIndicator(strokeWidth: 2.5, color: AppColors.backgroundDark),
                                  )
                                : Text(
                                    _ctaButtonText,
                                    style: const TextStyle(fontSize: 14.5, fontWeight: FontWeight.w900),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                          ),
                        ),
                      ),

                      // Bottom Navigation Tab Bar (Ride, Rent, Driver, Deliver)
                      Container(
                        decoration: BoxDecoration(
                          color: const Color(0xFF0F172A),
                          border: Border(top: BorderSide(color: Colors.white.withOpacity(0.08), width: 1)),
                        ),
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceAround,
                          children: [
                            _buildBottomTabItem(
                              type: ServiceType.ride,
                              label: 'Ride',
                              icon: Icons.directions_car_rounded,
                              activeColor: AppColors.primary,
                            ),
                            _buildBottomTabItem(
                              type: ServiceType.rent,
                              label: 'Rent',
                              icon: Icons.vpn_key_rounded,
                              activeColor: const Color(0xFF3B82F6),
                            ),
                            _buildBottomTabItem(
                              type: ServiceType.driver,
                              label: 'Driver',
                              icon: Icons.person_pin_circle_rounded,
                              activeColor: const Color(0xFF10B981),
                            ),
                            _buildBottomTabItem(
                              type: ServiceType.deliver,
                              label: 'Deliver',
                              icon: Icons.inventory_2_rounded,
                              activeColor: const Color(0xFFA855F7),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),

          // Uber-Style Floating Trip Widget (Shows when trip is active)
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

  // --- RENT SECTION: Live Vehicles Catalog from Backend API ---
  Widget _buildRentSection() {
    return Column(
      mainAxisSize: MainAxisSize.min,
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Category Pills (All, Economy, Compact, Sedan, SUV, Luxury, Van)
        SizedBox(
          height: 34,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 14),
            itemCount: _rentalCategories.length,
            itemBuilder: (context, index) {
              final cat = _rentalCategories[index];
              final isSelected = _selectedRentalCategory == cat;
              return Padding(
                padding: const EdgeInsets.only(right: 6),
                child: GestureDetector(
                  onTap: () {
                    setState(() => _selectedRentalCategory = cat);
                    _fetchRentalVehicles(category: cat == 'All' ? null : cat);
                  },
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: isSelected ? const Color(0xFF3B82F6) : AppColors.backgroundDark,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(
                        color: isSelected ? const Color(0xFF3B82F6) : Colors.white.withOpacity(0.08),
                      ),
                    ),
                    child: Center(
                      child: Text(
                        cat,
                        style: TextStyle(
                          color: isSelected ? Colors.white : AppColors.textMuted,
                          fontSize: 11.5,
                          fontWeight: isSelected ? FontWeight.w900 : FontWeight.w600,
                        ),
                      ),
                    ),
                  ),
                ),
              );
            },
          ),
        ),

        const SizedBox(height: 6),

        // Live Vehicles Carousel from API
        SizedBox(
          height: 156,
          child: _isLoadingVehicles
              ? const Center(
                  child: SizedBox(
                    width: 26,
                    height: 26,
                    child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF3B82F6)),
                  ),
                )
              : _rentalVehicles.isEmpty
                  ? Center(
                      child: Text(
                        'No rental vehicles found in this category.',
                        style: TextStyle(color: AppColors.textMuted, fontSize: 12),
                      ),
                    )
                  : ListView.builder(
                      scrollDirection: Axis.horizontal,
                      padding: const EdgeInsets.symmetric(horizontal: 14),
                      itemCount: _rentalVehicles.length,
                      itemBuilder: (context, index) {
                        final v = _rentalVehicles[index];
                        final isSelected = _selectedRentalVehicle?.id == v.id;
                        return GestureDetector(
                          onTap: () {
                            setState(() => _selectedRentalVehicle = v);
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => RentalDetailScreen(
                                  vehicle: v,
                                  initialPickupLocation: _pickupController.text.isNotEmpty ? _pickupController.text : null,
                                  initialDropoffLocation: _dropoffController.text.isNotEmpty ? _dropoffController.text : null,
                                ),
                              ),
                            );
                          },
                          child: Container(
                            width: 220,
                            margin: const EdgeInsets.only(right: 10, bottom: 4),
                            padding: const EdgeInsets.all(10),
                            decoration: BoxDecoration(
                              color: isSelected
                                  ? const Color(0xFF3B82F6).withOpacity(0.18)
                                  : AppColors.backgroundDark,
                              borderRadius: BorderRadius.circular(18),
                              border: Border.all(
                                color: isSelected ? const Color(0xFF3B82F6) : Colors.white.withOpacity(0.08),
                                width: isSelected ? 1.5 : 1,
                              ),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                // Top row: Category Badge & Verified tag
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFF3B82F6).withOpacity(0.25),
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: Text(
                                        v.category.toUpperCase(),
                                        style: const TextStyle(
                                          color: Color(0xFF60A5FA),
                                          fontSize: 9,
                                          fontWeight: FontWeight.w900,
                                        ),
                                      ),
                                    ),
                                    Text(
                                      'Verified Fleet',
                                      style: TextStyle(color: Colors.white.withOpacity(0.5), fontSize: 9.5),
                                    ),
                                  ],
                                ),

                                const SizedBox(height: 4),

                                // Vehicle Image
                                Expanded(
                                  child: Center(
                                    child: (v.imageUrl != null && v.imageUrl!.isNotEmpty)
                                        ? Image.network(
                                            v.imageUrl!,
                                            fit: BoxFit.contain,
                                            errorBuilder: (_, __, ___) => const Icon(
                                              Icons.directions_car_rounded,
                                              color: Color(0xFF3B82F6),
                                              size: 38,
                                            ),
                                          )
                                        : const Icon(
                                            Icons.directions_car_rounded,
                                            color: Color(0xFF3B82F6),
                                            size: 38,
                                          ),
                                  ),
                                ),

                                // Vehicle Title & Specs
                                Text(
                                  v.fullName,
                                  style: const TextStyle(
                                    color: AppColors.textLight,
                                    fontSize: 12.5,
                                    fontWeight: FontWeight.w800,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),

                                const SizedBox(height: 2),

                                // Specs row (Auto • Petrol/EV • Seats)
                                Row(
                                  children: [
                                    Text(
                                      '⚙️ ${v.transmission} • 👥 ${v.seats} Seats',
                                      style: const TextStyle(color: AppColors.textMuted, fontSize: 10),
                                    ),
                                    const Spacer(),
                                    Text(
                                      '${v.currencySymbol}${v.dailyRate.toStringAsFixed(0)}/d',
                                      style: const TextStyle(
                                        color: Color(0xFF60A5FA),
                                        fontSize: 13,
                                        fontWeight: FontWeight.w900,
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
        ),
      ],
    );
  }

  // --- DRIVER SECTION: Live Registered Drivers from Backend API ---
  Widget _buildDriverSection() {
    return Column(
      mainAxisSize: MainAxisSize.min,
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Driver Filter Chips
        SizedBox(
          height: 34,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 14),
            itemCount: _driverFilters.length,
            itemBuilder: (context, index) {
              final filter = _driverFilters[index];
              final isSelected = _selectedDriverFilter == filter;
              return Padding(
                padding: const EdgeInsets.only(right: 6),
                child: GestureDetector(
                  onTap: () {
                    setState(() => _selectedDriverFilter = filter);
                    _fetchDrivers(filter: filter);
                  },
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: isSelected ? const Color(0xFF10B981) : AppColors.backgroundDark,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(
                        color: isSelected ? const Color(0xFF10B981) : Colors.white.withOpacity(0.08),
                      ),
                    ),
                    child: Center(
                      child: Text(
                        filter,
                        style: TextStyle(
                          color: isSelected ? Colors.white : AppColors.textMuted,
                          fontSize: 11.5,
                          fontWeight: isSelected ? FontWeight.w900 : FontWeight.w600,
                        ),
                      ),
                    ),
                  ),
                ),
              );
            },
          ),
        ),

        const SizedBox(height: 6),

        // Live Drivers Carousel from API
        SizedBox(
          height: 156,
          child: _isLoadingDrivers
              ? const Center(
                  child: SizedBox(
                    width: 26,
                    height: 26,
                    child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF10B981)),
                  ),
                )
              : _drivers.isEmpty
                  ? Center(
                      child: Text(
                        'No drivers registered for this filter.',
                        style: TextStyle(color: AppColors.textMuted, fontSize: 12),
                      ),
                    )
                  : ListView.builder(
                      scrollDirection: Axis.horizontal,
                      padding: const EdgeInsets.symmetric(horizontal: 14),
                      itemCount: _drivers.length,
                      itemBuilder: (context, index) {
                        final d = _drivers[index];
                        final isSelected = _selectedDriver?.id == d.id;
                        return GestureDetector(
                          onTap: () {
                            setState(() => _selectedDriver = d);
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => DriverDetailScreen(
                                  driver: d,
                                  initialPickupLocation: _pickupController.text.isNotEmpty ? _pickupController.text : null,
                                  initialDropoffLocation: _dropoffController.text.isNotEmpty ? _dropoffController.text : null,
                                ),
                              ),
                            );
                          },
                          child: Container(
                            width: 230,
                            margin: const EdgeInsets.only(right: 10, bottom: 4),
                            padding: const EdgeInsets.all(10),
                            decoration: BoxDecoration(
                              color: isSelected
                                  ? const Color(0xFF10B981).withOpacity(0.18)
                                  : AppColors.backgroundDark,
                              borderRadius: BorderRadius.circular(18),
                              border: Border.all(
                                color: isSelected ? const Color(0xFF10B981) : Colors.white.withOpacity(0.08),
                                width: isSelected ? 1.5 : 1,
                              ),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                // Top row: Avatar, Name & Rating
                                Row(
                                  children: [
                                    CircleAvatar(
                                      radius: 18,
                                      backgroundColor: const Color(0xFF10B981),
                                      backgroundImage: (d.avatarUrl != null && d.avatarUrl!.isNotEmpty)
                                          ? NetworkImage(d.avatarUrl!)
                                          : null,
                                      child: (d.avatarUrl == null || d.avatarUrl!.isEmpty)
                                          ? Text(
                                              d.name.isNotEmpty ? d.name[0].toUpperCase() : 'D',
                                              style: const TextStyle(
                                                color: Colors.white,
                                                fontWeight: FontWeight.bold,
                                                fontSize: 14,
                                              ),
                                            )
                                          : null,
                                    ),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            d.name,
                                            style: const TextStyle(
                                              color: AppColors.textLight,
                                              fontWeight: FontWeight.bold,
                                              fontSize: 12.5,
                                            ),
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                          Row(
                                            children: [
                                              const Icon(Icons.star_rounded, color: Color(0xFFFFDC00), size: 13),
                                              const SizedBox(width: 2),
                                              Text(
                                                '${d.rating.toStringAsFixed(1)} • ${d.totalTrips} trips',
                                                style: const TextStyle(color: Color(0xFFFFDC00), fontSize: 10, fontWeight: FontWeight.bold),
                                              ),
                                            ],
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                ),

                                const SizedBox(height: 6),

                                // Bio snippet
                                Expanded(
                                  child: Text(
                                    d.bio,
                                    style: TextStyle(
                                      color: Colors.white.withOpacity(0.7),
                                      fontSize: 10.5,
                                      height: 1.25,
                                    ),
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),

                                const SizedBox(height: 4),

                                // Bottom row: Experience badge & Rate
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFF10B981).withOpacity(0.2),
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: Text(
                                        '🛡️ ${d.experienceYears}+ Yrs Exp',
                                        style: const TextStyle(
                                          color: Color(0xFF34D399),
                                          fontSize: 9.5,
                                          fontWeight: FontWeight.w800,
                                        ),
                                      ),
                                    ),
                                    Text(
                                      '${d.currencySymbol}${d.hourlyRate.toStringAsFixed(0)}/hr',
                                      style: const TextStyle(
                                        color: Color(0xFF34D399),
                                        fontSize: 13,
                                        fontWeight: FontWeight.w900,
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
        ),
      ],
    );
  }

  // --- STANDARD RIDE & DELIVER SECTIONS ---
  Widget _buildStandardRideOrDeliverSection() {
    final tiers = _selectedService == ServiceType.deliver ? _deliverTierOptions : _rideTierOptions;
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        // Input Fields Card
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 4, 16, 4),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
            decoration: BoxDecoration(
              color: AppColors.backgroundDark,
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: Colors.white.withOpacity(0.06)),
            ),
            child: Column(
              children: [
                // Pickup Field
                TextField(
                  controller: _pickupController,
                  onChanged: (val) => _onQueryChanged(val, isPickup: true),
                  style: const TextStyle(color: AppColors.textLight, fontSize: 13),
                  decoration: InputDecoration(
                    prefixIcon: Icon(Icons.circle, color: _serviceColor, size: 13),
                    hintText: _pickupHint,
                    hintStyle: const TextStyle(color: AppColors.textMuted, fontSize: 12.5),
                    border: InputBorder.none,
                    isDense: true,
                    suffixIcon: _pickupController.text.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.close_rounded, color: AppColors.textMuted, size: 16),
                            onPressed: () {
                              _pickupController.clear();
                              _userLat = null;
                              _userLng = null;
                              setState(() => _predictions = []);
                              _updateMapMarkers();
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
                    prefixIcon: const Icon(Icons.location_on_rounded, color: AppColors.danger, size: 15),
                    hintText: _dropoffHint,
                    hintStyle: const TextStyle(color: AppColors.textMuted, fontSize: 12.5),
                    border: InputBorder.none,
                    isDense: true,
                    suffixIcon: _dropoffController.text.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.close_rounded, color: AppColors.textMuted, size: 16),
                            onPressed: () {
                              _dropoffController.clear();
                              _dropoffLat = null;
                              _dropoffLng = null;
                              setState(() => _predictions = []);
                              _updateMapMarkers();
                            },
                          )
                        : null,
                  ),
                ),
              ],
            ),
          ),
        ),

        // Places Autocomplete Suggestions
        if (_isSearchingPlaces || _predictions.isNotEmpty)
          Container(
            margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
            constraints: const BoxConstraints(maxHeight: 160),
            decoration: BoxDecoration(
              color: AppColors.backgroundDark,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: _serviceColor.withOpacity(0.3)),
            ),
            child: _isSearchingPlaces
                ? const Padding(
                    padding: EdgeInsets.all(14.0),
                    child: Center(
                      child: SizedBox(
                        width: 20,
                        height: 20,
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
                        leading: Icon(Icons.location_on_outlined, color: _serviceColor, size: 18),
                        title: Text(
                          p.mainText,
                          style: const TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 12.5),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        subtitle: p.secondaryText.isNotEmpty
                            ? Text(
                                p.secondaryText,
                                style: const TextStyle(color: AppColors.textMuted, fontSize: 10.5),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              )
                            : null,
                        onTap: () => _selectPlace(p),
                      );
                    },
                  ),
          ),

        // Tier Options Selector Cards
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 4, 16, 4),
          child: Row(
            children: tiers.map((opt) {
              final isSelected = _selectedTierId == opt['id'];
              return Expanded(
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 3),
                  child: GestureDetector(
                    onTap: () {
                      setState(() => _selectedTierId = opt['id']);
                      final rideProv = Provider.of<RideProvider>(context, listen: false);
                      rideProv.setSelectedVehicle(opt['id']);
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 6),
                      decoration: BoxDecoration(
                        color: isSelected ? _serviceColor.withOpacity(0.18) : AppColors.backgroundDark,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(
                          color: isSelected ? _serviceColor : Colors.transparent,
                          width: 1.5,
                        ),
                      ),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            opt['icon'] as IconData,
                            color: isSelected ? _serviceColor : AppColors.textMuted,
                            size: 22,
                          ),
                          const SizedBox(height: 3),
                          Text(
                            opt['label'],
                            style: TextStyle(
                              color: isSelected ? AppColors.textLight : AppColors.textMuted,
                              fontWeight: FontWeight.bold,
                              fontSize: 11,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                          Text(
                            opt['price'],
                            style: TextStyle(
                              color: isSelected ? _serviceColor : AppColors.success,
                              fontWeight: FontWeight.w900,
                              fontSize: 11.5,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              );
            }).toList(),
          ),
        ),
      ],
    );
  }

  Widget _buildBottomTabItem({
    required ServiceType type,
    required String label,
    required IconData icon,
    required Color activeColor,
  }) {
    final isSelected = _selectedService == type;
    return Expanded(
      child: GestureDetector(
        behavior: HitTestBehavior.opaque,
        onTap: () => _onServiceTabChanged(type),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          curve: Curves.easeInOut,
          padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 4),
          decoration: BoxDecoration(
            color: isSelected ? activeColor.withOpacity(0.16) : Colors.transparent,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(
              color: isSelected ? activeColor.withOpacity(0.35) : Colors.transparent,
              width: 1,
            ),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                icon,
                color: isSelected ? activeColor : const Color(0xFF64748B),
                size: 22,
              ),
              const SizedBox(height: 3),
              Text(
                label,
                style: TextStyle(
                  color: isSelected ? activeColor : const Color(0xFF94A3B8),
                  fontWeight: isSelected ? FontWeight.w900 : FontWeight.w600,
                  fontSize: 11.5,
                  letterSpacing: isSelected ? 0.2 : 0,
                ),
                maxLines: 1,
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
            // User Header
            Padding(
              padding: const EdgeInsets.all(20.0),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 26,
                    backgroundColor: _serviceColor,
                    backgroundImage: (auth.avatarUrl != null && auth.avatarUrl!.isNotEmpty)
                        ? NetworkImage(auth.avatarUrl!)
                        : null,
                    child: (auth.avatarUrl == null || auth.avatarUrl!.isEmpty)
                        ? Text(
                            (auth.userName ?? 'R').trim().isNotEmpty
                                ? (auth.userName ?? 'R').trim()[0].toUpperCase()
                                : 'R',
                            style: const TextStyle(
                              color: AppColors.backgroundDark,
                              fontWeight: FontWeight.bold,
                              fontSize: 20,
                            ),
                          )
                        : null,
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
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const Divider(color: Colors.white10, height: 1),

            // Action Buttons Row (Help, Wallet, Activity)
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 14, 16, 10),
              child: Row(
                children: [
                  Expanded(
                    child: _buildDrawerActionBtn(
                      icon: Icons.help_outline_rounded,
                      label: 'Help',
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.push(context, MaterialPageRoute(builder: (_) => const HelpSupportScreen()));
                      },
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _buildDrawerActionBtn(
                      icon: Icons.account_balance_wallet_outlined,
                      label: 'Wallet',
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.push(context, MaterialPageRoute(builder: (_) => const WalletScreen()));
                      },
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _buildDrawerActionBtn(
                      icon: Icons.history_rounded,
                      label: 'Activity',
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.push(context, MaterialPageRoute(builder: (_) => const MyRidesScreen()));
                      },
                    ),
                  ),
                ],
              ),
            ),

            // Cash Balance Card
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                decoration: BoxDecoration(
                  color: AppColors.backgroundDark,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: Colors.white.withOpacity(0.06)),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('RideMyCars Cash', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 13)),
                    const Text('\$0.00', style: TextStyle(color: AppColors.success, fontWeight: FontWeight.w900, fontSize: 16)),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 8),
            const Divider(color: Colors.white10, height: 1),

            // Navigation List
            Expanded(
              child: ListView(
                padding: const EdgeInsets.symmetric(vertical: 6),
                children: [
                  ListTile(
                    leading: const Icon(Icons.person_outline_rounded, color: AppColors.primary),
                    title: const Text('Manage Account', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w600)),
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(context, MaterialPageRoute(builder: (_) => const ManageAccountScreen()));
                    },
                  ),
                  ListTile(
                    leading: const Icon(Icons.directions_car_filled_rounded, color: AppColors.info),
                    title: const Text('My Rides', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w600)),
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(context, MaterialPageRoute(builder: (_) => const MyRidesScreen()));
                    },
                  ),
                  ListTile(
                    leading: const Icon(Icons.account_balance_wallet_rounded, color: AppColors.purple),
                    title: const Text('Wallet & Payments', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w600)),
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(context, MaterialPageRoute(builder: (_) => const WalletScreen()));
                    },
                  ),
                  ListTile(
                    leading: const Icon(Icons.notifications_rounded, color: AppColors.primary),
                    title: const Text('Notifications', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w600)),
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(context, MaterialPageRoute(builder: (_) => const NotificationsScreen()));
                    },
                  ),
                  ListTile(
                    leading: const Icon(Icons.support_agent_rounded, color: AppColors.success),
                    title: const Text('Help & Support', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w600)),
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(context, MaterialPageRoute(builder: (_) => const HelpSupportScreen()));
                    },
                  ),
                ],
              ),
            ),

            const Divider(color: Colors.white10, height: 1),
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
            const SizedBox(height: 8),
          ],
        ),
      ),
    );
  }

  Widget _buildDrawerActionBtn({required IconData icon, required String label, required VoidCallback onTap}) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10),
        decoration: BoxDecoration(
          color: AppColors.backgroundDark,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.white.withOpacity(0.06)),
        ),
        child: Column(
          children: [
            Icon(icon, color: AppColors.textLight, size: 20),
            const SizedBox(height: 4),
            Text(label, style: const TextStyle(color: AppColors.textLight, fontSize: 11, fontWeight: FontWeight.bold)),
          ],
        ),
      ),
    );
  }
}
