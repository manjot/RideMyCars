import 'dart:async';
import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:intl/intl.dart';
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
import '../driver/hire_driver_catalog_screen.dart';
import '../notifications/notifications_screen.dart';
import '../rent/rental_catalog_screen.dart';
import '../rent/rental_detail_screen.dart';
import '../rides/my_rides_screen.dart';
import '../support/help_support_screen.dart';
import '../wallet/wallet_screen.dart';
import 'ride_tracking_screen.dart';
import 'widgets/floating_ride_widget.dart';
import 'widgets/ride_schedule_modal.dart';
import 'widgets/ride_passenger_modal.dart';
import 'widgets/saved_location_modal.dart';
import 'widgets/ride_payment_method_sheet.dart';
import '../safety/sos_contacts_screen.dart';
import '../safety/widgets/sos_floating_button.dart';

enum ServiceType {
  ride,
  rent,
  driver,
  deliver,
}

enum RideBookingStep {
  findTrip,
  chooseRide,
  confirmRide,
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
  final _pickupFocusNode = FocusNode();
  final _dropoffFocusNode = FocusNode();
  final _rentPickupFocusNode = FocusNode();
  final _rentDropoffFocusNode = FocusNode();
  final _driverPickupFocusNode = FocusNode();
  final _driverDropoffFocusNode = FocusNode();
  final _deliveryPickupFocusNode = FocusNode();
  final _deliveryDropoffFocusNode = FocusNode();
  String _activeSearchField = 'ride_dropoff';

  ServiceType _selectedService = ServiceType.ride;
  String _selectedTierId = 'Standard';

  // Ride Booking Step State (Find Trip -> Choose Ride -> Confirm Ride)
  RideBookingStep _rideBookingStep = RideBookingStep.findTrip;
  String _scheduleType = 'now';
  DateTime _scheduledDate = DateTime.now();
  TimeOfDay _scheduledTime = TimeOfDay.now();
  String _riderType = 'me';
  String? _riderName;
  String? _riderPhone;
  final TextEditingController _contactPhoneController = TextEditingController(text: '+233 55 977 6761');
  final List<Map<String, dynamic>> _additionalStops = [];
  String? _homeAddress;
  double? _homeLat;
  double? _homeLng;
  String? _officeAddress;
  double? _officeLat;
  double? _officeLng;
  String _paymentMethod = 'stripe';
  String? _momoPhone;
  String _momoNetwork = 'MTN';
  bool _isFinalSubmittingRide = false;

  // Rent a Car Website Flow State
  bool _rentDifferentDropoff = false;
  final TextEditingController _rentPickupController = TextEditingController();
  final TextEditingController _rentDropoffController = TextEditingController();
  DateTime _rentPickupDate = DateTime.now();
  TimeOfDay _rentPickupTime = const TimeOfDay(hour: 10, minute: 0);
  DateTime _rentReturnDate = DateTime.now().add(const Duration(days: 3));
  TimeOfDay _rentReturnTime = const TimeOfDay(hour: 10, minute: 0);
  final TextEditingController _rentDriverAgeController = TextEditingController(text: '25');

  // Hire a Driver Website Flow State
  String _driverServiceType = 'Hourly Driver';
  final TextEditingController _driverPickupController = TextEditingController();
  final TextEditingController _driverDropoffController = TextEditingController();
  final List<Map<String, dynamic>> _driverAdditionalStops = [];
  final TextEditingController _driverCarMakeModelController = TextEditingController(text: 'Toyota Camry');
  final TextEditingController _driverCarRegNumberController = TextEditingController(text: 'REG-8899');
  String _driverTransmission = 'automatic';

  // Delivery Flow State
  String _deliverySpeed = 'express';
  final TextEditingController _deliveryRecipientNameController = TextEditingController(text: 'John Mensah');
  final TextEditingController _deliveryRecipientPhoneController = TextEditingController(text: '+233 24 123 4567');



  // Rental Vehicles API Data
  List<VehicleModel> _rentalVehicles = [];
  List<VehicleModel> _allRentalVehicles = [];
  VehicleModel? _selectedRentalVehicle;
  bool _isLoadingVehicles = false;
  String _selectedRentalCategory = 'All';

  // Registered Drivers API Data
  List<DriverModel> _drivers = [];
  DriverModel? _selectedDriver;
  bool _isLoadingDrivers = false;

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

  String? _lastCountryCode;
  final Map<String, double> _dynamicDeliveryPrices = {};

  @override
  void initState() {
    super.initState();
    for (final node in [
      _pickupFocusNode,
      _dropoffFocusNode,
      _rentPickupFocusNode,
      _rentDropoffFocusNode,
      _driverPickupFocusNode,
      _driverDropoffFocusNode,
      _deliveryPickupFocusNode,
      _deliveryDropoffFocusNode,
    ]) {
      node.addListener(() {
        if (mounted) setState(() {});
      });
    }
    for (final c in [
      _pickupController,
      _dropoffController,
      _rentPickupController,
      _rentDropoffController,
      _driverPickupController,
      _driverDropoffController,
    ]) {
      c.addListener(() {
        if (mounted) setState(() {});
      });
    }
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
    _pickupFocusNode.dispose();
    _dropoffFocusNode.dispose();
    _rentPickupFocusNode.dispose();
    _rentDropoffFocusNode.dispose();
    _driverPickupFocusNode.dispose();
    _driverDropoffFocusNode.dispose();
    _deliveryPickupFocusNode.dispose();
    _deliveryDropoffFocusNode.dispose();
    _pickupController.dispose();
    _dropoffController.dispose();
    _contactPhoneController.dispose();
    _rentPickupController.dispose();
    _rentDropoffController.dispose();
    _rentDriverAgeController.dispose();
    _driverPickupController.dispose();
    _driverDropoffController.dispose();
    _driverCarMakeModelController.dispose();
    _driverCarRegNumberController.dispose();
    _deliveryRecipientNameController.dispose();
    _deliveryRecipientPhoneController.dispose();
    for (final s in _additionalStops) {
      (s['controller'] as TextEditingController?)?.dispose();
    }
    for (final s in _driverAdditionalStops) {
      (s['controller'] as TextEditingController?)?.dispose();
    }
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
    final targetCat = category ?? _selectedRentalCategory;
    final vehicles = await RentalService.getAvailableVehicles(
      category: targetCat == 'All' ? null : targetCat,
      country: country ?? countryProv.selectedCountryCode,
    );
    if (mounted) {
      setState(() {
        if (targetCat == 'All' || _allRentalVehicles.isEmpty) {
          _allRentalVehicles = vehicles;
        }
        _rentalVehicles = vehicles;
        _updateSelectedRentalVehicle();
        _isLoadingVehicles = false;
      });
    }
  }

  List<VehicleModel> get _displayedRentalVehicles {
    final source = _allRentalVehicles.isNotEmpty ? _allRentalVehicles : _rentalVehicles;
    if (_selectedRentalCategory == 'All') return source;
    final cat = _selectedRentalCategory.toLowerCase();
    return source.where((v) {
      final vCat = v.category.toLowerCase();
      final vType = v.type.toLowerCase();
      if (vCat == cat || vCat.contains(cat)) return true;
      if (vType == cat || vType.contains(cat)) return true;
      if (cat == 'economy' && (vCat.contains('compact') || vType.contains('compact') || vType.contains('economy'))) return true;
      if (cat == 'compact' && (vCat.contains('economy') || vType.contains('compact') || vType.contains('hatchback'))) return true;
      return false;
    }).toList();
  }

  void _updateSelectedRentalVehicle() {
    final list = _displayedRentalVehicles;
    if (list.isEmpty) {
      _selectedRentalVehicle = null;
    } else if (_selectedRentalVehicle == null || !list.any((v) => v.id == _selectedRentalVehicle!.id)) {
      _selectedRentalVehicle = list.first;
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

  void _onServiceTabChanged(ServiceType service) {
    setState(() {
      _selectedService = service;
      _rideBookingStep = RideBookingStep.findTrip;
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

  void _onQueryChanged(String query, {String? field, bool? isPickup}) {
    final resolvedField = field ?? (isPickup == true ? 'ride_pickup' : 'ride_dropoff');
    _activeSearchField = resolvedField;
    _isSearchingPickup = resolvedField.contains('pickup');
    _debounceTimer?.cancel();
    _debounceTimer = Timer(const Duration(milliseconds: 250), () async {
      if (query.trim().isEmpty) {
        setState(() {
          _predictions = [];
          _isSearchingPlaces = false;
        });
        return;
      }

      setState(() {
        _isSearchingPlaces = true;
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
    final details = await PlacesService.getPlaceDetails(prediction.placeId, prediction: prediction);
    final chosenText = prediction.mainText.isNotEmpty ? prediction.mainText : prediction.description;
    final fullAddress = details?.formattedAddress.isNotEmpty == true ? details!.formattedAddress : chosenText;

    setState(() {
      switch (_activeSearchField) {
        case 'rent_pickup':
          _rentPickupController.text = fullAddress;
          break;
        case 'rent_dropoff':
          _rentDropoffController.text = fullAddress;
          break;
        case 'driver_pickup':
          _driverPickupController.text = fullAddress;
          break;
        case 'driver_dropoff':
          _driverDropoffController.text = fullAddress;
          break;
        case 'delivery_pickup':
          _pickupController.text = fullAddress;
          if (details != null) {
            _userLat = details.lat;
            _userLng = details.lng;
          }
          _calculateDynamicDeliveryPrice();
          break;
        case 'delivery_dropoff':
          _dropoffController.text = fullAddress;
          if (details != null) {
            _dropoffLat = details.lat;
            _dropoffLng = details.lng;
          }
          _calculateDynamicDeliveryPrice();
          break;
        case 'ride_pickup':
          _pickupController.text = chosenText;
          if (details != null) {
            _userLat = details.lat;
            _userLng = details.lng;
          }
          _updateMapMarkers();
          if (_dropoffLat != null && _dropoffLng != null) {
            _recalculateDynamicPrices();
          }
          break;
        case 'ride_dropoff':
        default:
          _dropoffController.text = chosenText;
          if (details != null) {
            _dropoffLat = details.lat;
            _dropoffLng = details.lng;
          }
          _updateMapMarkers();
          if (_userLat != null && _userLng != null) {
            _recalculateDynamicPrices();
          }
          break;
      }
      _predictions = [];
      _isSearchingPlaces = false;
    });

    if (!mounted) return;
    _pickupFocusNode.unfocus();
    _dropoffFocusNode.unfocus();
    _rentPickupFocusNode.unfocus();
    _rentDropoffFocusNode.unfocus();
    _driverPickupFocusNode.unfocus();
    _driverDropoffFocusNode.unfocus();
    _deliveryPickupFocusNode.unfocus();
    _deliveryDropoffFocusNode.unfocus();
    FocusScope.of(context).unfocus();
  }

  Widget _buildPlacesSuggestionsList() {
    if (!_isSearchingPlaces && _predictions.isEmpty) {
      return const SizedBox.shrink();
    }

    return Container(
      margin: const EdgeInsets.symmetric(vertical: 6),
      constraints: const BoxConstraints(maxHeight: 220),
      decoration: BoxDecoration(
        color: const Color(0xFF161C28),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: _serviceColor.withOpacity(0.55), width: 1.5),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.55),
            blurRadius: 18,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Header with Quick Close
          Padding(
            padding: const EdgeInsets.fromLTRB(14, 8, 8, 4),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  _isSearchingPlaces ? 'Searching locations...' : 'Select Address',
                  style: TextStyle(color: _serviceColor, fontSize: 11.5, fontWeight: FontWeight.bold),
                ),
                GestureDetector(
                  onTap: () {
                    FocusScope.of(context).unfocus();
                    setState(() {
                      _predictions = [];
                      _isSearchingPlaces = false;
                    });
                  },
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.08),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: const [
                        Icon(Icons.close_rounded, size: 13, color: Colors.white70),
                        SizedBox(width: 4),
                        Text('Close', style: TextStyle(color: Colors.white70, fontSize: 11)),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
          const Divider(color: Colors.white10, height: 1),
          ConstrainedBox(
            constraints: const BoxConstraints(maxHeight: 175),
            child: _isSearchingPlaces && _predictions.isEmpty
                ? Padding(
                    padding: const EdgeInsets.symmetric(vertical: 18),
                    child: Center(
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          SizedBox(
                            width: 18,
                            height: 18,
                            child: CircularProgressIndicator(strokeWidth: 2, color: _serviceColor),
                          ),
                          const SizedBox(width: 10),
                          const Text('Searching matching places...', style: TextStyle(color: Colors.white70, fontSize: 12)),
                        ],
                      ),
                    ),
                  )
                : ListView.separated(
                    shrinkWrap: true,
                    physics: const ClampingScrollPhysics(),
                    padding: const EdgeInsets.symmetric(vertical: 4),
                    itemCount: _predictions.length,
                    separatorBuilder: (_, __) => const Divider(color: Colors.white10, height: 1),
                    itemBuilder: (context, index) {
                      final p = _predictions[index];
                      return ListTile(
                        dense: true,
                        leading: Icon(Icons.location_on_rounded, color: _serviceColor, size: 20),
                        title: Text(
                          p.mainText,
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        subtitle: p.secondaryText.isNotEmpty
                            ? Text(
                                p.secondaryText,
                                style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              )
                            : null,
                        trailing: const Icon(Icons.north_west_rounded, size: 14, color: Colors.white30),
                        onTap: () => _selectPlace(p),
                      );
                    },
                  ),
          ),
        ],
      ),
    );
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

    final isTypingAddress = _pickupFocusNode.hasFocus ||
        _dropoffFocusNode.hasFocus ||
        _rentPickupFocusNode.hasFocus ||
        _rentDropoffFocusNode.hasFocus ||
        _driverPickupFocusNode.hasFocus ||
        _driverDropoffFocusNode.hasFocus ||
        _deliveryPickupFocusNode.hasFocus ||
        _deliveryDropoffFocusNode.hasFocus;
    final isKeyboardOpen = isTypingAddress || MediaQuery.of(context).viewInsets.bottom > 0;

    return Scaffold(
      resizeToAvoidBottomInset: true,
      backgroundColor: AppColors.backgroundDark,
      drawer: _buildDrawer(context, auth),
      body: GestureDetector(
        behavior: HitTestBehavior.translucent,
        onTap: () {
          _pickupFocusNode.unfocus();
          _dropoffFocusNode.unfocus();
          _rentPickupFocusNode.unfocus();
          _rentDropoffFocusNode.unfocus();
          _driverPickupFocusNode.unfocus();
          _driverDropoffFocusNode.unfocus();
          _deliveryPickupFocusNode.unfocus();
          _deliveryDropoffFocusNode.unfocus();
          FocusScope.of(context).unfocus();
        },
        child: Stack(
          children: [
            // Google Map Background
            GoogleMap(
              initialCameraPosition: CameraPosition(target: initialCenter, zoom: 14.0),
              markers: _markers,
              polylines: _polylines,
              padding: EdgeInsets.only(top: 80, bottom: isKeyboardOpen ? 160 : 360),
              myLocationEnabled: true,
              myLocationButtonEnabled: false,
              zoomControlsEnabled: false,
              onMapCreated: (c) => _mapController = c,
              onTap: (_) {
                _pickupFocusNode.unfocus();
                _dropoffFocusNode.unfocus();
                _rentPickupFocusNode.unfocus();
                _rentDropoffFocusNode.unfocus();
                _driverPickupFocusNode.unfocus();
                _driverDropoffFocusNode.unfocus();
                _deliveryPickupFocusNode.unfocus();
                _deliveryDropoffFocusNode.unfocus();
                FocusScope.of(context).unfocus();
              },
            ),

          // Map Corner SOS Emergency Button
          Builder(
            builder: (ctx) {
              final rideProv = Provider.of<RideProvider>(ctx);
              final activeRide = rideProv.activeRide;
              final driver = activeRide?['driver'] as Map<String, dynamic>?;
              final driverPhone = driver?['phone']?.toString();
              final driverName = driver?['name']?.toString() ?? 'Assigned Driver';
              final rideId = activeRide?['id'] as int?;

              return Positioned(
                right: 16,
                top: 76,
                child: SosFloatingButton(
                  role: 'rider',
                  rideId: rideId,
                  assignedPhone: driverPhone,
                  assignedName: driverName,
                ),
              );
            },
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
                constraints: BoxConstraints(
                  maxHeight: MediaQuery.of(context).size.height -
                      MediaQuery.of(context).viewInsets.bottom -
                      MediaQuery.of(context).padding.top -
                      (isKeyboardOpen ? 12 : 70),
                ),
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
                  bottom: !isKeyboardOpen,
                  child: SingleChildScrollView(
                    keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
                    physics: const ClampingScrollPhysics(),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        // Mode-Specific Body matching Website & Design
                        if (_selectedService == ServiceType.ride) ...[
                          if (_rideBookingStep == RideBookingStep.findTrip)
                            _buildRideFindTripStep(isKeyboardOpen)
                          else if (_rideBookingStep == RideBookingStep.chooseRide)
                            _buildRideChooseRideStep()
                          else
                            _buildRideConfirmRideStep(),
                        ] else if (_selectedService == ServiceType.rent)
                          _buildRentSection(isKeyboardOpen)
                        else if (_selectedService == ServiceType.driver)
                          _buildDriverSection(isKeyboardOpen)
                        else
                          _buildDeliverySection(isKeyboardOpen),

                        // Bottom Navigation Tab Bar (Ride, Rent, Driver, Deliver) (Hidden when keyboard open or in checkout funnel)
                        if (!isKeyboardOpen && (_selectedService != ServiceType.ride || _rideBookingStep == RideBookingStep.findTrip))
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
    ),
  );
}

  // ==========================================
  // RIDE FLOW MODALS & HELPERS
  // ==========================================

  Future<void> _openScheduleModal() async {
    final res = await RideScheduleModal.show(
      context,
      initialScheduleType: _scheduleType,
      initialDate: _scheduledDate,
      initialTime: _scheduledTime,
    );
    if (res != null) {
      setState(() {
        _scheduleType = res.scheduleType;
        _scheduledDate = res.date;
        _scheduledTime = res.time;
      });
    }
  }

  Future<void> _openPassengerModal() async {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final res = await RidePassengerModal.show(
      context,
      initialRiderType: _riderType,
      initialName: _riderName,
      initialPhone: _riderPhone,
      currentUserName: auth.userName,
    );
    if (res != null) {
      setState(() {
        _riderType = res.riderType;
        _riderName = res.name;
        _riderPhone = res.phone;
      });
    }
  }

  Future<void> _openPaymentMethodSheet() async {
    final res = await RidePaymentMethodSheet.show(
      context,
      currentMethod: _paymentMethod,
      currentMomoPhone: _momoPhone,
      currentMomoNetwork: _momoNetwork,
    );
    if (res != null) {
      setState(() {
        _paymentMethod = res.method;
        _momoPhone = res.momoPhone;
        _momoNetwork = res.momoNetwork ?? _momoNetwork;
      });
    }
  }

  Future<void> _openSavedLocationModal(String locationType) async {
    final isHome = locationType == 'home';
    final res = await SavedLocationModal.show(
      context,
      locationType: locationType,
      currentAddress: isHome ? _homeAddress : _officeAddress,
      currentLat: isHome ? _homeLat : _officeLat,
      currentLng: isHome ? _homeLng : _officeLng,
    );
    if (res != null) {
      setState(() {
        if (isHome) {
          _homeAddress = res.address;
          _homeLat = res.lat;
          _homeLng = res.lng;
          if (_dropoffController.text.isEmpty) {
            _dropoffController.text = res.address;
            _dropoffLat = res.lat;
            _dropoffLng = res.lng;
          }
        } else {
          _officeAddress = res.address;
          _officeLat = res.lat;
          _officeLng = res.lng;
          if (_dropoffController.text.isEmpty) {
            _dropoffController.text = res.address;
            _dropoffLat = res.lat;
            _dropoffLng = res.lng;
          }
        }
      });
      _updateMapMarkers();
    }
  }

  // ==========================================
  // FINAL RIDE BOOKING EXECUTION
  // ==========================================

  Future<void> _handleFinalRideBooking() async {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    if (!auth.isAuthenticated || auth.token == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please log in to authorize your ride request.'),
          backgroundColor: AppColors.warning,
        ),
      );
      Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => const LoginScreen()),
      );
      return;
    }

    final pickup = _pickupController.text.trim();
    final dropoff = _dropoffController.text.trim();

    if (pickup.isEmpty || dropoff.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please specify both pickup and destination locations.'),
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
    final finalDist = rideProv.estimatedDistanceKm ?? computedDist ?? 5.2;
    final finalDur = rideProv.estimatedDurationMinutes ?? computedDur ?? 12;

    setState(() => _isFinalSubmittingRide = true);

    final success = await rideProv.bookRide(
      pickupLocation: pickup,
      dropoffLocation: dropoff,
      pickupLat: _userLat,
      pickupLng: _userLng,
      dropoffLat: _dropoffLat,
      dropoffLng: _dropoffLng,
      distanceKm: finalDist,
      durationMinutes: finalDur,
      paymentMethod: _paymentMethod,
      backupChauffeurEnabled: _enableBackupChauffeur,
      country: countryProv.selectedCountryCode,
      notes: _riderType != 'me' ? 'Passenger: $_riderName (${_riderPhone ?? ''})' : null,
      scheduleDate: _scheduleType == 'later' ? DateFormat('yyyy-MM-dd').format(_scheduledDate) : null,
      scheduleTime: _scheduleType == 'later' ? _scheduledTime.format(context) : null,
      momoPhone: _momoPhone,
      momoNetwork: _momoNetwork,
    );

    if (!mounted) return;
    setState(() => _isFinalSubmittingRide = false);

    if (success && rideProv.activeRide != null) {
      setState(() => _rideBookingStep = RideBookingStep.findTrip);
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => RideTrackingScreen(ride: rideProv.activeRide!),
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(rideProv.errorMessage ?? 'Failed to request ride. Please retry.'),
          backgroundColor: AppColors.danger,
        ),
      );
    }
  }

  // ==========================================
  // RIDE STEP 1: FIND TRIP (Screenshot 1)
  // ==========================================

  Widget _buildRideFindTripStep(bool isKeyboardOpen) {
    final hasFocus = _pickupFocusNode.hasFocus || _dropoffFocusNode.hasFocus;

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 10, 16, 8),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // 1. Top Row: [🕒 Pickup now ▼] [👤 For me ▼]
          Row(
            children: [
              Expanded(
                child: GestureDetector(
                  onTap: _openScheduleModal,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    decoration: BoxDecoration(
                      color: const Color(0xFF1E2433),
                      borderRadius: BorderRadius.circular(24),
                      border: Border.all(color: Colors.white.withOpacity(0.08)),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          children: [
                            const Icon(Icons.access_time_filled_rounded, size: 16, color: Color(0xFF94A3B8)),
                            const SizedBox(width: 8),
                            Text(
                              _scheduleType == 'now'
                                  ? 'Pickup now'
                                  : '${DateFormat('MMM d').format(_scheduledDate)}, ${_scheduledTime.format(context)}',
                              style: const TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                                fontSize: 13,
                              ),
                            ),
                          ],
                        ),
                        const Icon(Icons.arrow_drop_down, color: Color(0xFF94A3B8), size: 20),
                      ],
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: GestureDetector(
                  onTap: _openPassengerModal,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    decoration: BoxDecoration(
                      color: const Color(0xFF1E2433),
                      borderRadius: BorderRadius.circular(24),
                      border: Border.all(color: Colors.white.withOpacity(0.08)),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          children: [
                            const Icon(Icons.person_rounded, size: 16, color: Color(0xFF94A3B8)),
                            const SizedBox(width: 8),
                            Text(
                              _riderType == 'me'
                                  ? 'For me'
                                  : (_riderName != null && _riderName!.isNotEmpty
                                      ? 'For ${_riderName!.split(' ').first}'
                                      : 'Someone else'),
                              style: const TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                                fontSize: 13,
                              ),
                            ),
                          ],
                        ),
                        const Icon(Icons.arrow_drop_down, color: Color(0xFF94A3B8), size: 20),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // 2. Route Card: Pickup + Stops + Destination
          Container(
            decoration: BoxDecoration(
              color: const Color(0xFF161C28),
              borderRadius: BorderRadius.circular(18),
              border: Border.all(
                color: hasFocus ? const Color(0xFFFFDC00) : Colors.white.withOpacity(0.09),
                width: hasFocus ? 1.5 : 1.0,
              ),
            ),
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Pickup Field
                Row(
                  children: [
                    Container(
                      width: 16,
                      height: 16,
                      decoration: const BoxDecoration(
                        color: Color(0xFF10B981),
                        shape: BoxShape.circle,
                      ),
                      child: const Center(
                        child: Icon(Icons.circle, color: Colors.white, size: 6),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: TextField(
                        controller: _pickupController,
                        focusNode: _pickupFocusNode,
                        style: const TextStyle(color: Colors.white, fontSize: 13.5, fontWeight: FontWeight.w600),
                        decoration: InputDecoration(
                          hintText: 'Pickup location (Enter address)',
                          hintStyle: TextStyle(color: Colors.white.withOpacity(0.4), fontSize: 13),
                          border: InputBorder.none,
                          isDense: true,
                          contentPadding: const EdgeInsets.symmetric(vertical: 8),
                        ),
                        onTap: () {
                          setState(() => _isSearchingPickup = true);
                          if (_pickupController.text.isNotEmpty) {
                            _onQueryChanged(_pickupController.text, isPickup: true);
                          }
                        },
                        onChanged: (val) => _onQueryChanged(val, isPickup: true),
                      ),
                    ),
                    if (_pickupController.text.isNotEmpty)
                      GestureDetector(
                        onTap: () {
                          _pickupController.clear();
                          _userLat = null;
                          _userLng = null;
                          setState(() => _predictions = []);
                          _updateMapMarkers();
                        },
                        child: const Padding(
                          padding: EdgeInsets.symmetric(horizontal: 4),
                          child: Icon(Icons.close_rounded, size: 18, color: Color(0xFF94A3B8)),
                        ),
                      ),
                    GestureDetector(
                      onTap: _getCurrentLocation,
                      child: const Padding(
                        padding: EdgeInsets.symmetric(horizontal: 4),
                        child: Icon(Icons.my_location_rounded, size: 19, color: Color(0xFF94A3B8)),
                      ),
                    ),
                  ],
                ),

                // Additional Intermediate Stops
                for (int i = 0; i < _additionalStops.length; i++) ...[
                  Padding(
                    padding: const EdgeInsets.only(left: 7),
                    child: Align(
                      alignment: Alignment.centerLeft,
                      child: Container(width: 2, height: 12, color: Colors.white12),
                    ),
                  ),
                  Row(
                    children: [
                      Container(
                        width: 16,
                        height: 16,
                        decoration: const BoxDecoration(
                          color: Color(0xFF3B82F6),
                          shape: BoxShape.circle,
                        ),
                        child: const Center(
                          child: Icon(Icons.circle, color: Colors.white, size: 6),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: TextField(
                          controller: _additionalStops[i]['controller'] as TextEditingController,
                          style: const TextStyle(color: Colors.white, fontSize: 13.5, fontWeight: FontWeight.w600),
                          decoration: InputDecoration(
                            hintText: 'Stop ${i + 1} address',
                            hintStyle: TextStyle(color: Colors.white.withOpacity(0.4), fontSize: 13),
                            border: InputBorder.none,
                            isDense: true,
                            contentPadding: const EdgeInsets.symmetric(vertical: 8),
                          ),
                          onChanged: (val) => _onQueryChanged(val, isPickup: false),
                        ),
                      ),
                      GestureDetector(
                        onTap: () {
                          setState(() {
                            (_additionalStops[i]['controller'] as TextEditingController).dispose();
                            _additionalStops.removeAt(i);
                          });
                        },
                        child: const Padding(
                          padding: EdgeInsets.symmetric(horizontal: 4),
                          child: Icon(Icons.close_rounded, size: 18, color: Color(0xFFEF4444)),
                        ),
                      ),
                    ],
                  ),
                ],

                Padding(
                  padding: const EdgeInsets.only(left: 7),
                  child: Align(
                    alignment: Alignment.centerLeft,
                    child: Container(width: 2, height: 14, color: Colors.white12),
                  ),
                ),

                // Destination Field
                Row(
                  children: [
                    Container(
                      width: 16,
                      height: 16,
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: const Center(
                        child: Icon(Icons.square, color: Colors.black, size: 7),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: TextField(
                        controller: _dropoffController,
                        focusNode: _dropoffFocusNode,
                        style: const TextStyle(color: Colors.white, fontSize: 13.5, fontWeight: FontWeight.w600),
                        decoration: InputDecoration(
                          hintText: 'Where to? (Enter destination)',
                          hintStyle: TextStyle(color: Colors.white.withOpacity(0.4), fontSize: 13),
                          border: InputBorder.none,
                          isDense: true,
                          contentPadding: const EdgeInsets.symmetric(vertical: 8),
                        ),
                        onTap: () {
                          setState(() => _isSearchingPickup = false);
                          if (_dropoffController.text.isNotEmpty) {
                            _onQueryChanged(_dropoffController.text, isPickup: false);
                          }
                        },
                        onChanged: (val) => _onQueryChanged(val, isPickup: false),
                      ),
                    ),
                    if (_dropoffController.text.isNotEmpty)
                      GestureDetector(
                        onTap: () {
                          _dropoffController.clear();
                          _dropoffLat = null;
                          _dropoffLng = null;
                          setState(() => _predictions = []);
                          _updateMapMarkers();
                        },
                        child: const Padding(
                          padding: EdgeInsets.symmetric(horizontal: 4),
                          child: Icon(Icons.close_rounded, size: 18, color: Color(0xFF94A3B8)),
                        ),
                      ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),

          // Places Autocomplete Suggestions (Positioned directly beneath search inputs for full visibility)
          if (_isSearchingPlaces || _predictions.isNotEmpty) ...[
            Container(
              margin: const EdgeInsets.only(bottom: 10),
              constraints: const BoxConstraints(maxHeight: 220),
              decoration: BoxDecoration(
                color: const Color(0xFF161C28),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFFFFDC00).withOpacity(0.5), width: 1.5),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.4),
                    blurRadius: 16,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: ListView.separated(
                shrinkWrap: true,
                padding: const EdgeInsets.symmetric(vertical: 4),
                itemCount: _predictions.length,
                separatorBuilder: (_, __) => const Divider(color: Colors.white10, height: 1),
                itemBuilder: (context, index) {
                  final p = _predictions[index];
                  return ListTile(
                    dense: true,
                    leading: const Icon(Icons.location_on_outlined, color: Color(0xFFFFDC00), size: 18),
                    title: Text(
                      p.mainText,
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    subtitle: p.secondaryText.isNotEmpty
                        ? Text(
                            p.secondaryText,
                            style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          )
                        : null,
                    onTap: () => _selectPlace(p),
                  );
                },
              ),
            ),
          ],

          // 3. GPS Accuracy & Map Adjust Note (Hidden while searching to keep dropdown elevated)
          if (!_isSearchingPlaces && _predictions.isEmpty) ...[
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Container(
                      width: 7,
                      height: 7,
                      decoration: const BoxDecoration(
                        color: Color(0xFF10B981),
                        shape: BoxShape.circle,
                      ),
                    ),
                    const SizedBox(width: 6),
                    const Text(
                      'GPS Accurate (±187m)',
                      style: TextStyle(
                        color: Color(0xFF10B981),
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
                const Text(
                  'Drag pin on map to adjust',
                  style: TextStyle(color: Color(0xFF94A3B8), fontSize: 11),
                ),
              ],
            ),
            const SizedBox(height: 10),

            // 4. Quick Action Chips: [+ Add stop] [🏠 Home ✏️] [🏢 Office ✏️]
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  GestureDetector(
                    onTap: () {
                      setState(() {
                        _additionalStops.add({
                          'controller': TextEditingController(),
                          'address': '',
                          'lat': null,
                          'lng': null,
                        });
                      });
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
                      decoration: BoxDecoration(
                        color: const Color(0xFF1E2433),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: Colors.white.withOpacity(0.08)),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: const [
                          Icon(Icons.add, size: 14, color: Colors.white),
                          SizedBox(width: 4),
                          Text('Add stop', style: TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.bold)),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  GestureDetector(
                    onTap: () {
                      if (_homeAddress != null) {
                        _dropoffController.text = _homeAddress!;
                        _dropoffLat = _homeLat;
                        _dropoffLng = _homeLng;
                        _updateMapMarkers();
                      } else {
                        _openSavedLocationModal('home');
                      }
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
                      decoration: BoxDecoration(
                        color: const Color(0xFF1E2433),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: Colors.white.withOpacity(0.08)),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Text('🏠', style: TextStyle(fontSize: 12)),
                          const SizedBox(width: 5),
                          Text(
                            _homeAddress != null ? 'Home' : 'Add Home',
                            style: const TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(width: 5),
                          GestureDetector(
                            onTap: () => _openSavedLocationModal('home'),
                            child: const Icon(Icons.edit, size: 12, color: Color(0xFFE2E8F0)),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  GestureDetector(
                    onTap: () {
                      if (_officeAddress != null) {
                        _dropoffController.text = _officeAddress!;
                        _dropoffLat = _officeLat;
                        _dropoffLng = _officeLng;
                        _updateMapMarkers();
                      } else {
                        _openSavedLocationModal('office');
                      }
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
                      decoration: BoxDecoration(
                        color: const Color(0xFF1E2433),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: Colors.white.withOpacity(0.08)),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Text('🏢', style: TextStyle(fontSize: 12)),
                          const SizedBox(width: 5),
                          Text(
                            _officeAddress != null ? 'Office' : 'Add Office',
                            style: const TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(width: 5),
                          GestureDetector(
                            onTap: () => _openSavedLocationModal('office'),
                            child: const Icon(Icons.edit, size: 12, color: Color(0xFFE2E8F0)),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 10),

            // 5. Contact Number (Required) Card
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
              decoration: BoxDecoration(
                color: const Color(0xFF161C28),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: Colors.white.withOpacity(0.08)),
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEC4899).withOpacity(0.12),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.phone_in_talk_rounded, color: Color(0xFFEC4899), size: 18),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'CONTACT NUMBER (REQUIRED)',
                          style: TextStyle(
                            color: Color(0xFF94A3B8),
                            fontSize: 9.5,
                            fontWeight: FontWeight.w900,
                            letterSpacing: 0.5,
                          ),
                        ),
                        TextField(
                          controller: _contactPhoneController,
                          keyboardType: TextInputType.phone,
                          style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold),
                          decoration: InputDecoration(
                            hintText: 'Enter mobile phone number...',
                            hintStyle: TextStyle(color: Colors.white.withOpacity(0.4), fontSize: 12.5),
                            border: InputBorder.none,
                            isDense: true,
                            contentPadding: const EdgeInsets.symmetric(vertical: 2),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ],
          const SizedBox(height: 12),

          // 7. Yellow CTA Button: Search Rides →
          SizedBox(
            height: 50,
            child: ElevatedButton(
              onPressed: () {
                final pickup = _pickupController.text.trim();
                final dropoff = _dropoffController.text.trim();
                if (pickup.isEmpty || dropoff.isEmpty) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('Please enter both pickup and destination locations.'),
                      backgroundColor: AppColors.warning,
                    ),
                  );
                  return;
                }
                setState(() => _rideBookingStep = RideBookingStep.chooseRide);
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFFFFDC00),
                foregroundColor: Colors.black,
                elevation: 4,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: const [
                  Text(
                    'Search Rides',
                    style: TextStyle(
                      color: Colors.black,
                      fontWeight: FontWeight.w900,
                      fontSize: 16,
                      letterSpacing: 0.2,
                    ),
                  ),
                  SizedBox(width: 8),
                  Icon(Icons.arrow_forward_rounded, color: Colors.black, size: 20),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ==========================================
  // RIDE STEP 2: CHOOSE A RIDE (Screenshot 2)
  // ==========================================

  Widget _buildRideChooseRideStep() {
    final rideProv = Provider.of<RideProvider>(context);
    final countryProv = Provider.of<CountryProvider>(context);
    final sym = countryProv.currencySymbol;

    double? computedDist;
    int? computedDur;
    if (_userLat != null && _userLng != null && _dropoffLat != null && _dropoffLng != null) {
      computedDist = Geolocator.distanceBetween(_userLat!, _userLng!, _dropoffLat!, _dropoffLng!) / 1000.0;
      computedDur = (computedDist / 30.0 * 60).round().clamp(5, 300);
    }
    final finalDist = rideProv.estimatedDistanceKm ?? computedDist ?? 5.2;
    final finalDur = rideProv.estimatedDurationMinutes ?? computedDur ?? 10;

    final categories = rideProv.rideCategories.isNotEmpty
        ? rideProv.rideCategories
        : _fallbackRideCategories;
    final selectedCat = rideProv.selectedCategory ?? (categories.isNotEmpty ? categories.first : _fallbackRideCategories.first);
    final baseFare = selectedCat.fare;
    final totalFare = baseFare;

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 8),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // 1. Header Row: [← Back] Choose a ride [✓ Route Ready]
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              GestureDetector(
                onTap: () => setState(() => _rideBookingStep = RideBookingStep.findTrip),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                  decoration: BoxDecoration(
                    color: const Color(0xFF1E2433),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.white.withOpacity(0.08)),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: const [
                      Icon(Icons.arrow_back_rounded, size: 14, color: Colors.white),
                      SizedBox(width: 4),
                      Text('Back', style: TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.bold)),
                    ],
                  ),
                ),
              ),
              const Text(
                'Choose a ride',
                style: TextStyle(color: Colors.white, fontSize: 16.5, fontWeight: FontWeight.w900),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: const Color(0xFF3B82F6).withOpacity(0.16),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFF3B82F6).withOpacity(0.4)),
                ),
                child: const Text('Step 2 of 3', style: TextStyle(color: Color(0xFF60A5FA), fontSize: 10.5, fontWeight: FontWeight.w900)),
              ),
            ],
          ),
          const SizedBox(height: 6),

          // Informational Notice: Ride Not Yet Ordered
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.04),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: Colors.white10),
            ),
            child: const Row(
              children: [
                Icon(Icons.info_outline, size: 13, color: Color(0xFF94A3B8)),
                SizedBox(width: 6),
                Expanded(
                  child: Text(
                    'Ride not yet ordered • Compare vehicle options & estimated arrival times below',
                    style: TextStyle(color: Color(0xFF94A3B8), fontSize: 10.5, fontWeight: FontWeight.w500),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),

          // 2. Fare Summary Card
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: const Color(0xFF161C28),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: Colors.white.withOpacity(0.08)),
            ),
            child: Column(
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Estimated Distance', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 11.5)),
                    Text('${finalDist.toStringAsFixed(1)} km ($finalDur mins)', style: const TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.bold)),
                  ],
                ),
                const SizedBox(height: 4),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Base & Distance Fare', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 11.5)),
                    Text('$sym ${baseFare.toStringAsFixed(2)}', style: const TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.bold)),
                  ],
                ),
                Padding(
                  padding: const EdgeInsets.symmetric(vertical: 6),
                  child: Divider(color: Colors.white.withOpacity(0.08), height: 1),
                ),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Total Estimated Fare', style: TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.w900)),
                    Text('$sym ${totalFare.toStringAsFixed(2)}', style: const TextStyle(color: Color(0xFFFFDC00), fontSize: 16, fontWeight: FontWeight.w900)),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 10),

          // 3. Compact Vehicle Selection Boxes (Redesigned - non-bulky vertical list!)
          ConstrainedBox(
            constraints: const BoxConstraints(maxHeight: 220),
            child: ListView.builder(
              shrinkWrap: true,
              itemCount: categories.length,
              itemBuilder: (context, index) {
                final cat = categories[index];
                final isSelected = selectedCat.id == cat.id;

                return GestureDetector(
                  onTap: () {
                    rideProv.selectCategory(cat);
                    setState(() => _selectedTierId = cat.name);
                  },
                  child: Container(
                    margin: const EdgeInsets.only(bottom: 6),
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    decoration: BoxDecoration(
                      color: isSelected ? const Color(0xFFFFDC00).withOpacity(0.08) : const Color(0xFF161C28),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(
                        color: isSelected ? const Color(0xFFFFDC00) : Colors.white.withOpacity(0.08),
                        width: isSelected ? 2.0 : 1.0,
                      ),
                    ),
                    child: Row(
                      children: [
                        // Vehicle Icon Box
                        Container(
                          width: 42,
                          height: 42,
                          decoration: BoxDecoration(
                            color: const Color(0xFF1E2433),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          alignment: Alignment.center,
                          child: Text(cat.icon, style: const TextStyle(fontSize: 22)),
                        ),
                        const SizedBox(width: 12),
                        // Name & Seats & ETA
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Text(
                                    cat.name,
                                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                                  ),
                                  const SizedBox(width: 6),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                                    decoration: BoxDecoration(
                                      color: Colors.white.withOpacity(0.08),
                                      borderRadius: BorderRadius.circular(6),
                                    ),
                                    child: Text(
                                      cat.capacity,
                                      style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 9.5, fontWeight: FontWeight.w600),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 2),
                              Text(
                                'Arrives in ${cat.etaMinutes} mins',
                                style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11),
                              ),
                            ],
                          ),
                        ),
                        // Price
                        Text(
                          '$sym ${cat.fare.toStringAsFixed(2)}',
                          style: TextStyle(
                            color: isSelected ? const Color(0xFFFFDC00) : Colors.white,
                            fontWeight: FontWeight.w900,
                            fontSize: 14.5,
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
          const SizedBox(height: 8),

          // 4. Payment Method Selector Card
          GestureDetector(
            onTap: _openPaymentMethodSheet,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: const Color(0xFF161C28),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: Colors.white.withOpacity(0.08)),
              ),
              child: Row(
                children: [
                  Container(
                    width: 32,
                    height: 32,
                    decoration: BoxDecoration(
                      color: _paymentMethod == 'stripe'
                          ? const Color(0xFF6366F1)
                          : _paymentMethod == 'momo'
                              ? const Color(0xFFFFCC00)
                              : const Color(0xFF10B981),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    alignment: Alignment.center,
                    child: _paymentMethod == 'stripe'
                        ? const Text('S', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 16))
                        : _paymentMethod == 'momo'
                            ? const Text('M', style: TextStyle(color: Colors.black, fontWeight: FontWeight.w900, fontSize: 16))
                            : const Icon(Icons.payments_rounded, color: Colors.white, size: 16),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          _paymentMethod == 'stripe'
                              ? 'Stripe (Cards & Apple Pay)'
                              : _paymentMethod == 'momo'
                                  ? 'MoMo Pay ($_momoNetwork)'
                                  : 'Cash Direct Pay',
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12.5),
                        ),
                        Text(
                          _paymentMethod == 'stripe'
                              ? 'Pre-authorization hold secured by Stripe'
                              : _paymentMethod == 'momo'
                                  ? 'Prompt to ${_momoPhone ?? 'registered number'}'
                                  : 'Pay driver physical cash upon drop-off',
                          style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 10.5),
                        ),
                      ],
                    ),
                  ),
                  const Icon(Icons.chevron_right_rounded, color: Color(0xFF94A3B8), size: 20),
                ],
              ),
            ),
          ),
          const SizedBox(height: 10),

          // 5. Yellow CTA Button: Continue to Confirm →
          SizedBox(
            height: 50,
            child: ElevatedButton(
              onPressed: () => setState(() => _rideBookingStep = RideBookingStep.confirmRide),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFFFFDC00),
                foregroundColor: Colors.black,
                elevation: 4,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: const [
                  Text(
                    'Continue to Confirm',
                    style: TextStyle(
                      color: Colors.black,
                      fontWeight: FontWeight.w900,
                      fontSize: 16,
                      letterSpacing: 0.2,
                    ),
                  ),
                  SizedBox(width: 8),
                  Icon(Icons.arrow_forward_rounded, color: Colors.black, size: 20),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ==========================================
  // RIDE STEP 3: CONFIRM YOUR RIDE (Screenshot 4)
  // ==========================================

  Widget _buildRideConfirmRideStep() {
    final rideProv = Provider.of<RideProvider>(context);
    final countryProv = Provider.of<CountryProvider>(context);
    final sym = countryProv.currencySymbol;
    final categories = rideProv.rideCategories.isNotEmpty
        ? rideProv.rideCategories
        : _fallbackRideCategories;
    final cat = rideProv.selectedCategory ?? (categories.isNotEmpty ? categories.first : _fallbackRideCategories.first);

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 8),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // 1. Header Row: [← Back] Confirm your ride [Step 3 of 3]
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              GestureDetector(
                onTap: () => setState(() => _rideBookingStep = RideBookingStep.chooseRide),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                  decoration: BoxDecoration(
                    color: const Color(0xFF1E2433),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.white.withOpacity(0.08)),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: const [
                      Icon(Icons.arrow_back_rounded, size: 14, color: Colors.white),
                      SizedBox(width: 4),
                      Text('Back', style: TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.bold)),
                    ],
                  ),
                ),
              ),
              const Text(
                'Confirm your ride',
                style: TextStyle(color: Colors.white, fontSize: 16.5, fontWeight: FontWeight.w900),
              ),
              const Text(
                'Step 3 of 3',
                style: TextStyle(color: Color(0xFF94A3B8), fontSize: 11, fontWeight: FontWeight.bold),
              ),
            ],
          ),
          const SizedBox(height: 10),

          // 2. Route & Vehicle Summary Card
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: const Color(0xFF161C28),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: Colors.white.withOpacity(0.08)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Pickup
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      width: 14,
                      height: 14,
                      margin: const EdgeInsets.only(top: 2),
                      decoration: const BoxDecoration(
                        color: Color(0xFF10B981),
                        shape: BoxShape.circle,
                      ),
                      child: const Center(
                        child: Icon(Icons.circle, color: Colors.white, size: 5),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'PICKUP',
                            style: TextStyle(color: Color(0xFF10B981), fontSize: 9.5, fontWeight: FontWeight.w900),
                          ),
                          Text(
                            _pickupController.text.isNotEmpty ? _pickupController.text : 'Current Location',
                            style: const TextStyle(color: Colors.white, fontSize: 12.5, fontWeight: FontWeight.w700),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                Padding(
                  padding: const EdgeInsets.only(left: 6),
                  child: Container(width: 2, height: 12, color: Colors.white12),
                ),
                // Destination
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      width: 14,
                      height: 14,
                      margin: const EdgeInsets.only(top: 2),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(3),
                      ),
                      child: const Center(
                        child: Icon(Icons.square, color: Colors.black, size: 6),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'DESTINATION',
                            style: TextStyle(color: Color(0xFF94A3B8), fontSize: 9.5, fontWeight: FontWeight.w900),
                          ),
                          Text(
                            _dropoffController.text.isNotEmpty ? _dropoffController.text : 'Selected Destination',
                            style: const TextStyle(color: Colors.white, fontSize: 12.5, fontWeight: FontWeight.w700),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                Padding(
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  child: Divider(color: Colors.white.withOpacity(0.08), height: 1),
                ),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('VEHICLE', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 9.5, fontWeight: FontWeight.bold)),
                        Text(cat.name, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 13.5)),
                      ],
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        const Text('TOTAL FARE', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 9.5, fontWeight: FontWeight.bold)),
                        Text('$sym ${cat.fare.toStringAsFixed(2)}', style: const TextStyle(color: Color(0xFFFFDC00), fontWeight: FontWeight.w900, fontSize: 17)),
                      ],
                    ),
                  ],
                ),
                Padding(
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  child: Divider(color: Colors.white.withOpacity(0.08), height: 1),
                ),
                Row(
                  children: [
                    Container(
                      width: 26,
                      height: 26,
                      decoration: BoxDecoration(
                        color: _paymentMethod == 'stripe'
                            ? const Color(0xFF6366F1).withOpacity(0.2)
                            : _paymentMethod == 'momo'
                                ? const Color(0xFFFFDC00).withOpacity(0.2)
                                : const Color(0xFF10B981).withOpacity(0.2),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      alignment: Alignment.center,
                      child: _paymentMethod == 'stripe'
                          ? const Text('S', style: TextStyle(color: Color(0xFF818CF8), fontWeight: FontWeight.w900, fontSize: 13))
                          : _paymentMethod == 'momo'
                              ? const Text('M', style: TextStyle(color: Color(0xFFFFDC00), fontWeight: FontWeight.w900, fontSize: 13))
                              : const Icon(Icons.payments_rounded, color: Color(0xFF34D399), size: 15),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('PAYMENT METHOD', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 9, fontWeight: FontWeight.bold)),
                          Text(
                            _paymentMethod == 'stripe'
                                ? 'Stripe (Cards & Apple Pay)'
                                : _paymentMethod == 'momo'
                                    ? 'MoMo Pay ($_momoNetwork)'
                                    : 'Cash Direct Pay',
                            style: const TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 10),

          // 3. Backup Chauffeur Card (Recommended) - Responsive layout, no overflow
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: const Color(0xFF161C28),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(
                color: _enableBackupChauffeur ? const Color(0xFFFFDC00).withOpacity(0.35) : Colors.white.withOpacity(0.08),
              ),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFFDC00).withOpacity(0.12),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.shield_outlined, color: Color(0xFFFFDC00), size: 20),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Wrap(
                        crossAxisAlignment: WrapCrossAlignment.center,
                        spacing: 6,
                        runSpacing: 3,
                        children: [
                          const Text(
                            'Backup Chauffeur',
                            style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12.5),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1.5),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF59E0B).withOpacity(0.2),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: const Text(
                              'RECOMMENDED',
                              style: TextStyle(color: Color(0xFFF59E0B), fontSize: 8.5, fontWeight: FontWeight.w900),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 2),
                      const Text(
                        "If your assigned chauffeur is unavailable, we'll automatically find another nearby chauffeur.",
                        style: TextStyle(color: Color(0xFF94A3B8), fontSize: 10.5, height: 1.2),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 4),
                Transform.scale(
                  scale: 0.85,
                  child: Switch.adaptive(
                    value: _enableBackupChauffeur,
                    activeColor: const Color(0xFFFFDC00),
                    onChanged: (val) => setState(() => _enableBackupChauffeur = val),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),

          // 4. Yellow CTA Button: Confirm & Dispatch Ride →
          SizedBox(
            height: 52,
            child: ElevatedButton(
              onPressed: _isFinalSubmittingRide ? null : _handleFinalRideBooking,
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFFFFDC00),
                foregroundColor: Colors.black,
                elevation: 4,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
              ),
              child: _isFinalSubmittingRide
                  ? const SizedBox(
                      width: 22,
                      height: 22,
                      child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.black),
                    )
                  : Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: const [
                        Text(
                          'Confirm & Dispatch Ride',
                          style: TextStyle(
                            color: Colors.black,
                            fontWeight: FontWeight.w900,
                            fontSize: 16,
                            letterSpacing: 0.2,
                          ),
                        ),
                        SizedBox(width: 8),
                        Icon(Icons.arrow_forward_rounded, color: Colors.black, size: 20),
                      ],
                    ),
            ),
          ),
          const SizedBox(height: 16),
        ],
      ),
    );
  }

  // ==========================================
  // RENT SECTION: Live Vehicles Catalog from Backend API
  // ==========================================
  Widget _buildRentSection(bool isKeyboardOpen) {
    final countryProv = Provider.of<CountryProvider>(context);
    final sym = countryProv.currencySymbol;
    final isSearchingRent = _rentPickupFocusNode.hasFocus ||
        _rentDropoffFocusNode.hasFocus ||
        ((_activeSearchField == 'rent_pickup' || _activeSearchField == 'rent_dropoff') &&
            (_predictions.isNotEmpty || _isSearchingPlaces));

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 8),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Header Badge
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3.5),
                decoration: BoxDecoration(
                  color: const Color(0xFF3B82F6).withOpacity(0.16),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: const Color(0xFF3B82F6).withOpacity(0.4)),
                ),
                child: const Text(
                  'RIDEMYCARS PREMIUM CAR RENTAL',
                  style: TextStyle(color: Color(0xFF3B82F6), fontWeight: FontWeight.w900, fontSize: 9.5),
                ),
              ),
              const Text('20% deposit online', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 10.5, fontWeight: FontWeight.bold)),
            ],
          ),
          const SizedBox(height: 8),

          // Location & Schedule Card
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: const Color(0xFF161C28),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(
                color: isSearchingRent ? const Color(0xFF3B82F6).withOpacity(0.7) : Colors.white.withOpacity(0.08),
                width: isSearchingRent ? 1.5 : 1.0,
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Toggle: Return to different location
                Row(
                  children: [
                    SizedBox(
                      width: 20,
                      height: 20,
                      child: Checkbox(
                        value: _rentDifferentDropoff,
                        activeColor: const Color(0xFF3B82F6),
                        onChanged: (val) => setState(() => _rentDifferentDropoff = val ?? false),
                      ),
                    ),
                    const SizedBox(width: 8),
                    GestureDetector(
                      onTap: () => setState(() => _rentDifferentDropoff = !_rentDifferentDropoff),
                      child: const Text('Return car to a different location', style: TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
                const SizedBox(height: 6),

                // Pickup Field
                Row(
                  children: [
                    const Icon(Icons.location_on, color: Color(0xFF3B82F6), size: 18),
                    const SizedBox(width: 8),
                    Expanded(
                      child: TextField(
                        controller: _rentPickupController,
                        focusNode: _rentPickupFocusNode,
                        onChanged: (val) => _onQueryChanged(val, field: 'rent_pickup'),
                        style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold),
                        decoration: InputDecoration(
                          hintText: 'Pick-up Location (City, Airport, Address)...',
                          hintStyle: TextStyle(color: Colors.white.withOpacity(0.4), fontSize: 12),
                          border: InputBorder.none,
                          isDense: true,
                        ),
                      ),
                    ),
                    if (_rentPickupController.text.isNotEmpty)
                      GestureDetector(
                        onTap: () {
                          _rentPickupController.clear();
                          setState(() {
                            _predictions = [];
                            _isSearchingPlaces = false;
                          });
                        },
                        child: const Padding(
                          padding: EdgeInsets.symmetric(horizontal: 4),
                          child: Icon(Icons.close_rounded, size: 18, color: Colors.white54),
                        ),
                      ),
                    GestureDetector(
                      onTap: () async {
                        await _getCurrentLocation();
                        if (_userLat != null && _userLng != null) {
                          final addr = await PlacesService.getAddressFromCoordinates(_userLat!, _userLng!);
                          if (mounted && addr != null && addr.isNotEmpty) {
                            setState(() {
                              _rentPickupController.text = addr;
                            });
                          }
                        }
                      },
                      child: const Padding(
                        padding: EdgeInsets.symmetric(horizontal: 4),
                        child: Icon(Icons.my_location_rounded, size: 18, color: Color(0xFF3B82F6)),
                      ),
                    ),
                  ],
                ),

                if (_rentDifferentDropoff) ...[
                  Divider(color: Colors.white.withOpacity(0.08), height: 12),
                  Row(
                    children: [
                      const Icon(Icons.pin_drop, color: Color(0xFFF59E0B), size: 18),
                      const SizedBox(width: 8),
                      Expanded(
                        child: TextField(
                          controller: _rentDropoffController,
                          focusNode: _rentDropoffFocusNode,
                          onChanged: (val) => _onQueryChanged(val, field: 'rent_dropoff'),
                          style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold),
                          decoration: InputDecoration(
                            hintText: 'Drop-off Location (Return city/address)...',
                            hintStyle: TextStyle(color: Colors.white.withOpacity(0.4), fontSize: 12),
                            border: InputBorder.none,
                            isDense: true,
                          ),
                        ),
                      ),
                      if (_rentDropoffController.text.isNotEmpty)
                        GestureDetector(
                          onTap: () {
                            _rentDropoffController.clear();
                            setState(() {
                              _predictions = [];
                              _isSearchingPlaces = false;
                            });
                          },
                          child: const Padding(
                            padding: EdgeInsets.symmetric(horizontal: 4),
                            child: Icon(Icons.close_rounded, size: 18, color: Colors.white54),
                          ),
                        ),
                    ],
                  ),
                ],

                Divider(color: Colors.white.withOpacity(0.08), height: 14),

                // Dates & Times Row
                Row(
                  children: [
                    Expanded(
                      child: GestureDetector(
                        onTap: () async {
                          final d = await showDatePicker(
                            context: context,
                            initialDate: _rentPickupDate,
                            firstDate: DateTime.now(),
                            lastDate: DateTime.now().add(const Duration(days: 365)),
                          );
                          if (d != null && mounted) {
                            final t = await showTimePicker(context: context, initialTime: _rentPickupTime);
                            if (t != null) {
                              setState(() {
                                _rentPickupDate = d;
                                _rentPickupTime = t;
                              });
                            }
                          }
                        },
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                          decoration: BoxDecoration(
                            color: const Color(0xFF1E2433),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('PICKUP DATE', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 9, fontWeight: FontWeight.bold)),
                              Text(
                                '${DateFormat('MMM d').format(_rentPickupDate)} ${_rentPickupTime.format(context)}',
                                style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: GestureDetector(
                        onTap: () async {
                          final d = await showDatePicker(
                            context: context,
                            initialDate: _rentReturnDate,
                            firstDate: _rentPickupDate,
                            lastDate: DateTime.now().add(const Duration(days: 365)),
                          );
                          if (d != null && mounted) {
                            final t = await showTimePicker(context: context, initialTime: _rentReturnTime);
                            if (t != null) {
                              setState(() {
                                _rentReturnDate = d;
                                _rentReturnTime = t;
                              });
                            }
                          }
                        },
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                          decoration: BoxDecoration(
                            color: const Color(0xFF1E2433),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('RETURN DATE', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 9, fontWeight: FontWeight.bold)),
                              Text(
                                '${DateFormat('MMM d').format(_rentReturnDate)} ${_rentReturnTime.format(context)}',
                                style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),

          // Autocomplete Suggestions List for Rent
          if (_activeSearchField == 'rent_pickup' || _activeSearchField == 'rent_dropoff')
            _buildPlacesSuggestionsList(),

          // If searching or keyboard open, show a clean "Done" bar instead of bulky vehicle cards
          if (isSearchingRent) ...[
            Container(
              margin: const EdgeInsets.only(top: 4, bottom: 4),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
              decoration: BoxDecoration(
                color: const Color(0xFF161C28),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.white.withOpacity(0.08)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Select an address above or tap Done',
                    style: TextStyle(color: Color(0xFF94A3B8), fontSize: 11.5),
                  ),
                  GestureDetector(
                    onTap: () {
                      _rentPickupFocusNode.unfocus();
                      _rentDropoffFocusNode.unfocus();
                      FocusScope.of(context).unfocus();
                      setState(() {
                        _predictions = [];
                        _isSearchingPlaces = false;
                      });
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
                      decoration: BoxDecoration(
                        color: const Color(0xFF3B82F6).withOpacity(0.2),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: const [
                          Icon(Icons.check_rounded, size: 14, color: Color(0xFF3B82F6)),
                          SizedBox(width: 4),
                          Text('Done', style: TextStyle(color: Color(0xFF3B82F6), fontWeight: FontWeight.bold, fontSize: 12)),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ] else ...[
            // Category Pills
            SizedBox(
              height: 32,
              child: ListView(
                scrollDirection: Axis.horizontal,
                children: ['All', 'Economy', 'Compact', 'Sedan', 'SUV', 'Luxury', 'Van'].map((cat) {
                  final isSelected = _selectedRentalCategory == cat;
                  return GestureDetector(
                    onTap: () {
                      setState(() {
                        _selectedRentalCategory = cat;
                        _updateSelectedRentalVehicle();
                      });
                      _fetchRentalVehicles(category: cat);
                    },
                    child: Container(
                      margin: const EdgeInsets.only(right: 6),
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      decoration: BoxDecoration(
                        color: isSelected ? const Color(0xFF3B82F6) : const Color(0xFF1E2433),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: isSelected ? const Color(0xFF60A5FA) : Colors.white.withOpacity(0.08)),
                      ),
                      child: Text(
                        cat,
                        style: TextStyle(
                          color: isSelected ? Colors.white : AppColors.textMuted,
                          fontWeight: FontWeight.bold,
                          fontSize: 11.5,
                        ),
                      ),
                    ),
                  );
                }).toList(),
              ),
            ),
            const SizedBox(height: 8),

            // Compact Selection Boxes (Vehicles List)
            Builder(
              builder: (ctx) {
                final displayed = _displayedRentalVehicles;
                return ConstrainedBox(
                  constraints: const BoxConstraints(maxHeight: 180),
                  child: _isLoadingVehicles && displayed.isEmpty
                      ? const Center(child: CircularProgressIndicator(color: Color(0xFF3B82F6)))
                      : displayed.isEmpty
                          ? Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: const Color(0xFF161C28),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Row(
                                children: [
                                  const Icon(Icons.car_rental, color: Color(0xFF3B82F6), size: 24),
                                  const SizedBox(width: 10),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          _selectedRentalCategory == 'All' ? 'Explore Rental Fleet' : 'No $_selectedRentalCategory Cars Available',
                                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                                        ),
                                        Text(
                                          _selectedRentalCategory == 'All' ? 'Sedans, SUVs, and luxury cars available on catalog.' : 'Please select another category or check the catalog.',
                                          style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                            )
                          : ListView.builder(
                              shrinkWrap: true,
                              itemCount: displayed.length.clamp(0, 4),
                              itemBuilder: (context, idx) {
                                final v = displayed[idx];
                                final isSelected = _selectedRentalVehicle?.id == v.id;
                                return GestureDetector(
                                  onTap: () => setState(() => _selectedRentalVehicle = v),
                                  child: Container(
                                    margin: const EdgeInsets.only(bottom: 6),
                                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                    decoration: BoxDecoration(
                                      color: isSelected ? const Color(0xFF3B82F6).withOpacity(0.08) : const Color(0xFF161C28),
                                      borderRadius: BorderRadius.circular(14),
                                      border: Border.all(
                                        color: isSelected ? const Color(0xFF60A5FA) : Colors.white.withOpacity(0.08),
                                        width: isSelected ? 2 : 1,
                                      ),
                                    ),
                                    child: Row(
                                      children: [
                                        Container(
                                          width: 44,
                                          height: 44,
                                          decoration: BoxDecoration(
                                            color: const Color(0xFF1E2433),
                                            borderRadius: BorderRadius.circular(10),
                                          ),
                                          child: const Center(child: Icon(Icons.directions_car, color: Color(0xFF3B82F6))),
                                        ),
                                        const SizedBox(width: 10),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(v.fullName, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13)),
                                              Text(
                                                '${v.seats} seats • ${v.transmission} • 20% deposit ($sym${(v.dailyRate * 0.2).toStringAsFixed(0)})',
                                                style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 10.5),
                                              ),
                                            ],
                                          ),
                                        ),
                                        Text(
                                          '$sym${v.dailyRate.toStringAsFixed(0)}/day',
                                          style: TextStyle(
                                            color: isSelected ? const Color(0xFF60A5FA) : Colors.white,
                                            fontWeight: FontWeight.w900,
                                            fontSize: 14,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                );
                              },
                            ),
                );
              },
            ),
            const SizedBox(height: 8),

            // Rental CTA Button
            SizedBox(
              height: 50,
              child: ElevatedButton(
                onPressed: () {
                  final pickup = _rentPickupController.text.trim();
                  final dropoff = _rentDifferentDropoff ? _rentDropoffController.text.trim() : pickup;
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
                  } else {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => RentalCatalogScreen(
                          initialPickupLocation: pickup.isNotEmpty ? pickup : null,
                          initialDropoffLocation: dropoff.isNotEmpty ? dropoff : null,
                        ),
                      ),
                    );
                  }
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF3B82F6),
                  foregroundColor: Colors.white,
                  elevation: 4,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Flexible(
                      child: Text(
                        _selectedRentalVehicle != null
                            ? 'Rent ${_selectedRentalVehicle!.fullName} →'
                            : 'Search Available Rentals →',
                        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 15),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  // ==========================================
  // DRIVER SECTION: MATCHING book-driver.blade.php
  // ==========================================

  

  Widget _buildDriverSection(bool isKeyboardOpen) {
    final countryProv = Provider.of<CountryProvider>(context);
    final sym = countryProv.currencySymbol;
    final isSearchingDriver = _driverPickupFocusNode.hasFocus ||
        _driverDropoffFocusNode.hasFocus ||
        ((_activeSearchField == 'driver_pickup' || _activeSearchField == 'driver_dropoff') &&
            (_predictions.isNotEmpty || _isSearchingPlaces));

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 8),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Header Badge
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3.5),
                decoration: BoxDecoration(
                  color: const Color(0xFF10B981).withOpacity(0.16),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: const Color(0xFF10B981).withOpacity(0.4)),
                ),
                child: const Text(
                  'RIDEMYCARS DRIVER HIRING',
                  style: TextStyle(color: Color(0xFF10B981), fontWeight: FontWeight.w900, fontSize: 9.5),
                ),
              ),
              const Text('Verified & Insured', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 10.5, fontWeight: FontWeight.bold)),
            ],
          ),
          const SizedBox(height: 8),

          // Service Type Tabs
          SizedBox(
            height: 32,
            child: ListView(
              scrollDirection: Axis.horizontal,
              children: ['Hire Driver', 'Outstation Trip', 'City Run', 'Valet Service'].map((srv) {
                final isSelected = _driverServiceType == srv;
                return GestureDetector(
                  onTap: () => setState(() => _driverServiceType = srv),
                  child: Container(
                    margin: const EdgeInsets.only(right: 6),
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: isSelected ? const Color(0xFF10B981) : const Color(0xFF1E2433),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: isSelected ? const Color(0xFF34D399) : Colors.white.withOpacity(0.08)),
                    ),
                    child: Text(
                      srv,
                      style: TextStyle(
                        color: isSelected ? Colors.white : AppColors.textMuted,
                        fontWeight: FontWeight.bold,
                        fontSize: 11.5,
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),
          ),
          const SizedBox(height: 8),

          // Location & Vehicle Info Card
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: const Color(0xFF161C28),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(
                color: isSearchingDriver ? const Color(0xFF10B981).withOpacity(0.7) : Colors.white.withOpacity(0.08),
                width: isSearchingDriver ? 1.5 : 1.0,
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Pickup
                Row(
                  children: [
                    const Icon(Icons.location_on, color: Color(0xFF10B981), size: 18),
                    const SizedBox(width: 8),
                    Expanded(
                      child: TextField(
                        controller: _driverPickupController,
                        focusNode: _driverPickupFocusNode,
                        onChanged: (val) => _onQueryChanged(val, field: 'driver_pickup'),
                        style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold),
                        decoration: InputDecoration(
                          hintText: 'Pickup Address (Where driver meets you)...',
                          hintStyle: TextStyle(color: Colors.white.withOpacity(0.4), fontSize: 12),
                          border: InputBorder.none,
                          isDense: true,
                        ),
                      ),
                    ),
                    if (_driverPickupController.text.isNotEmpty)
                      GestureDetector(
                        onTap: () {
                          _driverPickupController.clear();
                          setState(() {
                            _predictions = [];
                            _isSearchingPlaces = false;
                          });
                        },
                        child: const Padding(
                          padding: EdgeInsets.symmetric(horizontal: 4),
                          child: Icon(Icons.close_rounded, size: 18, color: Colors.white54),
                        ),
                      ),
                    GestureDetector(
                      onTap: () async {
                        await _getCurrentLocation();
                        if (_userLat != null && _userLng != null) {
                          final addr = await PlacesService.getAddressFromCoordinates(_userLat!, _userLng!);
                          if (mounted && addr != null && addr.isNotEmpty) {
                            setState(() {
                              _driverPickupController.text = addr;
                            });
                          }
                        }
                      },
                      child: const Padding(
                        padding: EdgeInsets.symmetric(horizontal: 4),
                        child: Icon(Icons.my_location_rounded, size: 18, color: Color(0xFF10B981)),
                      ),
                    ),
                  ],
                ),
                Divider(color: Colors.white.withOpacity(0.08), height: 12),
                // Dropoff / Destination
                Row(
                  children: [
                    const Icon(Icons.pin_drop, color: Color(0xFF3B82F6), size: 18),
                    const SizedBox(width: 8),
                    Expanded(
                      child: TextField(
                        controller: _driverDropoffController,
                        focusNode: _driverDropoffFocusNode,
                        onChanged: (val) => _onQueryChanged(val, field: 'driver_dropoff'),
                        style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold),
                        decoration: InputDecoration(
                          hintText: 'Destination (Optional if hourly)...',
                          hintStyle: TextStyle(color: Colors.white.withOpacity(0.4), fontSize: 12),
                          border: InputBorder.none,
                          isDense: true,
                        ),
                      ),
                    ),
                    if (_driverDropoffController.text.isNotEmpty)
                      GestureDetector(
                        onTap: () {
                          _driverDropoffController.clear();
                          setState(() {
                            _predictions = [];
                            _isSearchingPlaces = false;
                          });
                        },
                        child: const Padding(
                          padding: EdgeInsets.symmetric(horizontal: 4),
                          child: Icon(Icons.close_rounded, size: 18, color: Colors.white54),
                        ),
                      ),
                  ],
                ),
                Divider(color: Colors.white.withOpacity(0.08), height: 12),

                // Vehicle Info & Duration Row
                Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('YOUR CAR MODEL', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 9, fontWeight: FontWeight.bold)),
                          TextField(
                            controller: _driverCarMakeModelController,
                            style: const TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.bold),
                            decoration: const InputDecoration(
                              hintText: 'e.g. Toyota Camry',
                              border: InputBorder.none,
                              isDense: true,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('TRANSMISSION', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 9, fontWeight: FontWeight.bold)),
                          DropdownButton<String>(
                            value: _driverTransmission,
                            dropdownColor: const Color(0xFF1E2433),
                            isDense: true,
                            underline: const SizedBox(),
                            style: const TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.bold),
                            items: const [
                              DropdownMenuItem(value: 'automatic', child: Text('Automatic')),
                              DropdownMenuItem(value: 'manual', child: Text('Manual')),
                            ],
                            onChanged: (val) => setState(() => _driverTransmission = val ?? 'automatic'),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),

          // Autocomplete Suggestions List for Driver
          if (_activeSearchField == 'driver_pickup' || _activeSearchField == 'driver_dropoff')
            _buildPlacesSuggestionsList(),

          // If searching or keyboard open, show a clean "Done" bar instead of bulky driver cards
          if (isSearchingDriver) ...[
            Container(
              margin: const EdgeInsets.only(top: 4, bottom: 4),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
              decoration: BoxDecoration(
                color: const Color(0xFF161C28),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.white.withOpacity(0.08)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Select an address above or tap Done',
                    style: TextStyle(color: Color(0xFF94A3B8), fontSize: 11.5),
                  ),
                  GestureDetector(
                    onTap: () {
                      _driverPickupFocusNode.unfocus();
                      _driverDropoffFocusNode.unfocus();
                      FocusScope.of(context).unfocus();
                      setState(() {
                        _predictions = [];
                        _isSearchingPlaces = false;
                      });
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
                      decoration: BoxDecoration(
                        color: const Color(0xFF10B981).withOpacity(0.2),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: const [
                          Icon(Icons.check_rounded, size: 14, color: Color(0xFF10B981)),
                          SizedBox(width: 4),
                          Text('Done', style: TextStyle(color: Color(0xFF10B981), fontWeight: FontWeight.bold, fontSize: 12)),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ] else ...[
            // Compact Selection Boxes (Drivers List)
            ConstrainedBox(
              constraints: const BoxConstraints(maxHeight: 180),
              child: _isLoadingDrivers
                  ? const Center(child: CircularProgressIndicator(color: Color(0xFF10B981)))
                  : _drivers.isEmpty
                      ? Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: const Color(0xFF161C28),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.person_pin_circle_rounded, color: Color(0xFF10B981), size: 24),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: const [
                                    Text('Hire Verified Chauffeur', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13)),
                                    Text('Licensed, vetted personal drivers for your car.', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 11)),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        )
                      : ListView.builder(
                          shrinkWrap: true,
                          itemCount: _drivers.length.clamp(0, 4),
                          itemBuilder: (context, idx) {
                            final d = _drivers[idx];
                            final isSelected = _selectedDriver?.id == d.id;
                            return GestureDetector(
                              onTap: () => setState(() => _selectedDriver = d),
                              child: Container(
                                margin: const EdgeInsets.only(bottom: 6),
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                decoration: BoxDecoration(
                                  color: isSelected ? const Color(0xFF10B981).withOpacity(0.08) : const Color(0xFF161C28),
                                  borderRadius: BorderRadius.circular(14),
                                  border: Border.all(
                                    color: isSelected ? const Color(0xFF34D399) : Colors.white.withOpacity(0.08),
                                    width: isSelected ? 2 : 1,
                                  ),
                                ),
                                child: Row(
                                  children: [
                                    CircleAvatar(
                                      radius: 18,
                                      backgroundColor: const Color(0xFF10B981),
                                      backgroundImage: d.avatarUrl != null && d.avatarUrl!.isNotEmpty
                                          ? NetworkImage(d.avatarUrl!)
                                          : null,
                                      child: (d.avatarUrl == null || d.avatarUrl!.isEmpty)
                                          ? Text(d.name.isNotEmpty ? d.name[0] : 'D', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold))
                                          : null,
                                    ),
                                    const SizedBox(width: 10),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(d.name, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13)),
                                          Text('⭐ ${d.rating.toStringAsFixed(1)} • ${d.totalTrips} trips • Professional', style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 10.5)),
                                        ],
                                      ),
                                    ),
                                    Text(
                                      '$sym${d.hourlyRate.toStringAsFixed(0)}/hr',
                                      style: TextStyle(
                                        color: isSelected ? const Color(0xFF34D399) : Colors.white,
                                        fontWeight: FontWeight.w900,
                                        fontSize: 14,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
            ),
            const SizedBox(height: 8),

            // Driver CTA Button
            SizedBox(
              height: 50,
              child: ElevatedButton(
                onPressed: () {
                  final pickup = _driverPickupController.text.trim();
                  final dropoff = _driverDropoffController.text.trim();
                  if (_selectedDriver != null) {
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
                  } else {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => HireDriverCatalogScreen(
                          initialPickupLocation: pickup.isNotEmpty ? pickup : null,
                          initialDropoffLocation: dropoff.isNotEmpty ? dropoff : null,
                        ),
                      ),
                    );
                  }
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF10B981),
                  foregroundColor: Colors.white,
                  elevation: 4,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Flexible(
                      child: Text(
                        _selectedDriver != null ? 'Hire ${_selectedDriver!.name} →' : 'Book Personal Chauffeur →',
                        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 15),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  // ==========================================
  // DELIVERY SECTION: MATCHING delivery.blade.php
  // ==========================================

  

  Widget _buildDeliverySection(bool isKeyboardOpen) {
    final countryProv = Provider.of<CountryProvider>(context);
    final sym = countryProv.currencySymbol;
    final isSearchingDelivery = _deliveryPickupFocusNode.hasFocus ||
        _deliveryDropoffFocusNode.hasFocus ||
        ((_activeSearchField == 'delivery_pickup' || _activeSearchField == 'delivery_dropoff') &&
            (_predictions.isNotEmpty || _isSearchingPlaces));

    final deliveryTiers = [
      {'id': 'Envelope', 'title': 'Envelope / Docs', 'icon': '✉️', 'weight': '< 1 kg', 'base': 8.0},
      {'id': 'Small', 'title': 'Small Box', 'icon': '📦', 'weight': '< 5 kg', 'base': 15.0},
      {'id': 'Medium', 'title': 'Medium Box', 'icon': '🍱', 'weight': '< 15 kg', 'base': 25.0},
      {'id': 'Heavy', 'title': 'Bulk Cargo', 'icon': '🚚', 'weight': '< 50 kg', 'base': 45.0},
    ];

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 8),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Header Badge
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3.5),
                decoration: BoxDecoration(
                  color: const Color(0xFFA855F7).withOpacity(0.16),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: const Color(0xFFA855F7).withOpacity(0.4)),
                ),
                child: const Text(
                  'RIDEMYCARS PARCEL DISPATCH',
                  style: TextStyle(color: Color(0xFFA855F7), fontWeight: FontWeight.w900, fontSize: 9.5),
                ),
              ),
              const Text('Express Door-to-Door', style: TextStyle(color: Color(0xFF94A3B8), fontSize: 10.5, fontWeight: FontWeight.bold)),
            ],
          ),
          const SizedBox(height: 8),

          // Speed Selector
          Row(
            children: [
              Expanded(
                child: GestureDetector(
                  onTap: () => setState(() => _deliverySpeed = 'express'),
                  child: Container(
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    decoration: BoxDecoration(
                      color: _deliverySpeed == 'express' ? const Color(0xFFA855F7) : const Color(0xFF1E2433),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    alignment: Alignment.center,
                    child: Text(
                      '⚡ Express (<2h)',
                      style: TextStyle(
                        color: _deliverySpeed == 'express' ? Colors.white : Colors.white70,
                        fontWeight: FontWeight.bold,
                        fontSize: 11.5,
                      ),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: GestureDetector(
                  onTap: () => setState(() => _deliverySpeed = 'standard'),
                  child: Container(
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    decoration: BoxDecoration(
                      color: _deliverySpeed == 'standard' ? const Color(0xFFA855F7) : const Color(0xFF1E2433),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    alignment: Alignment.center,
                    child: Text(
                      'Standard (Same Day)',
                      style: TextStyle(
                        color: _deliverySpeed == 'standard' ? Colors.white : Colors.white70,
                        fontWeight: FontWeight.bold,
                        fontSize: 11.5,
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),

          // Route & Contact Card
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: const Color(0xFF161C28),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(
                color: isSearchingDelivery ? const Color(0xFFA855F7).withOpacity(0.7) : Colors.white.withOpacity(0.08),
                width: isSearchingDelivery ? 1.5 : 1.0,
              ),
            ),
            child: Column(
              children: [
                Row(
                  children: [
                    const Icon(Icons.outbox_rounded, color: Color(0xFFA855F7), size: 18),
                    const SizedBox(width: 8),
                    Expanded(
                      child: TextField(
                        controller: _pickupController,
                        focusNode: _deliveryPickupFocusNode,
                        onChanged: (val) => _onQueryChanged(val, field: 'delivery_pickup'),
                        style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold),
                        decoration: InputDecoration(
                          hintText: 'Pickup address (Sender location)...',
                          hintStyle: TextStyle(color: Colors.white.withOpacity(0.4), fontSize: 12),
                          border: InputBorder.none,
                          isDense: true,
                        ),
                      ),
                    ),
                    if (_pickupController.text.isNotEmpty)
                      GestureDetector(
                        onTap: () {
                          _pickupController.clear();
                          setState(() {
                            _predictions = [];
                            _isSearchingPlaces = false;
                          });
                        },
                        child: const Padding(
                          padding: EdgeInsets.symmetric(horizontal: 4),
                          child: Icon(Icons.close_rounded, size: 18, color: Colors.white54),
                        ),
                      ),
                    GestureDetector(
                      onTap: () async {
                        await _getCurrentLocation();
                        if (_userLat != null && _userLng != null) {
                          final addr = await PlacesService.getAddressFromCoordinates(_userLat!, _userLng!);
                          if (mounted && addr != null && addr.isNotEmpty) {
                            setState(() {
                              _pickupController.text = addr;
                            });
                          }
                        }
                      },
                      child: const Padding(
                        padding: EdgeInsets.symmetric(horizontal: 4),
                        child: Icon(Icons.my_location_rounded, size: 18, color: Color(0xFFA855F7)),
                      ),
                    ),
                  ],
                ),
                Divider(color: Colors.white.withOpacity(0.08), height: 12),
                Row(
                  children: [
                    const Icon(Icons.move_to_inbox_rounded, color: Color(0xFF10B981), size: 18),
                    const SizedBox(width: 8),
                    Expanded(
                      child: TextField(
                        controller: _dropoffController,
                        focusNode: _deliveryDropoffFocusNode,
                        onChanged: (val) => _onQueryChanged(val, field: 'delivery_dropoff'),
                        style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold),
                        decoration: InputDecoration(
                          hintText: 'Delivery address (Recipient location)...',
                          hintStyle: TextStyle(color: Colors.white.withOpacity(0.4), fontSize: 12),
                          border: InputBorder.none,
                          isDense: true,
                        ),
                      ),
                    ),
                    if (_dropoffController.text.isNotEmpty)
                      GestureDetector(
                        onTap: () {
                          _dropoffController.clear();
                          setState(() {
                            _predictions = [];
                            _isSearchingPlaces = false;
                          });
                        },
                        child: const Padding(
                          padding: EdgeInsets.symmetric(horizontal: 4),
                          child: Icon(Icons.close_rounded, size: 18, color: Colors.white54),
                        ),
                      ),
                  ],
                ),
                Divider(color: Colors.white.withOpacity(0.08), height: 12),
                Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: _deliveryRecipientNameController,
                        style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                        decoration: InputDecoration(
                          hintText: 'Recipient Name...',
                          hintStyle: TextStyle(color: Colors.white.withOpacity(0.4), fontSize: 11.5),
                          border: InputBorder.none,
                          isDense: true,
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: TextField(
                        controller: _deliveryRecipientPhoneController,
                        keyboardType: TextInputType.phone,
                        style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                        decoration: InputDecoration(
                          hintText: 'Recipient Phone...',
                          hintStyle: TextStyle(color: Colors.white.withOpacity(0.4), fontSize: 11.5),
                          border: InputBorder.none,
                          isDense: true,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),

          // Autocomplete Suggestions List for Delivery
          if (_activeSearchField == 'delivery_pickup' || _activeSearchField == 'delivery_dropoff')
            _buildPlacesSuggestionsList(),

          // If searching or keyboard open, show a clean "Done" bar instead of bulky tier cards
          if (isSearchingDelivery) ...[
            Container(
              margin: const EdgeInsets.only(top: 4, bottom: 4),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
              decoration: BoxDecoration(
                color: const Color(0xFF161C28),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.white.withOpacity(0.08)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Select an address above or tap Done',
                    style: TextStyle(color: Color(0xFF94A3B8), fontSize: 11.5),
                  ),
                  GestureDetector(
                    onTap: () {
                      _deliveryPickupFocusNode.unfocus();
                      _deliveryDropoffFocusNode.unfocus();
                      FocusScope.of(context).unfocus();
                      setState(() {
                        _predictions = [];
                        _isSearchingPlaces = false;
                      });
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
                      decoration: BoxDecoration(
                        color: const Color(0xFFA855F7).withOpacity(0.2),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: const [
                          Icon(Icons.check_rounded, size: 14, color: Color(0xFFA855F7)),
                          SizedBox(width: 4),
                          Text('Done', style: TextStyle(color: Color(0xFFA855F7), fontWeight: FontWeight.bold, fontSize: 12)),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ] else ...[
            // Compact Selection Boxes (Delivery Tiers)
            Row(
              children: deliveryTiers.map((tier) {
                final isSelected = _selectedTierId == tier['id'];
                final price = (tier['base'] as double) * (_deliverySpeed == 'express' ? 1.3 : 1.0);
                return Expanded(
                  child: GestureDetector(
                    onTap: () => setState(() => _selectedTierId = tier['id'] as String),
                    child: Container(
                      margin: const EdgeInsets.symmetric(horizontal: 3),
                      padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 4),
                      decoration: BoxDecoration(
                        color: isSelected ? const Color(0xFFA855F7).withOpacity(0.08) : const Color(0xFF161C28),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: isSelected ? const Color(0xFFC084FC) : Colors.white.withOpacity(0.08),
                          width: isSelected ? 2.0 : 1.0,
                        ),
                      ),
                      child: Column(
                        children: [
                          Text(tier['icon'] as String, style: const TextStyle(fontSize: 18)),
                          const SizedBox(height: 2),
                          Text(
                            tier['id'] as String,
                            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 11),
                          ),
                          Text(
                            tier['weight'] as String,
                            style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 9.5),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            '$sym${price.toStringAsFixed(0)}',
                            style: TextStyle(
                              color: isSelected ? const Color(0xFFC084FC) : Colors.white,
                              fontWeight: FontWeight.w900,
                              fontSize: 12,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),
            const SizedBox(height: 8),

            // Parcel CTA Button
            SizedBox(
              height: 50,
              child: ElevatedButton(
                onPressed: () {
                  final pickup = _pickupController.text.trim();
                  final dropoff = _dropoffController.text.trim();
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
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFFA855F7),
                  foregroundColor: Colors.white,
                  elevation: 4,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: const [
                    Flexible(
                      child: Text(
                        'Continue to Package Details →',
                        style: TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 15),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ],
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
                    leading: const Icon(Icons.emergency_rounded, color: AppColors.danger),
                    title: const Text('SOS (Emergency Contacts)', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w600)),
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(context, MaterialPageRoute(builder: (_) => const SosContactsScreen()));
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
              onTap: () => _handleLogout(context, auth),
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

  Future<void> _handleLogout(BuildContext drawerContext, AuthProvider auth) async {
    final confirm = await showDialog<bool>(
      context: drawerContext,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppColors.surfaceDark,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.logout_rounded, color: AppColors.danger, size: 24),
            SizedBox(width: 10),
            Text('Log Out', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 18)),
          ],
        ),
        content: const Text(
          'Are you sure you want to log out of your account?',
          style: TextStyle(color: AppColors.textMuted, fontSize: 14),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Cancel', style: TextStyle(color: AppColors.textLight)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.danger,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Log Out', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );

    if (confirm != true || !mounted) return;

    try {
      Navigator.of(context).pop();
    } catch (_) {}

    await auth.logout();

    if (!mounted) return;

    Navigator.of(context, rootNavigator: true).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const LoginScreen()),
      (route) => false,
    );
  }
}
