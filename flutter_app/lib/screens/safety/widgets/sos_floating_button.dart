import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../core/constants/app_colors.dart';
import '../../../services/sos_service.dart';
import '../sos_contacts_screen.dart';

class SosFloatingButton extends StatelessWidget {
  final int? rideId;
  final String role;
  final VoidCallback? onTriggered;

  const SosFloatingButton({
    super.key,
    this.rideId,
    this.role = 'rider',
    this.onTriggered,
  });

  void _openEmergencySheet(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _EmergencyActionSheet(
        rideId: rideId,
        role: role,
        onTriggered: onTriggered,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      elevation: 8,
      shadowColor: Colors.red.withOpacity(0.5),
      shape: const CircleBorder(),
      child: InkWell(
        onTap: () => _openEmergencySheet(context),
        customBorder: const CircleBorder(),
        child: Container(
          width: 52,
          height: 52,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            gradient: const LinearGradient(
              colors: [Color(0xFFDC2626), Color(0xFFEF4444)], // Vibrant Red
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            border: Border.all(color: Colors.white, width: 2),
            boxShadow: [
              BoxShadow(
                color: Colors.red.withOpacity(0.4),
                blurRadius: 10,
                spreadRadius: 2,
              ),
            ],
          ),
          child: const Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.warning_rounded, color: Colors.white, size: 16),
                Text(
                  'SOS',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 10,
                    fontWeight: FontWeight.w900,
                    letterSpacing: 0.5,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _EmergencyActionSheet extends StatefulWidget {
  final int? rideId;
  final String role;
  final VoidCallback? onTriggered;

  const _EmergencyActionSheet({
    this.rideId,
    required this.role,
    this.onTriggered,
  });

  @override
  State<_EmergencyActionSheet> createState() => _EmergencyActionSheetState();
}

class _EmergencyActionSheetState extends State<_EmergencyActionSheet> {
  bool _isBroadcasting = false;

  Future<void> _makeCall(String phone) async {
    final uri = Uri.parse('tel:$phone');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }

  Future<void> _broadcastSos() async {
    setState(() => _isBroadcasting = true);

    double? lat;
    double? lng;

    try {
      LocationPermission perm = await Geolocator.checkPermission();
      if (perm == LocationPermission.denied) {
        perm = await Geolocator.requestPermission();
      }
      if (perm == LocationPermission.whileInUse || perm == LocationPermission.always) {
        final pos = await Geolocator.getCurrentPosition(timeLimit: const Duration(seconds: 5));
        lat = pos.latitude;
        lng = pos.longitude;
      }
    } catch (e) {
      debugPrint('Could not fetch location for SOS: $e');
    }

    final res = await SosService.triggerSos(
      latitude: lat,
      longitude: lng,
      rideId: widget.rideId,
      role: widget.role,
      emergencyServiceDialed: 'Broadcast Alert',
    );

    if (!mounted) return;
    setState(() => _isBroadcasting = false);

    if (res['success'] == true) {
      Navigator.pop(context);
      widget.onTriggered?.call();

      final data = res['data'] as Map<String, dynamic>? ?? {};
      final contactsCount = (data['contacts_notified'] as List?)?.length ?? 0;
      final msg = data['sos_message']?.toString() ?? 'Emergency SOS dispatched!';

      showDialog(
        context: context,
        builder: (ctx) => AlertDialog(
          backgroundColor: AppColors.surfaceDark,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: const Row(
            children: [
              Icon(Icons.shield_rounded, color: AppColors.danger, size: 28),
              SizedBox(width: 10),
              Text('SOS Dispatched!', style: TextStyle(color: AppColors.textLight, fontSize: 18, fontWeight: FontWeight.bold)),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Emergency alerts sent to $contactsCount trusted contacts and RideMyCars 24/7 Security Operations Center.',
                style: const TextStyle(color: AppColors.textLight, fontSize: 13, height: 1.4),
              ),
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.backgroundDark,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.white10),
                ),
                child: Text(
                  msg,
                  style: const TextStyle(color: AppColors.textMuted, fontSize: 11),
                ),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('Dismiss', style: TextStyle(color: AppColors.textMuted)),
            ),
            ElevatedButton.icon(
              onPressed: () {
                Navigator.pop(ctx);
                _makeCall('+18007433692');
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.danger,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              icon: const Icon(Icons.phone_in_talk_rounded, size: 16),
              label: const Text('Call Safety Desk', style: TextStyle(fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res['message'] ?? 'Could not dispatch SOS. Dialing 911 directly.'),
          backgroundColor: AppColors.danger,
        ),
      );
      _makeCall('911');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
      ),
      padding: const EdgeInsets.fromLTRB(20, 14, 20, 24),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Drag handle
          Center(
            child: Container(
              width: 44,
              height: 5,
              decoration: BoxDecoration(
                color: Colors.white24,
                borderRadius: BorderRadius.circular(10),
              ),
            ),
          ),
          const SizedBox(height: 16),

          // Header
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: AppColors.danger.withOpacity(0.15),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.emergency_rounded, color: AppColors.danger, size: 24),
              ),
              const SizedBox(width: 12),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Emergency SOS Assistance',
                      style: TextStyle(color: AppColors.textLight, fontSize: 17, fontWeight: FontWeight.w800),
                    ),
                    Text(
                      'Rapid response & trusted contacts broadcast',
                      style: TextStyle(color: AppColors.textMuted, fontSize: 11),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),

          // 1. One-Tap Broadcast SOS
          ElevatedButton(
            onPressed: _isBroadcasting ? null : _broadcastSos,
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFDC2626),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              padding: const EdgeInsets.symmetric(vertical: 16),
              elevation: 4,
            ),
            child: _isBroadcasting
                ? const SizedBox(
                    height: 22,
                    width: 22,
                    child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2.5),
                  )
                : const Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.wifi_tethering_rounded, size: 20),
                      SizedBox(width: 10),
                      Text(
                        '1-TAP BROADCAST SOS TO CONTACTS',
                        style: TextStyle(fontWeight: FontWeight.w900, fontSize: 13, letterSpacing: 0.5),
                      ),
                    ],
                  ),
          ),
          const SizedBox(height: 12),

          // 2. Dial Police / Local Emergency
          _buildActionTile(
            icon: Icons.local_police_rounded,
            iconColor: AppColors.danger,
            title: 'Call Local Police (911 / 112)',
            subtitle: 'Direct telephone connection to emergency services',
            onTap: () => _makeCall('911'),
          ),
          const SizedBox(height: 10),

          // 3. Dial 24/7 Safety Desk
          _buildActionTile(
            icon: Icons.headset_mic_rounded,
            iconColor: const Color(0xFFF97316),
            title: 'RideMyCars 24/7 Safety Desk',
            subtitle: '+1 (800) 743-3692 • Priority Security Dispatch',
            onTap: () => _makeCall('+18007433692'),
          ),
          const SizedBox(height: 10),

          // 4. Manage Trusted Contacts
          _buildActionTile(
            icon: Icons.people_alt_rounded,
            iconColor: AppColors.info,
            title: 'Manage Emergency Contacts',
            subtitle: 'Add, edit, or remove trusted safety contacts',
            onTap: () {
              Navigator.pop(context);
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const SosContactsScreen()),
              );
            },
          ),
          const SizedBox(height: 14),

          // Cancel
          OutlinedButton(
            onPressed: () => Navigator.pop(context),
            style: OutlinedButton.styleFrom(
              foregroundColor: AppColors.textLight,
              side: const BorderSide(color: Colors.white24),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              padding: const EdgeInsets.symmetric(vertical: 12),
            ),
            child: const Text('Cancel', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  Widget _buildActionTile({
    required IconData icon,
    required Color iconColor,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(14),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        decoration: BoxDecoration(
          color: AppColors.backgroundDark,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: Colors.white.withOpacity(0.06)),
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: iconColor.withOpacity(0.12),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, color: iconColor, size: 20),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title, style: const TextStyle(color: AppColors.textLight, fontSize: 13, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 2),
                  Text(subtitle, style: const TextStyle(color: AppColors.textMuted, fontSize: 11)),
                ],
              ),
            ),
            const Icon(Icons.arrow_forward_ios_rounded, color: AppColors.textMuted, size: 14),
          ],
        ),
      ),
    );
  }
}
