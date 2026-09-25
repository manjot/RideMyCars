import 'dart:async';
import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../models/driver_model.dart';
import '../../models/ride_category_model.dart';
import '../../models/vehicle_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/country_provider.dart';
import '../../providers/notification_provider.dart';
import '../../providers/ride_provider.dart';
import '../../services/delivery_service.dart';
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
  bool _enableBackupChauffeur = true;

  List<PlacePrediction> _predictions = [];
  bool _isSearchingPlaces = false;
  bool _isSearchingPickup = true;
  Timer? _debounceTimer;
  Set<Marker> _markers = {};
  Set<Polyline> _polylines = {};

  final List<String> _rentalCategories = ['All', 'Economy', 'Compact', 'Sedan', 'SUV', 'Luxury', 'Van'];
  final List<String> _driverFilters = ['All', 'Top Rated', 'City Duty', 'Executive'];
  String? _lastCountryCode;
  final Map<String, double> _dynamicDeliveryPrices = {};

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final rideProv = Provider.of<RideProvider>(context, listen: false);
      final countryProv = Provider.of<CountryProvider>(context, listen: false);
      _lastCountryCode = countryProv.selectedCountryCode;
      rideProv.startActiveRidePolling();
      rideProv.fetchCategories(country: countryProv.selectedCountryCode);
      Provider.of<NotificationProvider>(context, listen: false).startPolling();
      _getCurrentLocation();
      _fetchRentalVehicles(country: countryProv.selectedCountryCode);
      _fetchDrivers(country: countryProv.selectedCountryCode);
    });
  }

  @override
  void dispose() {
    _debounceTimer?.cancel();
    _pickupController.dispose();
    _dropoffController.dispose();
    super.dispose();
  }

  Future<void> _recalculateDynamicPrices() async {
    final countryProv = Provider.of<CountryProvider>(context, listen: false);
    final rideProv = Provider.of<RideProvider>(context, listen: false);

    // If coordinates missing but text entered, forward-geocode
    if ((_userLat == null || _userLng == null) && _pickupController.text.trim().isNotEmpty) {
      final pDetails = await PlacesService.getCoordinatesFromAddress(_pickupController.text.trim());
      if (pDetails != null && mounted) {
        setState(() {
          _userLat = pDetails.lat;
          _userLng = pDetails.lng;
        });
        _updateMapMarkers();
      }
    }

    if ((_dropoffLat == null || _dropoffLng == null) && _dropoffController.text.trim().isNotEmpty) {
      final dDetails = await PlacesService.getCoordinatesFromAddress(_dropoffController.text.trim());
      if (dDetails != null && mounted) {
        setState(() {
          _dropoffLat = dDetails.lat;
          _dropoffLng = dDetails.lng;
        });
        _updateMapMarkers();
      }
    }

    double distKm = 10.0;
    if (_userLat != null && _userLng != null && _dropoffLat != null && _dropoffLng != null) {
      distKm = Geolocator.distanceBetween(_userLat!, _userLng!, _dropoffLat!, _dropoffLng!) / 1000.0;
    }

    await rideProv.calculateDynamicFares(
      pickupLat: _userLat,
      pickupLng: _userLng,
      dropoffLat: _dropoffLat,
      dropoffLng: _dropoffLng,
      distanceKm: distKm,
      country: countryProv.selectedCountryCode,
    );
    _calculateDynamicDeliveryPrice(distKm: distKm);
  }

  Future<void> _calculateDynamicDeliveryPrice({double? distKm}) async {
    final countryProv = Provider.of<CountryProvider>(context, listen: false);
    for (final speed in ['Hyperlocal', 'Same Day', 'Express', 'Instant']) {
      final res = await DeliveryService.calculatePrice(
        pickupLat: _userLat,
        pickupLng: _userLng,
        dropoffLat: _dropoffLat,
        dropoffLng: _dropoffLng,
        deliveryType: speed,
        country: countryProv.selectedCountryCode,
      );
      if (res != null && res['total_price'] != null) {
        if (mounted) {
          setState(() {
            _dynamicDeliveryPrices[speed] = (res['total_price'] as num).toDouble();
          });
        }
      }
    }
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
    final countryProv = Provider.of<CountryProvider>(context, listen: false);
    final isGhana = countryProv.selectedCountryCode.toUpperCase() == 'GHA';
    switch (_selectedService) {
      case ServiceType.ride:
        return isGhana ? 'Pickup location (e.g. Kotoka Airport, Osu, Accra)' : 'Enter pickup address';
      case ServiceType.rent:
        return isGhana ? 'Pick-up hub (e.g. Accra Mall, Airport)' : 'Pick-up location / airport hub';
      case ServiceType.driver:
        return isGhana ? 'Reporting location (e.g. Cantonments, Airport)' : 'Reporting location (e.g. Home / Office)';
      case ServiceType.deliver:
        return isGhana ? 'Sender address (Pickup parcel in Accra)' : 'Sender address (Pickup parcel)';
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
    final rideProv = Provider.of<RideProvider>(context, listen: false);
    switch (_selectedService) {
      case ServiceType.ride:
        final selected = rideProv.selectedCategory;
        if (selected != null) {
          return 'Request ${selected.name} • ${selected.fareFormatted} →';
        }
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
        final delPrice = _dynamicDeliveryPrices[_selectedTierId];
        final countryProv = Provider.of<CountryProvider>(context, listen: false);
        final priceStr = delPrice != null ? '${countryProv.currencySymbol}${delPrice.toStringAsFixed(2)}' : '';
        return priceStr.isNotEmpty ? 'Send $_selectedTierId • $priceStr →' : 'Send Delivery Now →';
    }
  }

  List<RideCategoryModel> get _fallbackRideCategories {
    final countryProv = Provider.of<CountryProvider>(context, listen: false);
    final isGhana = countryProv.selectedCountryCode.toUpperCase() == 'GHA';
    final sym = countryProv.currencySymbol;

    if (isGhana) {
      return [
        RideCategoryModel(
          id: 'economy',
          slug: 'economy',
          categoryKey: 'economy',
          name: 'Economy',
          icon: '🚗',
          capacity: '1–4 seats',
          luggage: '2 Bags',
          etaMinutes: 3,
          fare: 18.50,
          fareFormatted: '$sym 18.50',
          baseFare: 4.50,
          perKmRate: 1.10,
          perMinuteRate: 0.20,
          minimumFare: 8.50,
          description: 'Small hatchbacks for affordable daily commuting in Accra',
        ),
        RideCategoryModel(
          id: 'standard',
          slug: 'standard',
          categoryKey: 'standard',
          name: 'Standard / Comfort',
          icon: '🚘',
          capacity: '1–4 seats',
          luggage: '3 Bags',
          etaMinutes: 5,
          fare: 29.50,
          fareFormatted: '$sym 29.50',
          baseFare: 7.00,
          perKmRate: 1.80,
          perMinuteRate: 0.30,
          minimumFare: 23.50,
          description: 'Clean climate-controlled sedans with top-rated drivers',
        ),
        RideCategoryModel(
          id: 'luxury',
          slug: 'luxury',
          categoryKey: 'luxury',
          name: 'Luxury SUV',
          icon: '🚙',
          capacity: '1–6 seats',
          luggage: '5 Bags',
          etaMinutes: 7,
          fare: 49.50,
          fareFormatted: '$sym 49.50',
          baseFare: 12.00,
          perKmRate: 3.00,
          perMinuteRate: 0.50,
          minimumFare: 35.20,
          description: 'High-ride premium SUVs for business travelers and airport runs',
        ),
        RideCategoryModel(
          id: 'van_xl',
          slug: 'van_xl',
          categoryKey: 'van_xl',
          name: 'Van XL',
          icon: '🚐',
          capacity: '1–7 seats',
          luggage: '6 Bags',
          etaMinutes: 9,
          fare: 71.25,
          fareFormatted: '$sym 71.25',
          baseFare: 15.00,
          perKmRate: 4.50,
          perMinuteRate: 0.75,
          minimumFare: 50.20,
          description: 'Multi-passenger vehicles for airport runs or large families',
        ),
        RideCategoryModel(
          id: 'vip_chauffeur',
          slug: 'vip_chauffeur',
          categoryKey: 'vip_chauffeur',
          name: 'VIP Chauffeurs',
          icon: '👑',
          capacity: '1–4 seats',
          luggage: '3 Bags',
          etaMinutes: 11,
          fare: 110.00,
          fareFormatted: '$sym 110.00',
          baseFare: 30.00,
          perKmRate: 6.50,
          perMinuteRate: 1.00,
          minimumFare: 109.50,
          description: 'High-end luxury executive sedans with suited vetted chauffeurs',
        ),
        RideCategoryModel(
          id: 'group_bus',
          slug: 'group_bus',
          categoryKey: 'group_bus',
          name: 'Group Bus (7–14)',
          icon: '🚌',
          capacity: '7–14 seats',
          luggage: '10 Bags',
          etaMinutes: 13,
          fare: 150.90,
          fareFormatted: '$sym 150.90',
          baseFare: 45.00,
          perKmRate: 8.00,
          perMinuteRate: 1.20,
          minimumFare: 150.90,
          description: 'Microbuses for event transport or corporate teams',
        ),
      ];
    }

    return [
      RideCategoryModel(
        id: 'economy',
        slug: 'economy',
        categoryKey: 'economy',
        name: 'Economy',
        icon: '🚗',
        capacity: '1–4 seats',
        etaMinutes: 3,
        fare: 15.00,
        fareFormatted: '$sym 15.00',
      ),
      RideCategoryModel(
        id: 'standard',
        slug: 'standard',
        categoryKey: 'standard',
        name: 'Comfort',
        icon: '🚘',
        capacity: '1–4 seats',
        etaMinutes: 5,
        fare: 22.00,
        fareFormatted: '$sym 22.00',
      ),
      RideCategoryModel(
        id: 'luxury',
        slug: 'luxury',
        categoryKey: 'luxury',
        name: 'SUV',
        icon: '🚙',
        capacity: '1–6 seats',
        etaMinutes: 7,
        fare: 35.00,
        fareFormatted: '$sym 35.00',
      ),
      RideCategoryModel(
        id: 'van_xl',
        slug: 'van_xl',
        categoryKey: 'van_xl',
        name: 'XL Van',
        icon: '🚐',
        capacity: '1–7 seats',
        etaMinutes: 9,
        fare: 45.00,
        fareFormatted: '$sym 45.00',
      ),
      RideCategoryModel(
        id: 'vip_chauffeur',
        slug: 'vip_chauffeur',
        categoryKey: 'vip_chauffeur',
        name: 'VIP Chauffeurs',
        icon: '👑',
        capacity: '1–4 seats',
        etaMinutes: 11,
        fare: 75.00,
        fareFormatted: '$sym 75.00',
      ),
    ];
  }

  List<Map<String, dynamic>> get _deliverTierOptions {
    final countryProv = Provider.of<CountryProvider>(context, listen: false);
    final sym = countryProv.currencySymbol;
    return [
      {
        'id': 'Hyperlocal',
        'label': 'Hyperlocal',
        'eta': '< 45 min',
        'price': _dynamicDeliveryPrices.containsKey('Hyperlocal')
            ? '$sym${_dynamicDeliveryPrices['Hyperlocal']!.toStringAsFixed(2)}'
            : countryProv.formatAmount(15.0),
        'icon': Icons.two_wheeler_rounded,
        'desc': 'Motorbike courier for fast intra-city deliveries',
      },
      {
        'id': 'Same Day',
        'label': 'Same Day',
        'eta': 'By 6 PM',
        'price': _dynamicDeliveryPrices.containsKey('Same Day')
            ? '$sym${_dynamicDeliveryPrices['Same Day']!.toStringAsFixed(2)}'
            : countryProv.formatAmount(19.0),
        'icon': Icons.local_shipping_rounded,
        'desc': 'Consolidated dispatch for parcels delivered today',
      },
      {
        'id': 'Express',
        'label': 'Express',
        'eta': '< 90 min',
        'price': _dynamicDeliveryPrices.containsKey('Express')
            ? '$sym${_dynamicDeliveryPrices['Express']!.toStringAsFixed(2)}'
            : countryProv.formatAmount(23.0),
        'icon': Icons.flash_on_rounded,
        'desc': 'Priority door-to-door courier route',
      },
      {
        'id': 'Instant',
        'label': 'Instant',
        'eta': '< 30 min',
        'price': _dynamicDeliveryPrices.containsKey('Instant')
            ? '$sym${_dynamicDeliveryPrices['Instant']!.toStringAsFixed(2)}'
            : countryProv.formatAmount(27.0),
        'icon': Icons.electric_bolt_rounded,
        'desc': 'Dedicated urgent direct delivery rider',
      },
    ];
  }

  void _onServiceTabChanged(ServiceType service) {
    setState(() {
      _selectedService = service;
      if (service == ServiceType.ride) {
        final rideProv = Provider.of<RideProvider>(context, listen: false);
        _selectedTierId = rideProv.selectedCategory?.slug ?? 'standard';
      } else if (service == ServiceType.deliver) {
        _selectedTierId = 'Hyperlocal';
      }
    });

    final countryProv = Provider.of<CountryProvider>(context, listen: false);
    if (service == ServiceType.rent && _rentalVehicles.isEmpty) {
      _fetchRentalVehicles(country: countryProv.selectedCountryCode);
    } else if (service == ServiceType.driver && _drivers.isEmpty) {
      _fetchDrivers(country: countryProv.selectedCountryCode);
    } else if (service == ServiceType.ride) {
      _recalculateDynamicPrices();
    } else if (service == ServiceType.deliver) {
      _calculateDynamicDeliveryPrice();
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
          if (_dropoffLat != null && _dropoffLng != null) {
            _recalculateDynamicPrices();
          }
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
    _recalculateDynamicPrices();
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
    final countryProv = Provider.of<CountryProvider>(context, listen: false);

    double? computedDist;
    int? computedDur;
    if (_userLat != null && _userLng != null && _dropoffLat != null && _dropoffLng != null) {
      computedDist = Geolocator.distanceBetween(_userLat!, _userLng!, _dropoffLat!, _dropoffLng!) / 1000.0;
      computedDur = (computedDist / 30.0 * 60).round().clamp(5, 300);
    }

    final finalDist = rideProv.estimatedDistanceKm ?? computedDist ?? 10.0;
    final finalDur = rideProv.estimatedDurationMinutes ?? computedDur ?? 15;

    String vehicleTypeTag;
    if (_selectedService == ServiceType.rent && _selectedRentalVehicle != null) {
      vehicleTypeTag = 'RENTAL_${_selectedRentalVehicle!.id}_${_selectedRentalVehicle!.fullName}';
    } else if (_selectedService == ServiceType.driver && _selectedDriver != null) {
      vehicleTypeTag = 'CHAUFFEUR_${_selectedDriver!.id}_${_selectedDriver!.name}';
    } else if (_selectedService == ServiceType.deliver) {
      vehicleTypeTag = 'DELIVERY_$_selectedTierId';
    } else {
      vehicleTypeTag = rideProv.selectedCategory?.name ?? rideProv.selectedCategory?.slug ?? _selectedTierId;
    }

    rideProv.setSelectedVehicle(vehicleTypeTag);

    final success = await rideProv.bookRide(
      pickupLocation: pickup,
      dropoffLocation: dropoff,
      pickupLat: _userLat,
      pickupLng: _userLng,
      dropoffLat: _dropoffLat,
      dropoffLng: _dropoffLng,
      distanceKm: finalDist,
      durationMinutes: finalDur,
      backupChauffeurEnabled: _enableBackupChauffeur,
      country: countryProv.selectedCountryCode,
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
                      onTap: () async {
                        final code = item.code;
                        Navigator.pop(ctx);
                        final rideProv = Provider.of<RideProvider>(context, listen: false);
                        await countryProv.setCountry(code);
                        await rideProv.fetchCategories(country: code);
                        _fetchRentalVehicles(country: code);
                        _fetchDrivers(country: code);
                        _recalculateDynamicPrices();
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

    if (_lastCountryCode != countryProv.selectedCountryCode) {
      _lastCountryCode = countryProv.selectedCountryCode;
      WidgetsBinding.instance.addPostFrameCallback((_) {
        rideProv.fetchCategories(country: countryProv.selectedCountryCode);
        _fetchRentalVehicles(country: countryProv.selectedCountryCode);
        _fetchDrivers(country: countryProv.selectedCountryCode);
        _recalculateDynamicPrices();
      });
    }

    LatLng defaultCenterForCountry(String code) {
      switch (code.toUpperCase()) {
        case 'GHA':
          return const LatLng(5.6037, -0.1870); // Accra, Ghana
        case 'USA':
          return const LatLng(40.7128, -74.0060); // New York, USA
        case 'GBR':
          return const LatLng(51.5074, -0.1278); // London, UK
        case 'IND':
          return const LatLng(28.6139, 77.2090); // New Delhi, India
        case 'ARE':
          return const LatLng(25.2048, 55.2708); // Dubai, UAE
        default:
          return const LatLng(5.6037, -0.1870);
      }
    }

    final initialCenter = LatLng(
      _userLat ?? defaultCenterForCountry(countryProv.selectedCountryCode).latitude,
      _userLng ?? defaultCenterForCountry(countryProv.selectedCountryCode).longitude,
    );

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

                      // Backup Chauffeur Switch Option
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                        child: Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: AppColors.backgroundDark,
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(
                              color: _enableBackupChauffeur
                                  ? AppColors.primary.withOpacity(0.4)
                                  : Colors.white.withOpacity(0.08),
                            ),
                          ),
                          child: Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(8),
                                decoration: BoxDecoration(
                                  color: AppColors.primary.withOpacity(0.12),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: const Icon(Icons.shield_outlined, color: AppColors.primary, size: 20),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text(
                                      'Enable Backup Chauffeur',
                                      style: TextStyle(
                                        color: AppColors.textLight,
                                        fontWeight: FontWeight.bold,
                                        fontSize: 13,
                                      ),
                                    ),
                                    const SizedBox(height: 2),
                                    Text(
                                      "If your assigned chauffeur is unavailable, we'll automatically find another nearby chauffeur.",
                                      style: TextStyle(
                                        color: AppColors.textMuted,
                                        fontSize: 11,
                                        height: 1.2,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              Switch.adaptive(
                                value: _enableBackupChauffeur,
                                activeColor: AppColors.primary,
                                onChanged: (val) {
                                  setState(() => _enableBackupChauffeur = val);
                                },
                              ),
                            ],
                          ),
                        ),
                      ),

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
    final rideProv = Provider.of<RideProvider>(context);
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
                  onSubmitted: (_) => _recalculateDynamicPrices(),
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
                  onSubmitted: (_) => _recalculateDynamicPrices(),
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

        // Multi-tier Dynamic Categories or Delivery Options Carousel
        Padding(
          padding: const EdgeInsets.only(top: 4, bottom: 4),
          child: _selectedService == ServiceType.deliver
              ? _buildDeliveryTiersList()
              : _buildRideCategoriesList(rideProv),
        ),
      ],
    );
  }

  Widget _buildRideCategoriesList(RideProvider rideProv) {
    if (rideProv.isLoadingCategories && rideProv.rideCategories.isEmpty) {
      return const SizedBox(
        height: 92,
        child: Center(
          child: SizedBox(
            width: 24,
            height: 24,
            child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.primary),
          ),
        ),
      );
    }

    final categories = rideProv.rideCategories.isNotEmpty
        ? rideProv.rideCategories
        : _fallbackRideCategories;

    final selected = rideProv.selectedCategory ?? (categories.isNotEmpty ? categories.first : null);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Route Distance & Duration & Surge Pill
        if (rideProv.estimatedDistanceKm != null && rideProv.estimatedDistanceKm! > 0)
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 2, 16, 6),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    const Icon(Icons.route_rounded, color: AppColors.primary, size: 14),
                    const SizedBox(width: 4),
                    Text(
                      '${rideProv.estimatedDistanceKm!.toStringAsFixed(1)} km • ~${rideProv.estimatedDurationMinutes ?? 15} mins',
                      style: const TextStyle(color: AppColors.textLight, fontSize: 11.5, fontWeight: FontWeight.bold),
                    ),
                  ],
                ),
                if (rideProv.surgeInfo != null && rideProv.surgeInfo!['is_surge'] == true)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFF9F0A).withOpacity(0.18),
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(color: const Color(0xFFFF9F0A).withOpacity(0.4)),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.bolt_rounded, color: Color(0xFFFF9F0A), size: 12),
                        const SizedBox(width: 2),
                        Text(
                          '${rideProv.surgeInfo!['multiplier']}x Surge Capped',
                          style: const TextStyle(color: Color(0xFFFF9F0A), fontSize: 10, fontWeight: FontWeight.bold),
                        ),
                      ],
                    ),
                  ),
              ],
            ),
          ),

        // Horizontal Category Cards Carousel (all 6 categories for Ghana or country tiers)
        SizedBox(
          height: 92,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 14),
            itemCount: categories.length,
            itemBuilder: (context, index) {
              final cat = categories[index];
              final isSelected = selected?.slug.toLowerCase() == cat.slug.toLowerCase() ||
                  selected?.name.toLowerCase() == cat.name.toLowerCase();

              return Padding(
                padding: const EdgeInsets.only(right: 8),
                child: GestureDetector(
                  onTap: () {
                    setState(() => _selectedTierId = cat.slug);
                    rideProv.selectCategory(cat);
                  },
                  child: Container(
                    width: 128,
                    padding: const EdgeInsets.symmetric(vertical: 7, horizontal: 8),
                    decoration: BoxDecoration(
                      color: isSelected ? _serviceColor.withOpacity(0.18) : AppColors.backgroundDark,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(
                        color: isSelected ? _serviceColor : Colors.white.withOpacity(0.08),
                        width: isSelected ? 1.8 : 1,
                      ),
                      boxShadow: isSelected
                          ? [
                              BoxShadow(
                                color: _serviceColor.withOpacity(0.25),
                                blurRadius: 8,
                                offset: const Offset(0, 2),
                              )
                            ]
                          : null,
                    ),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              cat.icon.isNotEmpty ? cat.icon : '🚗',
                              style: const TextStyle(fontSize: 18),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                              decoration: BoxDecoration(
                                color: isSelected
                                    ? _serviceColor.withOpacity(0.2)
                                    : Colors.white.withOpacity(0.08),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                '${cat.etaMinutes}m',
                                style: TextStyle(
                                  color: isSelected ? _serviceColor : AppColors.textMuted,
                                  fontSize: 9.5,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ],
                        ),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              cat.name,
                              style: TextStyle(
                                color: isSelected ? AppColors.textLight : AppColors.textMuted,
                                fontWeight: FontWeight.bold,
                                fontSize: 11.5,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                            Text(
                              cat.capacity,
                              style: TextStyle(
                                color: AppColors.textMuted.withOpacity(0.8),
                                fontSize: 9.5,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ],
                        ),
                        Text(
                          cat.fareFormatted,
                          style: TextStyle(
                            color: isSelected ? _serviceColor : const Color(0xFF34D399),
                            fontWeight: FontWeight.w900,
                            fontSize: 12.5,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _buildDeliveryTiersList() {
    final tiers = _deliverTierOptions;
    return SizedBox(
      height: 92,
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 14),
        itemCount: tiers.length,
        itemBuilder: (context, index) {
          final opt = tiers[index];
          final isSelected = _selectedTierId == opt['id'];
          return Padding(
            padding: const EdgeInsets.only(right: 8),
            child: GestureDetector(
              onTap: () => setState(() => _selectedTierId = opt['id']),
              child: Container(
                width: 128,
                padding: const EdgeInsets.symmetric(vertical: 7, horizontal: 8),
                decoration: BoxDecoration(
                  color: isSelected ? _serviceColor.withOpacity(0.18) : AppColors.backgroundDark,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(
                    color: isSelected ? _serviceColor : Colors.white.withOpacity(0.08),
                    width: isSelected ? 1.8 : 1,
                  ),
                  boxShadow: isSelected
                      ? [
                          BoxShadow(
                            color: _serviceColor.withOpacity(0.25),
                            blurRadius: 8,
                            offset: const Offset(0, 2),
                          )
                        ]
                      : null,
                ),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Icon(
                          opt['icon'] as IconData,
                          color: isSelected ? _serviceColor : AppColors.textMuted,
                          size: 18,
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                          decoration: BoxDecoration(
                            color: isSelected
                                ? _serviceColor.withOpacity(0.2)
                                : Colors.white.withOpacity(0.08),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            opt['eta'],
                            style: TextStyle(
                              color: isSelected ? _serviceColor : AppColors.textMuted,
                              fontSize: 9.5,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ],
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          opt['label'],
                          style: TextStyle(
                            color: isSelected ? AppColors.textLight : AppColors.textMuted,
                            fontWeight: FontWeight.bold,
                            fontSize: 11.5,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        Text(
                          opt['desc'] ?? '',
                          style: TextStyle(
                            color: AppColors.textMuted.withOpacity(0.8),
                            fontSize: 9.5,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                    Text(
                      opt['price'],
                      style: TextStyle(
                        color: isSelected ? _serviceColor : const Color(0xFF34D399),
                        fontWeight: FontWeight.w900,
                        fontSize: 12.5,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
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
