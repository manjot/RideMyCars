import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../core/constants/app_colors.dart';

class FloatingRideWidget extends StatelessWidget {
  final Map<String, dynamic> ride;
  final VoidCallback onTap;

  const FloatingRideWidget({
    super.key,
    required this.ride,
    required this.onTap,
  });

  String get _statusText {
    final s = ride['status'];
    final d = ride['driver']?['name'] ?? 'Driver';
    if (s == 'pending') return 'Looking for drivers...';
    if (s == 'accepted') return '$d accepted your ride';
    if (s == 'en_route') return '$d is on the way';
    if (s == 'arrived') return '$d has arrived at pickup';
    if (s == 'in_progress') return 'Trip in progress';
    return 'Trip active';
  }

  Future<void> _launchMaps() async {
    final dest = ride['dropoff_location'] ?? ride['pickup_location'] ?? '';
    if (dest.isEmpty) return;

    final uri = Uri.parse(
      'https://www.google.com/maps/dir/?api=1&destination=${Uri.encodeComponent(dest)}&travelmode=driving',
    );
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  @override
  Widget build(BuildContext context) {
    final driverName = ride['driver']?['name'] ?? 'RideMyCars';
    final driverRating = ride['driver']?['rating'] ?? 4.9;

    return Positioned(
      bottom: 0,
      right: 16,
      left: 16,
      child: SafeArea(
        top: false,
        bottom: true,
        child: Padding(
          padding: const EdgeInsets.only(bottom: 12),
          child: GestureDetector(
            onTap: onTap,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF0F172A), Color(0xFF1E293B)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(32),
                border: Border.all(color: AppColors.primary.withValues(alpha: 0.5), width: 1.5),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.55),
                    blurRadius: 25,
                    offset: const Offset(0, 10),
                  ),
                ],
              ),
              child: Row(
                children: [
                  // Circular App Icon with Pulse Radar
                  Stack(
                    alignment: Alignment.center,
                    children: [
                      Container(
                        width: 44,
                        height: 44,
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [AppColors.primary, AppColors.primaryDark],
                          ),
                          shape: BoxShape.circle,
                          boxShadow: [
                            BoxShadow(
                              color: AppColors.primary.withValues(alpha: 0.4),
                              blurRadius: 10,
                            ),
                          ],
                        ),
                        child: const Icon(
                          Icons.directions_car_filled_rounded,
                          color: AppColors.backgroundDark,
                          size: 24,
                        ),
                      ),
                      Positioned(
                        top: 0,
                        right: 0,
                        child: Container(
                          width: 12,
                          height: 12,
                          decoration: BoxDecoration(
                            color: AppColors.success,
                            shape: BoxShape.circle,
                            border: Border.all(color: AppColors.backgroundDark, width: 2),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(width: 12),

                  // Trip Info
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          '$driverName · ★ ${driverRating.toString()}',
                          style: const TextStyle(
                            color: AppColors.primary,
                            fontSize: 11,
                            fontWeight: FontWeight.w800,
                            letterSpacing: 0.3,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 2),
                        Text(
                          _statusText,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 13,
                            fontWeight: FontWeight.w700,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),

                  // Quick Google Maps Navigate Button
                  IconButton(
                    onPressed: _launchMaps,
                    style: IconButton.styleFrom(
                      backgroundColor: AppColors.success.withValues(alpha: 0.25),
                      foregroundColor: AppColors.success,
                    ),
                    icon: const Icon(Icons.navigation_rounded, size: 20),
                    tooltip: 'Navigate in Google Maps',
                  ),

                  // Expand Button
                  Container(
                    margin: const EdgeInsets.only(left: 4),
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      color: Colors.white12,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.fullscreen_rounded, color: Colors.white, size: 20),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
