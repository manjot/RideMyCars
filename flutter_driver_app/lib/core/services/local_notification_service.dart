import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import '../api/api_client.dart';
import '../constants/api_constants.dart';
import '../storage/token_storage.dart';
import 'sound_service.dart';

class LocalNotificationService {
  LocalNotificationService._();
  static final LocalNotificationService instance = LocalNotificationService._();

  final FlutterLocalNotificationsPlugin _notificationsPlugin =
      FlutterLocalNotificationsPlugin();

  static const String channelId = 'ridemycars_alerts';
  static const String channelName = 'Ride & Booking Alerts';
  static const String channelDesc =
      'High priority alerts for incoming ride assignments and requests';

  static const String incomingOrderChannelId = 'ridemycars_incoming_orders_v2';
  static const String incomingOrderChannelName = 'Incoming Order Ringtone Alerts';
  static const String incomingOrderChannelDesc =
      'Loud lock-screen ringtone alerts for new ride, delivery, and chauffeur orders';

  static const int incomingOrderNotificationId = 999111;

  final Set<int> _displayedNotificationIds = <int>{};
  String? _lastAlertedOrderKey;
  bool _initialized = false;
  VoidCallback? onOrderPushReceived;

  bool isDriverSwitchOnline = false;
  bool isActiveChauffeurSwitchOnline = false;

  void updateSwitchStates({
    required bool isDriverOnline,
    required bool isChauffeurOnline,
  }) {
    isDriverSwitchOnline = isDriverOnline;
    isActiveChauffeurSwitchOnline = isChauffeurOnline;
    TokenStorage.saveDriverSwitchStates(
      isDriverOnline: isDriverOnline,
      isChauffeurOnline: isChauffeurOnline,
    );
  }

  /// Checks whether an order request notification (Ride/Delivery vs Driver Hire/Chauffeur)
  /// is allowed based on the current Driver Switch and Active Chauffeur Switch states.
  bool isOrderTypeAllowed(String? orderType, {String? title}) {
    final t = (orderType ?? '').toLowerCase();
    final lowerTitle = (title ?? '').toLowerCase();
    final isDriverHire = t.contains('hire') ||
        t.contains('chauffeur') ||
        t.contains('booking') ||
        t.contains('hiring') ||
        lowerTitle.contains('chauffeur') ||
        lowerTitle.contains('driver hire');
    if (isDriverHire) {
      return isActiveChauffeurSwitchOnline;
    }
    return isDriverSwitchOnline;
  }

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
        requestCriticalPermission: true,
        defaultPresentAlert: true,
        defaultPresentBanner: true,
        defaultPresentList: true,
        defaultPresentSound: true,
        defaultPresentBadge: true,
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

      // Create Android High-Importance Channels with Custom Alert Ringtone
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
          audioAttributesUsage: AudioAttributesUsage.alarm,
          enableVibration: true,
          showBadge: true,
        );

        const AndroidNotificationChannel incomingOrderChannel =
            AndroidNotificationChannel(
          incomingOrderChannelId,
          incomingOrderChannelName,
          description: incomingOrderChannelDesc,
          importance: Importance.max,
          playSound: true,
          sound: RawResourceAndroidNotificationSound('incoming_order_ringtone'),
          audioAttributesUsage: AudioAttributesUsage.alarm,
          enableVibration: true,
          showBadge: true,
        );

        await androidPlugin?.createNotificationChannel(channel);
        await androidPlugin?.createNotificationChannel(incomingOrderChannel);

        // Request runtime permissions for Android 13+ / 14+
        await androidPlugin?.requestNotificationsPermission();
        try {
          await androidPlugin?.requestFullScreenIntentPermission();
        } catch (_) {}
      }

      // Request iOS permissions explicitly
      if (Platform.isIOS) {
        final iosPlugin = _notificationsPlugin
            .resolvePlatformSpecificImplementation<
                IOSFlutterLocalNotificationsPlugin>();
        await iosPlugin?.requestPermissions(
          alert: true,
          badge: true,
          sound: true,
          critical: true,
        );
      }

      // Request Firebase Messaging permissions
      try {
        final fcm = FirebaseMessaging.instance;
        await fcm.requestPermission(
          alert: true,
          badge: true,
          sound: true,
          criticalAlert: true,
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
      debugPrint(
          '[LocalNotificationService] Driver initialized successfully with incoming_order_ringtone channels');
    } catch (e) {
      debugPrint('[LocalNotificationService] Driver init error: $e');
    }
  }

  void _setupFcmListeners() {
    try {
      FirebaseMessaging.onMessage.listen((RemoteMessage message) {
        debugPrint(
            '[Driver FCM Foreground] Received: ${message.notification?.title}');
        final title = message.notification?.title ??
            message.data['title'] ??
            'Driver Partner Alert';
        final body = message.notification?.body ??
            message.data['message'] ??
            message.data['body'] ??
            'New ride request received.';
        final type = (message.data['type'] ?? '').toString().toLowerCase();
        final isOrderAlert = type.contains('ride') ||
            type.contains('booking') ||
            type.contains('delivery') ||
            type.contains('hiring') ||
            type.contains('request') ||
            title.toLowerCase().contains('request') ||
            title.toLowerCase().contains('order');

        if (isOrderAlert) {
          if (!isOrderTypeAllowed(type, title: title)) {
            debugPrint('[Driver FCM Foreground] Ignored order alert ($type) because corresponding switch is OFF');
            return;
          }
          final orderId = message.data['ride_id'] ??
              message.data['booking_id'] ??
              message.data['delivery_id'] ??
              DateTime.now().millisecondsSinceEpoch;
          SoundService.instance.startIncomingOrderRingtone(
            orderType: type.isNotEmpty ? type : 'ride',
            orderId: orderId,
            title: title,
            body: body,
          );
          onOrderPushReceived?.call();
        } else {
          final id = DateTime.now().millisecondsSinceEpoch ~/ 1000;
          showNotification(id: id, title: title, body: body, force: true);
        }
      });

      FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
        onOrderPushReceived?.call();
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
        debugPrint(
            '[FCM] Registering device token with backend: ${token.substring(0, 15)}...');
        await ApiClient().dio.post(ApiConstants.driverFcmToken, data: {
          'fcm_token': token,
          'token': token,
          'device_type': Platform.isAndroid ? 'android' : 'ios',
        });
        debugPrint('[FCM] Driver token registered successfully');
      }

      FirebaseMessaging.instance.onTokenRefresh.listen((newToken) async {
        debugPrint(
            '[FCM] Driver token refreshed: ${newToken.substring(0, 15)}...');
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

  /// Shows a full-screen, lock-screen-waking, insistent ringing notification for incoming orders.
  Future<void> showIncomingOrderAlert({
    required String title,
    required String body,
    String? orderKey,
    String? payload,
    bool forceRefresh = false,
  }) async {
    try {
      if (!forceRefresh &&
          orderKey != null &&
          _lastAlertedOrderKey == orderKey) {
        return;
      }
      if (orderKey != null) {
        _lastAlertedOrderKey = orderKey;
      }

      // FLAG_INSISTENT = 4 makes Android repeat the notification sound continuously until cancelled or tapped
      final AndroidNotificationDetails androidDetails =
          AndroidNotificationDetails(
        incomingOrderChannelId,
        incomingOrderChannelName,
        channelDescription: incomingOrderChannelDesc,
        importance: Importance.max,
        priority: Priority.max,
        ticker: 'Incoming Order Request',
        playSound: true,
        sound:
            const RawResourceAndroidNotificationSound('incoming_order_ringtone'),
        audioAttributesUsage: AudioAttributesUsage.alarm,
        additionalFlags: Int32List.fromList(<int>[4]),
        enableVibration: true,
        vibrationPattern: Int64List.fromList(<int>[0, 800, 400, 800, 400, 1000]),
        icon: '@mipmap/launcher_icon',
        visibility: NotificationVisibility.public,
        category: AndroidNotificationCategory.call,
        fullScreenIntent: true,
        ongoing: true,
        autoCancel: true,
      );

      const DarwinNotificationDetails iosDetails = DarwinNotificationDetails(
        presentAlert: true,
        presentBanner: true,
        presentList: true,
        presentBadge: true,
        presentSound: true,
        sound: 'incoming_order_ringtone.wav',
        interruptionLevel: InterruptionLevel.timeSensitive,
      );

      final NotificationDetails platformDetails = NotificationDetails(
        android: androidDetails,
        iOS: iosDetails,
      );

      await _notificationsPlugin.show(
        incomingOrderNotificationId,
        title,
        body,
        platformDetails,
        payload: payload ?? orderKey,
      );
      debugPrint(
          '[LocalNotificationService] Posted lock-screen incoming order alert: $title');
    } catch (e) {
      debugPrint(
          '[LocalNotificationService] Error showing incoming order alert: $e');
    }
  }

  /// Cancels the persistent incoming order lock-screen notification when answered or expired.
  Future<void> cancelIncomingOrderAlert() async {
    _lastAlertedOrderKey = null;
    try {
      await _notificationsPlugin.cancel(incomingOrderNotificationId);
    } catch (_) {}
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
        audioAttributesUsage: AudioAttributesUsage.alarm,
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
          presentBanner: true,
          presentList: true,
          presentBadge: true,
          presentSound: true,
          sound: 'incoming_order_ringtone.wav',
          interruptionLevel: InterruptionLevel.timeSensitive,
        ),
      );

      await _notificationsPlugin.show(
        id,
        title,
        body,
        platformDetails,
        payload: payload,
      );
      debugPrint(
          '[LocalNotificationService] Driver posted system notification: $id - $title');
    } catch (e) {
      debugPrint(
          '[LocalNotificationService] Driver error showing notification: $e');
    }
  }

  void markAsDisplayed(int id) {
    _displayedNotificationIds.add(id);
  }

  void markMultipleAsDisplayed(Iterable<int> ids) {
    _displayedNotificationIds.addAll(ids);
  }
}
