import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/country_provider.dart';
import '../rider/rider_home_screen.dart';
import '../rides/my_rides_screen.dart';

class BookingConfirmationScreen extends StatelessWidget {
  final String serviceType; // 'ride', 'rental', 'driver_booking', 'package_delivery'
  final String bookingReference;
  final String serviceTitle;
  final String pickupLocation;
  final String dropoffLocation;
  final String? dateTimeText;
  final double totalAmount;
  final double paidAmount;
  final double remainingBalance;
  final String paymentMethod;
  final String currencySymbol;
  final Map<String, dynamic>? extraDetails;
  final VoidCallback? onTrackPressed;

  const BookingConfirmationScreen({
    super.key,
    required this.serviceType,
    required this.bookingReference,
    required this.serviceTitle,
    required this.pickupLocation,
    required this.dropoffLocation,
    this.dateTimeText,
    required this.totalAmount,
    required this.paidAmount,
    this.remainingBalance = 0.0,
    required this.paymentMethod,
    this.currencySymbol = '\$',
    this.extraDetails,
    this.onTrackPressed,
  });

  String get _serviceBadgeTitle {
    switch (serviceType) {
      case 'rental':
        return 'CAR RENTAL VOUCHER';
      case 'driver_booking':
        return 'CHAUFFEUR RESERVATION';
      case 'package_delivery':
        return 'PACKAGE DISPATCH CONFIRMATION';
      default:
        return 'RIDE RESERVATION';
    }
  }

  Color get _serviceColor {
    switch (serviceType) {
      case 'rental':
        return const Color(0xFF8B5CF6); // Purple
      case 'driver_booking':
        return const Color(0xFF0EA5E9); // Cyan / Blue
      case 'package_delivery':
        return const Color(0xFFF97316); // Orange
      default:
        return AppColors.primary;
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final country = Provider.of<CountryProvider>(context, listen: false);
    final effectiveSymbol = currencySymbol != '\$' ? currencySymbol : country.currencySymbol;

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
          title: const Text(
            'Booking Confirmed',
            style: TextStyle(fontWeight: FontWeight.w900, fontSize: 18),
          ),
          centerTitle: true,
          elevation: 0,
          leading: IconButton(
            icon: const Icon(Icons.home_rounded),
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
              // Success Animation Banner
              Container(
                padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 16),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [
                      Colors.green.shade600,
                      Colors.green.shade800,
                    ],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(24),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.green.withOpacity(0.3),
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
                      decoration: const BoxDecoration(
                        color: Colors.white,
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(
                        Icons.check_rounded,
                        color: Colors.green,
                        size: 40,
                      ),
                    ),
                    const SizedBox(height: 14),
                    const Text(
                      'Booking Successfully Placed!',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.w900,
                        color: Colors.white,
                      ),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Reference: $bookingReference',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                        color: Colors.white.withOpacity(0.9),
                        letterSpacing: 1.2,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),

              // Service & Route Card
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF161B26) : Colors.white,
                  borderRadius: BorderRadius.circular(24),
                  border: Border.all(
                    color: isDark ? Colors.white10 : Colors.grey.shade200,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.04),
                      blurRadius: 16,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: _serviceColor.withOpacity(0.12),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: _serviceColor.withOpacity(0.3)),
                          ),
                          child: Text(
                            _serviceBadgeTitle,
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w900,
                              color: _serviceColor,
                              letterSpacing: 0.8,
                            ),
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: Colors.green.withOpacity(0.12),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Row(
                            children: [
                              Icon(Icons.shield_rounded, size: 12, color: Colors.green),
                              SizedBox(width: 4),
                              Text(
                                'CONFIRMED',
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w900,
                                  color: Colors.green,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Text(
                      serviceTitle,
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    Builder(builder: (_) {
                      String timeLabel = dateTimeText ?? '';
                      if (timeLabel.isEmpty) {
                        final now = DateTime.now();
                        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        final m = months[now.month - 1];
                        final d = now.day.toString().padLeft(2, '0');
                        final y = now.year;
                        final hour = now.hour % 12 == 0 ? 12 : now.hour % 12;
                        final min = now.minute.toString().padLeft(2, '0');
                        final ampm = now.hour >= 12 ? 'PM' : 'AM';
                        timeLabel = 'Requested: $d $m $y • $hour:$min $ampm';
                      } else if (!timeLabel.toLowerCase().startsWith('requested') && !timeLabel.toLowerCase().startsWith('schedule')) {
                        timeLabel = 'Requested: $timeLabel';
                      }
                      return Padding(
                        padding: const EdgeInsets.only(top: 4),
                        child: Row(
                          children: [
                            Icon(Icons.schedule, size: 14, color: isDark ? Colors.white54 : Colors.black54),
                            const SizedBox(width: 6),
                            Text(
                              timeLabel,
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w600,
                                color: isDark ? Colors.white70 : Colors.black87,
                              ),
                            ),
                          ],
                        ),
                      );
                    }),
                    const Divider(height: 24),

                    // Pickup / Dropoff Timeline
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Column(
                          children: [
                            const Icon(Icons.radio_button_checked, size: 18, color: Colors.green),
                            Container(width: 2, height: 26, color: Colors.grey.shade400),
                            const Icon(Icons.location_on, size: 18, color: Colors.red),
                          ],
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'PICKUP',
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w900,
                                  color: isDark ? Colors.white54 : Colors.grey.shade600,
                                ),
                              ),
                              Text(
                                pickupLocation,
                                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 14),
                              Text(
                                'DESTINATION / RETURN',
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w900,
                                  color: isDark ? Colors.white54 : Colors.grey.shade600,
                                ),
                              ),
                              Text(
                                dropoffLocation,
                                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),

              // Payment & Receipt Card
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF161B26) : Colors.white,
                  borderRadius: BorderRadius.circular(24),
                  border: Border.all(
                    color: isDark ? Colors.white10 : Colors.grey.shade200,
                  ),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Payment & Receipt Details',
                      style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900),
                    ),
                    const SizedBox(height: 12),
                    _buildReceiptRow('Payment Method', paymentMethod.toUpperCase(), isDark),
                    _buildReceiptRow('Total Amount', '$effectiveSymbol${totalAmount.toStringAsFixed(2)}', isDark),
                    _buildReceiptRow('Paid / Authorized Now', '$effectiveSymbol${paidAmount.toStringAsFixed(2)}', isDark, isHighlight: true),
                    if (remainingBalance > 0)
                      _buildReceiptRow('Remaining on Drop-off', '$effectiveSymbol${remainingBalance.toStringAsFixed(2)}', isDark),
                    _buildReceiptRow('Receipt Status', 'ISSUED & VERIFIED', isDark, isGreen: true),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              // Action Buttons
              if (onTrackPressed != null) ...[
                ElevatedButton(
                  onPressed: onTrackPressed,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    elevation: 4,
                  ),
                  child: const Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.near_me_rounded),
                      SizedBox(width: 8),
                      Text(
                        'Track Live Status →',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 12),
              ],

              OutlinedButton(
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const MyRidesScreen()),
                  );
                },
                style: OutlinedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                  side: BorderSide(color: isDark ? Colors.white24 : Colors.grey.shade400),
                ),
                child: const Text(
                  'View in My Bookings & Rides',
                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                ),
              ),
              const SizedBox(height: 8),

              TextButton(
                onPressed: () {
                  Navigator.pushAndRemoveUntil(
                    context,
                    MaterialPageRoute(builder: (_) => const RiderHomeScreen()),
                    (route) => false,
                  );
                },
                child: const Text(
                  'Back to Home',
                  style: TextStyle(fontWeight: FontWeight.bold),
                ),
              ),
              const SizedBox(height: 20),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildReceiptRow(String label, String value, bool isDark, {bool isHighlight = false, bool isGreen = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: TextStyle(
              fontSize: 12,
              color: isDark ? Colors.white60 : Colors.black54,
              fontWeight: FontWeight.w600,
            ),
          ),
          Text(
            value,
            style: TextStyle(
              fontSize: isHighlight ? 14 : 12,
              fontWeight: isHighlight || isGreen ? FontWeight.w900 : FontWeight.bold,
              color: isGreen
                  ? Colors.green
                  : (isHighlight ? AppColors.primary : (isDark ? Colors.white : Colors.black87)),
            ),
          ),
        ],
      ),
    );
  }
}
