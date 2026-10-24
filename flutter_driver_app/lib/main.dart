import 'package:flutter/material.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:provider/provider.dart';
import 'core/constants/app_colors.dart';
import 'core/services/local_notification_service.dart';
import 'providers/auth_provider.dart';
import 'providers/country_provider.dart';
import 'providers/driver_provider.dart';
import 'providers/notification_provider.dart';
import 'screens/splash_screen.dart';

@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  try {
    await Firebase.initializeApp();
  } catch (_) {}
  debugPrint('[Driver FCM Background] Handling background message: ${message.messageId}');
  try {
    await LocalNotificationService.instance.initialize();
    final title = message.notification?.title ??
        message.data['title'] ??
        'New Ride Request';
    final body = message.notification?.body ??
        message.data['message'] ??
        message.data['body'] ??
        'Incoming ride request. Tap to open and accept.';
    final id = message.messageId?.hashCode ?? (DateTime.now().millisecondsSinceEpoch ~/ 1000);
    await LocalNotificationService.instance.showNotification(
      id: id,
      title: title,
      body: body,
      force: true,
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
