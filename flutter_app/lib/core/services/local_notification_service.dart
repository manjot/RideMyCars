import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:firebase_messaging/firebase_messaging.dart';

class LocalNotificationService {
  LocalNotificationService._();
  static final LocalNotificationService instance = LocalNotificationService._();

  final FlutterLocalNotificationsPlugin _notificationsPlugin =
      FlutterLocalNotificationsPlugin();

  static const String channelId = 'ridemycars_alerts';
  static const String channelName = 'Ride & Booking Alerts';
  static const String channelDesc =
      'High priority alerts for rides, bookings, and notifications';

  final Set<int> _displayedNotificationIds = <int>{};
  bool _initialized = false;

  Future<void> initialize({Function(String?)? onSelectNotification}) async {
    if (_initialized) return;

    try {
      const AndroidInitializationSettings androidSettings =
          AndroidInitializationSettings('@mipmap/launcher_icon');

      const DarwinInitializationSettings iosSettings =
          DarwinInitializationSettings(
        requestAlertPermission: true,
        requestBadgePermission: true,
        requestSoundPermission: true,
      );

      const InitializationSettings initSettings = InitializationSettings(
        android: androidSettings,
        iOS: iosSettings,
      );

      await _notificationsPlugin.initialize(
        initSettings,
        onDidReceiveNotificationResponse: (NotificationResponse response) {
          if (onSelectNotification != null) {
            onSelectNotification(response.payload);
          }
        },
      );

      // Create Android High-Importance Channel
      if (Platform.isAndroid) {
        final androidPlugin = _notificationsPlugin
            .resolvePlatformSpecificImplementation<
                AndroidFlutterLocalNotificationsPlugin>();

        const AndroidNotificationChannel channel = AndroidNotificationChannel(
          channelId,
          channelName,
          description: channelDesc,
          importance: Importance.max,
          playSound: true,
          enableVibration: true,
          showBadge: true,
        );

        await androidPlugin?.createNotificationChannel(channel);

        // Request runtime permission for Android 13+ (TIRAMISU)
        await androidPlugin?.requestNotificationsPermission();
      }

      // Setup FCM Foreground Listener if Firebase is available
      _setupFcmListeners();

      _initialized = true;
      debugPrint('[LocalNotificationService] Initialized successfully');
    } catch (e) {
      debugPrint('[LocalNotificationService] Initialization error: $e');
    }
  }

  static const Set<String> _driverOnlyTypes = {
    'ride_assignment',
    'new_ride_request',
    'driver_hiring_assigned',
    'driver_booking',
    'package_delivery_assigned',
    'backup_ride_assigned',
    'backup_ride_request',
    'delivery_request',
    'courier_accepted_driver',
    'delivery_cancelled_driver',
    'delivery_completed_driver',
    'driver_switch_locked',
    'driver_switch_unlocked',
    'commission_threshold_warning',
    'commission_settled',
  };

  void _setupFcmListeners() {
    try {
      FirebaseMessaging.onMessage.listen((RemoteMessage message) {
        final type = (message.data['type'] ?? '').toString().toLowerCase();
        final targetRole = (message.data['target_role'] ?? '').toString().toLowerCase();
        final link = (message.data['link'] ?? '').toString();
        if (targetRole == 'driver' ||
            _driverOnlyTypes.contains(type) ||
            link.startsWith('/driver/')) {
          debugPrint('[FCM Foreground] Ignored driver-targeted notification on Rider app ($type)');
          return;
        }
        debugPrint('[FCM Foreground] Received: ${message.notification?.title}');
        final title = message.notification?.title ??
            message.data['title'] ??
            'RideMyCars Alert';
        final body = message.notification?.body ??
            message.data['message'] ??
            message.data['body'] ??
            '';
        final lowerTitle = title.toString().toLowerCase();
        final lowerBody = body.toString().toLowerCase();
        String? payload;
        if (type == 'completed' ||
            lowerTitle.contains('trip completed') ||
            lowerTitle.contains('rate your driver') ||
            lowerBody.contains('rate your driver')) {
          final rideId = int.tryParse((message.data['ride_id'] ?? 0).toString()) ?? 0;
          payload = 'rate_ride:$rideId';
        }
        final id = DateTime.now().millisecondsSinceEpoch ~/ 1000;
        showNotification(id: id, title: title, body: body, payload: payload);
      });
    } catch (e) {
      debugPrint('[FCM] listener init skipped/failed: $e');
    }
  }

  Future<void> showNotification({
    required int id,
    required String title,
    required String body,
    String? payload,
    bool force = false,
  }) async {
    try {
      if (!force && _displayedNotificationIds.contains(id)) {
        return; // Don't repeat on each poll
      }
      _displayedNotificationIds.add(id);

      const AndroidNotificationDetails androidDetails =
          AndroidNotificationDetails(
        channelId,
        channelName,
        channelDescription: channelDesc,
        importance: Importance.max,
        priority: Priority.high,
        ticker: 'RideMyCars Alert',
        playSound: true,
        enableVibration: true,
        icon: '@mipmap/launcher_icon',
        visibility: NotificationVisibility.public,
        category: AndroidNotificationCategory.status,
      );

      const NotificationDetails platformDetails = NotificationDetails(
        android: androidDetails,
        iOS: DarwinNotificationDetails(
          presentAlert: true,
          presentBadge: true,
          presentSound: true,
        ),
      );

      await _notificationsPlugin.show(
        id,
        title,
        body,
        platformDetails,
        payload: payload,
      );
      debugPrint('[LocalNotificationService] Posted system notification: $id - $title');
    } catch (e) {
      debugPrint('[LocalNotificationService] Error showing notification: $e');
    }
  }

  void markAsDisplayed(int id) {
    _displayedNotificationIds.add(id);
  }

  void markMultipleAsDisplayed(Iterable<int> ids) {
    _displayedNotificationIds.addAll(ids);
  }
}
