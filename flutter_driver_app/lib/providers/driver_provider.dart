import 'dart:async';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import '../core/api/api_client.dart';
import '../core/constants/api_constants.dart';
import '../core/services/sound_service.dart';

class DriverProvider extends ChangeNotifier {
  final Dio _dio = ApiClient().dio;

  bool _isOnline = false;
  bool _isLive = false;
  bool _isLoading = false;
  String? _errorMessage;
  double? _currentLat;
  double? _currentLng;

  List<Map<String, dynamic>> _pendingRequests = [];
  List<Map<String, dynamic>> _pendingVerifications = [];
  List<Map<String, dynamic>> _activeRides = [];
  Map<String, dynamic> _earnings = {'today': 0.0, 'week': 0.0, 'month': 0.0, 'total_trips': 0};
  Map<String, dynamic>? _lastAssignmentResponse;
  Map<String, dynamic>? _lastAcceptedRide;

  Timer? _pollingTimer;
  Timer? _locationTimer;

  bool get isOnline => _isOnline;
  bool get isLive => _isLive;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  double? get currentLat => _currentLat;
  double? get currentLng => _currentLng;
  List<Map<String, dynamic>> get pendingRequests => _pendingRequests;
  List<Map<String, dynamic>> get pendingVerifications => _pendingVerifications;
  List<Map<String, dynamic>> get activeRides => _activeRides;
  Map<String, dynamic> get earnings => _earnings;
  Map<String, dynamic>? get lastAssignmentResponse => _lastAssignmentResponse;
  Map<String, dynamic>? get lastAcceptedRide => _lastAcceptedRide;

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
        if (profile != null) {
          _isLive = profile['is_live'] == true || profile['is_live'] == 1 || profile['is_live'] == '1';
          final available = profile['is_available'] == true || profile['is_available'] == 1 || profile['is_available'] == '1';
          if (_isLive && available) {
            _isOnline = true;
            _startDispatchLoop();
            notifyListeners();
            return;
          } else {
            _isOnline = false;
            _stopDispatchLoop();
            notifyListeners();
          }
        }
      }
    } catch (_) {}

    // If online and live, ensure dispatch loop runs
    if (_isOnline && _isLive) {
      _startDispatchLoop();
    }
    // Initial fetch of pending requests
    if (_isLive) {
      pollPendingRequests();
    }
  }

  Future<bool> toggleOnline() async {
    final previousState = _isOnline;
    final targetState = !previousState;
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final res = await _dio.post(ApiConstants.driverToggleAvailability, data: {
        'is_available': targetState,
      });

      if (res.statusCode == 200 && (res.data['success'] == true || res.data['status'] == 'success')) {
        final val = res.data['is_available'];
        _isOnline = val == true || val == 1 || val == '1';
        if (res.data['is_live'] != null) {
          final liveVal = res.data['is_live'];
          _isLive = liveVal == true || liveVal == 1 || liveVal == '1';
        } else {
          _isLive = true;
        }

        if (_isOnline) {
          _startDispatchLoop();
        } else {
          _stopDispatchLoop();
        }
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _isOnline = previousState;
        _errorMessage = res.data['message']?.toString() ?? 'Failed to update status.';
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _isOnline = previousState;
      if (e is DioException && e.response?.data != null && e.response?.data is Map && e.response?.data['message'] != null) {
        _errorMessage = e.response?.data['message'].toString();
      } else {
        _errorMessage = 'Network connection error. Please try again.';
      }
      debugPrint('Error toggling availability: $e');
      _isLoading = false;
      notifyListeners();
      return false;
    }
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
    SoundService.instance.stopRingtone();
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
          desiredAccuracy: LocationAccuracy.high,
          timeLimit: const Duration(seconds: 5),
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
        if (res.statusCode == 200) {
          final List newReqs = (res.data is Map ? (res.data['requests'] ?? res.data['data']) : (res.data is List ? res.data : [])) ?? [];
          final mapped = newReqs.map((e) => Map<String, dynamic>.from(e)).toList();
          
          if (mapped.isNotEmpty) {
            // Loud and long ringtone starts ringing continuously until driver responds
            final firstOrder = mapped.first;
            final orderType = firstOrder['type']?.toString() ?? 'ride';
            final orderId = firstOrder['ride_id'] ?? firstOrder['assignment_id'] ?? firstOrder['delivery_id'] ?? firstOrder['id'];
            SoundService.instance.startIncomingOrderRingtone(orderType: orderType, orderId: orderId);
          } else if (_pendingRequests.isNotEmpty && mapped.isEmpty) {
            // Requests cleared / expired / answered elsewhere
            SoundService.instance.stopRingtone();
          }
          _pendingRequests = mapped;
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
            final firstVerif = mapped.first;
            SoundService.instance.startIncomingOrderRingtone(
              orderType: firstVerif['type']?.toString() ?? 'verification',
              orderId: firstVerif['id'],
            );
          } else if (_pendingVerifications.isNotEmpty && mapped.isEmpty && _pendingRequests.isEmpty) {
            SoundService.instance.stopRingtone();
          }
          _pendingVerifications = mapped;
        }
      } catch (e) {
        debugPrint('Error polling verifications: $e');
      }

      // 3. Fetch active rides continuously
      try {
        final activeRes = await _dio.get(ApiConstants.driverActiveRides);
        if (activeRes.statusCode == 200) {
          final List list = (activeRes.data is Map ? (activeRes.data['rides'] ?? activeRes.data['data']) : (activeRes.data is List ? activeRes.data : [])) ?? [];
          final mapped = list.map((e) => Map<String, dynamic>.from(e)).toList();
          if (mapped.isNotEmpty) {
            _activeRides = mapped;
          } else if (_lastAcceptedRide != null && _lastAcceptedRide!['status'] != 'completed' && _lastAcceptedRide!['status'] != 'cancelled') {
            _activeRides = [_lastAcceptedRide!];
          } else {
            _activeRides = [];
          }
        }
      } catch (e) {
        debugPrint('Error polling active rides: $e');
      }

      notifyListeners();
    } catch (e) {
      debugPrint('Error in pollPendingRequests: $e');
    }
  }

  void playNotificationChime() {
    SoundService.instance.playNotificationChime();
  }

  Future<bool> verifyBooking({
    required String serviceType,
    required int serviceId,
    required String action,
    String? rejectionReason,
  }) async {
    // Driver responded to booking/delivery verification -> stop sound
    await SoundService.instance.stopRingtone();

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

  Future<bool> respondToRequest(
    dynamic rawAssignmentId,
    String action, {
    dynamic rawRideId,
    dynamic rawDeliveryId,
    dynamic rawBookingId,
    int? rideId,
    int? deliveryId,
    int? bookingId,
    Map<String, dynamic>? jobData,
  }) async {
    final assignmentId = int.tryParse(rawAssignmentId?.toString() ?? '');
    final effectiveRideId = rideId ?? int.tryParse(rawRideId?.toString() ?? '');
    final effectiveDeliveryId = deliveryId ?? int.tryParse(rawDeliveryId?.toString() ?? '');
    final effectiveBookingId = bookingId ?? int.tryParse(rawBookingId?.toString() ?? '');

    // Driver responded (Accept or Reject) -> Immediately silence the incoming order ringtone!
    await SoundService.instance.stopRingtone();

    try {
      Response? res;
      try {
        res = await _dio.post(ApiConstants.driverRespond, data: {
          if (assignmentId != null && assignmentId > 0) 'assignment_id': assignmentId,
          if (effectiveRideId != null && effectiveRideId > 0) 'ride_id': effectiveRideId,
          if (effectiveDeliveryId != null && effectiveDeliveryId > 0) 'delivery_id': effectiveDeliveryId,
          if (effectiveBookingId != null && effectiveBookingId > 0) 'driver_booking_id': effectiveBookingId,
          'action': action,
        });
      } catch (e) {
        // Fallback to /api/driver/requests/{id}/respond if driverRespond fails
        final targetId = assignmentId ?? effectiveRideId ?? effectiveDeliveryId ?? effectiveBookingId;
        if (targetId != null && targetId > 0) {
          try {
            res = await _dio.post('/driver/requests/$targetId/respond', data: {
              'status': action == 'accept' ? 'accepted' : 'rejected',
            });
          } catch (_) {}
        }
      }

      if (res != null &&
          (res.statusCode == 200 || res.statusCode == 201) &&
          (res.data['success'] == true || res.data['status'] == 'accepted')) {
        _lastAssignmentResponse = Map<String, dynamic>.from(res.data);

        // Remove from pending list
        if (assignmentId != null) {
          _pendingRequests.removeWhere((r) => r['assignment_id'] == assignmentId || r['id'] == assignmentId);
        }
        if (effectiveRideId != null) {
          _pendingRequests.removeWhere((r) => r['ride_id'] == effectiveRideId || r['id'] == effectiveRideId);
        }

        if (action == 'accept') {
          // Resolve accepted ride object
          Map<String, dynamic>? acceptedRide;
          if (res.data['ride'] is Map) {
            acceptedRide = Map<String, dynamic>.from(res.data['ride']);
          } else if (res.data['delivery'] is Map) {
            acceptedRide = Map<String, dynamic>.from(res.data['delivery']);
          } else if (res.data['booking'] is Map) {
            acceptedRide = Map<String, dynamic>.from(res.data['booking']);
          } else if (jobData != null) {
            acceptedRide = Map<String, dynamic>.from(jobData);
          }

          if (acceptedRide != null) {
            acceptedRide['status'] = 'accepted';
            if (effectiveRideId != null && (acceptedRide['id'] == null || acceptedRide['id'] == 0)) {
              acceptedRide['id'] = effectiveRideId;
            }
            _lastAcceptedRide = acceptedRide;
            // Prepend immediately to _activeRides
            _activeRides.removeWhere((r) => r['id']?.toString() == acceptedRide!['id']?.toString());
            _activeRides.insert(0, acceptedRide);
          }

          _isOnline = true;
          // Background sync
          fetchActiveRides();
          fetchEarnings();
        }

        notifyListeners();
        return true;
      }
    } catch (e) {
      debugPrint('Error responding to assignment: $e');
    }
    return false;
  }

  Future<bool> cancelRide(dynamic rawRideId, {String? reason}) async {
    final rideId = int.tryParse(rawRideId?.toString() ?? '0') ?? 0;
    if (rideId <= 0) return false;

    try {
      _isLoading = true;
      notifyListeners();

      final res = await _dio.post(ApiConstants.rideCancel(rideId), data: {
        'reason': reason ?? 'Cancelled by driver',
      });

      if (res.statusCode == 200 && (res.data['success'] == true || res.data['status'] == 'cancelled')) {
        _activeRides.removeWhere((r) => int.tryParse(r['id']?.toString() ?? '0') == rideId);
        if (_lastAcceptedRide != null && int.tryParse(_lastAcceptedRide!['id']?.toString() ?? '0') == rideId) {
          _lastAcceptedRide = null;
        }
        _isOnline = true;
        await fetchActiveRides();
        await fetchEarnings();
        await pollPendingRequests();
        _isLoading = false;
        notifyListeners();
        return true;
      }
    } catch (e) {
      debugPrint('Error cancelling ride: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
    return false;
  }

  Future<void> fetchActiveRides() async {
    try {
      final res = await _dio.get(ApiConstants.driverActiveRides);
      if (res.statusCode == 200 && (res.data['success'] == true || res.data is List || res.data['rides'] != null)) {
        final List list = (res.data is Map ? (res.data['rides'] ?? res.data['data']) : (res.data is List ? res.data : [])) ?? [];
        final mapped = list.map((e) => Map<String, dynamic>.from(e)).toList();
        if (mapped.isNotEmpty) {
          _activeRides = mapped;
        } else if (_lastAcceptedRide != null && _lastAcceptedRide!['status'] != 'completed' && _lastAcceptedRide!['status'] != 'cancelled') {
          _activeRides = [_lastAcceptedRide!];
        } else {
          _activeRides = [];
        }
        notifyListeners();
      }
    } catch (e) {
      debugPrint('Error fetching active rides: $e');
    }
  }

  Future<bool> updateRideStatus(dynamic rawRideId, String newStatus) async {
    final rideId = int.tryParse(rawRideId?.toString() ?? '0') ?? 0;
    if (rideId <= 0) return false;

    try {
      Response? res;
      try {
        res = await _dio.post(ApiConstants.rideStatus(rideId), data: {
          'status': newStatus,
        });
      } catch (e) {
        try {
          res = await _dio.post('/driver/rides/$rideId/status', data: {
            'status': newStatus,
          });
        } catch (_) {}
      }

      if (res != null && (res.statusCode == 200 || res.statusCode == 201) && (res.data['success'] == true || res.data['status'] != null)) {
        if (newStatus == 'completed') {
          _activeRides.removeWhere((r) => int.tryParse(r['id']?.toString() ?? '0') == rideId);
          if (_lastAcceptedRide != null && int.tryParse(_lastAcceptedRide!['id']?.toString() ?? '0') == rideId) {
            _lastAcceptedRide = null;
          }
          _isOnline = true;
          await fetchEarnings();
        } else {
          // Immediately reflect state on active list
          for (var r in _activeRides) {
            if (int.tryParse(r['id']?.toString() ?? '0') == rideId) {
              r['status'] = newStatus;
            }
          }
          if (_lastAcceptedRide != null && int.tryParse(_lastAcceptedRide!['id']?.toString() ?? '0') == rideId) {
            _lastAcceptedRide!['status'] = newStatus;
          }
        }
        await fetchActiveRides();
        notifyListeners();
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
    SoundService.instance.stopRingtone();
    super.dispose();
  }
}
