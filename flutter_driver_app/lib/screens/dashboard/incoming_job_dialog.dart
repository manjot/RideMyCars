import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/constants/app_colors.dart';
import '../../core/services/sound_service.dart';
import '../../providers/country_provider.dart';

class IncomingJobDialog extends StatefulWidget {
  final Map<String, dynamic> request;
  final FutureOr<void> Function() onAccept;
  final FutureOr<void> Function() onDecline;

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
  bool _isAccepting = false;
  Timer? _countdownTimer;
  int _remainingSeconds = 300;

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

    final rawRemaining = widget.request['remaining_seconds'];
    final rawExpiresAt = widget.request['expires_at'];
    if (rawRemaining != null) {
      _remainingSeconds = int.tryParse(rawRemaining.toString()) ?? 300;
    } else if (rawExpiresAt != null) {
      try {
        final exp = DateTime.parse(rawExpiresAt.toString()).toLocal();
        final diff = exp.difference(DateTime.now()).inSeconds;
        _remainingSeconds = diff > 0 ? diff : 300;
      } catch (_) {
        _remainingSeconds = 300;
      }
    }
    if (_remainingSeconds <= 0) _remainingSeconds = 300;

    _countdownTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (_remainingSeconds > 1) {
        if (mounted) {
          setState(() {
            _remainingSeconds--;
          });
        }
      } else {
        timer.cancel();
        if (mounted) {
          _handleDecline();
        }
      }
    });
  }

  String _formatCountdown(int totalSeconds) {
    final s = totalSeconds < 0 ? 0 : totalSeconds;
    final minutes = s ~/ 60;
    final seconds = s % 60;
    return '${minutes.toString().padLeft(2, '0')}:${seconds.toString().padLeft(2, '0')}';
  }

  @override
  void dispose() {
    _countdownTimer?.cancel();
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

  Future<void> _previewMap(String location) async {
    if (location.isEmpty) return;
    final uri = Uri.parse('https://www.google.com/maps/search/?api=1&query=${Uri.encodeComponent(location)}');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  Future<void> _handleAccept() async {
    if (_isAccepting) return;
    setState(() => _isAccepting = true);
    SoundService.instance.stopRingtone();
    try {
      await Future.value(widget.onAccept()).timeout(const Duration(seconds: 10));
    } catch (e) {
      debugPrint('Error in _handleAccept: $e');
    } finally {
      if (mounted) {
        setState(() => _isAccepting = false);
      }
    }
  }

  void _handleDecline() {
    SoundService.instance.stopRingtone();
    widget.onDecline();
  }

  void _showPrescriptionViewerDialog(BuildContext context, List prescriptions) {
    if (prescriptions.isEmpty) return;
    final rx = prescriptions.first is Map ? Map<String, dynamic>.from(prescriptions.first) : {'file_name': 'Prescription'};
    final fileName = rx['file_name']?.toString() ?? 'Doctor Prescription';
    final fileType = rx['file_type']?.toString().toUpperCase() ?? 'DOC';
    final isPdf = fileType == 'PDF';
    final downloadUrl = rx['download_url']?.toString();

    showDialog(
      context: context,
      builder: (ctx) {
        TransformationController transformCtrl = TransformationController();
        return Dialog(
          backgroundColor: const Color(0xFF0F172A),
          insetPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          child: Container(
            padding: const EdgeInsets.all(16),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Row(
                  children: [
                    Icon(
                      isPdf ? Icons.picture_as_pdf_rounded : Icons.medical_services_rounded,
                      color: isPdf ? const Color(0xFFEF4444) : const Color(0xFF10B981),
                      size: 22,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            fileName,
                            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                          Text(
                            '$fileType Prescription • Pinch to zoom',
                            style: const TextStyle(color: Colors.white54, fontSize: 10.5),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close, color: Colors.white70),
                      onPressed: () => Navigator.pop(ctx),
                    ),
                  ],
                ),
                const Divider(color: Colors.white12, height: 16),
                Container(
                  height: 320,
                  width: double.infinity,
                  decoration: BoxDecoration(
                    color: const Color(0xFF1E293B),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: Colors.white12),
                  ),
                  clipBehavior: Clip.antiAlias,
                  child: InteractiveViewer(
                    transformationController: transformCtrl,
                    minScale: 0.8,
                    maxScale: 4.0,
                    child: Center(
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Container(
                              padding: const EdgeInsets.all(20),
                              decoration: BoxDecoration(
                                color: const Color(0xFF10B981).withOpacity(0.15),
                                shape: BoxShape.circle,
                              ),
                              child: Icon(
                                isPdf ? Icons.picture_as_pdf_rounded : Icons.receipt_long_rounded,
                                color: isPdf ? const Color(0xFFEF4444) : const Color(0xFF10B981),
                                size: 48,
                              ),
                            ),
                            const SizedBox(height: 12),
                            Text(
                              fileName,
                              textAlign: TextAlign.center,
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13.5),
                            ),
                            const SizedBox(height: 6),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                color: const Color(0xFF10B981).withOpacity(0.2),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: const Text(
                                '✓ VALID DOCTOR RX ATTACHED',
                                style: TextStyle(color: Color(0xFF10B981), fontSize: 10, fontWeight: FontWeight.w900),
                              ),
                            ),
                            const SizedBox(height: 8),
                            const Text(
                              'Present this to the pharmacist/counter for verification.',
                              textAlign: TextAlign.center,
                              style: TextStyle(color: Colors.white54, fontSize: 11),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        IconButton(
                          icon: const Icon(Icons.zoom_in, color: Color(0xFFF59E0B)),
                          tooltip: 'Zoom In',
                          onPressed: () {
                            transformCtrl.value = transformCtrl.value.scaled(1.25);
                          },
                        ),
                        IconButton(
                          icon: const Icon(Icons.zoom_out, color: Color(0xFFF59E0B)),
                          tooltip: 'Zoom Out',
                          onPressed: () {
                            transformCtrl.value = transformCtrl.value.scaled(0.8);
                          },
                        ),
                        IconButton(
                          icon: const Icon(Icons.restart_alt_rounded, color: Colors.white54),
                          tooltip: 'Reset Zoom',
                          onPressed: () {
                            transformCtrl.value = Matrix4.identity();
                          },
                        ),
                      ],
                    ),
                    if (downloadUrl != null && downloadUrl.isNotEmpty)
                      TextButton.icon(
                        icon: const Icon(Icons.download_rounded, color: Color(0xFF10B981), size: 16),
                        label: const Text('Download Rx', style: TextStyle(color: Color(0xFF10B981), fontWeight: FontWeight.bold, fontSize: 12)),
                        onPressed: () async {
                          final uri = Uri.parse(downloadUrl);
                          if (await canLaunchUrl(uri)) {
                            await launchUrl(uri, mode: LaunchMode.externalApplication);
                          }
                        },
                      ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final request = widget.request;
    final ride = request['ride'] is Map ? request['ride'] as Map<String, dynamic> : null;
    final booking = request['driver_booking'] is Map ? request['driver_booking'] as Map<String, dynamic> : null;

    final rawFare = request['fare'] ?? request['total_price'] ?? ride?['fare'] ?? ride?['total_amount'] ?? booking?['total_price'] ?? 0.0;
    final fare = double.tryParse(rawFare.toString()) ?? 0.0;
    final countryProv = Provider.of<CountryProvider>(context, listen: false);
    final currSym = (request['currency_symbol'] ?? ride?['currency_symbol'] ?? booking?['currency_symbol'] ?? countryProv.currencySymbol).toString();

    final type = request['type']?.toString();
    final isChauffeur = type == 'driver_booking' || (request['ride_id'] == null && (request['driver_booking_id'] != null || booking != null));
    final isDelivery = type == 'package_delivery' || request['package_delivery_id'] != null;
    final isBackup = request['is_backup'] == true ||
        request['assignment_type'] == 'backup' ||
        ride?['driver_assignment_type'] == 'backup' ||
        booking?['driver_assignment_type'] == 'backup';

    final category = (request['package_category'] ?? request['category'] ?? ride?['package_category'] ?? '').toString();
    final hasPrescription = request['has_prescription'] == true ||
        ride?['has_prescription'] == true ||
        (request['prescriptions'] is List && (request['prescriptions'] as List).isNotEmpty);
    final isPharmeasy = isDelivery && (category.toLowerCase() == 'pharmeasy' || hasPrescription);
    final List prescriptions = (request['prescriptions'] as List?) ?? (ride?['prescriptions'] as List?) ?? [];

    String title = 'New Ride Request!';
    String acceptButtonText = '✓ Accept Ride';
    Color themeColor = AppColors.primary;
    IconData orderIcon = Icons.local_taxi_rounded;

    if (isBackup) {
      title = isChauffeur ? 'Backup Chauffeur Request' : 'Proximity Backup Request';
      acceptButtonText = '✓ Accept & Reserve';
      themeColor = Colors.amber;
      orderIcon = Icons.shield_rounded;
    } else if (isChauffeur) {
      title = 'New Chauffeur Request!';
      acceptButtonText = '✓ Accept Chauffeur';
      themeColor = AppColors.purple;
      orderIcon = Icons.airline_seat_recline_extra_rounded;
    } else if (isPharmeasy) {
      title = '💊 Pharmeasy Medicine Delivery!';
      acceptButtonText = '✓ Accept Pharmeasy Delivery';
      themeColor = const Color(0xFF10B981);
      orderIcon = Icons.medical_services_rounded;
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
      insetPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
      child: Container(
        constraints: BoxConstraints(
          maxHeight: MediaQuery.of(context).size.height * 0.88,
        ),
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: AppColors.surfaceDark,
          borderRadius: BorderRadius.circular(28),
          border: Border.all(color: themeColor, width: 2.2),
          boxShadow: [
            BoxShadow(
              color: themeColor.withOpacity(0.35),
              blurRadius: 36,
              offset: const Offset(0, 10),
            ),
          ],
        ),
        child: SingleChildScrollView(
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

            // Proximity Chauffeur Backup Badge & Notice
            if (isBackup) ...[
              Container(
                margin: const EdgeInsets.only(bottom: 12),
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(
                  color: Colors.amber.withOpacity(0.15),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.amber.withOpacity(0.4), width: 1),
                ),
                child: const Row(
                  children: [
                    Icon(Icons.shield_rounded, color: Colors.amber, size: 18),
                    SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'PROXIMITY BACKUP: Primary driver unavailable. Acceptance temporarily reserves you while customer confirms.',
                        style: TextStyle(
                          color: Colors.amber,
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],

            // Live Driver Request Waiting Time Countdown Banner
            Container(
              margin: const EdgeInsets.only(bottom: 14),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: _remainingSeconds <= 30 ? Colors.red.withOpacity(0.18) : themeColor.withOpacity(0.12),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: _remainingSeconds <= 30 ? Colors.red : themeColor.withOpacity(0.4),
                  width: 1.5,
                ),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.timer_outlined,
                    color: _remainingSeconds <= 30 ? Colors.redAccent : themeColor,
                    size: 20,
                  ),
                  const SizedBox(width: 8),
                  const Text(
                    'Time to Accept: ',
                    style: TextStyle(
                      color: AppColors.textMuted,
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  Text(
                    _formatCountdown(_remainingSeconds),
                    style: TextStyle(
                      color: _remainingSeconds <= 30 ? Colors.redAccent : themeColor,
                      fontSize: 18,
                      fontWeight: FontWeight.w900,
                      fontFamily: 'monospace',
                      letterSpacing: 1.5,
                    ),
                  ),
                ],
              ),
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
            const SizedBox(height: 12),

            // Requested Date & Time Badge
            Builder(builder: (_) {
              final rawCreated = request['created_at'] ?? ride?['created_at'] ?? booking?['created_at'];
              final reqFormatted = request['request_time_formatted'] ?? ride?['request_time_formatted'] ?? booking?['request_time_formatted'];
              final reqHuman = request['request_time_human'] ?? ride?['request_time_human'] ?? booking?['request_time_human'];
              String displayTime = 'Just now';
              if (reqFormatted != null && reqFormatted.toString().isNotEmpty) {
                displayTime = reqFormatted.toString();
              } else if (rawCreated != null && rawCreated.toString().isNotEmpty) {
                try {
                  final dt = DateTime.parse(rawCreated.toString()).toLocal();
                  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                  final m = months[dt.month - 1];
                  final d = dt.day.toString().padLeft(2, '0');
                  final y = dt.year;
                  final hour = dt.hour % 12 == 0 ? 12 : dt.hour % 12;
                  final min = dt.minute.toString().padLeft(2, '0');
                  final ampm = dt.hour >= 12 ? 'PM' : 'AM';
                  displayTime = '$d $m $y • $hour:$min $ampm';
                } catch (_) {
                  displayTime = rawCreated.toString();
                }
              }

              final pickupDate = (request['pickup_date'] ?? ride?['pickup_date'] ?? booking?['pickup_date'])?.toString();
              final pickupTime = (request['pickup_time'] ?? ride?['pickup_time'] ?? booking?['pickup_time'])?.toString();
              final hasScheduled = (pickupDate != null && pickupDate.isNotEmpty && pickupDate != 'null') ||
                  (pickupTime != null && pickupTime.isNotEmpty && pickupTime != 'null');

              return Container(
                margin: const EdgeInsets.only(bottom: 12),
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.05),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.white12),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.schedule_rounded, color: AppColors.primary, size: 16),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Text(
                                'Requested: ',
                                style: TextStyle(color: AppColors.textMuted, fontSize: 11.5, fontWeight: FontWeight.bold),
                              ),
                              Expanded(
                                child: Text(
                                  displayTime + (reqHuman != null ? ' ($reqHuman)' : ''),
                                  style: const TextStyle(color: AppColors.textLight, fontSize: 11.5, fontWeight: FontWeight.bold),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
                          ),
                          if (hasScheduled) ...[
                            const SizedBox(height: 2),
                            Text(
                              '📅 Scheduled Pickup: ${pickupDate ?? ''} ${pickupTime ?? ''}'.trim(),
                              style: const TextStyle(color: Colors.amber, fontSize: 11, fontWeight: FontWeight.w700),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ],
                ),
              );
            }),

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
                    '$currSym${fare.toStringAsFixed(2)}',
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

            // Unified Payment Method Badge
            Builder(builder: (_) {
              final rawMethod = (request['payment_method'] ?? ride?['payment_method'] ?? booking?['payment_method'] ?? 'cash').toString().toLowerCase();
              String payName = 'Cash Direct Pay';
              String paySubtitle = 'Collect cash from passenger / recipient';
              Color payColor = const Color(0xFF10B981);
              IconData payIcon = Icons.payments_rounded;

              if (rawMethod.contains('stripe') || rawMethod.contains('card')) {
                payName = 'Stripe (Cards & Apple Pay)';
                paySubtitle = 'Prepaid online • No cash collection needed';
                payColor = const Color(0xFF6366F1);
                payIcon = Icons.credit_card_rounded;
              } else if (rawMethod.contains('momo')) {
                payName = 'MoMo Pay';
                paySubtitle = 'Prepaid via Mobile Money • Digital prompt';
                payColor = const Color(0xFFFFCC00);
                payIcon = Icons.phone_android_rounded;
              } else if (rawMethod.contains('wallet')) {
                payName = 'RideMyCars Wallet';
                paySubtitle = 'Prepaid with verified in-app balance';
                payColor = const Color(0xFFF59E0B);
                payIcon = Icons.account_balance_wallet_rounded;
              }

              return Container(
                margin: const EdgeInsets.only(top: 10),
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(
                  color: payColor.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: payColor.withOpacity(0.4)),
                ),
                child: Row(
                  children: [
                    Icon(payIcon, size: 20, color: payColor),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            payName,
                            style: TextStyle(
                              color: payColor,
                              fontSize: 12.5,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                          Text(
                            paySubtitle,
                            style: const TextStyle(
                              color: Colors.white70,
                              fontSize: 11,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              );
            }),
            // Pharmeasy Doctor Prescription Banner
            if (isPharmeasy) ...[
              Container(
                margin: const EdgeInsets.only(top: 12),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFF10B981).withOpacity(0.12),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: const Color(0xFF10B981).withOpacity(0.4)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        const Icon(Icons.local_pharmacy_rounded, color: Color(0xFF10B981), size: 20),
                        const SizedBox(width: 8),
                        const Expanded(
                          child: Text(
                            'PHARMEASY DELIVERY (Doctor Rx)',
                            style: TextStyle(
                              color: Color(0xFF10B981),
                              fontSize: 12,
                              fontWeight: FontWeight.w900,
                              letterSpacing: 0.5,
                            ),
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: const Color(0xFF10B981),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: const Text(
                            'PRESCRIPTION OK',
                            style: TextStyle(color: Colors.black, fontSize: 9, fontWeight: FontWeight.w900),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 6),
                    const Text(
                      'Mandatory doctor prescription attached. Present to pharmacy before medicine collection.',
                      style: TextStyle(color: Colors.white70, fontSize: 11),
                    ),
                    if (prescriptions.isNotEmpty) ...[
                      const SizedBox(height: 8),
                      InkWell(
                        onTap: () => _showPrescriptionViewerDialog(context, prescriptions),
                        borderRadius: BorderRadius.circular(8),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.08),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.zoom_in_rounded, color: Color(0xFFF59E0B), size: 16),
                              SizedBox(width: 6),
                              Text(
                                'View Doctor Prescription (Zoom)',
                                style: TextStyle(color: Color(0xFFF59E0B), fontSize: 11.5, fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ],
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
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Expanded(
                            child: Text(
                              isDelivery ? 'Pickup: $pickup' : pickup,
                              style: const TextStyle(
                                color: AppColors.textLight,
                                fontWeight: FontWeight.w600,
                                fontSize: 13,
                              ),
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          const SizedBox(width: 6),
                          InkWell(
                            onTap: () => _previewMap(pickup),
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: Colors.white.withOpacity(0.08),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: const Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(Icons.map_rounded, color: AppColors.success, size: 12),
                                  SizedBox(width: 4),
                                  Text('Map', style: TextStyle(color: AppColors.success, fontSize: 10, fontWeight: FontWeight.bold)),
                                ],
                              ),
                            ),
                          ),
                        ],
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
                    height: 52,
                    child: OutlinedButton(
                      onPressed: _handleDecline,
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppColors.danger,
                        padding: const EdgeInsets.symmetric(horizontal: 4),
                        side: BorderSide(color: AppColors.danger.withOpacity(0.4), width: 1.2),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                        ),
                      ),
                      child: const FittedBox(
                        fit: BoxFit.scaleDown,
                        child: Text(
                          '✕ Decline',
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
                    height: 52,
                    child: ElevatedButton(
                      onPressed: _isAccepting ? null : _handleAccept,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.success,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 6),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                        ),
                        elevation: 6,
                      ),
                      child: _isAccepting
                          ? const Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                SizedBox(
                                  width: 18,
                                  height: 18,
                                  child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white),
                                ),
                                SizedBox(width: 8),
                                Text(
                                  'Accepting...',
                                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                                ),
                              ],
                            )
                          : FittedBox(
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
    ),
  );
  }
}
