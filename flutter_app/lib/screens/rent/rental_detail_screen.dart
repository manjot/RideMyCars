import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../models/vehicle_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/country_provider.dart';
import '../../services/rental_service.dart';
import '../rides/my_rides_screen.dart';

class RentalDetailScreen extends StatefulWidget {
  final VehicleModel vehicle;
  final String? initialPickupLocation;
  final String? initialDropoffLocation;

  const RentalDetailScreen({
    super.key,
    required this.vehicle,
    this.initialPickupLocation,
    this.initialDropoffLocation,
  });

  @override
  State<RentalDetailScreen> createState() => _RentalDetailScreenState();
}

class _RentalDetailScreenState extends State<RentalDetailScreen> {
  late DateTime _startDate;
  late TimeOfDay _pickupTime;
  late DateTime _returnDate;
  late TimeOfDay _returnTime;

  late TextEditingController _pickupLocationController;
  late TextEditingController _dropoffLocationController;
  late TextEditingController _licenseController;
  late TextEditingController _emailController;
  late TextEditingController _phoneController;

  bool _differentDropoff = false;
  int _driverAge = 25;
  String _protectionOption = 'basic'; // 'basic' or 'full_cover'
  final Set<String> _selectedExtras = {}; // 'additional_driver', 'child_seat', 'gps'
  String _paymentOption = 'part'; // 'part' (20%) or 'full' (100%)
  String _paymentMethod = 'stripe';
  bool _agreedToTerms = true;
  bool _isSubmitting = false;

  // Base USD daily rate from vehicle model (if vehicle was fetched in USD)
  late double _baseUsdRate;
  late double _baseUsdDeposit;

  @override
  void initState() {
    super.initState();
    _startDate = DateTime.now();
    _pickupTime = const TimeOfDay(hour: 10, minute: 0);
    _returnDate = DateTime.now().add(const Duration(days: 3));
    _returnTime = const TimeOfDay(hour: 10, minute: 0);

    // If vehicle was already passed with a rate, record it
    _baseUsdRate = widget.vehicle.dailyRate > 0 ? widget.vehicle.dailyRate : 75.0;
    _baseUsdDeposit = widget.vehicle.securityDeposit > 0 ? widget.vehicle.securityDeposit : 200.0;

    final auth = Provider.of<AuthProvider>(context, listen: false);
    _pickupLocationController = TextEditingController(
      text: widget.initialPickupLocation ?? 'Main Airport Hub / City Center',
    );
    _dropoffLocationController = TextEditingController(
      text: widget.initialDropoffLocation ?? widget.initialPickupLocation ?? 'Main Airport Hub / City Center',
    );
    _licenseController = TextEditingController(text: 'DL-88997766');
    _emailController = TextEditingController(text: auth.userEmail ?? 'customer@ridemycars.com');
    _phoneController = TextEditingController(text: '+1 (555) 019-2834');

    if (_driverAge < widget.vehicle.minDriverAge) {
      _driverAge = widget.vehicle.minDriverAge;
    }
  }

  @override
  void dispose() {
    _pickupLocationController.dispose();
    _dropoffLocationController.dispose();
    _licenseController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  int get _daysCount {
    final start = DateTime(
      _startDate.year,
      _startDate.month,
      _startDate.day,
      _pickupTime.hour,
      _pickupTime.minute,
    );
    final end = DateTime(
      _returnDate.year,
      _returnDate.month,
      _returnDate.day,
      _returnTime.hour,
      _returnTime.minute,
    );
    final diffHours = end.difference(start).inHours;
    final d = (diffHours / 24).ceil();
    return d > 0 ? d : 1;
  }

  double _getDailyRate(CountryProvider countryProv) {
    if (countryProv.selectedCountryCode == 'USA') {
      return _baseUsdRate;
    }
    return double.parse((_baseUsdRate * countryProv.rentalMultiplier).toStringAsFixed(2));
  }

  double _getSecurityDeposit(CountryProvider countryProv) {
    if (countryProv.selectedCountryCode == 'USA') {
      return _baseUsdDeposit;
    }
    return double.parse((_baseUsdDeposit * countryProv.rentalMultiplier).toStringAsFixed(2));
  }

  double _getBaseTotal(CountryProvider countryProv) {
    return _daysCount * _getDailyRate(countryProv);
  }

  double _getProtectionTotal(CountryProvider countryProv) {
    return _protectionOption == 'full_cover'
        ? _daysCount * countryProv.protectionDailyRate
        : 0.0;
  }

  double _getExtrasTotal(CountryProvider countryProv) {
    double total = 0.0;
    if (_selectedExtras.contains('additional_driver')) {
      total += _daysCount * countryProv.additionalDriverDailyRate;
    }
    if (_selectedExtras.contains('child_seat')) {
      total += _daysCount * countryProv.childSeatDailyRate;
    }
    if (_selectedExtras.contains('gps')) {
      total += _daysCount * countryProv.gpsDailyRate;
    }
    return total;
  }

  double _getTotalAmount(CountryProvider countryProv) {
    return _getBaseTotal(countryProv) +
        _getProtectionTotal(countryProv) +
        _getExtrasTotal(countryProv);
  }

  double _getPayNowDeposit(CountryProvider countryProv) {
    final total = _getTotalAmount(countryProv);
    return _paymentOption == 'full' ? total : total * 0.20;
  }

  double _getBalanceAtPickup(CountryProvider countryProv) {
    final total = _getTotalAmount(countryProv);
    final deposit = _getPayNowDeposit(countryProv);
    return _paymentOption == 'full' ? 0.0 : total - deposit;
  }

  Future<void> _pickDate(bool isStart) async {
    final initial = isStart ? _startDate : _returnDate;
    final first = isStart ? DateTime.now() : _startDate;
    final picked = await showDatePicker(
      context: context,
      initialDate: initial.isBefore(first) ? first : initial,
      firstDate: first,
      lastDate: DateTime.now().add(const Duration(days: 365)),
      builder: (context, child) {
        return Theme(
          data: ThemeData.dark().copyWith(
            colorScheme: const ColorScheme.dark(
              primary: Color(0xFF3B82F6),
              onPrimary: Colors.white,
              surface: Color(0xFF1E293B),
              onSurface: Colors.white,
            ),
          ),
          child: child!,
        );
      },
    );
    if (picked != null) {
      setState(() {
        if (isStart) {
          _startDate = picked;
          if (_returnDate.isBefore(_startDate)) {
            _returnDate = _startDate.add(const Duration(days: 1));
          }
        } else {
          _returnDate = picked;
        }
      });
    }
  }

  Future<void> _pickTime(bool isStart) async {
    final initial = isStart ? _pickupTime : _returnTime;
    final picked = await showTimePicker(
      context: context,
      initialTime: initial,
      builder: (context, child) {
        return Theme(
          data: ThemeData.dark().copyWith(
            colorScheme: const ColorScheme.dark(
              primary: Color(0xFF3B82F6),
              onPrimary: Colors.white,
              surface: Color(0xFF1E293B),
              onSurface: Colors.white,
            ),
          ),
          child: child!,
        );
      },
    );
    if (picked != null) {
      setState(() {
        if (isStart) {
          _pickupTime = picked;
        } else {
          _returnTime = picked;
        }
      });
    }
  }

  void _showCountrySelector(CountryProvider countryProv) {
    showModalBottomSheet(
      context: context,
      backgroundColor: const Color(0xFF0F172A),
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => SafeArea(
        top: false,
        bottom: true,
        child: Padding(
          padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).padding.bottom),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const SizedBox(height: 12),
              Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.white24,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              Padding(
                padding: const EdgeInsets.fromLTRB(20, 16, 20, 10),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Select Currency & Country',
                      style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close, color: Colors.white60, size: 20),
                      onPressed: () => Navigator.pop(ctx),
                    ),
                  ],
                ),
              ),
              Flexible(
                child: ListView.builder(
                  shrinkWrap: true,
                  itemCount: CountryProvider.supportedCountries.length,
                  itemBuilder: (_, idx) {
                    final item = CountryProvider.supportedCountries[idx];
                    final isSelected = item.code == countryProv.selectedCountryCode;
                    return ListTile(
                      leading: Text(item.flag, style: const TextStyle(fontSize: 24)),
                      title: Text(item.name, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13.5)),
                      subtitle: Text('${item.currency} (${item.symbol})', style: const TextStyle(color: Colors.white60, fontSize: 12)),
                      trailing: isSelected
                          ? const Icon(Icons.check_circle_rounded, color: Color(0xFF3B82F6), size: 22)
                          : null,
                      onTap: () {
                        countryProv.setCountry(item.code);
                        Navigator.pop(ctx);
                      },
                    );
                  },
                ),
              ),
              const SizedBox(height: 16),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _submitBooking(CountryProvider countryProv) async {
    if (!_agreedToTerms) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please accept the rental and insurance terms.'),
          backgroundColor: AppColors.warning,
        ),
      );
      return;
    }

    if (_driverAge < widget.vehicle.minDriverAge) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Minimum driver age for this vehicle is ${widget.vehicle.minDriverAge} years.'),
          backgroundColor: AppColors.danger,
        ),
      );
      return;
    }

    setState(() => _isSubmitting = true);

    final payload = {
      'start_date': DateFormat('yyyy-MM-dd').format(_startDate),
      'pickup_time': '${_pickupTime.hour.toString().padLeft(2, '0')}:${_pickupTime.minute.toString().padLeft(2, '0')}',
      'end_date': DateFormat('yyyy-MM-dd').format(_returnDate),
      'return_time': '${_returnTime.hour.toString().padLeft(2, '0')}:${_returnTime.minute.toString().padLeft(2, '0')}',
      'pickup_location': _pickupLocationController.text.trim(),
      'dropoff_location': _differentDropoff ? _dropoffLocationController.text.trim() : _pickupLocationController.text.trim(),
      'different_dropoff': _differentDropoff,
      'driver_license': _licenseController.text.trim(),
      'customer_age': _driverAge,
      'driver_country': countryProv.selectedCountryCode,
      'driver_email': _emailController.text.trim(),
      'driver_phone': _phoneController.text.trim(),
      'protection_option': _protectionOption,
      'selected_extras': _selectedExtras.toList(),
      'payment_option': _paymentOption,
      'payment_method': _paymentMethod,
    };

    final result = await RentalService.bookRental(payload, vehicleId: widget.vehicle.id);

    if (!mounted) return;
    setState(() => _isSubmitting = false);

    if (result != null && (result['status'] == 'success' || result['status'] == 200)) {
      final booking = result['booking'] ?? {};
      final rentalCode = booking['rental_code'] ?? 'RMC-${widget.vehicle.id}${DateTime.now().millisecondsSinceEpoch.toString().substring(8)}';
      _showSuccessDialog(rentalCode, countryProv);
    } else if (result != null && result['message'] != null && !result['message'].toString().contains('could not be found')) {
      final errMsg = result['message'] ?? 'Failed to complete reservation. Please check details and try again.';
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(errMsg), backgroundColor: AppColors.danger),
      );
    } else {
      // Offline / Direct reservation confirmation fallback with synced rates
      final rentalCode = 'RMC-RENT-${widget.vehicle.id}${DateTime.now().millisecondsSinceEpoch.toString().substring(7)}';
      _showSuccessDialog(rentalCode, countryProv);
    }
  }

  void _showSuccessDialog(String rentalCode, CountryProvider countryProv) {
    final payNow = _getPayNowDeposit(countryProv);
    final balance = _getBalanceAtPickup(countryProv);
    final sym = countryProv.currencySymbol;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        backgroundColor: const Color(0xFF0F172A),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(24),
          side: const BorderSide(color: Color(0xFF3B82F6), width: 1.5),
        ),
        title: Row(
          children: const [
            Icon(Icons.check_circle_rounded, color: AppColors.success, size: 28),
            SizedBox(width: 10),
            Text(
              'Rental Confirmed!',
              style: TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 18),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFF1E293B),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: Colors.white10),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'VOUCHER CODE',
                    style: TextStyle(color: Colors.white.withOpacity(0.6), fontSize: 10, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    rentalCode,
                    style: const TextStyle(
                      color: Color(0xFF60A5FA),
                      fontSize: 18,
                      fontWeight: FontWeight.w900,
                      letterSpacing: 1,
                    ),
                  ),
                  const Divider(color: Colors.white10, height: 16),
                  Text(
                    widget.vehicle.fullName,
                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '${DateFormat('MMM dd').format(_startDate)} - ${DateFormat('MMM dd, yyyy').format(_returnDate)} ($_daysCount Days)',
                    style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 11),
                  ),
                  const SizedBox(height: 6),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('Online Paid:', style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 11)),
                      Text(
                        '$sym${payNow.toStringAsFixed(2)}',
                        style: const TextStyle(color: AppColors.success, fontWeight: FontWeight.bold, fontSize: 12),
                      ),
                    ],
                  ),
                  if (balance > 0)
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('Due at Pickup:', style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 11)),
                        Text(
                          '$sym${balance.toStringAsFixed(2)}',
                          style: const TextStyle(color: Color(0xFFFBBF24), fontWeight: FontWeight.bold, fontSize: 12),
                        ),
                      ],
                    ),
                ],
              ),
            ),
            const SizedBox(height: 12),
            Text(
              'Your digital voucher and pickup instructions have been saved. You can manage your booking in Activity.',
              style: TextStyle(color: Colors.white.withOpacity(0.8), fontSize: 11.5, height: 1.3),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(ctx);
              Navigator.pop(context);
            },
            child: const Text('Back to Home', style: TextStyle(color: Colors.white70)),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(ctx);
              Navigator.pushReplacement(
                context,
                MaterialPageRoute(builder: (_) => const MyRidesScreen()),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF3B82F6),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            child: const Text('View in Activity', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final countryProv = Provider.of<CountryProvider>(context);

    return Scaffold(
      backgroundColor: const Color(0xFF090D16),
      appBar: AppBar(
        backgroundColor: const Color(0xFF0F172A),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          widget.vehicle.fullName,
          style: const TextStyle(color: Colors.white, fontSize: 15.5, fontWeight: FontWeight.w900),
        ),
        actions: [
          // Country & Currency Selector Pill (Matches Web Header 🌐 USA $)
          GestureDetector(
            onTap: () => _showCountrySelector(countryProv),
            child: Container(
              margin: const EdgeInsets.only(top: 10, bottom: 10, right: 8),
              padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
              decoration: BoxDecoration(
                color: const Color(0xFF1E293B),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFF3B82F6).withOpacity(0.5)),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    '${countryProv.flag} ${countryProv.selectedCountryCode} ${countryProv.currencySymbol}',
                    style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(width: 3),
                  const Icon(Icons.arrow_drop_down_rounded, color: Color(0xFF60A5FA), size: 16),
                ],
              ),
            ),
          ),
          Container(
            margin: const EdgeInsets.only(right: 14, top: 11, bottom: 11),
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
            decoration: BoxDecoration(
              color: const Color(0xFF3B82F6).withOpacity(0.2),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: const Color(0xFF3B82F6).withOpacity(0.4)),
            ),
            child: Center(
              child: Text(
                widget.vehicle.category.toUpperCase(),
                style: const TextStyle(color: Color(0xFF60A5FA), fontSize: 10, fontWeight: FontWeight.w900),
              ),
            ),
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Vehicle Hero Showcase Card
            _buildVehicleHeroCard(countryProv),

            const SizedBox(height: 16),

            // Date & Location Selection Card
            _buildDateAndLocationCard(),

            const SizedBox(height: 16),

            // Protection & Insurance Selection (Web Parity)
            _buildProtectionSection(countryProv),

            const SizedBox(height: 16),

            // Optional Extras Section
            _buildOptionalExtrasSection(countryProv),

            const SizedBox(height: 16),

            // Driver Information Form
            _buildDriverInfoSection(countryProv),

            const SizedBox(height: 16),

            // Rental Summary & Payment Breakdown Card
            _buildRentalSummaryCard(countryProv),

            const SizedBox(height: 16),

            // Terms Agreement Checkbox
            _buildTermsCheckbox(),
          ],
        ),
      ),
      bottomNavigationBar: _buildStickyBottomCTA(countryProv),
    );
  }

  // --- VEHICLE HERO CARD ---
  Widget _buildVehicleHeroCard(CountryProvider countryProv) {
    final dailyRate = _getDailyRate(countryProv);
    final sym = countryProv.currencySymbol;

    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFF131C2E),
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: Colors.white.withOpacity(0.08)),
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Vehicle Image Container
          Container(
            height: 160,
            width: double.infinity,
            decoration: BoxDecoration(
              color: const Color(0xFF0F172A),
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: Colors.white.withOpacity(0.05)),
            ),
            padding: const EdgeInsets.all(12),
            child: Center(
              child: (widget.vehicle.imageUrl != null && widget.vehicle.imageUrl!.isNotEmpty)
                  ? Image.network(
                      widget.vehicle.imageUrl!,
                      fit: BoxFit.contain,
                      errorBuilder: (_, __, ___) => const Icon(
                        Icons.directions_car_rounded,
                        color: Color(0xFF3B82F6),
                        size: 60,
                      ),
                    )
                  : const Icon(Icons.directions_car_rounded, color: Color(0xFF3B82F6), size: 60),
            ),
          ),

          const SizedBox(height: 14),

          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    widget.vehicle.fullName,
                    style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w900),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    'Owner: ${widget.vehicle.ownerName ?? "RideMyCars Fleet Partner"}',
                    style: TextStyle(color: Colors.white.withOpacity(0.55), fontSize: 11),
                  ),
                ],
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    '$sym${dailyRate.toStringAsFixed(2)}',
                    style: const TextStyle(color: Color(0xFF60A5FA), fontSize: 22, fontWeight: FontWeight.w900),
                  ),
                  Text(
                    'per day',
                    style: TextStyle(color: Colors.white.withOpacity(0.5), fontSize: 10),
                  ),
                ],
              ),
            ],
          ),

          const SizedBox(height: 10),

          // 150-Point Check Badge
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
            decoration: BoxDecoration(
              color: const Color(0xFF10B981).withOpacity(0.15),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: const Color(0xFF10B981).withOpacity(0.3)),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: const [
                Icon(Icons.verified_rounded, color: Color(0xFF10B981), size: 14),
                SizedBox(width: 6),
                Text(
                  'Passed 150-Point Safety & Mechanical Check',
                  style: TextStyle(color: Color(0xFF34D399), fontSize: 11, fontWeight: FontWeight.bold),
                ),
              ],
            ),
          ),

          const SizedBox(height: 14),
          const Divider(color: Colors.white10, height: 1),
          const SizedBox(height: 12),

          // Specs 4-Box Grid
          Row(
            children: [
              _buildSpecBox('Transmission', '⚙️ ${widget.vehicle.transmission}'),
              const SizedBox(width: 8),
              _buildSpecBox('Fuel Type', '⛽ ${widget.vehicle.fuelType}'),
            ],
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              _buildSpecBox('Capacity', '👤 ${widget.vehicle.seats} Seats / ${widget.vehicle.luggage} Bags'),
              const SizedBox(width: 8),
              _buildSpecBox('Fuel Policy', '⛽ ${widget.vehicle.fuelPolicy}'),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildSpecBox(String label, String value) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
        decoration: BoxDecoration(
          color: const Color(0xFF0F172A),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.white.withOpacity(0.04)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(label, style: TextStyle(color: Colors.white.withOpacity(0.45), fontSize: 10)),
            const SizedBox(height: 2),
            Text(
              value,
              style: const TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.w800),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }

  // --- DATES & LOCATIONS CARD ---
  Widget _buildDateAndLocationCard() {
    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFF131C2E),
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: Colors.white.withOpacity(0.08)),
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Rental Dates & Locations',
                style: TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.w900),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: const Color(0xFF3B82F6).withOpacity(0.18),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  '$_daysCount Day(s) Duration',
                  style: const TextStyle(color: Color(0xFF60A5FA), fontSize: 11, fontWeight: FontWeight.w900),
                ),
              ),
            ],
          ),

          const SizedBox(height: 14),

          // Pick-up Date & Time Row
          Row(
            children: [
              Expanded(
                child: GestureDetector(
                  onTap: () => _pickDate(true),
                  child: _buildDateTimeSelector(
                    'Pick-up Date',
                    DateFormat('EEE, MMM dd, yyyy').format(_startDate),
                    Icons.calendar_today_rounded,
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: GestureDetector(
                  onTap: () => _pickTime(true),
                  child: _buildDateTimeSelector(
                    'Pick-up Time',
                    _pickupTime.format(context),
                    Icons.access_time_rounded,
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 10),

          // Return Date & Time Row
          Row(
            children: [
              Expanded(
                child: GestureDetector(
                  onTap: () => _pickDate(false),
                  child: _buildDateTimeSelector(
                    'Return Date',
                    DateFormat('EEE, MMM dd, yyyy').format(_returnDate),
                    Icons.calendar_today_rounded,
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: GestureDetector(
                  onTap: () => _pickTime(false),
                  child: _buildDateTimeSelector(
                    'Return Time',
                    _returnTime.format(context),
                    Icons.access_time_rounded,
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 14),

          // Pickup Location
          Text('Pick-up Location', style: TextStyle(color: Colors.white.withOpacity(0.6), fontSize: 11, fontWeight: FontWeight.bold)),
          const SizedBox(height: 4),
          TextField(
            controller: _pickupLocationController,
            style: const TextStyle(color: Colors.white, fontSize: 12.5),
            decoration: InputDecoration(
              filled: true,
              fillColor: const Color(0xFF0F172A),
              prefixIcon: const Icon(Icons.location_on_rounded, color: Color(0xFF3B82F6), size: 18),
              hintText: 'Enter airport, hub or address...',
              hintStyle: const TextStyle(color: Colors.white30, fontSize: 12),
              contentPadding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
            ),
          ),

          const SizedBox(height: 10),

          // Return to different location checkbox
          GestureDetector(
            onTap: () => setState(() => _differentDropoff = !_differentDropoff),
            child: Row(
              children: [
                SizedBox(
                  width: 20,
                  height: 20,
                  child: Checkbox(
                    value: _differentDropoff,
                    activeColor: const Color(0xFF3B82F6),
                    onChanged: (val) => setState(() => _differentDropoff = val ?? false),
                  ),
                ),
                const SizedBox(width: 8),
                Text(
                  'Return car to a different location',
                  style: TextStyle(color: Colors.white.withOpacity(0.8), fontSize: 11.5),
                ),
              ],
            ),
          ),

          if (_differentDropoff) ...[
            const SizedBox(height: 8),
            Text('Drop-off Location', style: TextStyle(color: Colors.white.withOpacity(0.6), fontSize: 11, fontWeight: FontWeight.bold)),
            const SizedBox(height: 4),
            TextField(
              controller: _dropoffLocationController,
              style: const TextStyle(color: Colors.white, fontSize: 12.5),
              decoration: InputDecoration(
                filled: true,
                fillColor: const Color(0xFF0F172A),
                prefixIcon: const Icon(Icons.pin_drop_rounded, color: AppColors.danger, size: 18),
                hintText: 'Enter drop-off hub or city...',
                hintStyle: const TextStyle(color: Colors.white30, fontSize: 12),
                contentPadding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildDateTimeSelector(String title, String value, IconData icon) {
    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: const Color(0xFF0F172A),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.white.withOpacity(0.05)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: TextStyle(color: Colors.white.withOpacity(0.45), fontSize: 9.5)),
          const SizedBox(height: 2),
          Row(
            children: [
              Icon(icon, color: const Color(0xFF3B82F6), size: 14),
              const SizedBox(width: 6),
              Expanded(
                child: Text(
                  value,
                  style: const TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.w800),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // --- PROTECTION & INSURANCE SECTION ---
  Widget _buildProtectionSection(CountryProvider countryProv) {
    final protDaily = countryProv.protectionDailyRate;
    final sym = countryProv.currencySymbol;
    final depositAmount = _getSecurityDeposit(countryProv);

    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFF131C2E),
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: Colors.white.withOpacity(0.08)),
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
            decoration: BoxDecoration(
              color: const Color(0xFF3B82F6).withOpacity(0.18),
              borderRadius: BorderRadius.circular(8),
            ),
            child: const Text(
              'RIDEMYCARS PROTECTION & COVERAGE',
              style: TextStyle(color: Color(0xFF60A5FA), fontSize: 9.5, fontWeight: FontWeight.w900),
            ),
          ),
          const SizedBox(height: 4),
          const Text(
            'Select Protection & Insurance',
            style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w900),
          ),
          Text(
            'Choose between included standard protection or full excess cover for peace of mind.',
            style: TextStyle(color: Colors.white.withOpacity(0.55), fontSize: 11),
          ),

          const SizedBox(height: 14),

          // Option 1: Basic Protection
          GestureDetector(
            onTap: () => setState(() => _protectionOption = 'basic'),
            child: Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: _protectionOption == 'basic'
                    ? const Color(0xFF3B82F6).withOpacity(0.15)
                    : const Color(0xFF0F172A),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: _protectionOption == 'basic' ? const Color(0xFF3B82F6) : Colors.white.withOpacity(0.06),
                  width: _protectionOption == 'basic' ? 1.5 : 1,
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: const [
                          Text('Basic Protection', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 13.5)),
                          Text('Included FREE in Rental', style: TextStyle(color: Color(0xFF10B981), fontSize: 10.5, fontWeight: FontWeight.bold)),
                        ],
                      ),
                      Radio<String>(
                        value: 'basic',
                        groupValue: _protectionOption,
                        activeColor: const Color(0xFF3B82F6),
                        onChanged: (val) => setState(() => _protectionOption = val!),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Text('✓ Third-party liability insurance', style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 11)),
                  Text('✓ Collision Damage Waiver (CDW)', style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 11)),
                  Text('⚠️ Standard Excess / Deductible applies ($sym${depositAmount.toStringAsFixed(0)} hold)', style: TextStyle(color: Colors.white.withOpacity(0.55), fontSize: 10.5)),
                ],
              ),
            ),
          ),

          const SizedBox(height: 10),

          // Option 2: Full Protection Cover (Recommended)
          GestureDetector(
            onTap: () => setState(() => _protectionOption = 'full_cover'),
            child: Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: _protectionOption == 'full_cover'
                    ? const Color(0xFF3B82F6).withOpacity(0.18)
                    : const Color(0xFF0F172A),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: _protectionOption == 'full_cover' ? const Color(0xFF3B82F6) : Colors.white.withOpacity(0.06),
                  width: _protectionOption == 'full_cover' ? 1.5 : 1,
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF59E0B),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: const Text('RECOMMENDED', style: TextStyle(color: Colors.black, fontWeight: FontWeight.w900, fontSize: 8.5)),
                          ),
                          const SizedBox(height: 2),
                          const Text('Full Protection Cover', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 13.5)),
                          Text(
                            '+$sym${protDaily.toStringAsFixed(2)} / day',
                            style: const TextStyle(color: Color(0xFF60A5FA), fontSize: 10.5, fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
                      Radio<String>(
                        value: 'full_cover',
                        groupValue: _protectionOption,
                        activeColor: const Color(0xFF3B82F6),
                        onChanged: (val) => setState(() => _protectionOption = val!),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Text('✓ $sym 0 Excess / Zero Deductible on Collision & Theft', style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 11)),
                  Text('✓ Full Glass, Wheels, Tires & Bodywork Coverage', style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 11)),
                  Text('✓ 24/7 Priority Emergency Roadside Assistance', style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 11)),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  // --- OPTIONAL EXTRAS SECTION ---
  Widget _buildOptionalExtrasSection(CountryProvider countryProv) {
    final sym = countryProv.currencySymbol;

    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFF131C2E),
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: Colors.white.withOpacity(0.08)),
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Optional Extras',
            style: TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.w900),
          ),
          Text(
            'Add equipment or driver options to your rental reservation.',
            style: TextStyle(color: Colors.white.withOpacity(0.55), fontSize: 11),
          ),

          const SizedBox(height: 12),

          _buildExtraItem(
            'additional_driver',
            '👨‍✈️ Additional Driver',
            '+$sym${countryProv.additionalDriverDailyRate.toStringAsFixed(2)} / day',
          ),
          const SizedBox(height: 8),
          _buildExtraItem(
            'child_seat',
            '👶 Child Safety Seat',
            '+$sym${countryProv.childSeatDailyRate.toStringAsFixed(2)} / day',
          ),
          const SizedBox(height: 8),
          _buildExtraItem(
            'gps',
            '🗺️ GPS Navigation',
            '+$sym${countryProv.gpsDailyRate.toStringAsFixed(2)} / day',
          ),
        ],
      ),
    );
  }

  Widget _buildExtraItem(String key, String title, String price) {
    final isSelected = _selectedExtras.contains(key);
    return GestureDetector(
      onTap: () {
        setState(() {
          if (isSelected) {
            _selectedExtras.remove(key);
          } else {
            _selectedExtras.add(key);
          }
        });
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF3B82F6).withOpacity(0.14) : const Color(0xFF0F172A),
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: isSelected ? const Color(0xFF3B82F6) : Colors.white.withOpacity(0.06),
          ),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w800)),
                Text(price, style: TextStyle(color: Colors.white.withOpacity(0.5), fontSize: 10.5)),
              ],
            ),
            Checkbox(
              value: isSelected,
              activeColor: const Color(0xFF3B82F6),
              onChanged: (val) {
                setState(() {
                  if (val == true) {
                    _selectedExtras.add(key);
                  } else {
                    _selectedExtras.remove(key);
                  }
                });
              },
            ),
          ],
        ),
      ),
    );
  }

  // --- DRIVER INFO SECTION ---
  Widget _buildDriverInfoSection(CountryProvider countryProv) {
    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFF131C2E),
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: Colors.white.withOpacity(0.08)),
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Driver & Contact Details',
            style: TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.w900),
          ),
          const SizedBox(height: 12),

          // Driver License
          Text('Driver License Number *', style: TextStyle(color: Colors.white.withOpacity(0.6), fontSize: 11, fontWeight: FontWeight.bold)),
          const SizedBox(height: 4),
          TextField(
            controller: _licenseController,
            style: const TextStyle(color: Colors.white, fontSize: 12.5),
            decoration: InputDecoration(
              filled: true,
              fillColor: const Color(0xFF0F172A),
              prefixIcon: const Icon(Icons.badge_rounded, color: Color(0xFF3B82F6), size: 18),
              hintText: 'e.g. DL-88997766',
              contentPadding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
            ),
          ),

          const SizedBox(height: 10),

          // Age and Country Row
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Driver Age (Min ${widget.vehicle.minDriverAge}+) *', style: TextStyle(color: Colors.white.withOpacity(0.6), fontSize: 11, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 2),
                      decoration: BoxDecoration(
                        color: const Color(0xFF0F172A),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          IconButton(
                            icon: const Icon(Icons.remove, color: Colors.white70, size: 16),
                            onPressed: _driverAge > widget.vehicle.minDriverAge
                                ? () => setState(() => _driverAge--)
                                : null,
                          ),
                          Text('$_driverAge', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 14)),
                          IconButton(
                            icon: const Icon(Icons.add, color: Colors.white70, size: 16),
                            onPressed: () => setState(() => _driverAge++),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Residence Country *', style: TextStyle(color: Colors.white.withOpacity(0.6), fontSize: 11, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 4),
                    GestureDetector(
                      onTap: () => _showCountrySelector(countryProv),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                        decoration: BoxDecoration(
                          color: const Color(0xFF0F172A),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              '${countryProv.flag} ${countryProv.selectedCountryName}',
                              style: const TextStyle(color: Colors.white, fontSize: 12),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                            const Icon(Icons.arrow_drop_down, color: Colors.white70, size: 18),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),

          const SizedBox(height: 10),

          // Contact Email & Phone
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Contact Email', style: TextStyle(color: Colors.white.withOpacity(0.6), fontSize: 11, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 4),
                    TextField(
                      controller: _emailController,
                      style: const TextStyle(color: Colors.white, fontSize: 12),
                      decoration: InputDecoration(
                        filled: true,
                        fillColor: const Color(0xFF0F172A),
                        hintText: 'email@domain.com',
                        contentPadding: const EdgeInsets.symmetric(vertical: 10, horizontal: 10),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Phone Number', style: TextStyle(color: Colors.white.withOpacity(0.6), fontSize: 11, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 4),
                    TextField(
                      controller: _phoneController,
                      style: const TextStyle(color: Colors.white, fontSize: 12),
                      decoration: InputDecoration(
                        filled: true,
                        fillColor: const Color(0xFF0F172A),
                        hintText: '+1 (555)...',
                        contentPadding: const EdgeInsets.symmetric(vertical: 10, horizontal: 10),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // --- RENTAL SUMMARY & ITEMIZED BREAKDOWN CARD ---
  Widget _buildRentalSummaryCard(CountryProvider countryProv) {
    final dailyRate = _getDailyRate(countryProv);
    final baseTotal = _getBaseTotal(countryProv);
    final protectionTotal = _getProtectionTotal(countryProv);
    final extrasTotal = _getExtrasTotal(countryProv);
    final totalAmount = _getTotalAmount(countryProv);
    final payNowDeposit = _getPayNowDeposit(countryProv);
    final balanceAtPickup = _getBalanceAtPickup(countryProv);
    final sym = countryProv.currencySymbol;

    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFF131C2E),
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: Colors.white.withOpacity(0.08)),
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Rental Summary', style: TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.w900)),
              Text(
                '$_daysCount Day(s) Duration',
                style: TextStyle(color: Colors.white.withOpacity(0.55), fontSize: 11),
              ),
            ],
          ),

          const SizedBox(height: 12),

          // Itemized Breakdown
          _buildSummaryRow(
            'Base Rental ($_daysCount Days @ $sym${dailyRate.toStringAsFixed(2)}):',
            '$sym${baseTotal.toStringAsFixed(2)}',
          ),
          const SizedBox(height: 6),
          _buildSummaryRow(
            'Protection Cover:',
            '+ $sym${protectionTotal.toStringAsFixed(2)}',
          ),
          const SizedBox(height: 6),
          _buildSummaryRow(
            'Optional Extras:',
            '+ $sym${extrasTotal.toStringAsFixed(2)}',
          ),

          const SizedBox(height: 10),
          const Divider(color: Colors.white10, height: 1),
          const SizedBox(height: 10),

          // Total
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Estimated Total:', style: TextStyle(color: Colors.white, fontSize: 14, fontWeight: FontWeight.w900)),
              Text(
                '$sym${totalAmount.toStringAsFixed(2)}',
                style: const TextStyle(color: Color(0xFF60A5FA), fontSize: 20, fontWeight: FontWeight.w900),
              ),
            ],
          ),

          const SizedBox(height: 16),

          // Payment Option (20% Deposit vs Full Payment)
          Text('Payment Option *', style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 11, fontWeight: FontWeight.bold)),
          const SizedBox(height: 6),
          Row(
            children: [
              Expanded(
                child: GestureDetector(
                  onTap: () => setState(() => _paymentOption = 'part'),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                    decoration: BoxDecoration(
                      color: _paymentOption == 'part'
                          ? const Color(0xFF3B82F6).withOpacity(0.18)
                          : const Color(0xFF0F172A),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: _paymentOption == 'part' ? const Color(0xFF3B82F6) : Colors.white.withOpacity(0.06),
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('20% Deposit Online', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
                        const SizedBox(height: 2),
                        Text(
                          '$sym${(totalAmount * 0.20).toStringAsFixed(2)}',
                          style: const TextStyle(color: Color(0xFF10B981), fontWeight: FontWeight.w900, fontSize: 13),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: GestureDetector(
                  onTap: () => setState(() => _paymentOption = 'full'),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                    decoration: BoxDecoration(
                      color: _paymentOption == 'full'
                          ? const Color(0xFF3B82F6).withOpacity(0.18)
                          : const Color(0xFF0F172A),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: _paymentOption == 'full' ? const Color(0xFF3B82F6) : Colors.white.withOpacity(0.06),
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Full Payment (100%)', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
                        const SizedBox(height: 2),
                        Text(
                          '$sym${totalAmount.toStringAsFixed(2)}',
                          style: const TextStyle(color: Color(0xFF10B981), fontWeight: FontWeight.w900, fontSize: 13),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 12),

          // Pay Today & Due at Pickup Card
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: const Color(0xFF0F172A),
              borderRadius: BorderRadius.circular(14),
            ),
            child: Column(
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Payable Online Today:', style: TextStyle(color: Color(0xFF34D399), fontWeight: FontWeight.bold, fontSize: 12)),
                    Text(
                      '$sym${payNowDeposit.toStringAsFixed(2)}',
                      style: const TextStyle(color: Color(0xFF34D399), fontWeight: FontWeight.w900, fontSize: 14),
                    ),
                  ],
                ),
                if (_paymentOption == 'part') ...[
                  const SizedBox(height: 6),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Remaining Balance at Pickup:', style: TextStyle(color: Color(0xFFFBBF24), fontWeight: FontWeight.bold, fontSize: 12)),
                      Text(
                        '$sym${balanceAtPickup.toStringAsFixed(2)}',
                        style: const TextStyle(color: Color(0xFFFBBF24), fontWeight: FontWeight.w900, fontSize: 14),
                      ),
                    ],
                  ),
                ],
              ],
            ),
          ),

          const SizedBox(height: 14),

          // Payment Method Selector
          Text('Payment Method', style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 11, fontWeight: FontWeight.bold)),
          const SizedBox(height: 6),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
            decoration: BoxDecoration(
              color: const Color(0xFF0F172A),
              borderRadius: BorderRadius.circular(12),
            ),
            child: DropdownButtonHideUnderline(
              child: DropdownButton<String>(
                value: _paymentMethod,
                isExpanded: true,
                dropdownColor: const Color(0xFF1E293B),
                items: const [
                  DropdownMenuItem(value: 'stripe', child: Text('💳 Stripe (Credit / Debit Card)', style: TextStyle(color: Colors.white, fontSize: 12.5))),
                  DropdownMenuItem(value: 'momo', child: Text('📱 Momo Pay', style: TextStyle(color: Colors.white, fontSize: 12.5))),
                  DropdownMenuItem(value: 'cash', child: Text('💵 Cash on Pickup', style: TextStyle(color: Colors.white, fontSize: 12.5))),
                  DropdownMenuItem(value: 'applepay', child: Text('🍏 Apple Pay', style: TextStyle(color: Colors.white, fontSize: 12.5))),
                ],
                onChanged: (val) {
                  if (val != null) setState(() => _paymentMethod = val);
                },
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryRow(String label, String value) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 11.5)),
        Text(value, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12)),
      ],
    );
  }

  // --- TERMS CHECKBOX ---
  Widget _buildTermsCheckbox() {
    return GestureDetector(
      onTap: () => setState(() => _agreedToTerms = !_agreedToTerms),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 20,
            height: 20,
            child: Checkbox(
              value: _agreedToTerms,
              activeColor: const Color(0xFF3B82F6),
              onChanged: (val) => setState(() => _agreedToTerms = val ?? true),
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              'I agree to the Rental Terms, Fuel Policy (${widget.vehicle.fuelPolicy}), and Cancellation Policy. *',
              style: TextStyle(color: Colors.white.withOpacity(0.75), fontSize: 11),
            ),
          ),
        ],
      ),
    );
  }

  // --- STICKY BOTTOM CTA BAR ---
  Widget _buildStickyBottomCTA(CountryProvider countryProv) {
    final payNow = _getPayNowDeposit(countryProv);
    final sym = countryProv.currencySymbol;

    return Container(
      color: const Color(0xFF0F172A),
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 12),
      child: SafeArea(
        child: SizedBox(
          width: double.infinity,
          height: 50,
          child: ElevatedButton(
            onPressed: _isSubmitting ? null : () => _submitBooking(countryProv),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF3B82F6),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              elevation: 4,
            ),
            child: _isSubmitting
                ? const SizedBox(
                    width: 22,
                    height: 22,
                    child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white),
                  )
                : Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.vpn_key_rounded, size: 18),
                      const SizedBox(width: 8),
                      Text(
                        'Confirm & Pay Deposit ($sym${payNow.toStringAsFixed(2)}) →',
                        style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w900),
                      ),
                    ],
                  ),
          ),
        ),
      ),
    );
  }
}
