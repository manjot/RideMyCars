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
  double? _currentLat;
  double? _currentLng;

  List<Map<String, dynamic>> _pendingRequests = [];
  List<Map<String, dynamic>> _activeRides = [];
  Map<String, dynamic> _earnings = {'today': 0.0, 'week': 0.0, 'month': 0.0, 'total_trips': 0};

  Timer? _pollingTimer;
  Timer? _locationTimer;

  bool get isOnline => _isOnline;
  bool get isLoading => _isLoading;
  double? get currentLat => _currentLat;
  double? get currentLng => _currentLng;
  List<Map<String, dynamic>> get pendingRequests => _pendingRequests;
  List<Map<String, dynamic>> get activeRides => _activeRides;
  Map<String, dynamic> get earnings => _earnings;

  void init() {
    fetchEarnings();
    fetchActiveRides();
  }

  Future<void> toggleOnline() async {
    _isLoading = true;
    notifyListeners();

    try {
      final res = await _dio.post(ApiConstants.driverToggleAvailability, data: {
        'is_available': !_isOnline,
      });

      if (res.statusCode == 200 && res.data['success'] == true) {
        _isOnline = res.data['is_available'] ?? !_isOnline;

        if (_isOnline) {
          _startDispatchLoop();
        } else {
          _stopDispatchLoop();
        }
      }
    } catch (e) {
      debugPrint('Error toggling availability: $e');
    }

    _isLoading = false;
    notifyListeners();
  }

  void _startDispatchLoop() {
    _getCurrentLocationAndSend();

    _pollingTimer?.cancel();
    _pollingTimer = Timer.periodic(const Duration(seconds: 4), (_) {
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
    if (!_isOnline) return;

    try {
      final res = await _dio.get(ApiConstants.driverRequests);
      if (res.statusCode == 200 && res.data['success'] == true) {
        final List newReqs = res.data['requests'] ?? [];
        final mapped = newReqs.map((e) => Map<String, dynamic>.from(e)).toList();

        if (mapped.isNotEmpty && mapped.length > _pendingRequests.length) {
          _playNotificationSound();
        }

        _pendingRequests = mapped;
        notifyListeners();
      }
    } catch (e) {
      debugPrint('Error polling requests: $e');
    }
  }

  void _playNotificationSound() {
    try {
      _audioPlayer.play(AssetSource('audio/notification.mp3')).catchError((_) {});
    } catch (_) {}
  }

  Future<bool> respondToRequest(int assignmentId, String action) async {
    try {
      final res = await _dio.post(ApiConstants.driverRespond, data: {
        'assignment_id': assignmentId,
        'action': action,
      });

      if (res.statusCode == 200 && res.data['success'] == true) {
        _pendingRequests.removeWhere((r) => r['assignment_id'] == assignmentId);
        fetchActiveRides();
        fetchEarnings();
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
