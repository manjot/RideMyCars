import 'dart:async';
import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/constants/app_colors.dart';
import '../../core/services/sound_service.dart';

class IncomingJobDialog extends StatefulWidget {
  final Map<String, dynamic> request;
  final VoidCallback onAccept;
  final VoidCallback onDecline;

  const IncomingJobDialog({
    super.key,
    required this.request,
    required this.onAccept,
    required this.onDecline,
  });

  @override
  State<IncomingJobDialog> createState() => _IncomingJobDialogState();
}

class _IncomingJobDialogState extends State<IncomingJobDialog> with SingleTickerProviderStateMixin {
  late AnimationController _pulseController;
  late Animation<double> _pulseAnimation;

  @override
  void initState() {
    super.initState();
    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 900),
    )..repeat(reverse: true);

    _pulseAnimation = Tween<double>(begin: 1.0, end: 1.2).animate(
      CurvedAnimation(parent: _pulseController, curve: Curves.easeInOut),
    );

    // Guarantee the loud and long ringtone starts playing immediately on dialog opening
    final type = widget.request['type']?.toString() ?? 'order';
    final id = widget.request['ride_id'] ?? widget.request['package_delivery_id'] ?? widget.request['assignment_id'] ?? widget.request['id'];
    SoundService.instance.startIncomingOrderRingtone(orderType: type, orderId: id);
  }

  @override
  void dispose() {
    _pulseController.dispose();
    // Guarantee sound stops whenever the dialog closes/dismisses
    SoundService.instance.stopRingtone();
    super.dispose();
  }

  Future<void> _callPhone(String? phone) async {
    if (phone == null || phone.isEmpty) return;
    final uri = Uri.parse('tel:$phone');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }

  void _handleAccept() {
    SoundService.instance.stopRingtone();
    widget.onAccept();
  }

  void _handleDecline() {
    SoundService.instance.stopRingtone();
    widget.onDecline();
  }

  @override
  Widget build(BuildContext context) {
    final request = widget.request;
    final ride = request['ride'] is Map ? request['ride'] as Map<String, dynamic> : null;
    final booking = request['driver_booking'] is Map ? request['driver_booking'] as Map<String, dynamic> : null;

    final rawFare = request['fare'] ?? request['total_price'] ?? ride?['fare'] ?? ride?['total_amount'] ?? booking?['total_price'] ?? 0.0;
    final fare = double.tryParse(rawFare.toString()) ?? 0.0;

    final type = request['type']?.toString();
    final isChauffeur = type == 'driver_booking' || (request['ride_id'] == null && (request['driver_booking_id'] != null || booking != null));
    final isDelivery = type == 'package_delivery' || request['package_delivery_id'] != null;

    String title = 'New Ride Request!';
    String acceptButtonText = '✓ Accept Ride';
    Color themeColor = AppColors.primary;
    IconData orderIcon = Icons.local_taxi_rounded;

    if (isChauffeur) {
      title = 'New Chauffeur Request!';
      acceptButtonText = '✓ Accept Chauffeur';
      themeColor = AppColors.purple;
      orderIcon = Icons.airline_seat_recline_extra_rounded;
    } else if (isDelivery) {
      title = 'New Delivery Request!';
      acceptButtonText = '✓ Accept Delivery';
      themeColor = AppColors.info;
      orderIcon = Icons.local_shipping_rounded;
    }

    final pickup = (request['pickup_location'] ?? ride?['pickup_location'] ?? booking?['pickup_location'] ?? 'Pickup location').toString();
    final dropoff = (request['dropoff_location'] ?? ride?['dropoff_location'] ?? booking?['dropoff_location'] ?? 'Destination').toString();

    final customerName = (request['customer_name'] ?? request['rider_name'] ?? request['passenger_name'] ?? request['client_name'] ?? ride?['passenger_name'] ?? ride?['rider']?['name'] ?? booking?['client']?['name'] ?? 'Customer').toString();
    final customerPhone = request['customer_phone'] ?? request['rider_phone'] ?? request['passenger_phone'] ?? ride?['rider_phone'] ?? ride?['rider']?['phone'] ?? booking?['client']?['phone'];
    final pocName = request['poc_name'] ?? ride?['poc_name'] ?? booking?['contact_person_name'];
    final pocPhone = request['poc_phone'] ?? ride?['poc_phone'] ?? booking?['contact_phone'];
    final bool hasPoc = pocName != null && pocName.toString().isNotEmpty && pocName.toString() != customerName;

    final distanceKm = request['distance_km'] ?? ride?['distance_km'];
    final durationMins = request['duration_minutes'] ?? ride?['duration_minutes'] ?? 15;

    return Dialog(
      backgroundColor: Colors.transparent,
      insetPadding: const EdgeInsets.symmetric(horizontal: 20),
      child: Container(
        padding: const EdgeInsets.all(22),
        decoration: BoxDecoration(
          color: AppColors.surfaceDark,
          borderRadius: BorderRadius.circular(28),
          border: Border.all(color: themeColor, width: 2),
          boxShadow: [
            BoxShadow(
              color: themeColor.withOpacity(0.3),
              blurRadius: 36,
              offset: const Offset(0, 10),
            ),
          ],
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Sound Alert Top Banner with Mute / Unmute Control
            ValueListenableBuilder<bool>(
              valueListenable: SoundService.instance.isMutedNotifier,
              builder: (context, isMuted, _) {
                return Container(
                  margin: const EdgeInsets.only(bottom: 14),
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  decoration: BoxDecoration(
                    color: isMuted ? Colors.white.withOpacity(0.05) : Colors.redAccent.withOpacity(0.15),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(
                      color: isMuted ? Colors.white24 : Colors.redAccent.withOpacity(0.5),
                      width: 1,
                    ),
                  ),
                  child: Row(
                    children: [
                      ScaleTransition(
                        scale: isMuted ? const AlwaysStoppedAnimation(1.0) : _pulseAnimation,
                        child: Icon(
                          isMuted ? Icons.volume_off_rounded : Icons.campaign_rounded,
                          color: isMuted ? AppColors.textMuted : Colors.redAccent,
                          size: 20,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          isMuted ? 'Ringtone Muted (Waiting response)' : 'LOUD RINGTONE PLAYING...',
                          style: TextStyle(
                            color: isMuted ? AppColors.textMuted : Colors.redAccent,
                            fontSize: 11,
                            fontWeight: FontWeight.w900,
                            letterSpacing: 0.5,
                          ),
                        ),
                      ),
                      InkWell(
                        onTap: () => SoundService.instance.toggleMute(),
                        borderRadius: BorderRadius.circular(8),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            isMuted ? 'Unmute' : 'Mute Sound',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                );
              },
            ),

            // Header Row: Type Icon & Order Title
            Row(
              children: [
                ScaleTransition(
                  scale: _pulseAnimation,
                  child: Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: themeColor.withOpacity(0.2),
                      shape: BoxShape.circle,
                    ),
                    child: Icon(
                      orderIcon,
                      color: themeColor,
                      size: 26,
                    ),
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: const TextStyle(
                          color: AppColors.textLight,
                          fontWeight: FontWeight.w900,
                          fontSize: 17,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        isDelivery ? 'Sender: $customerName' : 'Customer: $customerName',
                        style: const TextStyle(
                          color: AppColors.textLight,
                          fontWeight: FontWeight.bold,
                          fontSize: 14,
                        ),
                      ),
                      if (customerPhone != null && customerPhone.toString().isNotEmpty)
                        Text(
                          customerPhone.toString(),
                          style: TextStyle(
                            color: themeColor,
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                    ],
                  ),
                ),
                if (customerPhone != null && customerPhone.toString().isNotEmpty)
                  ElevatedButton.icon(
                    onPressed: () => _callPhone(customerPhone.toString()),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.success,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      minimumSize: Size.zero,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                    icon: const Icon(Icons.phone_rounded, size: 14),
                    label: const Text('Call', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11)),
                  ),
              ],
            ),

            if (hasPoc) ...[
              const SizedBox(height: 10),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(
                  color: Colors.amber.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.amber.withOpacity(0.3)),
                ),
                child: Row(
                  children: [
                    Icon(isDelivery ? Icons.inventory_2_rounded : Icons.badge_rounded, color: Colors.amber, size: 18),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            isDelivery ? 'RECIPIENT / DROP-OFF POC' : 'PASSENGER / POC',
                            style: const TextStyle(color: Colors.amber, fontSize: 9, fontWeight: FontWeight.w900, letterSpacing: 0.5),
                          ),
                          Text(
                            pocName.toString(),
                            style: const TextStyle(
                              color: AppColors.textLight,
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          if (pocPhone != null && pocPhone.toString().isNotEmpty)
                            Text(
                              pocPhone.toString(),
                              style: const TextStyle(
                                color: AppColors.textMuted,
                                fontSize: 11,
                              ),
                            ),
                        ],
                      ),
                    ),
                    if (pocPhone != null && pocPhone.toString().isNotEmpty)
                      ElevatedButton.icon(
                        onPressed: () => _callPhone(pocPhone.toString()),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.amber.shade700,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                          minimumSize: Size.zero,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                        icon: const Icon(Icons.phone_rounded, size: 14),
                        label: Text(isDelivery ? 'Call Recipient' : 'Call POC', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 11)),
                      ),
                  ],
                ),
              ),
            ],
            const SizedBox(height: 14),

            // Huge Fare Badge
            Container(
              padding: const EdgeInsets.symmetric(vertical: 14),
              decoration: BoxDecoration(
                color: AppColors.backgroundDark,
                borderRadius: BorderRadius.circular(18),
                border: Border.all(color: Colors.white10),
              ),
              child: Column(
                children: [
                  Text(
                    isDelivery ? 'ESTIMATED DELIVERY EARNING' : 'ESTIMATED FARE',
                    style: const TextStyle(
                      color: AppColors.textMuted,
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                      letterSpacing: 1,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '\$${fare.toStringAsFixed(2)}',
                    style: const TextStyle(
                      color: AppColors.success,
                      fontSize: 32,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  if (distanceKm != null)
                    Text(
                      '~$distanceKm km ($durationMins mins)',
                      style: const TextStyle(
                        color: AppColors.textMuted,
                        fontSize: 12,
                      ),
                    ),
                ],
              ),
            ),
            const SizedBox(height: 18),

            // Route Details
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Column(
                  children: [
                    const Icon(Icons.circle, color: AppColors.success, size: 12),
                    Container(width: 2, height: 26, color: Colors.white24),
                    const Icon(Icons.location_on_rounded, color: AppColors.danger, size: 14),
                  ],
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        isDelivery ? 'Pickup: $pickup' : pickup,
                        style: const TextStyle(
                          color: AppColors.textLight,
                          fontWeight: FontWeight.w600,
                          fontSize: 13,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 14),
                      Text(
                        isDelivery ? 'Delivery Dropoff: $dropoff' : dropoff,
                        style: const TextStyle(
                          color: AppColors.textLight,
                          fontWeight: FontWeight.w600,
                          fontSize: 13,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 24),

            // Action Buttons: Decline and Accept
            Row(
              children: [
                Expanded(
                  flex: 2,
                  child: SizedBox(
                    height: 50,
                    child: OutlinedButton(
                      onPressed: _handleDecline,
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppColors.textMuted,
                        padding: const EdgeInsets.symmetric(horizontal: 4),
                        side: BorderSide(color: Colors.white.withOpacity(0.15)),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
                        ),
                      ),
                      child: const FittedBox(
                        fit: BoxFit.scaleDown,
                        child: Text(
                          'Decline',
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                        ),
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  flex: 3,
                  child: SizedBox(
                    height: 50,
                    child: ElevatedButton(
                      onPressed: _handleAccept,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.success,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 6),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
                        ),
                        elevation: 6,
                      ),
                      child: FittedBox(
                        fit: BoxFit.scaleDown,
                        child: Text(
                          acceptButtonText,
                          style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w900),
                        ),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
