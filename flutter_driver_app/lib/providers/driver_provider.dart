import 'dart:async';
import 'package:audioplayers/audioplayers.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import '../core/api/api_client.dart';
import '../core/constants/api_constants.dart';

class DriverProvider extends ChangeNotifier {
  final Dio _dio = ApiClient().dio;
  final AudioPlayer _audioPlayer = AudioPlayer();

  bool _isOnline = false;
  bool _isLoading = false;
  String? _errorMessage;
  double? _currentLat;
  double? _currentLng;

  List<Map<String, dynamic>> _pendingRequests = [];
  List<Map<String, dynamic>> _pendingVerifications = [];
  List<Map<String, dynamic>> _activeRides = [];
  Map<String, dynamic> _earnings = {'today': 0.0, 'week': 0.0, 'month': 0.0, 'total_trips': 0};

  Timer? _pollingTimer;
  Timer? _locationTimer;

  bool get isOnline => _isOnline;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  double? get currentLat => _currentLat;
  double? get currentLng => _currentLng;
  List<Map<String, dynamic>> get pendingRequests => _pendingRequests;
  List<Map<String, dynamic>> get pendingVerifications => _pendingVerifications;
  List<Map<String, dynamic>> get activeRides => _activeRides;
  Map<String, dynamic> get earnings => _earnings;

  void init() {
    checkAvailabilityAndInit();
    fetchEarnings();
    fetchActiveRides();
  }

  Future<void> checkAvailabilityAndInit() async {
    try {
      final res = await _dio.get(ApiConstants.me);
      if (res.statusCode == 200 && res.data['success'] == true) {
        final profile = res.data['driver_profile'];
        if (profile != null && profile['is_available'] == true) {
          _isOnline = true;
          _startDispatchLoop();
          notifyListeners();
          return;
        }
      }
    } catch (_) {}

    // If online, ensure dispatch loop runs
    if (_isOnline) {
      _startDispatchLoop();
    }
    // Initial fetch of pending requests
    pollPendingRequests();
  }

  Future<void> toggleOnline() async {
    final previousState = _isOnline;
    _isOnline = !previousState;
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final res = await _dio.post(ApiConstants.driverToggleAvailability, data: {
        'is_available': _isOnline,
      });

      if (res.statusCode == 200 && (res.data['success'] == true || res.data['status'] == 'success')) {
        final val = res.data['is_available'];
        _isOnline = val == true || val == 1 || val == '1';

        if (_isOnline) {
          _startDispatchLoop();
        } else {
          _stopDispatchLoop();
        }
      } else {
        _isOnline = previousState;
        _errorMessage = res.data['message'] ?? 'Failed to update status.';
      }
    } catch (e) {
      _isOnline = previousState;
      _errorMessage = 'Network connection error. Please try again.';
      debugPrint('Error toggling availability: $e');
    }

    _isLoading = false;
    notifyListeners();
  }

  void _startDispatchLoop() {
    pollPendingRequests();
    fetchActiveRides();
    _getCurrentLocationAndSend();

    _pollingTimer?.cancel();
    _pollingTimer = Timer.periodic(const Duration(seconds: 3), (_) {
      pollPendingRequests();
      fetchActiveRides();
    });

    _locationTimer?.cancel();
    _locationTimer = Timer.periodic(const Duration(seconds: 10), (_) {
      _getCurrentLocationAndSend();
    });
  }

  void _stopDispatchLoop() {
    _pollingTimer?.cancel();
    _locationTimer?.cancel();
    _pendingRequests.clear();
    notifyListeners();
  }

  Future<void> _getCurrentLocationAndSend() async {
    try {
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }

      if (permission == LocationPermission.always || permission == LocationPermission.whileInUse) {
        final position = await Geolocator.getCurrentPosition(
          locationSettings: const LocationSettings(
            accuracy: LocationAccuracy.high,
            timeLimit: Duration(seconds: 5),
          ),
        );
        _currentLat = position.latitude;
        _currentLng = position.longitude;

        await _dio.post(ApiConstants.driverLocation, data: {
          'lat': _currentLat,
          'lng': _currentLng,
        });

        notifyListeners();
      }
    } catch (e) {
      debugPrint('Error updating GPS: $e');
    }
  }

  Future<void> pollPendingRequests() async {
    try {
      // 1. Fetch direct incoming dispatch requests
      try {
        final res = await _dio.get(ApiConstants.driverRequests);
        if (res.statusCode == 200 && res.data['success'] == true) {
          final List newReqs = res.data['requests'] ?? [];
          _pendingRequests = newReqs.map((e) => Map<String, dynamic>.from(e)).toList();
        }
      } catch (e) {
        debugPrint('Error polling dispatch requests: $e');
      }

      // 2. Fetch pending payment & booking verification requests (parity with web dashboard!)
      try {
        final verifRes = await _dio.get(ApiConstants.driverPendingVerifications);
        if (verifRes.statusCode == 200 && (verifRes.data['success'] == true || verifRes.data['items'] != null)) {
          final List items = verifRes.data['items'] ?? [];
          final mapped = items.map((e) => Map<String, dynamic>.from(e)).toList();

          if (mapped.isNotEmpty && mapped.length > _pendingVerifications.length) {
            _playNotificationSound();
          }
          _pendingVerifications = mapped;
        }
      } catch (e) {
        debugPrint('Error polling verifications: $e');
      }

      notifyListeners();
    } catch (e) {
      debugPrint('Error in pollPendingRequests: $e');
    }
  }

  void _playNotificationSound() {
    try {
      _audioPlayer.play(AssetSource('audio/notification.mp3')).catchError((_) {});
    } catch (_) {}
  }

  Future<bool> verifyBooking({
    required String serviceType,
    required int serviceId,
    required String action,
    String? rejectionReason,
  }) async {
    try {
      final res = await _dio.post(ApiConstants.driverVerifyBooking, data: {
        'service_type': serviceType,
        'service_id': serviceId,
        'action': action,
        if (rejectionReason != null) 'rejection_reason': rejectionReason,
      });

      if (res.statusCode == 200) {
        _pendingVerifications.removeWhere((item) => item['type'] == serviceType && item['id'] == serviceId);
        await fetchActiveRides();
        await fetchEarnings();
        notifyListeners();
        return true;
      }
    } catch (e) {
      debugPrint('Error verifying booking: $e');
    }
    return false;
  }

  Future<bool> respondToRequest(int? assignmentId, String action, {int? rideId}) async {
    try {
      final res = await _dio.post(ApiConstants.driverRespond, data: {
        if (assignmentId != null) 'assignment_id': assignmentId,
        if (rideId != null) 'ride_id': rideId,
        'action': action,
      });

      if (res.statusCode == 200 && res.data['success'] == true) {
        if (assignmentId != null) {
          _pendingRequests.removeWhere((r) => r['assignment_id'] == assignmentId);
        }
        if (rideId != null) {
          _pendingRequests.removeWhere((r) => r['ride_id'] == rideId);
        }
        await fetchActiveRides();
        await fetchEarnings();
        notifyListeners();
        return true;
      }
    } catch (e) {
      debugPrint('Error responding to assignment: $e');
    }
    return false;
  }

  Future<void> fetchActiveRides() async {
    try {
      final res = await _dio.get(ApiConstants.driverActiveRides);
      if (res.statusCode == 200 && res.data['success'] == true) {
        final List list = res.data['rides'] ?? [];
        _activeRides = list.map((e) => Map<String, dynamic>.from(e)).toList();
        notifyListeners();
      }
    } catch (e) {
      debugPrint('Error fetching active rides: $e');
    }
  }

  Future<bool> updateRideStatus(int rideId, String newStatus) async {
    try {
      final res = await _dio.post(ApiConstants.rideStatus(rideId), data: {
        'status': newStatus,
      });

      if (res.statusCode == 200 && res.data['success'] == true) {
        fetchActiveRides();
        if (newStatus == 'completed') {
          fetchEarnings();
        }
        return true;
      }
    } catch (e) {
      debugPrint('Error updating status: $e');
    }
    return false;
  }

  Future<void> fetchEarnings() async {
    try {
      final res = await _dio.get(ApiConstants.driverEarnings);
      if (res.statusCode == 200 && res.data['success'] == true) {
        _earnings = Map<String, dynamic>.from(res.data);
        notifyListeners();
      }
    } catch (e) {
      debugPrint('Error fetching earnings: $e');
    }
  }

  @override
  void dispose() {
    _stopDispatchLoop();
    _audioPlayer.dispose();
    super.dispose();
  }
}
