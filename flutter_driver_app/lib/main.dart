import 'package:flutter/material.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:provider/provider.dart';
import 'core/constants/app_colors.dart';
import 'core/services/local_notification_service.dart';
import 'core/services/sound_service.dart';
import 'core/storage/token_storage.dart';
import 'providers/auth_provider.dart';
import 'providers/country_provider.dart';
import 'providers/driver_provider.dart';
import 'providers/notification_provider.dart';
import 'screens/splash_screen.dart';

@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  WidgetsFlutterBinding.ensureInitialized();
  try {
    await Firebase.initializeApp();
  } catch (_) {}
  debugPrint('[Driver FCM Background] Handling background message: ${message.messageId}');
  try {
    await LocalNotificationService.instance.initialize();
    final switchStates = await TokenStorage.getDriverSwitchStates();
    LocalNotificationService.instance.isDriverSwitchOnline = switchStates['isDriverOnline'] ?? false;
    LocalNotificationService.instance.isActiveChauffeurSwitchOnline = switchStates['isChauffeurOnline'] ?? false;

    final title = message.notification?.title ??
        message.data['title'] ??
        '🚨 New Ride Order Request!';
    final body = message.notification?.body ??
        message.data['message'] ??
        message.data['body'] ??
        'Incoming order request. Tap to open and accept.';
    final type = (message.data['type'] ?? 'ride').toString();

    if (!LocalNotificationService.instance.isOrderTypeAllowed(type, title: title)) {
      debugPrint('[Driver FCM Background] Suppressed order notification ($type) because corresponding switch is OFF');
      return;
    }

    final orderId = message.data['ride_id'] ??
        message.data['booking_id'] ??
        message.data['delivery_id'] ??
        message.messageId ??
        (DateTime.now().millisecondsSinceEpoch ~/ 1000);

    await LocalNotificationService.instance.showIncomingOrderAlert(
      title: title,
      body: body,
      orderKey: '${type}_$orderId',
      forceRefresh: true,
    );
    await SoundService.instance.startIncomingOrderRingtone(
      orderType: type,
      orderId: orderId,
      title: title,
      body: body,
    );
  } catch (e) {
    debugPrint('[Driver FCM Background] Error displaying notification: $e');
  }
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  try {
    await Firebase.initializeApp();
    FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);
  } catch (e) {
    debugPrint('Firebase init: $e');
  }
  try {
    await LocalNotificationService.instance.initialize();
    // Proactively register or update device FCM token
    LocalNotificationService.instance.syncFcmToken();
  } catch (e) {
    debugPrint('LocalNotificationService init error: $e');
  }
  runApp(const RideMyCarsDriverApp());
}

class RideMyCarsDriverApp extends StatelessWidget {
  const RideMyCarsDriverApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => CountryProvider()),
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => DriverProvider()),
        ChangeNotifierProvider(create: (_) => NotificationProvider()),
      ],
      child: MaterialApp(
        title: 'Driver Partner',
        debugShowCheckedModeBanner: false,
        theme: ThemeData(
          brightness: Brightness.dark,
          scaffoldBackgroundColor: AppColors.backgroundDark,
          primaryColor: AppColors.primary,
          colorScheme: const ColorScheme.dark(
            primary: AppColors.primary,
            secondary: AppColors.primaryDark,
            surface: AppColors.surfaceDark,
            error: AppColors.danger,
          ),
          useMaterial3: true,
          fontFamily: 'Inter',
        ),
        home: const SplashScreen(),
      ),
    );
  }
}
