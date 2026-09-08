import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:intl/intl.dart';
import '../../core/constants/app_colors.dart';
import '../../models/driver_model.dart';
import '../../services/driver_service.dart';
import '../../services/places_service.dart';

class DriverDetailScreen extends StatefulWidget {
  final DriverModel? driver;
  final String? initialPickupLocation;
  final String? initialDropoffLocation;

  const DriverDetailScreen({
    super.key,
    this.driver,
    this.initialPickupLocation,
    this.initialDropoffLocation,
  });

  @override
  State<DriverDetailScreen> createState() => _DriverDetailScreenState();
}

class _DriverDetailScreenState extends State<DriverDetailScreen> {
  // Service Type Tabs
  final List<String> _serviceTypes = [
    'Hire Driver',
    'Hourly Driver',
    'Daily Driver',
    'Outstation',
    'Package',
  ];
  String _selectedServiceType = 'Hire Driver';

  // Routing & Locations
  final TextEditingController _pickupController = TextEditingController();
  final TextEditingController _dropoffController = TextEditingController();
  final List<TextEditingController> _additionalStopControllers = [];
  bool _isLocating = false;

  // Schedule & Duration
  String _scheduleMode = 'now'; // 'now' or 'later'
  DateTime _startDate = DateTime.now();
  TimeOfDay _startTime = const TimeOfDay(hour: 9, minute: 0);

  String _durationType = 'hourly'; // 'hourly', 'daily', 'weekly'
  int _durationCount = 4;

  // Vehicle Info
  final TextEditingController _carMakeModelController =
      TextEditingController(text: 'Toyota Camry');
  final TextEditingController _regNumberController =
      TextEditingController(text: 'REG-8899');
  String _transmission = 'automatic';

  // Driver Preferences
  String _preferredGender = 'any';
  String _preferredLanguage = 'English';

  // Payment & Country
  String _selectedCountry = 'USA';
  String _currencySymbol = '\$';
  String _paymentMethod = 'stripe'; // 'stripe', 'momo', 'cash', 'applepay'

  // Dynamic Fare & Breakdown
  bool _isCalculating = false;
  double _hourlyRate = 35.0;
  double _dailyRate = 238.0;
  double _weeklyRate = 1416.0;
  double _subtotal = 140.0;
  double _serviceFee = 7.0;
  double _tax = 7.0;
  double _totalPrice = 154.0;
  String _appliedRateText = '4 Hours @ \$35.00/hr';

  // Booking status
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    if (widget.initialPickupLocation != null &&
        widget.initialPickupLocation!.isNotEmpty) {
      _pickupController.text = widget.initialPickupLocation!;
    }
    if (widget.initialDropoffLocation != null &&
        widget.initialDropoffLocation!.isNotEmpty) {
      _dropoffController.text = widget.initialDropoffLocation!;
    }

    if (widget.driver != null) {
      _hourlyRate = widget.driver!.hourlyRate > 0 ? widget.driver!.hourlyRate : 35.0;
      _dailyRate = widget.driver!.dailyRate > 0 ? widget.driver!.dailyRate : (_hourlyRate * 8 * 0.85);
      _weeklyRate = widget.driver!.weeklyRate > 0 ? widget.driver!.weeklyRate : (_dailyRate * 7 * 0.85);
      _selectedCountry = widget.driver!.country.isNotEmpty ? widget.driver!.country : 'USA';
    }

    _recalculatePrice();
  }

  @override
  void dispose() {
    _pickupController.dispose();
    _dropoffController.dispose();
    _carMakeModelController.dispose();
    _regNumberController.dispose();
    for (var c in _additionalStopControllers) {
      c.dispose();
    }
    super.dispose();
  }

  void _onServiceTypeSelected(String st) {
    setState(() {
      _selectedServiceType = st;
      if (st == 'Daily Driver') {
        _durationType = 'daily';
        _durationCount = 1;
      } else if (st == 'Hourly Driver') {
        _durationType = 'hourly';
        _durationCount = 4;
      } else if (st == 'Outstation') {
        _durationType = 'daily';
        _durationCount = 2;
      } else if (st == 'Package') {
        _durationType = 'weekly';
        _durationCount = 1;
      } else {
        _durationType = 'hourly';
        _durationCount = 4;
      }
    });
    _recalculatePrice();
  }

  void _addStop([String defaultText = '']) {
    if (_additionalStopControllers.length < 5) {
      setState(() {
        _additionalStopControllers.add(TextEditingController(text: defaultText));
      });
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Maximum 5 additional stops allowed.'),
          backgroundColor: AppColors.warning,
        ),
      );
    }
  }

  void _removeStop(int index) {
    setState(() {
      _additionalStopControllers[index].dispose();
      _additionalStopControllers.removeAt(index);
    });
  }

  Future<void> _useCurrentLocation() async {
    setState(() => _isLocating = true);
    try {
      bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        throw Exception('Location services disabled.');
      }
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
        if (permission == LocationPermission.denied) {
          throw Exception('Location permissions denied.');
        }
      }
      final pos = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
      );
      final addr = await PlacesService.getAddressFromCoordinates(pos.latitude, pos.longitude);
      if (addr != null && addr.isNotEmpty) {
        setState(() {
          _pickupController.text = addr;
        });
      } else {
        setState(() {
          _pickupController.text = 'Current Location (${pos.latitude.toStringAsFixed(4)}, ${pos.longitude.toStringAsFixed(4)})';
        });
      }
    } catch (e) {
      debugPrint('Error getting location: $e');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Could not detect location: $e'),
          backgroundColor: AppColors.danger,
        ),
      );
    } finally {
      if (mounted) setState(() => _isLocating = false);
    }
  }

  Future<void> _recalculatePrice() async {
    setState(() => _isCalculating = true);

    // Call API calculation if driver id exists
    if (widget.driver != null && widget.driver!.id > 0) {
      final res = await DriverService.calculatePrice(
        driverProfileId: widget.driver!.id,
        durationType: _durationType,
        durationCount: _durationCount,
        country: _selectedCountry,
      );
      if (res != null && mounted) {
        setState(() {
          _subtotal = (res['subtotal'] as num?)?.toDouble() ?? _subtotal;
          _serviceFee = (res['service_fee'] as num?)?.toDouble() ?? _serviceFee;
          _tax = (res['tax'] as num?)?.toDouble() ?? _tax;
          _totalPrice = (res['total_price'] as num?)?.toDouble() ?? _totalPrice;
          _currencySymbol = res['currency_symbol']?.toString() ?? _currencySymbol;
          _appliedRateText = res['applied_rate_text']?.toString() ?? _appliedRateText;
          _isCalculating = false;
        });
        return;
      }
    }

    // Fallback local calculation matching backend PricingService
    double sub = 0.0;
    String rateText = '';

    if (_durationType == 'weekly') {
      sub = _weeklyRate * _durationCount;
      rateText = '$_durationCount Week(s) @ $_currencySymbol${_weeklyRate.toStringAsFixed(2)}/week';
    } else if (_durationType == 'daily') {
      sub = _dailyRate * _durationCount;
      rateText = '$_durationCount Day(s) @ $_currencySymbol${_dailyRate.toStringAsFixed(2)}/day';
    } else {
      final hours = _durationCount;
      if (hours >= 8) {
        final days = (hours / 8.0).ceil();
        sub = _dailyRate * days;
        rateText = 'Full Day Rate (8+ hrs) @ $_currencySymbol${_dailyRate.toStringAsFixed(2)}';
      } else if (hours > 4) {
        final effective = _hourlyRate * 0.95;
        sub = effective * hours;
        rateText = '$hours Hours (Tiered 4-8 hr) @ $_currencySymbol${effective.toStringAsFixed(2)}/hr';
      } else {
        sub = _hourlyRate * hours;
        rateText = '$hours Hours @ $_currencySymbol${_hourlyRate.toStringAsFixed(2)}/hr';
      }
    }

    final fee = double.parse((sub * 0.05).toStringAsFixed(2));
    final tx = double.parse((sub * 0.05).toStringAsFixed(2));
    final tot = double.parse((sub + fee + tx).toStringAsFixed(2));

    if (mounted) {
      setState(() {
        _subtotal = sub;
        _serviceFee = fee;
        _tax = tx;
        _totalPrice = tot;
        _appliedRateText = rateText;
        _isCalculating = false;
      });
    }
  }

  Future<void> _handleBookDriver() async {
    final pickup = _pickupController.text.trim();
    if (pickup.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please enter a pick-up location.'),
          backgroundColor: AppColors.warning,
        ),
      );
      return;
    }

    final carModel = _carMakeModelController.text.trim();
    if (carModel.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please provide your vehicle make & model.'),
          backgroundColor: AppColors.warning,
        ),
      );
      return;
    }

    setState(() => _isSubmitting = true);

    final List<Map<String, dynamic>> additionalStops = [];
    for (var ctrl in _additionalStopControllers) {
      if (ctrl.text.trim().isNotEmpty) {
        additionalStops.add({'location': ctrl.text.trim()});
      }
    }

    final payload = {
      'driver_profile_id': widget.driver?.id ?? 1,
      'service_category': 'private',
      'service_type': _selectedServiceType,
      'country': _selectedCountry,
      'pickup_location': pickup,
      'dropoff_location': _dropoffController.text.trim().isNotEmpty
          ? _dropoffController.text.trim()
          : null,
      'additional_stops': additionalStops.isNotEmpty ? additionalStops : null,
      'start_date': DateFormat('yyyy-MM-dd').format(_startDate),
      'start_time': '${_startTime.hour.toString().padLeft(2, '0')}:${_startTime.minute.toString().padLeft(2, '0')}',
      'duration_type': _durationType,
      'duration_count': _durationCount,
      'car_make_model': carModel,
      'registration_number': _regNumberController.text.trim().isNotEmpty
          ? _regNumberController.text.trim()
          : 'REG-8899',
      'transmission': _transmission,
      'preferred_gender': _preferredGender,
      'preferred_language': _preferredLanguage,
      'payment_method': _paymentMethod,
    };

    final result = await DriverService.bookDriver(payload);

    setState(() => _isSubmitting = false);

    if (!mounted) return;

    if (result != null && (result['status'] == 'success' || result['booking_code'] != null || result['data'] != null)) {
      final bookingData = result['data'] is Map<String, dynamic>
          ? result['data'] as Map<String, dynamic>
          : result;
      final bookingCode = bookingData['booking_code']?.toString() ?? 'DRV-REQUESTED';

      _showConfirmationDialog(bookingCode);
    } else {
      final errMsg = result?['message']?.toString() ?? 'Failed to submit driver request. Please try again.';
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(errMsg),
          backgroundColor: AppColors.danger,
        ),
      );
    }
  }

  void _showConfirmationDialog(String bookingCode) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => SafeArea(
        top: false,
        bottom: true,
        child: Container(
          padding: EdgeInsets.fromLTRB(
            24,
            20,
            24,
            20 + MediaQuery.of(ctx).padding.bottom,
          ),
          decoration: const BoxDecoration(
            color: Color(0xFF1E293B),
            borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              Container(
                width: 44,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.white24,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const SizedBox(height: 18),
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: const Color(0xFF10B981).withOpacity(0.2),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.check_circle_rounded,
                    color: Color(0xFF10B981), size: 48),
              ),
              const SizedBox(height: 14),
              const Text(
                'Driver Request Submitted!',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 20,
                  fontWeight: FontWeight.w900,
                ),
              ),
              const SizedBox(height: 6),
              Text(
                'Your chauffeur booking has been scheduled successfully.',
                textAlign: TextAlign.center,
                style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 13),
              ),
              const SizedBox(height: 20),

              // Voucher Card
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: const Color(0xFF0F172A),
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(color: const Color(0xFF10B981).withOpacity(0.3)),
                ),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'BOOKING CODE',
                          style: TextStyle(
                            color: AppColors.textMuted,
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        Text(
                          bookingCode,
                          style: const TextStyle(
                            color: Color(0xFF10B981),
                            fontSize: 14,
                            fontWeight: FontWeight.w900,
                            letterSpacing: 1.1,
                          ),
                        ),
                      ],
                    ),
                    const Divider(color: Colors.white12, height: 20),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Assigned Chauffeur',
                            style: TextStyle(color: Colors.white70, fontSize: 12)),
                        Text(
                          widget.driver?.name ?? 'Assigned Partner',
                          style: const TextStyle(
                              color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Service Type',
                            style: TextStyle(color: Colors.white70, fontSize: 12)),
                        Text(
                          _selectedServiceType,
                          style: const TextStyle(
                              color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Duration',
                            style: TextStyle(color: Colors.white70, fontSize: 12)),
                        Text(
                          '$_durationCount ${_durationType.toUpperCase()}',
                          style: const TextStyle(
                              color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Estimated Fare',
                            style: TextStyle(color: Colors.white70, fontSize: 12)),
                        Text(
                          '$_currencySymbol${_totalPrice.toStringAsFixed(2)}',
                          style: const TextStyle(
                            color: Color(0xFF10B981),
                            fontWeight: FontWeight.w900,
                            fontSize: 14,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                height: 52,
                child: ElevatedButton(
                  onPressed: () {
                    Navigator.pop(ctx);
                    Navigator.pop(context);
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF10B981),
                    elevation: 3,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                  ),
                  child: const Text(
                    'Done & Return to Map',
                    style: TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.w900,
                      fontSize: 15,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 4),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0F172A),
      appBar: AppBar(
        backgroundColor: const Color(0xFF0F172A),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Hire a Driver',
          style: TextStyle(
            color: Colors.white,
            fontSize: 17,
            fontWeight: FontWeight.w800,
          ),
        ),
        centerTitle: true,
        actions: [
          Container(
            margin: const EdgeInsets.only(right: 14),
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: const Color(0xFF10B981).withOpacity(0.18),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFF10B981).withOpacity(0.3)),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Text('🌍 ', style: TextStyle(fontSize: 11)),
                Text(
                  '$_selectedCountry • $_currencySymbol',
                  style: const TextStyle(
                    color: Color(0xFF34D399),
                    fontSize: 11,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header Banner & Preselected Driver Card
            _buildHeaderBanner(),

            const SizedBox(height: 16),

            // 1. Select Driver Service Type Tabs
            _buildServiceTypeSelector(),

            const SizedBox(height: 16),

            // 2. Locations & Additional Stops Card
            _buildLocationRoutingCard(),

            const SizedBox(height: 16),

            // 3. Schedule & Duration Card
            _buildScheduleDurationCard(),

            const SizedBox(height: 16),

            // 4. Vehicle Information Card
            _buildVehicleInformationCard(),

            const SizedBox(height: 16),

            // 5. Driver Preferences Card
            _buildDriverPreferencesCard(),

            const SizedBox(height: 16),

            // 6. Price Estimate & Fare Breakdown Card
            _buildFareBreakdownCard(),

            const SizedBox(height: 16),

            // 7. Payment Method & PCI Security Card
            _buildPaymentMethodCard(),

            const SizedBox(height: 90), // Spacing for sticky bottom CTA
          ],
        ),
      ),
      bottomNavigationBar: _buildStickyBottomCTA(),
    );
  }

  // --- 1. Header Banner & Pre-selected Driver Card ---
  Widget _buildHeaderBanner() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF1E293B),
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: Colors.white.withOpacity(0.08)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3.5),
                decoration: BoxDecoration(
                  color: const Color(0xFF10B981).withOpacity(0.2),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: const Color(0xFF10B981).withOpacity(0.4)),
                ),
                child: const Text(
                  'RIDEMYCARS DRIVER HIRING',
                  style: TextStyle(
                    color: Color(0xFF34D399),
                    fontSize: 9.5,
                    fontWeight: FontWeight.w900,
                    letterSpacing: 0.8,
                  ),
                ),
              ),
              const Spacer(),
              const Text(
                '🛡️ Verified & Insured',
                style: TextStyle(color: Color(0xFF34D399), fontSize: 10.5, fontWeight: FontWeight.bold),
              ),
            ],
          ),
          const SizedBox(height: 8),
          const Text(
            'Hire a Personal or Commercial Driver',
            style: TextStyle(
              color: Colors.white,
              fontSize: 18,
              fontWeight: FontWeight.w900,
              letterSpacing: -0.3,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'On-demand professional drivers for your vehicle. Verified, insured, and background-checked.',
            style: TextStyle(color: Colors.white.withOpacity(0.65), fontSize: 12, height: 1.3),
          ),

          if (widget.driver != null) ...[
            const SizedBox(height: 14),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFF0F172A),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFF10B981).withOpacity(0.4)),
              ),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 24,
                    backgroundColor: const Color(0xFF10B981),
                    backgroundImage: (widget.driver!.avatarUrl != null &&
                            widget.driver!.avatarUrl!.isNotEmpty)
                        ? NetworkImage(widget.driver!.avatarUrl!)
                        : null,
                    child: (widget.driver!.avatarUrl == null ||
                            widget.driver!.avatarUrl!.isEmpty)
                        ? Text(
                            widget.driver!.name.isNotEmpty
                                ? widget.driver!.name[0].toUpperCase()
                                : 'D',
                            style: const TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.w900,
                              fontSize: 18,
                            ),
                          )
                        : null,
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'PRE-SELECTED DRIVER',
                          style: TextStyle(
                            color: Color(0xFF34D399),
                            fontSize: 9.5,
                            fontWeight: FontWeight.w900,
                            letterSpacing: 0.6,
                          ),
                        ),
                        Text(
                          widget.driver!.name,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 15,
                            fontWeight: FontWeight.w900,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 2),
                        Row(
                          children: [
                            const Icon(Icons.star_rounded, color: Color(0xFFFFDC00), size: 14),
                            const SizedBox(width: 2),
                            Text(
                              '${widget.driver!.rating.toStringAsFixed(1)} • ${widget.driver!.totalTrips} Trips',
                              style: const TextStyle(
                                color: Color(0xFFFFDC00),
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(width: 8),
                            Text(
                              '🛡️ ${widget.driver!.experienceYears}+ Yrs',
                              style: const TextStyle(color: Colors.white70, fontSize: 11),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    decoration: BoxDecoration(
                      color: const Color(0xFF10B981).withOpacity(0.15),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      '$_currencySymbol${widget.driver!.hourlyRate.toStringAsFixed(0)}/hr',
                      style: const TextStyle(
                        color: Color(0xFF34D399),
                        fontSize: 13,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  // --- 2. Service Type Selector Tabs ---
  Widget _buildServiceTypeSelector() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF1E293B),
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: Colors.white.withOpacity(0.08)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'SELECT DRIVER SERVICE TYPE *',
            style: TextStyle(
              color: Colors.white70,
              fontSize: 11,
              fontWeight: FontWeight.w900,
              letterSpacing: 0.8,
            ),
          ),
          const SizedBox(height: 10),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: _serviceTypes.map((st) {
                final isSelected = _selectedServiceType == st;
                return Padding(
                  padding: const EdgeInsets.only(right: 8),
                  child: GestureDetector(
                    onTap: () => _onServiceTypeSelected(st),
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 180),
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                      decoration: BoxDecoration(
                        color: isSelected ? const Color(0xFF10B981) : const Color(0xFF0F172A),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(
                          color: isSelected ? const Color(0xFF10B981) : Colors.white12,
                        ),
                      ),
                      child: Text(
                        st,
                        style: TextStyle(
                          color: isSelected ? Colors.white : Colors.white70,
                          fontSize: 12,
                          fontWeight: isSelected ? FontWeight.w900 : FontWeight.w600,
                        ),
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),
          ),
        ],
      ),
    );
  }

  // --- 3. Locations & Routing Card ---
  Widget _buildLocationRoutingCard() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF1E293B),
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: Colors.white.withOpacity(0.08)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Text(
                '📍 Location & Routing',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 15,
                  fontWeight: FontWeight.w900,
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),

          // Pick-up Location
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Pick-up Location *',
                style: TextStyle(color: Colors.white70, fontSize: 11.5, fontWeight: FontWeight.bold),
              ),
              GestureDetector(
                onTap: _isLocating ? null : _useCurrentLocation,
                child: Row(
                  children: [
                    if (_isLocating)
                      const SizedBox(
                        width: 10,
                        height: 10,
                        child: CircularProgressIndicator(
                          strokeWidth: 1.5,
                          color: Color(0xFF10B981),
                        ),
                      )
                    else
                      const Icon(Icons.my_location_rounded, color: Color(0xFF10B981), size: 13),
                    const SizedBox(width: 4),
                    const Text(
                      'Use My Location',
                      style: TextStyle(
                        color: Color(0xFF10B981),
                        fontSize: 11.5,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          TextField(
            controller: _pickupController,
            style: const TextStyle(color: Colors.white, fontSize: 13),
            decoration: InputDecoration(
              filled: true,
              fillColor: const Color(0xFF0F172A),
              hintText: 'Enter pickup address, city, or airport...',
              hintStyle: const TextStyle(color: Colors.white38, fontSize: 12),
              prefixIcon: const Icon(Icons.circle, color: Color(0xFF10B981), size: 12),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: const BorderSide(color: Colors.white12),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: const BorderSide(color: Colors.white12),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: const BorderSide(color: Color(0xFF10B981), width: 1.5),
              ),
              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            ),
          ),

          // Additional Stops
          if (_additionalStopControllers.isNotEmpty) ...[
            const SizedBox(height: 12),
            ...List.generate(_additionalStopControllers.length, (index) {
              return Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: _additionalStopControllers[index],
                        style: const TextStyle(color: Colors.white, fontSize: 13),
                        decoration: InputDecoration(
                          filled: true,
                          fillColor: const Color(0xFF0F172A),
                          hintText: 'Stop #${index + 1} address, landmark...',
                          hintStyle: const TextStyle(color: Colors.white38, fontSize: 12),
                          prefixIcon: const Icon(Icons.flag_rounded, color: Color(0xFFFFB703), size: 15),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(14),
                            borderSide: const BorderSide(color: Color(0xFFFFB703), width: 0.8),
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(14),
                            borderSide: BorderSide(color: const Color(0xFFFFB703).withOpacity(0.5)),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(14),
                            borderSide: const BorderSide(color: Color(0xFFFFB703), width: 1.5),
                          ),
                          contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    IconButton(
                      icon: const Icon(Icons.close_rounded, color: AppColors.danger, size: 20),
                      onPressed: () => _removeStop(index),
                    ),
                  ],
                ),
              );
            }),
          ],

          const SizedBox(height: 10),

          // Action buttons: + Add Additional Stop, Home, Office
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              GestureDetector(
                onTap: () => _addStop(),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFFB703).withOpacity(0.15),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFFFB703).withOpacity(0.3)),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.add_location_alt_rounded, color: Color(0xFFFFB703), size: 14),
                      SizedBox(width: 4),
                      Text(
                        '+ Add Additional Stop',
                        style: TextStyle(
                          color: Color(0xFFFFB703),
                          fontSize: 11.5,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              GestureDetector(
                onTap: () => _addStop('Home Residence Address'),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  decoration: BoxDecoration(
                    color: const Color(0xFF0F172A),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.white12),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text('🏠 ', style: TextStyle(fontSize: 12)),
                      Text(
                        '+ Home',
                        style: TextStyle(
                          color: Colors.white70,
                          fontSize: 11.5,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              GestureDetector(
                onTap: () => _addStop('Corporate Office Address'),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  decoration: BoxDecoration(
                    color: const Color(0xFF0F172A),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.white12),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text('🏢 ', style: TextStyle(fontSize: 12)),
                      Text(
                        '+ Office',
                        style: TextStyle(
                          color: Colors.white70,
                          fontSize: 11.5,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 14),

          // Final Destination (Optional)
          const Text(
            'Final Destination (Optional)',
            style: TextStyle(color: Colors.white70, fontSize: 11.5, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 6),
          TextField(
            controller: _dropoffController,
            style: const TextStyle(color: Colors.white, fontSize: 13),
            decoration: InputDecoration(
              filled: true,
              fillColor: const Color(0xFF0F172A),
              hintText: 'Enter final destination location...',
              hintStyle: const TextStyle(color: Colors.white38, fontSize: 12),
              prefixIcon: const Icon(Icons.location_on_rounded, color: AppColors.danger, size: 16),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: const BorderSide(color: Colors.white12),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: const BorderSide(color: Colors.white12),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: const BorderSide(color: Color(0xFF10B981), width: 1.5),
              ),
              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            ),
          ),
        ],
      ),
    );
  }

  // --- 4. Schedule & Duration Card ---
  Widget _buildScheduleDurationCard() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF1E293B),
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: Colors.white.withOpacity(0.08)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            '⏰ Schedule & Duration',
            style: TextStyle(
              color: Colors.white,
              fontSize: 15,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 12),

          // Book Now vs Schedule for Later
          Row(
            children: [
              Expanded(
                child: GestureDetector(
                  onTap: () => setState(() => _scheduleMode = 'now'),
                  child: Container(
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    decoration: BoxDecoration(
                      color: _scheduleMode == 'now'
                          ? const Color(0xFF10B981)
                          : const Color(0xFF0F172A),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(
                        color: _scheduleMode == 'now'
                            ? const Color(0xFF10B981)
                            : Colors.white12,
                      ),
                    ),
                    child: Center(
                      child: Text(
                        '⚡ Book Now (Immediate)',
                        style: TextStyle(
                          color: _scheduleMode == 'now' ? Colors.white : Colors.white70,
                          fontSize: 11.5,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: GestureDetector(
                  onTap: () => setState(() => _scheduleMode = 'later'),
                  child: Container(
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    decoration: BoxDecoration(
                      color: _scheduleMode == 'later'
                          ? const Color(0xFF10B981)
                          : const Color(0xFF0F172A),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(
                        color: _scheduleMode == 'later'
                            ? const Color(0xFF10B981)
                            : Colors.white12,
                      ),
                    ),
                    child: Center(
                      child: Text(
                        '📅 Schedule for Later',
                        style: TextStyle(
                          color: _scheduleMode == 'later' ? Colors.white : Colors.white70,
                          fontSize: 11.5,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),

          if (_scheduleMode == 'later') ...[
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: GestureDetector(
                    onTap: () async {
                      final picked = await showDatePicker(
                        context: context,
                        initialDate: _startDate,
                        firstDate: DateTime.now(),
                        lastDate: DateTime.now().add(const Duration(days: 90)),
                      );
                      if (picked != null) setState(() => _startDate = picked);
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 11),
                      decoration: BoxDecoration(
                        color: const Color(0xFF0F172A),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: Colors.white12),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.calendar_today_rounded,
                              color: Color(0xFF10B981), size: 14),
                          const SizedBox(width: 6),
                          Text(
                            DateFormat('MMM dd, yyyy').format(_startDate),
                            style: const TextStyle(
                                color: Colors.white,
                                fontSize: 12,
                                fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: GestureDetector(
                    onTap: () async {
                      final picked = await showTimePicker(
                        context: context,
                        initialTime: _startTime,
                      );
                      if (picked != null) setState(() => _startTime = picked);
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 11),
                      decoration: BoxDecoration(
                        color: const Color(0xFF0F172A),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: Colors.white12),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.access_time_rounded,
                              color: Color(0xFF10B981), size: 14),
                          const SizedBox(width: 6),
                          Text(
                            _startTime.format(context),
                            style: const TextStyle(
                                color: Colors.white,
                                fontSize: 12,
                                fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ],

          const SizedBox(height: 14),
          const Divider(color: Colors.white10, height: 1),
          const SizedBox(height: 14),

          // Duration Selection
          const Text(
            'Duration Selection *',
            style: TextStyle(color: Colors.white70, fontSize: 11.5, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              // Duration Unit Dropdown
              Expanded(
                flex: 3,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                  decoration: BoxDecoration(
                    color: const Color(0xFF0F172A),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.white12),
                  ),
                  child: DropdownButtonHideUnderline(
                    child: DropdownButton<String>(
                      value: _durationType,
                      isExpanded: true,
                      dropdownColor: const Color(0xFF1E293B),
                      icon: const Icon(Icons.arrow_drop_down_rounded, color: Colors.white70),
                      style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                      items: const [
                        DropdownMenuItem(
                          value: 'hourly',
                          child: Text('Hourly (1–8 hrs)'),
                        ),
                        DropdownMenuItem(
                          value: 'daily',
                          child: Text('Daily (Full Day)'),
                        ),
                        DropdownMenuItem(
                          value: 'weekly',
                          child: Text('Weekly (7+ Days)'),
                        ),
                      ],
                      onChanged: (val) {
                        if (val != null) {
                          setState(() {
                            _durationType = val;
                            if (val == 'hourly' && _durationCount > 24) _durationCount = 4;
                            if (val == 'daily' && _durationCount > 30) _durationCount = 1;
                            if (val == 'weekly' && _durationCount > 12) _durationCount = 1;
                          });
                          _recalculatePrice();
                        }
                      },
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 10),

              // Duration Count Stepper
              Container(
                decoration: BoxDecoration(
                  color: const Color(0xFF0F172A),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.white12),
                ),
                child: Row(
                  children: [
                    IconButton(
                      icon: const Icon(Icons.remove_rounded, color: Colors.white70, size: 18),
                      onPressed: _durationCount > 1
                          ? () {
                              setState(() => _durationCount--);
                              _recalculatePrice();
                            }
                          : null,
                    ),
                    Text(
                      '$_durationCount',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 14,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.add_rounded, color: Color(0xFF10B981), size: 18),
                      onPressed: () {
                        setState(() => _durationCount++);
                        _recalculatePrice();
                      },
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

  // --- 5. Vehicle Information Card ---
  Widget _buildVehicleInformationCard() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF1E293B),
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: Colors.white.withOpacity(0.08)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            '🚘 Vehicle Information',
            style: TextStyle(
              color: Colors.white,
              fontSize: 15,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                flex: 3,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Make & Model *',
                        style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 4),
                    TextField(
                      controller: _carMakeModelController,
                      style: const TextStyle(color: Colors.white, fontSize: 12),
                      decoration: InputDecoration(
                        filled: true,
                        fillColor: const Color(0xFF0F172A),
                        hintText: 'e.g. Toyota Camry',
                        hintStyle: const TextStyle(color: Colors.white38, fontSize: 11),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: const BorderSide(color: Colors.white12),
                        ),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                flex: 2,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Registration No *',
                        style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 4),
                    TextField(
                      controller: _regNumberController,
                      style: const TextStyle(color: Colors.white, fontSize: 12),
                      decoration: InputDecoration(
                        filled: true,
                        fillColor: const Color(0xFF0F172A),
                        hintText: 'REG-8899',
                        hintStyle: const TextStyle(color: Colors.white38, fontSize: 11),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: const BorderSide(color: Colors.white12),
                        ),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          const Text('Transmission *',
              style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold)),
          const SizedBox(height: 4),
          Row(
            children: [
              Expanded(
                child: GestureDetector(
                  onTap: () => setState(() => _transmission = 'automatic'),
                  child: Container(
                    padding: const EdgeInsets.symmetric(vertical: 9),
                    decoration: BoxDecoration(
                      color: _transmission == 'automatic'
                          ? const Color(0xFF10B981).withOpacity(0.2)
                          : const Color(0xFF0F172A),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: _transmission == 'automatic'
                            ? const Color(0xFF10B981)
                            : Colors.white12,
                      ),
                    ),
                    child: Center(
                      child: Text(
                        '⚙️ Automatic',
                        style: TextStyle(
                          color: _transmission == 'automatic'
                              ? const Color(0xFF34D399)
                              : Colors.white70,
                          fontSize: 11.5,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: GestureDetector(
                  onTap: () => setState(() => _transmission = 'manual'),
                  child: Container(
                    padding: const EdgeInsets.symmetric(vertical: 9),
                    decoration: BoxDecoration(
                      color: _transmission == 'manual'
                          ? const Color(0xFF10B981).withOpacity(0.2)
                          : const Color(0xFF0F172A),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: _transmission == 'manual'
                            ? const Color(0xFF10B981)
                            : Colors.white12,
                      ),
                    ),
                    child: Center(
                      child: Text(
                        '🕹️ Manual',
                        style: TextStyle(
                          color: _transmission == 'manual'
                              ? const Color(0xFF34D399)
                              : Colors.white70,
                          fontSize: 11.5,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // --- 6. Driver Preferences Card ---
  Widget _buildDriverPreferencesCard() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF1E293B),
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: Colors.white.withOpacity(0.08)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            '👨‍✈️ Driver Preferences',
            style: TextStyle(
              color: Colors.white,
              fontSize: 15,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Preferred Gender',
                        style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 2),
                      decoration: BoxDecoration(
                        color: const Color(0xFF0F172A),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: Colors.white12),
                      ),
                      child: DropdownButtonHideUnderline(
                        child: DropdownButton<String>(
                          value: _preferredGender,
                          isExpanded: true,
                          dropdownColor: const Color(0xFF1E293B),
                          style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                          items: const [
                            DropdownMenuItem(value: 'any', child: Text('Any Gender')),
                            DropdownMenuItem(value: 'male', child: Text('Male Driver')),
                            DropdownMenuItem(value: 'female', child: Text('Female Driver')),
                          ],
                          onChanged: (val) => setState(() => _preferredGender = val ?? 'any'),
                        ),
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
                    const Text('Language',
                        style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 2),
                      decoration: BoxDecoration(
                        color: const Color(0xFF0F172A),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: Colors.white12),
                      ),
                      child: DropdownButtonHideUnderline(
                        child: DropdownButton<String>(
                          value: _preferredLanguage,
                          isExpanded: true,
                          dropdownColor: const Color(0xFF1E293B),
                          style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                          items: const [
                            DropdownMenuItem(value: 'English', child: Text('English')),
                            DropdownMenuItem(value: 'Spanish', child: Text('Spanish')),
                            DropdownMenuItem(value: 'French', child: Text('French')),
                            DropdownMenuItem(value: 'Hindi', child: Text('Hindi')),
                          ],
                          onChanged: (val) => setState(() => _preferredLanguage = val ?? 'English'),
                        ),
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

  // --- 7. Price Estimate & Fare Breakdown Card ---
  Widget _buildFareBreakdownCard() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: const Color(0xFF1E293B),
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: const Color(0xFF10B981).withOpacity(0.35)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.25),
            blurRadius: 10,
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
              const Text(
                'PRICE ESTIMATE',
                style: TextStyle(
                  color: Color(0xFF10B981),
                  fontSize: 11,
                  fontWeight: FontWeight.w900,
                  letterSpacing: 1.0,
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: const Color(0xFF10B981).withOpacity(0.2),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  '🌍 $_selectedCountry • $_currencySymbol',
                  style: const TextStyle(
                    color: Color(0xFF34D399),
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          const Text(
            'Estimated Fare',
            style: TextStyle(
              color: Colors.white,
              fontSize: 20,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            _appliedRateText,
            style: TextStyle(color: Colors.white.withOpacity(0.6), fontSize: 11.5),
          ),
          const SizedBox(height: 12),
          const Divider(color: Colors.white10, height: 1),
          const SizedBox(height: 12),

          // Itemized lines
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('Driver Service Fee:',
                  style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 12.5)),
              Text(
                '$_currencySymbol${_subtotal.toStringAsFixed(2)}',
                style: const TextStyle(color: Colors.white, fontSize: 12.5, fontWeight: FontWeight.bold),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('Service Fee (5%):',
                  style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 12.5)),
              Text(
                '$_currencySymbol${_serviceFee.toStringAsFixed(2)}',
                style: const TextStyle(color: Colors.white, fontSize: 12.5, fontWeight: FontWeight.bold),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('Taxes (5%):',
                  style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 12.5)),
              Text(
                '$_currencySymbol${_tax.toStringAsFixed(2)}',
                style: const TextStyle(color: Colors.white, fontSize: 12.5, fontWeight: FontWeight.bold),
              ),
            ],
          ),
          const SizedBox(height: 12),
          const Divider(color: Colors.white10, height: 1),
          const SizedBox(height: 12),

          // Total Price
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Estimated Total:',
                style: TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.w900),
              ),
              _isCalculating
                  ? const SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF10B981)),
                    )
                  : Text(
                      '$_currencySymbol${_totalPrice.toStringAsFixed(2)}',
                      style: const TextStyle(
                        color: Color(0xFFFFB703),
                        fontSize: 22,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
            ],
          ),
        ],
      ),
    );
  }

  // --- 8. Payment Method & PCI Security Card ---
  Widget _buildPaymentMethodCard() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF1E293B),
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: Colors.white.withOpacity(0.08)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Payment Method *',
            style: TextStyle(
              color: Colors.white,
              fontSize: 14,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 10),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
            decoration: BoxDecoration(
              color: const Color(0xFF0F172A),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: Colors.white12),
            ),
            child: DropdownButtonHideUnderline(
              child: DropdownButton<String>(
                value: _paymentMethod,
                isExpanded: true,
                dropdownColor: const Color(0xFF1E293B),
                style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold),
                items: const [
                  DropdownMenuItem(value: 'stripe', child: Text('💳 Stripe (Credit/Debit Card)')),
                  DropdownMenuItem(value: 'momo', child: Text('📱 Momo Pay')),
                  DropdownMenuItem(value: 'cash', child: Text('💵 Cash on Duty')),
                  DropdownMenuItem(value: 'applepay', child: Text('🍏 Apple Pay')),
                ],
                onChanged: (val) => setState(() => _paymentMethod = val ?? 'stripe'),
              ),
            ),
          ),

          if (_paymentMethod == 'stripe') ...[
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFF10B981).withOpacity(0.12),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: const Color(0xFF10B981).withOpacity(0.3)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.shield_rounded, color: Color(0xFF10B981), size: 20),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Stripe PCI-DSS Level 1 Secure Checkout',
                          style: TextStyle(
                            color: Color(0xFF34D399),
                            fontSize: 11,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                        Text(
                          'Card information is collected securely via encrypted tokenization.',
                          style: TextStyle(color: Colors.white.withOpacity(0.65), fontSize: 10),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  // --- 9. Sticky Bottom CTA ---
  Widget _buildStickyBottomCTA() {
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 10, 16, 20),
      decoration: BoxDecoration(
        color: const Color(0xFF1E293B),
        border: Border(top: BorderSide(color: Colors.white.withOpacity(0.08))),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.4),
            blurRadius: 12,
            offset: const Offset(0, -3),
          ),
        ],
      ),
      child: SafeArea(
        child: SizedBox(
          width: double.infinity,
          height: 52,
          child: ElevatedButton(
            onPressed: _isSubmitting ? null : _handleBookDriver,
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF10B981),
              disabledBackgroundColor: const Color(0xFF10B981).withOpacity(0.4),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              elevation: 4,
            ),
            child: _isSubmitting
                ? const SizedBox(
                    width: 22,
                    height: 22,
                    child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white),
                  )
                : Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Text(
                        '🚀 REQUEST DRIVER NOW',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 14,
                          fontWeight: FontWeight.w900,
                          letterSpacing: 0.5,
                        ),
                      ),
                      const SizedBox(width: 6),
                      Text(
                        '($_currencySymbol${_totalPrice.toStringAsFixed(2)})',
                        style: const TextStyle(
                          color: Color(0xFFFFB703),
                          fontSize: 14,
                          fontWeight: FontWeight.w900,
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
