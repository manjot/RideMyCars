import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import '../api/api_client.dart';
import '../constants/api_constants.dart';

class LocalNotificationService {
  LocalNotificationService._();
  static final LocalNotificationService instance = LocalNotificationService._();

  final FlutterLocalNotificationsPlugin _notificationsPlugin =
      FlutterLocalNotificationsPlugin();

  static const String channelId = 'ridemycars_alerts';
  static const String channelName = 'Ride & Booking Alerts';
  static const String channelDesc =
      'High priority alerts for incoming ride assignments and requests';

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

      // Create Android High-Importance Channel with Custom Alert Ringtone
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
          sound: RawResourceAndroidNotificationSound('incoming_order_ringtone'),
          enableVibration: true,
          showBadge: true,
        );

        await androidPlugin?.createNotificationChannel(channel);

        // Request runtime permission for Android 13+ (TIRAMISU)
        await androidPlugin?.requestNotificationsPermission();
      }

      // Request Firebase Messaging permissions
      try {
        final fcm = FirebaseMessaging.instance;
        await fcm.requestPermission(
          alert: true,
          badge: true,
          sound: true,
          provisional: false,
        );
        await fcm.setForegroundNotificationPresentationOptions(
          alert: true,
          badge: true,
          sound: true,
        );
      } catch (e) {
        debugPrint('[FCM] permission request error: $e');
      }

      // Setup FCM Foreground Listener
      _setupFcmListeners();

      _initialized = true;
      debugPrint('[LocalNotificationService] Driver initialized successfully with incoming_order_ringtone channel');
    } catch (e) {
      debugPrint('[LocalNotificationService] Driver init error: $e');
    }
  }

  void _setupFcmListeners() {
    try {
      FirebaseMessaging.onMessage.listen((RemoteMessage message) {
        debugPrint('[Driver FCM Foreground] Received: ${message.notification?.title}');
        final title = message.notification?.title ??
            message.data['title'] ??
            'Driver Partner Alert';
        final body = message.notification?.body ??
            message.data['message'] ??
            message.data['body'] ??
            'New ride request received.';
        final id = DateTime.now().millisecondsSinceEpoch ~/ 1000;
        showNotification(id: id, title: title, body: body, force: true);
      });
    } catch (e) {
      debugPrint('[Driver FCM] listener init skipped/failed: $e');
    }
  }

  /// Sync device FCM token to backend
  Future<void> syncFcmToken() async {
    try {
      final token = await FirebaseMessaging.instance.getToken();
      if (token != null && token.isNotEmpty) {
        debugPrint('[FCM] Registering device token with backend: ${token.substring(0, 15)}...');
        await ApiClient().dio.post(ApiConstants.driverFcmToken, data: {
          'fcm_token': token,
          'token': token,
          'device_type': Platform.isAndroid ? 'android' : 'ios',
        });
        debugPrint('[FCM] Driver token registered successfully');
      }

      FirebaseMessaging.instance.onTokenRefresh.listen((newToken) async {
        debugPrint('[FCM] Driver token refreshed: ${newToken.substring(0, 15)}...');
        try {
          await ApiClient().dio.post(ApiConstants.driverFcmToken, data: {
            'fcm_token': newToken,
            'token': newToken,
            'device_type': Platform.isAndroid ? 'android' : 'ios',
          });
        } catch (_) {}
      });
    } catch (e) {
      debugPrint('[FCM] syncFcmToken error: $e');
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
        ticker: 'Driver Partner Alert',
        playSound: true,
        sound: RawResourceAndroidNotificationSound('incoming_order_ringtone'),
        audioAttributesUsage: AudioAttributesUsage.notificationRingtone,
        enableVibration: true,
        icon: '@mipmap/launcher_icon',
        visibility: NotificationVisibility.public,
        category: AndroidNotificationCategory.call,
        fullScreenIntent: true,
      );

      const NotificationDetails platformDetails = NotificationDetails(
        android: androidDetails,
        iOS: DarwinNotificationDetails(
          presentAlert: true,
          presentBadge: true,
          presentSound: true,
          sound: 'incoming_order_ringtone.mp3',
        ),
      );

      await _notificationsPlugin.show(
        id,
        title,
        body,
        platformDetails,
        payload: payload,
      );
      debugPrint('[LocalNotificationService] Driver posted system notification: $id - $title');
    } catch (e) {
      debugPrint('[LocalNotificationService] Driver error showing notification: $e');
    }
  }

  void markAsDisplayed(int id) {
    _displayedNotificationIds.add(id);
  }

  void markMultipleAsDisplayed(Iterable<int> ids) {
    _displayedNotificationIds.addAll(ids);
  }
}
