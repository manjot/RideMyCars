import 'dart:async';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../core/api/api_client.dart';
import '../core/constants/api_constants.dart';

class RideProvider extends ChangeNotifier {
  final Dio _dio = ApiClient().dio;

  Map<String, dynamic>? _activeRide;
  bool _isBooking = false;
  String _selectedVehicle = 'Standard';
  Timer? _ridePollTimer;

  Map<String, dynamic>? get activeRide => _activeRide;
  bool get isBooking => _isBooking;
  String get selectedVehicle => _selectedVehicle;

  void setSelectedVehicle(String v) {
    _selectedVehicle = v;
    notifyListeners();
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
  }) async {
    _isBooking = true;
    notifyListeners();

    try {
      final res = await _dio.post(ApiConstants.rides, data: {
        'pickup_location': pickupLocation,
        'dropoff_location': dropoffLocation,
        'pickup_lat': pickupLat,
        'pickup_lng': pickupLng,
        'dropoff_lat': dropoffLat,
        'dropoff_lng': dropoffLng,
        'vehicle_type': _selectedVehicle,
        'payment_method': paymentMethod,
        'distance_km': distanceKm ?? 10.0,
        'duration_minutes': durationMinutes ?? 15,
      });

      if ((res.statusCode == 200 || res.statusCode == 201) && res.data['success'] == true) {
        _activeRide = Map<String, dynamic>.from(res.data['ride']);
        startActiveRidePolling();
        _isBooking = false;
        notifyListeners();
        return true;
      }
    } catch (e) {
      debugPrint('Error booking ride: $e');
    }

    _isBooking = false;
    notifyListeners();
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

  @override
  void dispose() {
    stopActiveRidePolling();
    super.dispose();
  }
}
