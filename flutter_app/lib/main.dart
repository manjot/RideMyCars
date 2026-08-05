import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'screens/home_screen.dart';
import 'screens/ride_screen.dart';
import 'screens/rent_screen.dart';
import 'screens/hire_screen.dart';

void main() {
  runApp(const RideMyCarsApp());
}

final GoRouter _router = GoRouter(
  initialLocation: '/',
  routes: [
    GoRoute(
      path: '/',
      builder: (context, state) => const HomeScreen(),
    ),
    GoRoute(
      path: '/ride',
      builder: (context, state) => const RideScreen(),
    ),
    GoRoute(
      path: '/rent',
      builder: (context, state) => const RentScreen(),
    ),
    GoRoute(
      path: '/hire',
      builder: (context, state) => const HireScreen(),
    ),
  ],
);

class RideMyCarsApp extends StatelessWidget {
  const RideMyCarsApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp.router(
      title: 'RideMyCars',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF6366F1), // Indigo 500
          brightness: Brightness.light,
        ),
        useMaterial3: true,
        fontFamily: 'Inter',
      ),
      darkTheme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF6366F1),
          brightness: Brightness.dark,
        ),
        useMaterial3: true,
        fontFamily: 'Inter',
      ),
      themeMode: ThemeMode.system,
      routerConfig: _router,
    );
  }
}
