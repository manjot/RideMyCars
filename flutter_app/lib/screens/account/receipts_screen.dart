import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/api/api_client.dart';
import '../../core/constants/api_constants.dart';
import '../../core/constants/app_colors.dart';

class ReceiptsScreen extends StatefulWidget {
  const ReceiptsScreen({super.key});

  @override
  State<ReceiptsScreen> createState() => _ReceiptsScreenState();
}

class _ReceiptsScreenState extends State<ReceiptsScreen> {
  bool _isLoading = true;
  String _selectedFilter = 'all'; // all, ride, rental, driver_booking, delivery
  List<dynamic> _receipts = [];
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _fetchReceipts();
  }

  Future<void> _fetchReceipts() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await ApiClient().dio.get(ApiConstants.receipts);
      if (response.statusCode == 200 && response.data != null) {
        final data = response.data['data'] ?? response.data['receipts'] ?? [];
        setState(() {
          _receipts = data is List ? data : [];
          _isLoading = false;
        });
      } else {
        setState(() {
          _errorMessage = 'Failed to load receipts.';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Could not retrieve receipts. Please check your connection.';
        _isLoading = false;
      });
    }
  }

  List<dynamic> get _filteredReceipts {
    if (_selectedFilter == 'all') return _receipts;
    return _receipts.where((r) {
      final type = (r['booking_type'] ?? '').toString().toLowerCase();
      if (_selectedFilter == 'ride') return type == 'ride';
      if (_selectedFilter == 'rental') return type == 'rental';
      if (_selectedFilter == 'driver_booking') return type == 'driver_booking' || type == 'chauffeur';
      if (_selectedFilter == 'delivery') return type == 'delivery' || type == 'package_delivery';
      return true;
    }).toList();
  }

  Future<void> _openUrl(String url) async {
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Could not open link: $url'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  Future<void> _resendEmail(int receiptId, String email) async {
    try {
      await ApiClient().dio.post(ApiConstants.receiptResend(receiptId));
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Receipt successfully sent to $email'),
            backgroundColor: AppColors.success,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Failed to re-send receipt email. Please try again later.'),
            backgroundColor: AppColors.error,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.backgroundDark,
      appBar: AppBar(
        title: const Text(
          'Booking History & Receipts',
          style: TextStyle(
            color: AppColors.textLight,
            fontWeight: FontWeight.w800,
            fontSize: 18,
          ),
        ),
        backgroundColor: AppColors.surfaceDark,
        elevation: 0,
        iconTheme: const IconThemeData(color: AppColors.textLight),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: _fetchReceipts,
            tooltip: 'Refresh',
          ),
        ],
      ),
      body: Column(
        children: [
          _buildFilterPills(),
          Expanded(
            child: _isLoading
                ? const Center(
                    child: CircularProgressIndicator(color: AppColors.primary),
                  )
                : _errorMessage != null
                    ? _buildErrorView()
                    : _filteredReceipts.isEmpty
                        ? _buildEmptyView()
                        : RefreshIndicator(
                            color: AppColors.primary,
                            onRefresh: _fetchReceipts,
                            child: ListView.builder(
                              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                              itemCount: _filteredReceipts.length,
                              itemBuilder: (context, index) {
                                final r = _filteredReceipts[index];
                                return _buildReceiptCard(r);
                              },
                            ),
                          ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterPills() {
    final filters = [
      {'key': 'all', 'label': 'All Receipts'},
      {'key': 'ride', 'label': 'Rides'},
      {'key': 'rental', 'label': 'Rentals'},
      {'key': 'driver_booking', 'label': 'Chauffeurs'},
      {'key': 'delivery', 'label': 'Deliveries'},
    ];

    return Container(
      height: 56,
      padding: const EdgeInsets.symmetric(vertical: 8),
      color: AppColors.surfaceDark,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        itemCount: filters.length,
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (context, i) {
          final f = filters[i];
          final isSelected = _selectedFilter == f['key'];
          return ChoiceChip(
            label: Text(
              f['label']!,
              style: TextStyle(
                color: isSelected ? AppColors.backgroundDark : AppColors.textLight,
                fontSize: 12,
                fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
              ),
            ),
            selected: isSelected,
            selectedColor: AppColors.primary,
            backgroundColor: const Color(0xFF0F172A),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(20),
              side: BorderSide(
                color: isSelected ? AppColors.primary : Colors.white12,
              ),
            ),
            onSelected: (val) {
              if (val) {
                setState(() => _selectedFilter = f['key']!);
              }
            },
          );
        },
      ),
    );
  }

  Widget _buildReceiptCard(Map<String, dynamic> r) {
    final type = (r['booking_type'] ?? 'ride').toString().toLowerCase();
    final receiptNum = r['receipt_number'] ?? 'CRN-${r['id']}';
    final amount = (r['total_amount'] as num? ?? 0.0).toDouble();
    final currency = r['currency'] ?? 'USD';
    final currencySymbol = _getCurrencySymbol(currency);
    final date = r['created_at'] != null ? r['created_at'].toString().split('T').first : 'Recent';
    final paymentMethod = (r['payment_method'] ?? 'card').toString().toUpperCase();
    final downloadUrl = r['download_url'] ?? ApiConstants.receiptDownloadWeb(r['verification_token'] ?? '');

    final typeBadge = _getTypeBadge(type);

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: Colors.white.withOpacity(0.08)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.3),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(20),
          onTap: () => _showReceiptDetailModal(r),
          child: Padding(
            padding: const EdgeInsets.all(18),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Top Header: Date & Type Badge
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        const Icon(Icons.receipt_long_rounded, color: AppColors.primary, size: 18),
                        const SizedBox(width: 8),
                        Text(
                          date,
                          style: const TextStyle(color: AppColors.textMuted, fontSize: 12, fontWeight: FontWeight.w600),
                        ),
                      ],
                    ),
                    typeBadge,
                  ],
                ),
                const SizedBox(height: 12),

                // Amount & CRN / Receipt #
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  crossAxisAlignment: CrossAxisAlignment.baseline,
                  textBaseline: TextBaseline.alphabetic,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          '$currencySymbol${amount.toStringAsFixed(2)}',
                          style: const TextStyle(
                            color: AppColors.textLight,
                            fontSize: 24,
                            fontWeight: FontWeight.w900,
                            letterSpacing: -0.5,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          receiptNum,
                          style: const TextStyle(
                            color: AppColors.primary,
                            fontSize: 12,
                            fontWeight: FontWeight.w700,
                            letterSpacing: 0.8,
                            fontFamily: 'monospace',
                          ),
                        ),
                      ],
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: AppColors.success.withOpacity(0.15),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Row(
                        children: [
                          Icon(Icons.check_circle_rounded, color: AppColors.success, size: 14),
                          SizedBox(width: 4),
                          Text(
                            'PAID',
                            style: TextStyle(
                              color: AppColors.success,
                              fontSize: 11,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),

                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 14),
                  child: Divider(color: Colors.white12, height: 1),
                ),

                // Payment Method & Actions
                Row(
                  children: [
                    Icon(Icons.credit_card_rounded, color: Colors.white.withOpacity(0.5), size: 16),
                    const SizedBox(width: 6),
                    Text(
                      'Paid via $paymentMethod',
                      style: const TextStyle(color: AppColors.textMuted, fontSize: 11.5, fontWeight: FontWeight.w600),
                    ),
                    const Spacer(),
                    // Download PDF Button
                    OutlinedButton.icon(
                      onPressed: () => _openUrl(downloadUrl),
                      icon: const Icon(Icons.file_download_outlined, size: 15),
                      label: const Text('PDF'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppColors.textLight,
                        side: const BorderSide(color: Colors.white24),
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        minimumSize: Size.zero,
                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        textStyle: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700),
                      ),
                    ),
                    const SizedBox(width: 8),
                    // View Online Button
                    ElevatedButton.icon(
                      onPressed: () => _showReceiptDetailModal(r),
                      icon: const Icon(Icons.visibility_outlined, size: 15),
                      label: const Text('View'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        foregroundColor: AppColors.backgroundDark,
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        minimumSize: Size.zero,
                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        elevation: 0,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        textStyle: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _getTypeBadge(String type) {
    Color bg;
    Color fg;
    String label;

    switch (type) {
      case 'rental':
        bg = const Color(0xFF3B82F6).withOpacity(0.2);
        fg = const Color(0xFF60A5FA);
        label = 'CAR RENTAL';
        break;
      case 'driver_booking':
      case 'chauffeur':
        bg = const Color(0xFFF59E0B).withOpacity(0.2);
        fg = const Color(0xFFFBBF24);
        label = 'CHAUFFEUR';
        break;
      case 'delivery':
      case 'package_delivery':
        bg = const Color(0xFF8B5CF6).withOpacity(0.2);
        fg = const Color(0xFFA78BFA);
        label = 'DELIVERY';
        break;
      case 'ride':
      default:
        bg = AppColors.primary.withOpacity(0.18);
        fg = AppColors.primary;
        label = 'RIDE HAILING';
        break;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3.5),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: fg.withOpacity(0.4)),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: fg,
          fontSize: 9.5,
          fontWeight: FontWeight.w900,
          letterSpacing: 0.5,
        ),
      ),
    );
  }

  void _showReceiptDetailModal(Map<String, dynamic> r) {
    final snap = r['snapshot_data'] is Map ? r['snapshot_data'] : {};
    final amount = (r['total_amount'] as num? ?? 0.0).toDouble();
    final currency = r['currency'] ?? 'USD';
    final currencySymbol = _getCurrencySymbol(currency);
    final receiptNum = r['receipt_number'] ?? 'CRN-${r['id']}';
    final date = r['created_at'] != null ? r['created_at'].toString().split('T').first : 'Recent';
    final customerName = snap['customer_name'] ?? 'Valued Customer';
    final driverName = snap['driver_name'] ?? snap['courier_name'];
    final vehicleDetails = snap['vehicle_details'] ?? snap['vehicle_name'];
    final pickup = snap['pickup_location'] ?? 'Pickup Address';
    final dropoff = snap['dropoff_location'] ?? 'Dropoff Address';
    final distance = snap['distance_km'] != null ? '${snap['distance_km']} km' : null;
    final duration = snap['duration_minutes'] != null ? '${snap['duration_minutes']} min' : null;
    final paymentMethod = snap['payment_method'] ?? r['payment_method'] ?? 'Card';
    final subtotal = (snap['subtotal'] as num? ?? amount).toDouble();
    final discount = (snap['discount'] as num? ?? r['discount_amount'] as num? ?? 0.0).toDouble();
    final tax = (snap['tax'] as num? ?? r['tax_amount'] as num? ?? 0.0).toDouble();
    final downloadUrl = r['download_url'] ?? ApiConstants.receiptDownloadWeb(r['verification_token'] ?? '');
    final viewUrl = r['view_url'] ?? ApiConstants.receiptViewWeb(r['verification_token'] ?? '');
    final receiptId = r['id'] is int ? r['id'] : int.tryParse(r['id']?.toString() ?? '0') ?? 0;
    final email = r['sent_to_email'] ?? '';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => DraggableScrollableSheet(
        initialChildSize: 0.88,
        minChildSize: 0.5,
        maxChildSize: 0.96,
        builder: (_, scrollController) => Container(
          decoration: const BoxDecoration(
            color: Color(0xFFFFFFFF), // Ola style paper white receipt
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          child: Column(
            children: [
              // Pull Handle
              Container(
                margin: const EdgeInsets.only(top: 12, bottom: 8),
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),

              // Scrollable Ola-styled receipt body
              Expanded(
                child: ListView(
                  controller: scrollController,
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                  children: [
                    // Top Bar: Date & Brand Logo
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          date,
                          style: TextStyle(color: Colors.grey.shade600, fontSize: 13, fontWeight: FontWeight.w600),
                        ),
                        Row(
                          children: [
                            Container(
                              width: 24,
                              height: 24,
                              decoration: const BoxDecoration(
                                color: Color(0xFF0F172A),
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.directions_car_rounded, color: AppColors.primary, size: 14),
                            ),
                            const SizedBox(width: 6),
                            const Text(
                              'RIDEMYCARS',
                              style: TextStyle(
                                color: Color(0xFF0F172A),
                                fontWeight: FontWeight.w900,
                                fontSize: 15,
                                letterSpacing: 1,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),

                    // Big Center Fare & CRN
                    Center(
                      child: Column(
                        children: [
                          Text(
                            '$currencySymbol${amount.toStringAsFixed(0)}',
                            style: const TextStyle(
                              color: Color(0xFF0F172A),
                              fontSize: 42,
                              fontWeight: FontWeight.w900,
                              letterSpacing: -1,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            receiptNum,
                            style: TextStyle(
                              color: Colors.grey.shade600,
                              fontSize: 13,
                              fontWeight: FontWeight.w600,
                              letterSpacing: 0.5,
                            ),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            'Thanks for travelling with us, $customerName',
                            style: TextStyle(
                              color: Colors.grey.shade800,
                              fontSize: 13,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ],
                      ),
                    ),

                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 20),
                      child: Divider(color: Colors.black12, height: 1),
                    ),

                    // Trip Details Header
                    const Text(
                      'Ride & Booking Details',
                      style: TextStyle(
                        color: Color(0xFF0F172A),
                        fontSize: 15,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(height: 12),

                    // Driver & Vehicle Box if available
                    if (driverName != null || vehicleDetails != null) ...[
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: Colors.grey.shade50,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: Colors.grey.shade200),
                        ),
                        child: Row(
                          children: [
                            CircleAvatar(
                              radius: 20,
                              backgroundColor: Colors.grey.shade300,
                              child: const Icon(Icons.person, color: Colors.black54),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  if (driverName != null)
                                    Text(
                                      driverName,
                                      style: const TextStyle(
                                        color: Color(0xFF0F172A),
                                        fontWeight: FontWeight.w800,
                                        fontSize: 14,
                                      ),
                                    ),
                                  if (vehicleDetails != null)
                                    Text(
                                      vehicleDetails,
                                      style: TextStyle(
                                        color: Colors.grey.shade600,
                                        fontSize: 12,
                                        fontWeight: FontWeight.w500,
                                      ),
                                    ),
                                ],
                              ),
                            ),
                            if (distance != null || duration != null)
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.end,
                                children: [
                                  if (distance != null)
                                    Text(
                                      distance,
                                      style: const TextStyle(
                                        color: Color(0xFF0F172A),
                                        fontWeight: FontWeight.w800,
                                        fontSize: 12,
                                      ),
                                    ),
                                  if (duration != null)
                                    Text(
                                      duration,
                                      style: TextStyle(color: Colors.grey.shade600, fontSize: 11),
                                    ),
                                ],
                              ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 14),
                    ],

                    // Pickup & Dropoff Address Markers
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Column(
                          children: [
                            const Icon(Icons.circle, color: Color(0xFF10B981), size: 12),
                            Container(width: 2, height: 28, color: Colors.grey.shade300),
                            const Icon(Icons.stop, color: Color(0xFFEF4444), size: 14),
                          ],
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                pickup,
                                style: const TextStyle(
                                  color: Color(0xFF0F172A),
                                  fontSize: 13,
                                  fontWeight: FontWeight.w600,
                                ),
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 18),
                              Text(
                                dropoff,
                                style: const TextStyle(
                                  color: Color(0xFF0F172A),
                                  fontSize: 13,
                                  fontWeight: FontWeight.w600,
                                ),
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),

                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 20),
                      child: Divider(color: Colors.black12, height: 1),
                    ),

                    // Bill Details Header
                    const Text(
                      'Bill Details',
                      style: TextStyle(
                        color: Color(0xFF0F172A),
                        fontSize: 15,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(height: 12),

                    // Fare Breakdown
                    _buildBillRow('Base Fare', '$currencySymbol${subtotal.toStringAsFixed(2)}'),
                    if (discount > 0)
                      _buildBillRow('Special Discount', '-$currencySymbol${discount.toStringAsFixed(2)}', isDiscount: true),
                    if (tax > 0)
                      _buildBillRow('Taxes & GST', '$currencySymbol${tax.toStringAsFixed(2)}'),
                    const SizedBox(height: 6),
                    const Divider(color: Colors.black12, height: 1),
                    const SizedBox(height: 8),
                    _buildBillRow(
                      'Total Bill (rounded)',
                      '$currencySymbol${amount.toStringAsFixed(2)}',
                      isTotal: true,
                    ),

                    const SizedBox(height: 16),
                    // Payment Method Pill
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                      decoration: BoxDecoration(
                        color: Colors.grey.shade100,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            children: [
                              const Icon(Icons.account_balance_wallet_rounded, color: Colors.black87, size: 18),
                              const SizedBox(width: 8),
                              Text(
                                'Paid by $paymentMethod',
                                style: const TextStyle(
                                  color: Color(0xFF0F172A),
                                  fontWeight: FontWeight.w700,
                                  fontSize: 13,
                                ),
                              ),
                            ],
                          ),
                          Text(
                            '$currencySymbol${amount.toStringAsFixed(2)}',
                            style: const TextStyle(
                              color: Color(0xFF0F172A),
                              fontWeight: FontWeight.w900,
                              fontSize: 14,
                            ),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 24),

                    // Grievance / Legal Disclaimer
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.grey.shade50,
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: Colors.grey.shade200),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Official Digital Tax & Trip Receipt',
                            style: TextStyle(
                              color: Colors.black87,
                              fontWeight: FontWeight.w700,
                              fontSize: 11,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'This document is issued at the passenger’s request for reference and accounting purposes. Verification Token: ${r['verification_token'] ?? 'VERIFIED'}',
                            style: TextStyle(color: Colors.grey.shade600, fontSize: 10, height: 1.4),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 24),
                  ],
                ),
              ),

              // Bottom Sticky Action Buttons
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.08),
                      blurRadius: 10,
                      offset: const Offset(0, -4),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    if (email.isNotEmpty && receiptId > 0) ...[
                      IconButton.outlined(
                        onPressed: () {
                          _resendEmail(receiptId, email);
                        },
                        icon: const Icon(Icons.email_outlined, color: Color(0xFF0F172A)),
                        tooltip: 'Email Receipt',
                      ),
                      const SizedBox(width: 8),
                    ],
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () => _openUrl(viewUrl),
                        icon: const Icon(Icons.open_in_browser_rounded, size: 18),
                        label: const Text('View Online'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: const Color(0xFF0F172A),
                          side: const BorderSide(color: Color(0xFF0F172A), width: 1.2),
                          padding: const EdgeInsets.symmetric(vertical: 13),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          textStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () => _openUrl(downloadUrl),
                        icon: const Icon(Icons.file_download_rounded, size: 18),
                        label: const Text('Download PDF'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF0F172A),
                          foregroundColor: AppColors.primary,
                          elevation: 0,
                          padding: const EdgeInsets.symmetric(vertical: 13),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          textStyle: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildBillRow(String title, String value, {bool isDiscount = false, bool isTotal = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3.5),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            title,
            style: TextStyle(
              color: isTotal ? const Color(0xFF0F172A) : Colors.grey.shade700,
              fontSize: isTotal ? 14 : 12.5,
              fontWeight: isTotal ? FontWeight.w900 : FontWeight.w500,
            ),
          ),
          Text(
            value,
            style: TextStyle(
              color: isDiscount
                  ? const Color(0xFF10B981)
                  : (isTotal ? const Color(0xFF0F172A) : Colors.grey.shade900),
              fontSize: isTotal ? 16 : 12.5,
              fontWeight: isTotal ? FontWeight.w900 : (isDiscount ? FontWeight.w700 : FontWeight.w600),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyView() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppColors.surfaceDark,
                shape: BoxShape.circle,
                border: Border.all(color: Colors.white12),
              ),
              child: const Icon(Icons.receipt_long_rounded, color: AppColors.textMuted, size: 40),
            ),
            const SizedBox(height: 20),
            const Text(
              'No Receipts Found',
              style: TextStyle(
                color: AppColors.textLight,
                fontSize: 18,
                fontWeight: FontWeight.w800,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'Completed bookings and confirmed payments will automatically produce downloadable PDF receipts here.',
              textAlign: TextAlign.center,
              style: TextStyle(
                color: AppColors.textMuted,
                fontSize: 13,
                height: 1.5,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildErrorView() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline_rounded, color: AppColors.error, size: 48),
            const SizedBox(height: 16),
            Text(
              _errorMessage ?? 'An error occurred',
              textAlign: TextAlign.center,
              style: const TextStyle(color: AppColors.textLight, fontSize: 14),
            ),
            const SizedBox(height: 16),
            ElevatedButton.icon(
              onPressed: _fetchReceipts,
              icon: const Icon(Icons.refresh_rounded),
              label: const Text('Try Again'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: AppColors.backgroundDark,
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _getCurrencySymbol(String currency) {
    switch (currency.toUpperCase()) {
      case 'INR':
        return '₹';
      case 'GHS':
        return 'GH₵ ';
      case 'NGN':
        return '₦';
      case 'ZAR':
        return 'R ';
      case 'EUR':
        return '€';
      case 'GBP':
        return '£';
      case 'USD':
      default:
        return '\$';
    }
  }
}
