import 'dart:async';
import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/api/api_client.dart';
import '../../core/constants/app_colors.dart';
import '../rider/rider_home_screen.dart';

class DeliveryTrackerScreen extends StatefulWidget {
  final int deliveryId;
  final String deliveryCode;
  final String pickupLocation;
  final String dropoffLocation;
  final String recipientName;
  final String recipientPhone;
  final String packageCategory;
  final double totalPrice;
  final String currencySymbol;
  final String initialOtp;
  final String paymentMethod;

  const DeliveryTrackerScreen({
    super.key,
    required this.deliveryId,
    required this.deliveryCode,
    required this.pickupLocation,
    required this.dropoffLocation,
    required this.recipientName,
    required this.recipientPhone,
    required this.packageCategory,
    required this.totalPrice,
    this.currencySymbol = '\$',
    required this.initialOtp,
    required this.paymentMethod,
  });

  @override
  State<DeliveryTrackerScreen> createState() => _DeliveryTrackerScreenState();
}

class _DeliveryTrackerScreenState extends State<DeliveryTrackerScreen> {
  Timer? _pollTimer;
  String _deliveryStatus = 'pending';
  String _paymentStatus = 'pending';
  Map<String, dynamic>? _courierData;
  late String _otp;
  String? _requestedTime;
  String? _scheduledPickup;
  List<Map<String, dynamic>> _prescriptions = [];
  bool _hasPrescription = false;

  final List<String> _stages = [
    'Order Confirmed',
    'Courier Assigned',
    'Parcel Picked Up',
    'In Transit',
    'PIN Verified & Delivered',
  ];

  @override
  void initState() {
    super.initState();
    _otp = widget.initialOtp;
    _hasPrescription = widget.packageCategory == 'Pharmeasy';
    _pollStatus();
    _pollTimer = Timer.periodic(const Duration(seconds: 4), (_) => _pollStatus());
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    super.dispose();
  }

  Future<void> _pollStatus() async {
    try {
      final res = await ApiClient().dio.get('/delivery/${widget.deliveryId}/status');
      if (res.statusCode == 200 && res.data != null) {
        final data = res.data;
        if (mounted) {
          setState(() {
            _deliveryStatus = data['status']?.toString() ?? _deliveryStatus;
            _paymentStatus = data['payment_status']?.toString() ?? _paymentStatus;
            if (data['delivery_otp'] != null) {
              _otp = data['delivery_otp'].toString();
            }
            if (data['courier'] != null && data['courier'] is Map) {
              _courierData = Map<String, dynamic>.from(data['courier']);
            }
            if (data['has_prescription'] == true || widget.packageCategory == 'Pharmeasy') {
              _hasPrescription = true;
            }
            if (data['prescriptions'] is List) {
              _prescriptions = List<Map<String, dynamic>>.from(
                (data['prescriptions'] as List).map((x) => Map<String, dynamic>.from(x as Map)),
              );
            }
            if (data['request_time_formatted'] != null && data['request_time_formatted'].toString().isNotEmpty) {
              _requestedTime = data['request_time_formatted'].toString();
            } else if (data['created_at'] != null && data['created_at'].toString().isNotEmpty) {
              try {
                final dt = DateTime.parse(data['created_at'].toString()).toLocal();
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                final m = months[dt.month - 1];
                final d = dt.day.toString().padLeft(2, '0');
                final y = dt.year;
                final hour = dt.hour % 12 == 0 ? 12 : dt.hour % 12;
                final min = dt.minute.toString().padLeft(2, '0');
                final ampm = dt.hour >= 12 ? 'PM' : 'AM';
                _requestedTime = '$d $m $y • $hour:$min $ampm';
              } catch (_) {
                _requestedTime = data['created_at'].toString();
              }
            }
            final pDate = data['pickup_date']?.toString();
            final pTime = data['pickup_time']?.toString();
            if ((pDate != null && pDate.isNotEmpty && pDate != 'null') || (pTime != null && pTime.isNotEmpty && pTime != 'null')) {
              _scheduledPickup = '${pDate ?? ''} ${pTime ?? ''}'.trim();
            }
          });
        }
      }
    } catch (_) {}
  }

  int get _currentStageIndex {
    switch (_deliveryStatus) {
      case 'courier_assigned':
      case 'courier_accepted':
      case 'going_to_pickup':
        return 1;
      case 'arrived_at_pickup':
      case 'parcel_picked_up':
        return 2;
      case 'in_transit':
      case 'arrived_at_destination':
        return 3;
      case 'delivered':
        return 4;
      default:
        return 0; // Order Confirmed / Pending
    }
  }

  Future<void> _launchCall(String phone) async {
    final uri = Uri.parse('tel:$phone');
    if (await canLaunchUrl(uri)) await launchUrl(uri);
  }

  Future<void> _launchWhatsApp(String phone) async {
    final cleaned = phone.replaceAll(RegExp(r'[^0-9]'), '');
    final uri = Uri.parse('https://wa.me/$cleaned?text=Hello,%20checking%20status%20of%20my%20parcel%20delivery%20${widget.deliveryCode}');
    if (await canLaunchUrl(uri)) await launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  void _showPrescriptionViewer(Map<String, dynamic> rx) {
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
                      isPdf ? Icons.picture_as_pdf_rounded : Icons.medical_information_rounded,
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
                            '$fileType Document • Pinch or click to zoom',
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
                  height: 300,
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
                                '✓ VALID PRESCRIPTION ATTACHED',
                                style: TextStyle(color: Color(0xFF10B981), fontSize: 10, fontWeight: FontWeight.w900),
                              ),
                            ),
                            const SizedBox(height: 8),
                            const Text(
                              'Pinch with 2 fingers to zoom in/out\nDriver & Pharmacy store will inspect this prescription',
                              textAlign: TextAlign.center,
                              style: TextStyle(color: Colors.white38, fontSize: 10.5),
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
                        label: const Text('Download', style: TextStyle(color: Color(0xFF10B981), fontWeight: FontWeight.bold, fontSize: 12)),
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
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final isPending = _currentStageIndex == 0;

    return WillPopScope(
      onWillPop: () async {
        Navigator.pushAndRemoveUntil(
          context,
          MaterialPageRoute(builder: (_) => const RiderHomeScreen()),
          (route) => false,
        );
        return false;
      },
      child: Scaffold(
        backgroundColor: isDark ? const Color(0xFF0D1117) : const Color(0xFFF8FAFC),
        appBar: AppBar(
          title: Text(
            'Parcel #${widget.deliveryCode}',
            style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 17),
          ),
          centerTitle: true,
          elevation: 0,
          leading: IconButton(
            icon: const Icon(Icons.arrow_back_rounded),
            onPressed: () {
              Navigator.pushAndRemoveUntil(
                context,
                MaterialPageRoute(builder: (_) => const RiderHomeScreen()),
                (route) => false,
              );
            },
          ),
        ),
        body: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Radar Animation if searching / pending
              if (isPending)
                Container(
                  padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 16),
                  margin: const EdgeInsets.only(bottom: 20),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [
                        Colors.amber.shade700,
                        Colors.orange.shade800,
                      ],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(24),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.orange.withOpacity(0.3),
                        blurRadius: 20,
                        offset: const Offset(0, 8),
                      ),
                    ],
                  ),
                  child: Column(
                    children: [
                      Container(
                        width: 64,
                        height: 64,
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.2),
                          shape: BoxShape.circle,
                        ),
                        child: const Center(
                          child: Text('🛵', style: TextStyle(fontSize: 32)),
                        ),
                      ),
                      const SizedBox(height: 12),
                      const Text(
                        'Dispatching Nearest Courier...',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w900,
                          color: Colors.white,
                        ),
                      ),
                      const SizedBox(height: 4),
                      const Text(
                        'Matching vetted drivers within 5km radius',
                        style: TextStyle(fontSize: 12, color: Colors.white70),
                      ),
                    ],
                  ),
                ),

              // Recipient 4-Digit Security OTP Card
              Container(
                padding: const EdgeInsets.all(18),
                margin: const EdgeInsets.only(bottom: 18),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF161B26) : Colors.white,
                  borderRadius: BorderRadius.circular(22),
                  border: Border.all(color: Colors.orange.withOpacity(0.35)),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.orange.withOpacity(0.08),
                      blurRadius: 16,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.orange.withOpacity(0.12),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.pin_rounded, color: Colors.orange, size: 28),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'RECIPIENT DELIVERY OTP',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w900,
                              color: Colors.orange,
                              letterSpacing: 1.0,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            _otp,
                            style: const TextStyle(
                              fontSize: 26,
                              fontWeight: FontWeight.w900,
                              letterSpacing: 6.0,
                            ),
                          ),
                          const Text(
                            'Recipient must provide this code to courier upon delivery.',
                            style: TextStyle(fontSize: 10, color: Colors.grey),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),

              // Assigned Courier Card (if courier assigned)
              if (_courierData != null) ...[
                Container(
                  padding: const EdgeInsets.all(18),
                  margin: const EdgeInsets.only(bottom: 18),
                  decoration: BoxDecoration(
                    color: isDark ? const Color(0xFF161B26) : Colors.white,
                    borderRadius: BorderRadius.circular(22),
                    border: Border.all(color: Colors.green.withOpacity(0.35)),
                  ),
                  child: Row(
                    children: [
                      CircleAvatar(
                        radius: 26,
                        backgroundColor: Colors.green.withOpacity(0.15),
                        child: Text(
                          (_courierData!['name']?.toString() ?? 'C').substring(0, 1).toUpperCase(),
                          style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.green),
                        ),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              _courierData!['name']?.toString() ?? 'Assigned Courier',
                              style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w900),
                            ),
                            Text(
                              _courierData!['vehicle']?.toString() ?? 'Courier Partner',
                              style: TextStyle(fontSize: 11, color: isDark ? Colors.white60 : Colors.black54),
                            ),
                          ],
                        ),
                      ),
                      if (_courierData!['phone'] != null) ...[
                        IconButton(
                          icon: const Icon(Icons.phone, color: Colors.green),
                          onPressed: () => _launchCall(_courierData!['phone'].toString()),
                        ),
                        IconButton(
                          icon: const Icon(Icons.chat_bubble_outline, color: Colors.green),
                          onPressed: () => _launchWhatsApp(_courierData!['phone'].toString()),
                        ),
                      ],
                    ],
                  ),
                ),
              ],

              // 5-Stage Delivery Progress Timeline
              Container(
                padding: const EdgeInsets.all(20),
                margin: const EdgeInsets.only(bottom: 18),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF161B26) : Colors.white,
                  borderRadius: BorderRadius.circular(24),
                  border: Border.all(color: isDark ? Colors.white10 : Colors.grey.shade200),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Delivery Progress Timeline',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
                    ),
                    const SizedBox(height: 16),
                    ListView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: _stages.length,
                      itemBuilder: (context, i) {
                        final isCompleted = i <= _currentStageIndex;
                        final isCurrent = i == _currentStageIndex;
                        return Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Column(
                              children: [
                                Container(
                                  width: 26,
                                  height: 26,
                                  decoration: BoxDecoration(
                                    color: isCompleted ? Colors.green : (isDark ? Colors.white10 : Colors.grey.shade300),
                                    shape: BoxShape.circle,
                                    border: isCurrent
                                        ? Border.all(color: Colors.green.shade200, width: 3)
                                        : null,
                                  ),
                                  child: Center(
                                    child: isCompleted
                                        ? const Icon(Icons.check, size: 14, color: Colors.white)
                                        : Text(
                                            '${i + 1}',
                                            style: TextStyle(
                                              fontSize: 11,
                                              fontWeight: FontWeight.bold,
                                              color: isDark ? Colors.white54 : Colors.grey.shade700,
                                            ),
                                          ),
                                  ),
                                ),
                                if (i < _stages.length - 1)
                                  Container(
                                    width: 2,
                                    height: 28,
                                    color: isCompleted ? Colors.green : (isDark ? Colors.white10 : Colors.grey.shade300),
                                  ),
                              ],
                            ),
                            const SizedBox(width: 14),
                            Expanded(
                              child: Padding(
                                padding: const EdgeInsets.only(top: 2),
                                child: Text(
                                  _stages[i],
                                  style: TextStyle(
                                    fontSize: 13,
                                    fontWeight: isCurrent ? FontWeight.w900 : (isCompleted ? FontWeight.bold : FontWeight.normal),
                                    color: isCurrent
                                        ? (isDark ? Colors.white : Colors.black87)
                                        : (isCompleted ? Colors.green : Colors.grey),
                                  ),
                                ),
                              ),
                            ),
                          ],
                        );
                      },
                    ),
                  ],
                ),
              ),

              // Doctor Prescription Verification Card (Pharmeasy)
              if (_hasPrescription || widget.packageCategory == 'Pharmeasy') ...[
                Container(
                  padding: const EdgeInsets.all(18),
                  margin: const EdgeInsets.only(bottom: 18),
                  decoration: BoxDecoration(
                    color: isDark ? const Color(0xFF161B26) : Colors.white,
                    borderRadius: BorderRadius.circular(24),
                    border: Border.all(color: const Color(0xFF10B981).withOpacity(0.4)),
                    boxShadow: [
                      BoxShadow(color: const Color(0xFF10B981).withOpacity(0.08), blurRadius: 16, offset: const Offset(0, 4)),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            children: const [
                              Icon(Icons.medical_services_rounded, color: Color(0xFF10B981), size: 20),
                              SizedBox(width: 8),
                              Text(
                                'Doctor Prescription (Rx)',
                                style: TextStyle(fontSize: 14, fontWeight: FontWeight.w900, color: Color(0xFF10B981)),
                              ),
                            ],
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: const Color(0xFF10B981).withOpacity(0.15),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: const Text(
                              'VERIFIED',
                              style: TextStyle(color: Color(0xFF10B981), fontSize: 10, fontWeight: FontWeight.w900),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Attached for certified pharmacy verification and courier medicine pickup.',
                        style: TextStyle(fontSize: 11.5, color: isDark ? Colors.white60 : Colors.black54),
                      ),
                      const SizedBox(height: 12),
                      if (_prescriptions.isNotEmpty) ...[
                        ..._prescriptions.map((rx) {
                          final isPdf = (rx['file_type']?.toString().toLowerCase() == 'pdf');
                          return Container(
                            margin: const EdgeInsets.only(bottom: 8),
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                            decoration: BoxDecoration(
                              color: isDark ? const Color(0xFF1E293B) : Colors.grey.shade50,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: isDark ? Colors.white10 : Colors.grey.shade200),
                            ),
                            child: Row(
                              children: [
                                Icon(
                                  isPdf ? Icons.picture_as_pdf_rounded : Icons.image_rounded,
                                  color: isPdf ? const Color(0xFFEF4444) : const Color(0xFF10B981),
                                  size: 18,
                                ),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    rx['file_name']?.toString() ?? 'Prescription File',
                                    style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                                TextButton.icon(
                                  style: TextButton.styleFrom(
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                    minimumSize: Size.zero,
                                  ),
                                  icon: const Icon(Icons.zoom_in_rounded, size: 16, color: Color(0xFFF59E0B)),
                                  label: const Text('View & Zoom', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFFF59E0B))),
                                  onPressed: () => _showPrescriptionViewer(rx),
                                ),
                              ],
                            ),
                          );
                        }).toList(),
                      ] else ...[
                        Row(
                          children: const [
                            Icon(Icons.check_circle_rounded, color: Color(0xFF10B981), size: 16),
                            SizedBox(width: 6),
                            Text('Prescription submitted with delivery order', style: TextStyle(color: Color(0xFF10B981), fontSize: 11.5, fontWeight: FontWeight.bold)),
                          ],
                        ),
                      ],
                    ],
                  ),
                ),
              ],

              // Route & Recipient Summary Card
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF161B26) : Colors.white,
                  borderRadius: BorderRadius.circular(24),
                  border: Border.all(color: isDark ? Colors.white10 : Colors.grey.shade200),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Dispatch Details',
                      style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900),
                    ),
                    const SizedBox(height: 12),
                    if (_requestedTime != null)
                      _buildInfoRow('Requested', _requestedTime!, isDark),
                    if (_scheduledPickup != null)
                      _buildInfoRow('Scheduled Pickup', _scheduledPickup!, isDark),
                    _buildInfoRow('Category', widget.packageCategory, isDark),
                    _buildInfoRow('Recipient', '${widget.recipientName} (${widget.recipientPhone})', isDark),
                    _buildInfoRow('Pickup', widget.pickupLocation, isDark),
                    _buildInfoRow('Dropoff', widget.dropoffLocation, isDark),
                    _buildInfoRow('Fare Total', '${widget.currencySymbol}${widget.totalPrice.toStringAsFixed(2)}', isDark, isBold: true),
                    _buildInfoRow('Payment Method', widget.paymentMethod.toUpperCase(), isDark),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              ElevatedButton(
                onPressed: () {
                  Navigator.pushAndRemoveUntil(
                    context,
                    MaterialPageRoute(builder: (_) => const RiderHomeScreen()),
                    (route) => false,
                  );
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                ),
                child: const Text(
                  'Back to Home Screen',
                  style: TextStyle(fontWeight: FontWeight.w900, fontSize: 15),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value, bool isDark, {bool isBold = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(
              label,
              style: TextStyle(
                fontSize: 11,
                color: isDark ? Colors.white54 : Colors.grey.shade600,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: TextStyle(
                fontSize: 12,
                fontWeight: isBold ? FontWeight.w900 : FontWeight.bold,
                color: isBold ? AppColors.primary : (isDark ? Colors.white : Colors.black87),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
