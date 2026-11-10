import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:geolocator/geolocator.dart';
import '../core/api/api_client.dart';
import '../core/constants/api_constants.dart';
import '../core/services/sound_service.dart';
import '../core/services/local_notification_service.dart';
import '../core/storage/token_storage.dart';

class DriverProvider extends ChangeNotifier with WidgetsBindingObserver {
  final Dio _dio = ApiClient().dio;
  static const MethodChannel _nativeAlertChannel =
      MethodChannel('com.ridemycars.driver/order_alert_service');

  bool _isOnline = false;
  bool _isLive = false;
  bool _isLoading = false;
  bool _isChauffeur = false;
  bool _activeChauffeurOnline = false;
  bool _isDriverDocsVerified = false;
  bool _isGuarantorVerified = false;
  bool _isCommissionLocked = false;
  double _commissionWalletBalance = 0.0;
  int? _commissionDaysRemaining;
  String? _commissionWarningMessage;
  bool _hasNotifiedThresholdWarning = false;
  bool _hasNotifiedLockWarning = false;
  String _professionalBadgeLabel = 'Driver (Document Verification Pending)';
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
  StreamSubscription<Position>? _positionStreamSubscription;
  DateTime? _lastStreamPollAt;
  DateTime? _lastGpsPostAt;
  bool _observerRegistered = false;

  bool get isOnline => _isOnline;
  bool get isLive => _isLive;
  bool get isLoading => _isLoading;
  bool get isChauffeur => _isChauffeur;
  bool get activeChauffeurOnline => _activeChauffeurOnline;
  bool get isDriverDocsVerified => _isDriverDocsVerified;
  bool get isGuarantorVerified => _isGuarantorVerified;
  bool get isCommissionLocked => _isCommissionLocked;
  double get commissionWalletBalance => _commissionWalletBalance;
  int? get commissionDaysRemaining => _commissionDaysRemaining;
  String? get commissionWarningMessage => _commissionWarningMessage;
  String get professionalBadgeLabel => _professionalBadgeLabel;
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
    if (!_observerRegistered) {
      WidgetsBinding.instance.addObserver(this);
      _observerRegistered = true;
    }
    LocalNotificationService.instance.onOrderPushReceived = () {
      if (_isOnline || _activeChauffeurOnline) {
        pollPendingRequests();
        fetchActiveRides();
      }
    };
    checkAvailabilityAndInit();
    fetchEarnings();
    fetchActiveRides();
    try {
      LocalNotificationService.instance.syncFcmToken();
    } catch (_) {}
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      if (_isOnline || _activeChauffeurOnline) {
        SoundService.instance.setOnlineKeepAlive(true);
        _syncNativeOrderAlertService(true);
        pollPendingRequests();
        fetchActiveRides();
      }
    } else if (state == AppLifecycleState.paused ||
        state == AppLifecycleState.inactive ||
        state == AppLifecycleState.hidden ||
        state == AppLifecycleState.detached) {
      // Keep audio session, native background service & location stream alive when screen locks or app is closed/backgrounded
      if (_isOnline || _activeChauffeurOnline) {
        SoundService.instance.setOnlineKeepAlive(true);
        _syncNativeOrderAlertService(true);
        _startBackgroundLocationStream();
        pollPendingRequests();
      }
    }
  }

  Future<void> checkAvailabilityAndInit() async {
    try {
      final res = await _dio.get(ApiConstants.me);
      if (res.statusCode == 200 && res.data['success'] == true) {
        final profile = res.data['driver_profile'];
        if (profile != null) {
          _isLive = profile['is_live'] == true || profile['is_live'] == 1 || profile['is_live'] == '1';
          _isChauffeur = profile['is_chauffeur'] == true || profile['is_chauffeur'] == 1 || profile['is_chauffeur'] == '1' || profile['driver_type'] == 'chauffeur';
          _activeChauffeurOnline = profile['active_chauffeur_online'] == true || profile['active_chauffeur_online'] == 1 || profile['active_chauffeur_online'] == '1' || profile['chauffeur_mode'] == true;
          _isDriverDocsVerified = profile['is_driver_docs_verified'] == true || profile['is_driver_docs_verified'] == 1;
          _isGuarantorVerified = profile['is_guarantor_verified'] == true || profile['is_guarantor_verified'] == 1;
          _commissionWalletBalance = (profile['cash_commission_wallet'] as num?)?.toDouble() ?? 0.0;
          _isCommissionLocked = profile['is_commission_locked'] == true || profile['commission_locked'] == true || profile['commission_locked'] == 1;
          _commissionDaysRemaining = (profile['commission_days_remaining'] as num?)?.toInt();
          _commissionWarningMessage = profile['commission_warning_message']?.toString();

          if (_commissionWalletBalance >= 100 && !_hasNotifiedThresholdWarning) {
            _hasNotifiedThresholdWarning = true;
            LocalNotificationService.instance.showStatusNotification(
              id: 88100,
              title: 'Commission Wallet Alert',
              body: 'Kindly top up your wallet balance within 7 days for Commission Deduction or risk deactivation.',
            );
          }

          if (profile['professional_badge_label'] != null && profile['professional_badge_label'].toString().isNotEmpty) {
            _professionalBadgeLabel = profile['professional_badge_label'].toString();
          } else if (_isChauffeur) {
            _professionalBadgeLabel = _isGuarantorVerified ? 'Verified Professional Chauffeur' : 'Chauffeur (Guarantor Verification Pending)';
          } else {
            _professionalBadgeLabel = _isDriverDocsVerified ? 'Verified Professional Driver' : 'Driver (Document Verification Pending)';
          }
          final available = profile['is_available'] == true || profile['is_available'] == 1 || profile['is_available'] == '1';
          if (_isCommissionLocked) {
            _isOnline = false;
            _activeChauffeurOnline = false;
            if (!_hasNotifiedLockWarning) {
              _hasNotifiedLockWarning = true;
              LocalNotificationService.instance.showStatusNotification(
                id: 88101,
                title: 'Driver Switch Turned Off & Locked',
                body: 'Your Driver Switch has been turned off and locked due to unpaid commission exceeding the 7-day payment deadline. Please pay your outstanding commission to unlock your Driver Switch.',
              );
            }
          } else {
            _isOnline = _isLive && available;
          }
          LocalNotificationService.instance.updateSwitchStates(
            isDriverOnline: _isOnline,
            isChauffeurOnline: _activeChauffeurOnline,
          );
          if (_isOnline || _activeChauffeurOnline) {
            _startDispatchLoop();
            notifyListeners();
            return;
          } else {
            _stopDispatchLoop();
            notifyListeners();
          }
        }
      }
    } catch (_) {}

    // If online (driver or chauffeur), ensure dispatch loop runs
    if ((_isOnline && _isLive) || _activeChauffeurOnline) {
      _startDispatchLoop();
    }
    // Initial fetch of pending requests
    if (_isLive || _activeChauffeurOnline) {
      pollPendingRequests();
    }
  }

  /// Sync commission status from Wallet & Payouts summary response
  void syncCommissionFromWalletSummary(Map<String, dynamic> summary) {
    final newCommBal = (summary['commission_wallet_balance'] as num?)?.toDouble()
        ?? (summary['commission_wallet']?['balance'] as num?)?.toDouble()
        ?? _commissionWalletBalance;
    final locked = summary['is_commission_locked'] == true
        || summary['commission_wallet']?['is_commission_locked'] == true;
    final daysRem = (summary['commission_days_remaining'] as num?)?.toInt()
        ?? (summary['commission_wallet']?['commission_days_remaining'] as num?)?.toInt();
    final warnMsg = (summary['commission_warning_message'] ?? summary['commission_wallet']?['commission_warning_message'])?.toString();

    _commissionWalletBalance = newCommBal;
    _commissionDaysRemaining = daysRem;
    _commissionWarningMessage = warnMsg;

    if (newCommBal >= 100 && !_hasNotifiedThresholdWarning) {
      _hasNotifiedThresholdWarning = true;
      LocalNotificationService.instance.showStatusNotification(
        id: 88100,
        title: 'Commission Wallet Alert',
        body: 'Kindly top up your wallet balance within 7 days for Commission Deduction or risk deactivation.',
      );
    } else if (newCommBal < 100) {
      _hasNotifiedThresholdWarning = false;
    }

    if (locked) {
      _isCommissionLocked = true;
      _isOnline = false;
      _activeChauffeurOnline = false;
      _stopDispatchLoop();
      if (!_hasNotifiedLockWarning) {
        _hasNotifiedLockWarning = true;
        LocalNotificationService.instance.showStatusNotification(
          id: 88101,
          title: 'Driver Switch Turned Off & Locked',
          body: 'Your Driver Switch has been turned off and locked due to unpaid commission exceeding the 7-day payment deadline. Please pay your outstanding commission to unlock your Driver Switch.',
        );
      }
    } else if (_isCommissionLocked && !locked) {
      _isCommissionLocked = false;
      _hasNotifiedLockWarning = false;
    }
    notifyListeners();
  }

  /// Called immediately after the driver pays/settles their outstanding commission
  Future<void> unlockSwitchAfterCommissionPayment() async {
    _isCommissionLocked = false;
    _hasNotifiedLockWarning = false;
    _hasNotifiedThresholdWarning = false;
    _isOnline = true;
    _isLive = true;
    _errorMessage = null;
    LocalNotificationService.instance.updateSwitchStates(
      isDriverOnline: _isOnline,
      isChauffeurOnline: _activeChauffeurOnline,
    );
    LocalNotificationService.instance.showStatusNotification(
      id: 88102,
      title: 'Driver Switch Turned On & Unlocked',
      body: 'Your Driver Switch has been turned on and unlocked. You can now receive ride requests.',
    );
    _startDispatchLoop();
    notifyListeners();
  }

  /// Requirement 5: Toggle Active Chauffeur Switch (works independently even when regular Driver status is offline)
  Future<bool> toggleChauffeurOnline() async {
    final targetState = !_activeChauffeurOnline;
    if (targetState && _isCommissionLocked) {
      _errorMessage = 'Your Driver Switch has been turned off and locked due to unpaid commission exceeding the 7-day payment deadline. Please top up your wallet to pay the outstanding commission and unlock your Driver Switch.';
      notifyListeners();
      return false;
    }
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final res = await _dio.post(ApiConstants.driverChauffeurSwitch, data: {
        'online': targetState,
      });

      if (res.statusCode == 200 && (res.data['success'] == true || res.data['status'] == 'success')) {
        _activeChauffeurOnline = res.data['active_chauffeur_online'] == true || res.data['active_chauffeur_online'] == 1;
        _isChauffeur = res.data['is_chauffeur'] == true || _isChauffeur;
        if (_activeChauffeurOnline) {
          _isLive = true;
        }
        LocalNotificationService.instance.updateSwitchStates(
          isDriverOnline: _isOnline,
          isChauffeurOnline: _activeChauffeurOnline,
        );
        if (!_activeChauffeurOnline) {
          _pendingRequests.removeWhere((j) {
            final type = j['type']?.toString().toLowerCase() ?? '';
            return type.contains('hire') || type.contains('chauffeur') || type.contains('booking') || j['driver_booking_id'] != null;
          });
          _pendingVerifications.removeWhere((j) {
            final type = j['type']?.toString().toLowerCase() ?? '';
            return type.contains('hire') || type.contains('chauffeur') || type.contains('booking');
          });
          if (_pendingRequests.isEmpty && _pendingVerifications.isEmpty) {
            SoundService.instance.stopRingtone();
          }
        }
        if (_activeChauffeurOnline || _isOnline) {
          _startDispatchLoop();
        } else {
          _stopDispatchLoop();
        }
        _isLoading = false;
        notifyListeners();
        await pollPendingRequests();
        return true;
      } else {
        if (res.data is Map && res.data['is_commission_locked'] == true) {
          _isCommissionLocked = true;
          _isOnline = false;
          _activeChauffeurOnline = false;
        }
        _errorMessage = res.data['message'] ?? 'Failed to update Chauffeur status';
      }
    } catch (e) {
      if (e is DioException && e.response?.data is Map) {
        final d = e.response!.data as Map;
        if (d['is_commission_locked'] == true) {
          _isCommissionLocked = true;
          _isOnline = false;
          _activeChauffeurOnline = false;
        }
        _errorMessage = d['message']?.toString() ?? 'Failed to update Chauffeur status';
      } else {
        _errorMessage = 'Network error while updating Chauffeur status';
      }
      debugPrint('Error toggling chauffeur switch: $e');
    }
    _isLoading = false;
    notifyListeners();
    return false;
  }

  Future<bool> toggleOnline() async {
    final previousState = _isOnline;
    final targetState = !previousState;
    if (targetState && _isCommissionLocked) {
      _errorMessage = 'Your Driver Switch has been turned off and locked due to unpaid commission exceeding the 7-day payment deadline. Please top up your wallet to pay the outstanding commission and unlock your Driver Switch.';
      notifyListeners();
      return false;
    }
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

        LocalNotificationService.instance.updateSwitchStates(
          isDriverOnline: _isOnline,
          isChauffeurOnline: _activeChauffeurOnline,
        );
        if (!_isOnline) {
          _pendingRequests.removeWhere((j) {
            final type = j['type']?.toString().toLowerCase() ?? '';
            final isDriverHire = type.contains('hire') || type.contains('chauffeur') || type.contains('booking') || j['driver_booking_id'] != null;
            return !isDriverHire;
          });
          _pendingVerifications.removeWhere((j) {
            final type = j['type']?.toString().toLowerCase() ?? '';
            final isDriverHire = type.contains('hire') || type.contains('chauffeur') || type.contains('booking');
            return !isDriverHire;
          });
          if (_pendingRequests.isEmpty && _pendingVerifications.isEmpty) {
            SoundService.instance.stopRingtone();
          }
        }

        if (_isOnline || _activeChauffeurOnline) {
          _startDispatchLoop();
        } else {
          _stopDispatchLoop();
        }
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        if (res.data is Map && res.data['is_commission_locked'] == true) {
          _isCommissionLocked = true;
          _isOnline = false;
          _activeChauffeurOnline = false;
        } else {
          _isOnline = previousState;
        }
        _errorMessage = res.data['message']?.toString() ?? 'Failed to update status.';
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _isOnline = previousState;
      if (e is DioException && e.response?.data != null && e.response?.data is Map) {
        final d = e.response!.data as Map;
        if (d['is_commission_locked'] == true) {
          _isCommissionLocked = true;
          _isOnline = false;
          _activeChauffeurOnline = false;
        }
        if (d['message'] != null) {
          _errorMessage = d['message'].toString();
        } else {
          _errorMessage = 'Network connection error. Please try again.';
        }
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
    SoundService.instance.setOnlineKeepAlive(true);
    _syncNativeOrderAlertService(true);
    _startBackgroundLocationStream();
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
    _pollingTimer = null;
    _locationTimer?.cancel();
    _locationTimer = null;
    _positionStreamSubscription?.cancel();
    _positionStreamSubscription = null;
    _pendingRequests.clear();
    _syncNativeOrderAlertService(false);
    SoundService.instance.setOnlineKeepAlive(false);
    SoundService.instance.stopRingtone();
    notifyListeners();
  }

  Future<void> _syncNativeOrderAlertService(bool active) async {
    LocalNotificationService.instance.updateSwitchStates(
      isDriverOnline: _isOnline,
      isChauffeurOnline: _activeChauffeurOnline,
    );
    if (!Platform.isAndroid) return;
    try {
      if (active) {
        final token = await TokenStorage.getToken() ?? '';
        final email = await TokenStorage.getUserEmail() ?? '';
        final countryCode = await TokenStorage.getSelectedCountryCode() ?? '';
        await _nativeAlertChannel.invokeMethod('startService', {
          'token': token,
          'email': email,
          'countryCode': countryCode,
          'baseUrl': ApiConstants.baseUrl,
          'isOnline': _isOnline,
          'isChauffeurOnline': _activeChauffeurOnline,
          'lat': _currentLat?.toString() ?? '',
          'lng': _currentLng?.toString() ?? '',
        });
      } else {
        await _nativeAlertChannel.invokeMethod('stopService');
      }
    } catch (e) {
      debugPrint('Native order alert service sync notice: $e');
    }
  }

  Future<void> _startBackgroundLocationStream() async {
    if (_positionStreamSubscription != null) return;
    try {
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }
      if (permission != LocationPermission.always &&
          permission != LocationPermission.whileInUse) {
        return;
      }

      late LocationSettings locationSettings;
      if (Platform.isAndroid) {
        locationSettings = AndroidSettings(
          accuracy: LocationAccuracy.high,
          distanceFilter: 0,
          intervalDuration: const Duration(seconds: 4),
        );
      } else if (Platform.isIOS) {
        locationSettings = AppleSettings(
          accuracy: LocationAccuracy.high,
          activityType: ActivityType.automotiveNavigation,
          distanceFilter: 0,
          allowBackgroundLocationUpdates: true,
          pauseLocationUpdatesAutomatically: false,
          showBackgroundLocationIndicator: true,
        );
      } else {
        locationSettings = const LocationSettings(
          accuracy: LocationAccuracy.high,
          distanceFilter: 0,
        );
      }

      _positionStreamSubscription =
          Geolocator.getPositionStream(locationSettings: locationSettings)
              .listen(
        (Position pos) {
          _currentLat = pos.latitude;
          _currentLng = pos.longitude;
          if (Platform.isAndroid) {
            _nativeAlertChannel.invokeMethod('updateLocation', {
              'lat': _currentLat.toString(),
              'lng': _currentLng.toString(),
            }).catchError((_) {});
          }
          final now = DateTime.now();
          if (_isOnline || _activeChauffeurOnline) {
            if (_lastStreamPollAt == null ||
                now.difference(_lastStreamPollAt!).inSeconds >= 3) {
              _lastStreamPollAt = now;
              pollPendingRequests();
            }
            if (_lastGpsPostAt == null ||
                now.difference(_lastGpsPostAt!).inSeconds >= 10) {
              _lastGpsPostAt = now;
              _dio.post(ApiConstants.driverLocation, data: {
                'lat': _currentLat,
                'lng': _currentLng,
              }).catchError((_) => Response(requestOptions: RequestOptions()));
            }
          }
        },
        onError: (e) {
          debugPrint('Background location stream notice: $e');
          _positionStreamSubscription?.cancel();
          _positionStreamSubscription = null;
        },
      );
    } catch (e) {
      debugPrint('Error starting background location stream: $e');
    }
  }

  void stopDispatch() {
    _stopDispatchLoop();
    _activeRides.clear();
    _lastAcceptedRide = null;
    _lastAssignmentResponse = null;
    _isOnline = false;
    notifyListeners();
  }

  Future<void> refreshDashboard() async {
    try {
      await Future.wait([
        pollPendingRequests(),
        fetchEarnings(),
      ]).timeout(const Duration(seconds: 4));
    } catch (e) {
      debugPrint('refreshDashboard notice: $e');
    } finally {
      notifyListeners();
    }
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

        if (Platform.isAndroid) {
          try {
            _nativeAlertChannel.invokeMethod('updateLocation', {
              'lat': _currentLat.toString(),
              'lng': _currentLng.toString(),
            });
          } catch (_) {}
        }

        await _dio.post(ApiConstants.driverLocation, data: {
          'lat': _currentLat,
          'lng': _currentLng,
        }).timeout(const Duration(seconds: 4));

        notifyListeners();
      }
    } catch (e) {
      debugPrint('Error updating GPS: $e');
    }
  }

  Future<void> pollPendingRequests() async {
    try {
      // Collect IDs of already-accepted/active rides so they never ring again as pending requests
      final Set<String> activeIds = <String>{};
      for (final ar in _activeRides) {
        final id = (ar['id'] ?? ar['ride_id'] ?? ar['delivery_id'] ?? ar['booking_id'] ?? '').toString();
        if (id.isNotEmpty && id != '0') {
          activeIds.add(id);
        }
      }
      if (_lastAcceptedRide != null) {
        final lid = (_lastAcceptedRide!['id'] ?? _lastAcceptedRide!['ride_id'] ?? '').toString();
        if (lid.isNotEmpty && lid != '0') {
          activeIds.add(lid);
        }
      }

      // 1. Fetch direct incoming dispatch requests (with current GPS for proximity matching)
      try {
        if (_currentLat == null || _currentLng == null) {
          try {
            final lastPos = await Geolocator.getLastKnownPosition().timeout(
              const Duration(seconds: 1),
              onTimeout: () => null,
            );
            if (lastPos != null) {
              _currentLat = lastPos.latitude;
              _currentLng = lastPos.longitude;
            }
          } catch (_) {}
        }

        final res = await _dio.get(
          ApiConstants.driverRequests,
          queryParameters: {
            if (_currentLat != null) 'lat': _currentLat,
            if (_currentLng != null) 'lng': _currentLng,
          },
        ).timeout(const Duration(seconds: 4));
        if (res.statusCode == 200) {
          if (res.data is Map && res.data['is_commission_locked'] == true) {
            _isCommissionLocked = true;
            _isOnline = false;
            _activeChauffeurOnline = false;
            _pendingRequests = [];
            _stopDispatchLoop();
            if (!_hasNotifiedLockWarning) {
              _hasNotifiedLockWarning = true;
              LocalNotificationService.instance.showStatusNotification(
                id: 88101,
                title: 'Driver Switch Turned Off & Locked',
                body: 'Your Driver Switch has been turned off and locked due to unpaid commission exceeding the 7-day payment deadline. Please pay your outstanding commission to unlock your Driver Switch.',
              );
            }
            notifyListeners();
            return;
          }
          final List newReqs = (res.data is Map ? (res.data['requests'] ?? res.data['data']) : (res.data is List ? res.data : [])) ?? [];
          final mapped = newReqs
              .map((e) => Map<String, dynamic>.from(e))
              .where((j) {
                final rem = j['remaining_seconds'];
                if (rem != null && (int.tryParse(rem.toString()) ?? 1) <= 0) return false;
                final exp = j['expires_at'];
                if (exp != null) {
                  try {
                    if (DateTime.parse(exp.toString()).toLocal().isBefore(DateTime.now())) return false;
                  } catch (_) {}
                }
                final rIdStr = (j['ride_id'] ?? j['delivery_id'] ?? j['package_delivery_id'] ?? j['driver_booking_id'] ?? j['booking_id'] ?? '').toString();
                if (rIdStr.isNotEmpty && activeIds.contains(rIdStr)) {
                  return false;
                }
                final type = j['type']?.toString().toLowerCase() ?? '';
                final isDriverHire = type.contains('hire') || type.contains('chauffeur') || type.contains('booking') || j['driver_booking_id'] != null;
                if (isDriverHire) {
                  if (!_activeChauffeurOnline) return false;
                } else {
                  if (!_isOnline) return false;
                }
                return true;
              })
              .toList();
          
          if (mapped.isNotEmpty) {
            // Loud and long ringtone + lock-screen alert starts ringing continuously until driver responds
            final firstOrder = mapped.first;
            final orderType = firstOrder['type']?.toString() ?? 'ride';
            final orderId = firstOrder['delivery_id'] ?? firstOrder['package_delivery_id'] ?? firstOrder['ride_id'] ?? firstOrder['driver_booking_id'] ?? firstOrder['booking_id'] ?? firstOrder['assignment_id'] ?? firstOrder['id'];
            final pickup = firstOrder['pickup_location'] ?? firstOrder['pickup'] ?? 'Pickup Location';
            final dropoff = firstOrder['dropoff_location'] ?? firstOrder['dropoff'] ?? 'Destination';
            final sym = firstOrder['currency_symbol'] ?? '';
            final fare = firstOrder['fare'] ?? firstOrder['total_price'] ?? '';
            final alertTitle = orderType.toLowerCase().contains('booking') || orderType.toLowerCase().contains('hire')
                ? '🚨 New VIP Chauffeur Request! ($sym$fare)'
                : (orderType.toLowerCase().contains('delivery')
                    ? '🚨 New Parcel Delivery Order! ($sym$fare)'
                    : '🚨 New Ride Order Request! ($sym$fare)');
            final alertBody = 'Pickup: $pickup → Dropoff: $dropoff. Tap to accept!';
            SoundService.instance.startIncomingOrderRingtone(
              orderType: orderType,
              orderId: orderId,
              title: alertTitle,
              body: alertBody,
            );
          } else if (_pendingRequests.isNotEmpty && mapped.isEmpty && _pendingVerifications.isEmpty) {
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
        final verifRes = await _dio.get(
          ApiConstants.driverPendingVerifications,
          queryParameters: {
            if (_currentLat != null) 'lat': _currentLat,
            if (_currentLng != null) 'lng': _currentLng,
          },
        ).timeout(const Duration(seconds: 4));
        if (verifRes.statusCode == 200 && (verifRes.data['success'] == true || verifRes.data['items'] != null)) {
          final List items = verifRes.data['items'] ?? [];
          final mapped = items.map((e) => Map<String, dynamic>.from(e)).toList();

          mapped.removeWhere((item) {
            final itemIdStr = (item['id'] ?? '').toString();
            if (itemIdStr.isNotEmpty && activeIds.contains(itemIdStr)) {
              return true;
            }
            final type = item['type']?.toString().toLowerCase() ?? '';
            final isDriverHire = type.contains('hire') || type.contains('chauffeur') || type.contains('booking');
            if (isDriverHire) {
              return !_activeChauffeurOnline;
            }
            return !_isOnline;
          });

          if (mapped.isNotEmpty && mapped.length > _pendingVerifications.length) {
            final firstVerif = mapped.first;
            final pickup = firstVerif['pickup'] ?? 'Pickup';
            final dropoff = firstVerif['dropoff'] ?? 'Destination';
            final formattedAmt = firstVerif['formatted_amount'] ?? '';
            SoundService.instance.startIncomingOrderRingtone(
              orderType: firstVerif['type']?.toString() ?? 'verification',
              orderId: firstVerif['id'],
              title: '🚨 New ${firstVerif['type_label'] ?? 'Order'} Request! ($formattedAmt)',
              body: 'Pickup: $pickup → Dropoff: $dropoff. Tap to respond!',
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
        final activeRes = await _dio.get(ApiConstants.driverActiveRides).timeout(const Duration(seconds: 4));
        if (activeRes.statusCode == 200) {
          final List list = (activeRes.data is Map ? (activeRes.data['rides'] ?? activeRes.data['data']) : (activeRes.data is List ? activeRes.data : [])) ?? [];
          final mapped = list
              .map((e) => Map<String, dynamic>.from(e))
              .where((r) => r['status'] != 'completed' && r['status'] != 'delivered' && r['status'] != 'cancelled')
              .toList();
          if (mapped.isNotEmpty) {
            _activeRides = mapped;
          } else {
            _activeRides = [];
            _lastAcceptedRide = null;
          }
        }
      } catch (e) {
        debugPrint('Error polling active rides: $e');
      }

      if (_pendingRequests.isEmpty && _pendingVerifications.isEmpty && SoundService.instance.isRinging) {
        SoundService.instance.stopRingtone();
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

  void removePendingRequestLocally({int? assignmentId, int? rideId, int? deliveryId, int? bookingId}) {
    _pendingRequests.removeWhere((r) {
      final aId = int.tryParse((r['assignment_id'] ?? r['id'] ?? '').toString());
      final rId = int.tryParse((r['ride_id'] ?? '').toString());
      final dId = int.tryParse((r['package_delivery_id'] ?? r['delivery_id'] ?? '').toString());
      final bId = int.tryParse((r['driver_booking_id'] ?? r['booking_id'] ?? '').toString());
      return (assignmentId != null && aId == assignmentId) ||
             (rideId != null && rId == rideId) ||
             (deliveryId != null && dId == deliveryId) ||
             (bookingId != null && bId == bookingId);
    });
    if (_pendingRequests.isEmpty) {
      SoundService.instance.stopRingtone();
    }
    notifyListeners();
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

    // Driver responded (Accept or Reject) -> Immediately silence ringtone and optimistically remove from pending list!
    await SoundService.instance.stopRingtone();
    removePendingRequestLocally(
      assignmentId: assignmentId,
      rideId: effectiveRideId,
      deliveryId: effectiveDeliveryId,
      bookingId: effectiveBookingId,
    );

    try {
      Response? res;
      try {
        res = await _dio.post(ApiConstants.driverRespond, data: {
          if (assignmentId != null && assignmentId > 0) 'assignment_id': assignmentId,
          if (effectiveRideId != null && effectiveRideId > 0) 'ride_id': effectiveRideId,
          if (effectiveDeliveryId != null && effectiveDeliveryId > 0) 'delivery_id': effectiveDeliveryId,
          if (effectiveBookingId != null && effectiveBookingId > 0) 'driver_booking_id': effectiveBookingId,
          'action': action,
          if (_currentLat != null) 'lat': _currentLat,
          if (_currentLng != null) 'lng': _currentLng,
          if (_currentLat != null) 'latitude': _currentLat,
          if (_currentLng != null) 'longitude': _currentLng,
        }).timeout(const Duration(seconds: 8));
      } on DioException catch (dioErr) {
        debugPrint('driverRespond DioException: ${dioErr.response?.statusCode} ${dioErr.response?.data}');
        final respData = dioErr.response?.data;
        if (respData is Map) {
          final sMsg = respData['message'] ?? respData['error'];
          if (sMsg != null && sMsg.toString().isNotEmpty) {
            _errorMessage = sMsg.toString();
          }
        }
        if (dioErr.response?.statusCode == 410 || (respData is Map && respData['is_expired'] == true)) {
          _errorMessage = respData is Map
              ? (respData['message']?.toString() ?? 'This request has expired and is no longer available.')
              : 'This request has expired and is no longer available.';
          removePendingRequestLocally(
            assignmentId: assignmentId,
            rideId: effectiveRideId,
            deliveryId: effectiveDeliveryId,
            bookingId: effectiveBookingId,
          );
          notifyListeners();
          return false;
        }
        // Fallback to /api/driver/requests/{id}/respond if driverRespond fails
        final targetId = assignmentId ?? effectiveRideId ?? effectiveDeliveryId ?? effectiveBookingId;
        if (targetId != null && targetId > 0) {
          try {
            res = await _dio.post('/driver/requests/$targetId/respond', data: {
              'status': action == 'accept' ? 'accepted' : 'rejected',
              if (_currentLat != null) 'lat': _currentLat,
              if (_currentLng != null) 'lng': _currentLng,
            }).timeout(const Duration(seconds: 6));
          } catch (fallbackErr) {
            if (fallbackErr is DioException) {
              final fbData = fallbackErr.response?.data;
              if (fbData is Map && fbData['message'] != null) {
                _errorMessage = fbData['message'].toString();
              }
              if (fallbackErr.response?.statusCode == 410) {
                _errorMessage = fbData is Map
                    ? (fbData['message']?.toString() ?? 'This request has expired and is no longer available.')
                    : 'This request has expired and is no longer available.';
                removePendingRequestLocally(
                  assignmentId: assignmentId,
                  rideId: effectiveRideId,
                  deliveryId: effectiveDeliveryId,
                  bookingId: effectiveBookingId,
                );
                notifyListeners();
                return false;
              }
            }
          }
        }
      } catch (e) {
        debugPrint('driverRespond failed: $e');
      }

      if (action == 'reject') {
        // Declined locally & network attempted -> guaranteed clean state
        notifyListeners();
        return true;
      }

      if (res != null &&
          (res.statusCode == 200 || res.statusCode == 201) &&
          (res.data['success'] == true || res.data['status'] == 'accepted')) {
        _lastAssignmentResponse = Map<String, dynamic>.from(res.data);

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
            if (res.data['is_queued'] == true) {
              acceptedRide['is_queued'] = true;
            }
            _lastAcceptedRide = acceptedRide;

            // Preserve ongoing trip at index 0 if one is already in progress/motion
            final hasOngoingTrip = _activeRides.any((r) =>
                ['in_progress', 'started', 'arrived', 'en_route', 'driver_assigned'].contains(r['status']));

            _activeRides.removeWhere((r) => r['id']?.toString() == acceptedRide!['id']?.toString());
            if (hasOngoingTrip) {
              acceptedRide['is_queued'] = true;
              _activeRides.add(acceptedRide);
            } else {
              _activeRides.insert(0, acceptedRide);
            }
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
    return action == 'reject';
  }

  Future<bool> cancelRide(dynamic rawRideId, {String? reason, Map<String, dynamic>? fallbackRide}) async {
    final cleanIdStr = (rawRideId ?? fallbackRide?['id'] ?? fallbackRide?['ride_id'] ?? '')
        .toString()
        .replaceAll(RegExp(r'[^0-9]'), '');
    final rideId = int.tryParse(cleanIdStr) ?? 0;

    try {
      _isLoading = true;
      notifyListeners();

      String? type = fallbackRide?['type']?.toString();
      if ((type == null || type.isEmpty) && fallbackRide != null) {
        if (fallbackRide['package_delivery_id'] != null ||
            fallbackRide['delivery_id'] != null ||
            fallbackRide['delivery_code'] != null ||
            (fallbackRide['code']?.toString().toUpperCase().startsWith('DEL') ?? false)) {
          type = 'package_delivery';
        }
      }
      final cancelData = <String, dynamic>{
        'status': 'cancelled',
        'reason': reason ?? 'Cancelled by driver',
        if (type != null && type.isNotEmpty) 'type': type,
      };

      bool backendSuccess = false;

      if (rideId > 0) {
        if (type != null && type.toLowerCase().contains('delivery')) {
          try {
            final res = await _dio.post('/delivery/$rideId/cancel', data: cancelData);
            if (res.statusCode == 200) {
              backendSuccess = true;
            }
          } catch (_) {}
        }

        // 1. Try dedicated ride cancel endpoint
        if (!backendSuccess) {
          try {
            final res = await _dio.post(ApiConstants.rideCancel(rideId), data: cancelData);
            dynamic data = res.data;
            if (data is String) {
              try { data = jsonDecode(data); } catch (_) {}
            }
            if (res.statusCode == 200 && (data is Map && (data['success'] == true || data['status'] == 'cancelled' || data['status'] == 'not_found'))) {
              backendSuccess = true;
            }
          } catch (e) {
            debugPrint('rideCancel endpoint notice: $e, falling back to rideStatus...');
          }
        }

        // 2. Fallback to status update endpoint with 'cancelled'
        if (!backendSuccess) {
          try {
            final res = await _dio.post(ApiConstants.rideStatus(rideId), data: cancelData);
            dynamic data = res.data;
            if (data is String) {
              try { data = jsonDecode(data); } catch (_) {}
            }
            if (res.statusCode == 200 && (data is Map && (data['success'] == true || data['status'] == 'cancelled'))) {
              backendSuccess = true;
            }
          } catch (e) {
            debugPrint('rideStatus cancel notice: $e');
          }
        }
      }

      // Always reliably clean up locally so the driver dashboard updates immediately
      _activeRides.removeWhere((r) {
        final rIdStr = (r['id'] ?? r['ride_id'] ?? '').toString().replaceAll(RegExp(r'[^0-9]'), '');
        final matchId = rideId > 0 && rIdStr == rideId.toString();
        final matchFallback = fallbackRide != null && (r == fallbackRide || (r['id'] != null && r['id'] == fallbackRide['id']));
        return matchId || matchFallback;
      });

      if (_lastAcceptedRide != null) {
        final lIdStr = (_lastAcceptedRide!['id'] ?? '').toString().replaceAll(RegExp(r'[^0-9]'), '');
        if (rideId > 0 && lIdStr == rideId.toString()) {
          _lastAcceptedRide = null;
        } else if (fallbackRide != null && _lastAcceptedRide == fallbackRide) {
          _lastAcceptedRide = null;
        }
      }

      _isOnline = true;
      try {
        await fetchActiveRides();
        await fetchEarnings();
        await pollPendingRequests();
      } catch (_) {}

      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      debugPrint('Error in cancelRide: $e');
      _activeRides.removeWhere((r) {
        final rIdStr = (r['id'] ?? r['ride_id'] ?? '').toString().replaceAll(RegExp(r'[^0-9]'), '');
        return rideId > 0 && rIdStr == rideId.toString();
      });
      _isOnline = true;
      _isLoading = false;
      notifyListeners();
      return true;
    }
  }

  Future<void> fetchActiveRides() async {
    try {
      final res = await _dio.get(ApiConstants.driverActiveRides).timeout(const Duration(seconds: 4));
      if (res.statusCode == 200 && (res.data['success'] == true || res.data is List || res.data['rides'] != null)) {
        final List list = (res.data is Map ? (res.data['rides'] ?? res.data['data']) : (res.data is List ? res.data : [])) ?? [];
        final mapped = list
            .map((e) => Map<String, dynamic>.from(e))
            .where((r) => r['status'] != 'completed' && r['status'] != 'delivered' && r['status'] != 'cancelled')
            .toList();
        mapped.sort((a, b) {
          int priority(dynamic status) {
            switch (status?.toString()) {
              case 'in_progress':
              case 'started':
                return 1;
              case 'arrived':
                return 2;
              case 'en_route':
                return 3;
              case 'driver_assigned':
              case 'confirmed':
                return 4;
              case 'accepted':
                return 5;
              default:
                return 10;
            }
          }
          return priority(a['status']).compareTo(priority(b['status']));
        });
        if (mapped.isNotEmpty) {
          _activeRides = mapped;
        } else {
          _activeRides = [];
          _lastAcceptedRide = null;
        }
        notifyListeners();
      }
    } catch (e) {
      debugPrint('Error fetching active rides: $e');
    }
  }

  String? _lastStatusError;
  String? get lastStatusError => _lastStatusError;

  Future<bool> updateRideStatus(dynamic rawRideId, String newStatus, {String? type, String? otp}) async {
    final rideId = int.tryParse(rawRideId?.toString() ?? '0') ?? 0;
    if (rideId <= 0) return false;

    _lastStatusError = null;

    final postData = <String, dynamic>{
      'status': newStatus,
      if (type != null && type.isNotEmpty) 'type': type,
      if (otp != null && otp.isNotEmpty) ...{
        'otp': otp.trim(),
        'pin': otp.trim(),
        'start_pin': otp.trim(),
      },
    };

    try {
      Response? res;
      try {
        res = await _dio.post(ApiConstants.rideStatus(rideId), data: postData).timeout(const Duration(seconds: 5));
      } on DioException catch (dioErr) {
        if (dioErr.response?.data is Map && dioErr.response?.data['message'] != null) {
          _lastStatusError = dioErr.response?.data['message']?.toString();
        }
        res = dioErr.response;
        if (res == null || res.statusCode != 422) {
          try {
            res = await _dio.post('/driver/rides/$rideId/status', data: postData).timeout(const Duration(seconds: 5));
          } catch (_) {}
        }
      } catch (e) {
        try {
          res = await _dio.post('/driver/rides/$rideId/status', data: postData).timeout(const Duration(seconds: 5));
        } catch (_) {}
      }

      if (res != null && (res.statusCode == 200 || res.statusCode == 201) && (res.data['success'] == true || res.data['status'] != null)) {
        if (newStatus == 'completed' || newStatus == 'delivered' || newStatus == 'cancelled') {
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
      } else if (res?.data is Map && res?.data['message'] != null) {
        _lastStatusError = res!.data['message'].toString();
      }
    } catch (e) {
      debugPrint('Error updating status: $e');
    }
    return false;
  }

  Future<void> fetchEarnings() async {
    try {
      final res = await _dio.get(ApiConstants.driverEarnings).timeout(const Duration(seconds: 4));
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
    if (_observerRegistered) {
      WidgetsBinding.instance.removeObserver(this);
      _observerRegistered = false;
    }
    _stopDispatchLoop();
    SoundService.instance.stopRingtone();
    super.dispose();
  }
}
