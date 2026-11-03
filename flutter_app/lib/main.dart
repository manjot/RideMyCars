import 'package:flutter/material.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:provider/provider.dart';
import 'core/constants/app_colors.dart';
import 'core/services/local_notification_service.dart';
import 'providers/auth_provider.dart';
import 'providers/country_provider.dart';
import 'providers/notification_provider.dart';
import 'providers/ride_provider.dart';
import 'screens/rides/my_rides_screen.dart';
import 'screens/splash_screen.dart';

final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

void _handleNotificationPayload(String? payload) {
  if (payload == null || payload.isEmpty) return;
  if (payload.startsWith('rate_ride:')) {
    final rideId = int.tryParse(payload.replaceFirst('rate_ride:', '').trim()) ?? 0;
    navigatorKey.currentState?.push(
      MaterialPageRoute(
        builder: (_) => MyRidesScreen(
          initialCategoryFilter: 'ride',
          initialTabIndex: 2,
          autoRateRideId: rideId,
        ),
      ),
    );
  }
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  try {
    await Firebase.initializeApp();
    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
      final type = (message.data['type'] ?? '').toString().toLowerCase();
      final title = (message.notification?.title ?? message.data['title'] ?? '').toString().toLowerCase();
      final body = (message.notification?.body ?? message.data['message'] ?? message.data['body'] ?? '').toString().toLowerCase();
      if (type == 'completed' ||
          title.contains('trip completed') ||
          title.contains('rate your driver') ||
          body.contains('rate your driver')) {
        final rideId = int.tryParse((message.data['ride_id'] ?? 0).toString()) ?? 0;
        _handleNotificationPayload('rate_ride:$rideId');
      }
    });
  } catch (e) {
    debugPrint('Firebase init: $e');
  }
  try {
    await LocalNotificationService.instance.initialize(
      onSelectNotification: _handleNotificationPayload,
    );
  } catch (e) {
    debugPrint('LocalNotificationService init error: $e');
  }
  runApp(const RideMyCarsRiderApp());
}

class RideMyCarsRiderApp extends StatelessWidget {
  const RideMyCarsRiderApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => CountryProvider()),
        ChangeNotifierProvider(create: (_) => RideProvider()),
        ChangeNotifierProvider(create: (_) => NotificationProvider()),
      ],
      child: MaterialApp(
        navigatorKey: navigatorKey,
        title: 'RideMyCars',
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
