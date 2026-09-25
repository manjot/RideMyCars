import 'dart:async';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../core/api/api_client.dart';
import '../core/constants/api_constants.dart';
import '../models/ride_category_model.dart';
import '../services/ride_service.dart';

class RideProvider extends ChangeNotifier {
  final Dio _dio = ApiClient().dio;

  Map<String, dynamic>? _activeRide;
  bool _isBooking = false;
  String? _errorMessage;
  String _selectedVehicle = 'standard';
  bool _backupChauffeurEnabled = false;
  Timer? _ridePollTimer;

  // Dynamic Categories & Dynamic Pricing
  List<RideCategoryModel> _rideCategories = [];
  RideCategoryModel? _selectedCategory;
  double? _estimatedDistanceKm;
  int? _estimatedDurationMinutes;
  String _currencySymbol = '\$';
  String _currencyCode = 'USD';
  Map<String, dynamic>? _surgeInfo;
  bool _isCalculatingFares = false;
  bool _isLoadingCategories = false;

  Map<String, dynamic>? get activeRide => _activeRide;
  bool get isBooking => _isBooking;
  String? get errorMessage => _errorMessage;
  String get selectedVehicle => _selectedVehicle;
  bool get backupChauffeurEnabled => _backupChauffeurEnabled;

  List<RideCategoryModel> get rideCategories => _rideCategories;
  RideCategoryModel? get selectedCategory => _selectedCategory;
  double? get estimatedDistanceKm => _estimatedDistanceKm;
  int? get estimatedDurationMinutes => _estimatedDurationMinutes;
  String get currencySymbol => _currencySymbol;
  String get currencyCode => _currencyCode;
  Map<String, dynamic>? get surgeInfo => _surgeInfo;
  bool get isCalculatingFares => _isCalculatingFares;
  bool get isLoadingCategories => _isLoadingCategories;

  void setSelectedVehicle(String v) {
    _selectedVehicle = v;
    final match = _rideCategories.firstWhere(
      (c) => c.slug.toLowerCase() == v.toLowerCase() || c.name.toLowerCase() == v.toLowerCase(),
      orElse: () => _rideCategories.isNotEmpty ? _rideCategories.first : _defaultFallbackCategory,
    );
    _selectedCategory = match;
    notifyListeners();
  }

  void selectCategory(RideCategoryModel cat) {
    _selectedCategory = cat;
    _selectedVehicle = cat.slug;
    notifyListeners();
  }

  void setBackupChauffeurEnabled(bool val) {
    _backupChauffeurEnabled = val;
    notifyListeners();
  }

  /// Fetch vehicle categories matching active country
  Future<void> fetchCategories({String? country}) async {
    _isLoadingCategories = true;
    notifyListeners();

    try {
      final cats = await RideService.getCategories(country: country);
      if (cats.isNotEmpty) {
        _rideCategories = cats;
        // Keep current selected if valid, else pick first
        final currentSlug = _selectedCategory?.slug ?? _selectedVehicle;
        _selectedCategory = _rideCategories.firstWhere(
          (c) => c.slug.toLowerCase() == currentSlug.toLowerCase() || c.name.toLowerCase() == currentSlug.toLowerCase(),
          orElse: () => _rideCategories.first,
        );
        _selectedVehicle = _selectedCategory!.slug;
      }
    } catch (e) {
      debugPrint('Error fetching categories in provider: $e');
    } finally {
      _isLoadingCategories = false;
      notifyListeners();
    }
  }

  /// Dynamically recalculate prices for all categories when route changes
  Future<void> calculateDynamicFares({
    double? pickupLat,
    double? pickupLng,
    double? dropoffLat,
    double? dropoffLng,
    double? distanceKm,
    int? durationMinutes,
    int stopsCount = 0,
    String? country,
  }) async {
    _isCalculatingFares = true;
    notifyListeners();

    try {
      final result = await RideService.calculatePrice(
        pickupLat: pickupLat,
        pickupLng: pickupLng,
        dropoffLat: dropoffLat,
        dropoffLng: dropoffLng,
        distanceKm: distanceKm,
        durationMinutes: durationMinutes,
        stopsCount: stopsCount,
        country: country,
      );

      if (result != null && result['categories'] is List<RideCategoryModel>) {
        final List<RideCategoryModel> updated = result['categories'];
        if (updated.isNotEmpty) {
          _rideCategories = updated;
          _estimatedDistanceKm = result['distance_km'] as double?;
          _estimatedDurationMinutes = result['duration_minutes'] as int?;
          _currencySymbol = result['currency_symbol'] as String? ?? _currencySymbol;
          _currencyCode = result['currency_code'] as String? ?? _currencyCode;
          _surgeInfo = result['surge_info'] as Map<String, dynamic>?;

          // Preserve selected tier or fallback to first
          final currentSlug = _selectedCategory?.slug ?? _selectedVehicle;
          _selectedCategory = _rideCategories.firstWhere(
            (c) => c.slug.toLowerCase() == currentSlug.toLowerCase() || c.name.toLowerCase() == currentSlug.toLowerCase(),
            orElse: () => _rideCategories.first,
          );
          _selectedVehicle = _selectedCategory!.slug;
        }
      }
    } catch (e) {
      debugPrint('Error updating dynamic fares: $e');
    } finally {
      _isCalculatingFares = false;
      notifyListeners();
    }
  }

  void startActiveRidePolling() {
    fetchActiveRide();
    _ridePollTimer?.cancel();
    _ridePollTimer = Timer.periodic(const Duration(seconds: 4), (_) {
      fetchActiveRide();
    });
  }

  void stopActiveRidePolling() {
    _ridePollTimer?.cancel();
  }

  Future<void> fetchActiveRide() async {
    try {
      final res = await _dio.get(ApiConstants.activeRide);
      if (res.statusCode == 200 && res.data['success'] == true) {
        final r = res.data['ride'];
        _activeRide = r != null ? Map<String, dynamic>.from(r) : null;
        notifyListeners();
      }
    } catch (e) {
      debugPrint('Error fetching active ride: $e');
    }
  }

  Future<bool> bookRide({
    required String pickupLocation,
    required String dropoffLocation,
    double? pickupLat,
    double? pickupLng,
    double? dropoffLat,
    double? dropoffLng,
    double? distanceKm,
    int? durationMinutes,
    String paymentMethod = 'cash',
    bool? backupChauffeurEnabled,
    String? country,
  }) async {
    _isBooking = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final actualDist = distanceKm ?? _estimatedDistanceKm ?? 10.0;
      final actualDur = durationMinutes ?? _estimatedDurationMinutes ?? 15;
      final actualVehicleType = _selectedCategory?.name ?? _selectedCategory?.slug ?? _selectedVehicle;

      final res = await _dio.post(ApiConstants.rides, data: {
        'pickup_location': pickupLocation,
        'dropoff_location': dropoffLocation,
        'pickup_lat': pickupLat,
        'pickup_lng': pickupLng,
        'dropoff_lat': dropoffLat,
        'dropoff_lng': dropoffLng,
        'vehicle_type': actualVehicleType,
        'payment_method': paymentMethod,
        'distance_km': actualDist,
        'duration_minutes': actualDur,
        'backup_chauffeur_enabled': backupChauffeurEnabled ?? _backupChauffeurEnabled,
        if (country != null) 'country': country,
      });

      if ((res.statusCode == 200 || res.statusCode == 201) && res.data['success'] == true) {
        _activeRide = Map<String, dynamic>.from(res.data['ride']);
        startActiveRidePolling();
        _isBooking = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = res.data['message'] ?? 'Failed to request ride.';
      }
    } on DioException catch (e) {
      if (e.response?.data is Map && e.response?.data['message'] != null) {
        _errorMessage = e.response!.data['message'].toString();
      } else if (e.response?.statusCode == 401) {
        _errorMessage = 'Session expired. Please sign in to request a ride.';
      } else if (e.type == DioExceptionType.connectionTimeout || e.type == DioExceptionType.connectionError) {
        _errorMessage = 'Network connection issue. Please retry.';
      } else if (e.response?.statusCode == 301 || e.response?.statusCode == 302 || e.response?.statusCode == 308) {
        _errorMessage = 'Connecting to server. Please tap Request Ride again.';
      } else {
        _errorMessage = 'Unable to request ride (${e.response?.statusCode ?? 'Connection Error'}). Please try again.';
      }
      debugPrint('Error booking ride: $e');
    } catch (e) {
      _errorMessage = 'An unexpected error occurred.';
      debugPrint('Error booking ride: $e');
    }

    _isBooking = false;
    notifyListeners();
    return false;
  }

  Future<bool> confirmBackupDriver(int rideId) async {
    try {
      final res = await _dio.post(ApiConstants.rideBackupConfirm(rideId));
      if (res.statusCode == 200 && res.data['success'] == true) {
        await fetchActiveRide();
        return true;
      }
    } catch (e) {
      debugPrint('Error confirming backup chauffeur: $e');
    }
    return false;
  }

  Future<bool> declineBackupDriver(int rideId) async {
    try {
      final res = await _dio.post(ApiConstants.rideBackupDecline(rideId));
      if (res.statusCode == 200 && res.data['success'] == true) {
        await fetchActiveRide();
        return true;
      }
    } catch (e) {
      debugPrint('Error declining backup chauffeur: $e');
    }
    return false;
  }

  Future<bool> cancelRide(int rideId) async {
    try {
      final res = await _dio.post(ApiConstants.rideCancel(rideId));
      if (res.statusCode == 200 && res.data['success'] == true) {
        _activeRide = null;
        notifyListeners();
        return true;
      }
    } catch (e) {
      debugPrint('Error cancelling ride: $e');
    }
    return false;
  }

  RideCategoryModel get _defaultFallbackCategory {
    return const RideCategoryModel(
      id: 'economy',
      slug: 'economy',
      categoryKey: 'economy',
      name: 'Economy',
      icon: '🚗',
      capacity: '1–4 seats',
      fare: 25.0,
      fareFormatted: 'GH₵25.00',
    );
  }

  @override
  void dispose() {
    stopActiveRidePolling();
    super.dispose();
  }
}
