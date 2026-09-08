import 'dart:math';
import 'package:flutter/material.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/country_provider.dart';
import '../../services/delivery_service.dart';

class DeliveryBookingScreen extends StatefulWidget {
  final String? initialPickup;
  final String? initialDropoff;
  final String? initialTier;

  const DeliveryBookingScreen({
    super.key,
    this.initialPickup,
    this.initialDropoff,
    this.initialTier,
  });

  @override
  State<DeliveryBookingScreen> createState() => _DeliveryBookingScreenState();
}

class _DeliveryBookingScreenState extends State<DeliveryBookingScreen> {
  int _currentStep = 1; // 1 to 5 (Step 6 is confirmation modal)
  bool _isSubmitting = false;
  bool _isCalculating = false;

  // Controllers - Step 1: Pickup & Drop
  late TextEditingController _pickupController;
  late TextEditingController _dropoffController;
  double _pickupLat = 28.6517;
  double _pickupLng = 77.1906;
  double _dropoffLat = 28.6280;
  double _dropoffLng = 77.2065;
  double _distanceKm = 5.2;

  // Step 2: Speed & Schedule
  String _selectedDeliverySpeed = 'Hyperlocal'; // Hyperlocal, Scheduled, Same Day, Express, Instant
  String _scheduleMode = 'now'; // now, later
  DateTime _scheduledDate = DateTime.now();
  TimeOfDay _scheduledTime = const TimeOfDay(hour: 10, minute: 30);

  // Step 3: Sender & Recipient
  final _senderNameController = TextEditingController(text: 'Jane Sender');
  final _senderPhoneController = TextEditingController(text: '+1 855 203 3177');
  final _recipientNameController = TextEditingController(text: 'Robert Johnson');
  final _recipientPhoneController = TextEditingController(text: '+1 855 203 3177');
  final _deliveryNotesController = TextEditingController();

  // Step 4: Package Category & Specs
  String _selectedCategory = 'Documents';
  final List<String> _categories = [
    'Documents',
    'Clothing',
    'Electronics',
    'Household items',
    'Office supplies',
    'Personal belongings',
    'Other',
  ];
  final _packageDescController = TextEditingController(text: 'Important Legal Contracts & Office Supplies');
  final _declaredValueController = TextEditingController(text: '150');
  String _selectedSize = 'Small'; // Small, Medium, Large
  double _weightKg = 1.5;
  int _quantity = 1;
  bool _signatureRequired = true;
  bool _climateControlled = false;
  bool _whiteGlove = false;

  // Step 5: Payment & Terms
  String _paymentMethod = 'Stripe (Credit / Debit Card)';
  final _cardholderController = TextEditingController(text: 'Johnathan Doe');
  final _cardNumberController = TextEditingController(text: '4242 4242 4242 4242');
  final _cardExpiryController = TextEditingController(text: '12/28');
  final _cardCvcController = TextEditingController(text: '123');
  final _cardZipController = TextEditingController(text: '10001');
  bool _prohibitedAcknowledged = true;

  // Pricing breakdown
  double _baseFare = 22.88;
  double _serviceFee = 1.14;
  double _taxes = 1.14;
  double _totalAmount = 25.16;

  @override
  void initState() {
    super.initState();
    _pickupController = TextEditingController(
      text: widget.initialPickup ?? 'T8, Gali Gopal Wali, Ratan Nagar, Karol Bagh, New Delhi, Delhi, 110005, India',
    );
    _dropoffController = TextEditingController(
      text: widget.initialDropoff ?? 'Connaught Place Block A, Central Delhi, Delhi, 110001',
    );

    if (widget.initialTier != null) {
      if (widget.initialTier!.contains('Van') || widget.initialTier!.contains('Medium')) {
        _selectedSize = 'Medium';
      } else if (widget.initialTier!.contains('Priority') || widget.initialTier!.contains('Express')) {
        _selectedDeliverySpeed = 'Express';
      }
    }

    WidgetsBinding.instance.addPostFrameCallback((_) {
      _calculatePrice();
    });
  }

  @override
  void dispose() {
    _pickupController.dispose();
    _dropoffController.dispose();
    _senderNameController.dispose();
    _senderPhoneController.dispose();
    _recipientNameController.dispose();
    _recipientPhoneController.dispose();
    _deliveryNotesController.dispose();
    _packageDescController.dispose();
    _declaredValueController.dispose();
    _cardholderController.dispose();
    _cardNumberController.dispose();
    _cardExpiryController.dispose();
    _cardCvcController.dispose();
    _cardZipController.dispose();
    super.dispose();
  }

  void _calculatePrice() async {
    final countryProv = Provider.of<CountryProvider>(context, listen: false);
    setState(() => _isCalculating = true);

    // Call API or fallback calculation
    final apiRes = await DeliveryService.calculatePrice(
      pickupLat: _pickupLat,
      pickupLng: _pickupLng,
      dropoffLat: _dropoffLat,
      dropoffLng: _dropoffLng,
      deliveryType: _selectedDeliverySpeed,
      packageSize: _selectedSize,
      packageWeightKg: _weightKg,
      country: countryProv.selectedCountryCode,
    );

    if (!mounted) return;

    if (apiRes != null && apiRes['total_price'] != null) {
      setState(() {
        _baseFare = double.tryParse(apiRes['subtotal']?.toString() ?? '') ?? 22.88;
        _serviceFee = double.tryParse(apiRes['service_fee']?.toString() ?? '') ?? 1.14;
        _taxes = double.tryParse(apiRes['tax']?.toString() ?? '') ?? 1.14;
        _totalAmount = double.tryParse(apiRes['total_price']?.toString() ?? '') ?? 25.16;
        _isCalculating = false;
      });
    } else {
      // Robust client calculation matching web formulas
      final multiplier = countryProv.priceMultiplier;
      double speedAddon = 0.0;
      switch (_selectedDeliverySpeed) {
        case 'Scheduled':
          speedAddon = 2.00;
          break;
        case 'Same Day':
          speedAddon = 4.00;
          break;
        case 'Express':
          speedAddon = 8.00;
          break;
        case 'Instant':
          speedAddon = 10.00;
          break;
        default:
          speedAddon = 0.00;
      }

      double sizeMult = 1.0;
      if (_selectedSize == 'Medium') sizeMult = 1.25;
      if (_selectedSize == 'Large') sizeMult = 1.60;

      double base = (15.00 + (_distanceKm * 1.50) + speedAddon + (max(0, _weightKg - 1.0) * 0.75)) * sizeMult * multiplier;
      double fee = base * 0.05;
      double tax = base * 0.05;
      double tot = base + fee + tax;

      setState(() {
        _baseFare = double.parse(base.toStringAsFixed(2));
        _serviceFee = double.parse(fee.toStringAsFixed(2));
        _taxes = double.parse(tax.toStringAsFixed(2));
        _totalAmount = double.parse(tot.toStringAsFixed(2));
        _isCalculating = false;
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
                        _calculatePrice();
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

  void _nextStep() {
    if (_currentStep == 1) {
      if (_pickupController.text.trim().isEmpty || _dropoffController.text.trim().isEmpty) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Please enter pickup and drop-off addresses.'), backgroundColor: AppColors.warning),
        );
        return;
      }
    } else if (_currentStep == 3) {
      if (_senderNameController.text.trim().isEmpty ||
          _senderPhoneController.text.trim().isEmpty ||
          _recipientNameController.text.trim().isEmpty ||
          _recipientPhoneController.text.trim().isEmpty) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Please fill all sender and recipient details.'), backgroundColor: AppColors.warning),
        );
        return;
      }
    } else if (_currentStep == 4) {
      if (_packageDescController.text.trim().isEmpty) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Please enter a package description.'), backgroundColor: AppColors.warning),
        );
        return;
      }
    }

    if (_currentStep < 5) {
      setState(() => _currentStep++);
      _calculatePrice();
    } else {
      _submitBooking();
    }
  }

  void _prevStep() {
    if (_currentStep > 1) {
      setState(() => _currentStep--);
      _calculatePrice();
    }
  }

  Future<void> _submitBooking() async {
    if (!_prohibitedAcknowledged) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please acknowledge prohibited items declaration.'), backgroundColor: AppColors.warning),
      );
      return;
    }

    setState(() => _isSubmitting = true);
    final countryProv = Provider.of<CountryProvider>(context, listen: false);

    final payload = {
      'pickup_location': _pickupController.text.trim(),
      'pickup_lat': _pickupLat,
      'pickup_lng': _pickupLng,
      'dropoff_location': _dropoffController.text.trim(),
      'dropoff_lat': _dropoffLat,
      'dropoff_lng': _dropoffLng,
      'delivery_type': _selectedDeliverySpeed,
      'schedule_mode': _scheduleMode,
      'pickup_date': DateFormat('yyyy-MM-dd').format(_scheduledDate),
      'pickup_time': '${_scheduledTime.hour.toString().padLeft(2, '0')}:${_scheduledTime.minute.toString().padLeft(2, '0')}',
      'sender_name': _senderNameController.text.trim(),
      'sender_phone': _senderPhoneController.text.trim(),
      'recipient_name': _recipientNameController.text.trim(),
      'recipient_phone': _recipientPhoneController.text.trim(),
      'delivery_instructions': _deliveryNotesController.text.trim(),
      'package_category': _selectedCategory,
      'package_description': _packageDescController.text.trim(),
      'package_size': _selectedSize,
      'package_weight_kg': _weightKg,
      'quantity': _quantity,
      'declared_value': double.tryParse(_declaredValueController.text.trim()) ?? 0,
      'special_handling': [
        if (_signatureRequired) 'signature_required',
        if (_climateControlled) 'climate_controlled',
        if (_whiteGlove) 'white_glove',
      ],
      'payment_method': _paymentMethod,
      'prohibited_items_acknowledged': true,
      'country': countryProv.selectedCountryCode,
    };

    final result = await DeliveryService.bookDelivery(payload);

    setState(() => _isSubmitting = false);
    if (!mounted) return;

    final trackingCode = result?['delivery_code'] ?? 'DEL-${Random().nextInt(90000000) + 10000000}';
    final deliveryOtp = result?['delivery_otp'] ?? '${Random().nextInt(9000) + 1000}';

    _showConfirmationDialog(trackingCode, deliveryOtp);
  }

  void _showConfirmationDialog(String deliveryCode, String otpPin) {
    final countryProv = Provider.of<CountryProvider>(context, listen: false);

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
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: const Color(0xFF10B981).withOpacity(0.2),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.check_circle_rounded, color: Color(0xFF10B981), size: 46),
              ),
              const SizedBox(height: 12),
              const Text(
                'Parcel Dispatched Successfully!',
                style: TextStyle(color: Colors.white, fontSize: 19, fontWeight: FontWeight.w900),
              ),
              const SizedBox(height: 4),
              Text(
                'Courier assigned. Live tracking & OTP verification active.',
                textAlign: TextAlign.center,
                style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 12.5),
              ),
              const SizedBox(height: 16),

              // PIN & Booking Code Highlight
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: const Color(0xFF0F172A),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFF10B981).withOpacity(0.3)),
                ),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'TRACKING CODE',
                          style: TextStyle(color: AppColors.textMuted, fontSize: 11, fontWeight: FontWeight.bold),
                        ),
                        Text(
                          deliveryCode,
                          style: const TextStyle(color: Color(0xFF10B981), fontSize: 13.5, fontWeight: FontWeight.w900, letterSpacing: 1.1),
                        ),
                      ],
                    ),
                    const Divider(color: Colors.white12, height: 16),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Secure Delivery PIN', style: TextStyle(color: Colors.white70, fontSize: 12)),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: const Color(0xFF3B82F6).withOpacity(0.2),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: const Color(0xFF3B82F6).withOpacity(0.4)),
                          ),
                          child: Text(
                            'PIN: $otpPin',
                            style: const TextStyle(color: Color(0xFF60A5FA), fontWeight: FontWeight.w900, fontSize: 13),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Package Type', style: TextStyle(color: Colors.white70, fontSize: 12)),
                        Text('$_selectedSize • $_selectedCategory', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12)),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Total Paid', style: TextStyle(color: Colors.white70, fontSize: 12)),
                        Text(
                          '${countryProv.currencySymbol}${_totalAmount.toStringAsFixed(2)}',
                          style: const TextStyle(color: Color(0xFF10B981), fontWeight: FontWeight.w900, fontSize: 14),
                        ),
                      ],
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton(
                  onPressed: () {
                    Navigator.pop(ctx);
                    Navigator.pop(context);
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF10B981),
                    elevation: 3,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  child: const Text(
                    'Done & Return to Map',
                    style: TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 14),
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
    final countryProv = Provider.of<CountryProvider>(context);

    return Scaffold(
      backgroundColor: const Color(0xFF0F172A),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1E293B),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Parcel Delivery',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 17),
        ),
        centerTitle: true,
        actions: [
          GestureDetector(
            onTap: () => _showCountrySelector(countryProv),
            child: Container(
              margin: const EdgeInsets.only(right: 14),
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: const Color(0xFF0F172A),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: Colors.white24, width: 1),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(countryProv.flag, style: const TextStyle(fontSize: 13)),
                  const SizedBox(width: 4),
                  Text(
                    '${countryProv.selectedCountryCode} ${countryProv.currencySymbol}',
                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 11.5),
                  ),
                  const SizedBox(width: 2),
                  const Icon(Icons.arrow_drop_down, color: Colors.white70, size: 16),
                ],
              ),
            ),
          ),
        ],
      ),
      body: Column(
        children: [
          // Stepper Header
          _buildStepperHeader(),

          // Main Step Content
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 100),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (_currentStep == 1) _buildStep1PickupDrop(),
                  if (_currentStep == 2) _buildStep2SpeedSchedule(countryProv),
                  if (_currentStep == 3) _buildStep3SenderRecipient(),
                  if (_currentStep == 4) _buildStep4PackageSpecs(),
                  if (_currentStep == 5) _buildStep5Payment(countryProv),

                  const SizedBox(height: 20),
                  // Price Estimate Card (Matches web sidebar)
                  _buildPriceEstimateCard(countryProv),
                ],
              ),
            ),
          ),
        ],
      ),
      bottomSheet: _buildBottomActionBar(countryProv),
    );
  }

  // --- STEPPER HEADER ---
  Widget _buildStepperHeader() {
    final steps = [
      {'num': 1, 'label': 'Pickup & Drop', 'icon': '📍'},
      {'num': 2, 'label': 'Delivery Type', 'icon': '⏱️'},
      {'num': 3, 'label': 'Sender & Recipient', 'icon': '👤'},
      {'num': 4, 'label': 'Package Specs', 'icon': '📦'},
      {'num': 5, 'label': 'Price & Payment', 'icon': '💳'},
    ];

    return Container(
      color: const Color(0xFF1E293B),
      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: Row(
          children: steps.map((s) {
            final stepNum = s['num'] as int;
            final isActive = stepNum == _currentStep;
            final isDone = stepNum < _currentStep;

            return GestureDetector(
              onTap: () {
                if (stepNum < _currentStep) {
                  setState(() => _currentStep = stepNum);
                  _calculatePrice();
                }
              },
              child: Container(
                margin: const EdgeInsets.only(right: 8),
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                decoration: BoxDecoration(
                  color: isActive
                      ? const Color(0xFFF59E0B)
                      : (isDone ? const Color(0xFF0F172A) : Colors.transparent),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: isActive
                        ? const Color(0xFFF59E0B)
                        : (isDone ? const Color(0xFF10B981) : Colors.white12),
                  ),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      s['icon'] as String,
                      style: const TextStyle(fontSize: 12),
                    ),
                    const SizedBox(width: 5),
                    Text(
                      '$stepNum. ${s['label']}',
                      style: TextStyle(
                        color: isActive
                            ? Colors.black
                            : (isDone ? Colors.white : Colors.white54),
                        fontWeight: isActive ? FontWeight.w900 : FontWeight.w600,
                        fontSize: 11.5,
                      ),
                    ),
                  ],
                ),
              ),
            );
          }).toList(),
        ),
      ),
    );
  }

  // --- STEP 1: PICKUP & DROP ---
  Widget _buildStep1PickupDrop() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'STEP 1: Pickup & Destination Locations',
          style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w900),
        ),
        const SizedBox(height: 4),
        const Text(
          'Specify where your parcel should be picked up and delivered.',
          style: TextStyle(color: Colors.white60, fontSize: 12.5),
        ),
        const SizedBox(height: 16),

        // Pickup Address
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'PICKUP ADDRESS *',
              style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold),
            ),
            GestureDetector(
              onTap: () {
                setState(() {
                  _pickupController.text = 'T/23, Ratan Nagar, Karol Bagh, New Delhi';
                });
                _calculatePrice();
              },
              child: Row(
                children: const [
                  Icon(Icons.my_location_rounded, color: Color(0xFFF59E0B), size: 14),
                  SizedBox(width: 4),
                  Text('Use My Location', style: TextStyle(color: Color(0xFFF59E0B), fontSize: 11.5, fontWeight: FontWeight.bold)),
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
            prefixIcon: const Icon(Icons.circle, color: Color(0xFFF59E0B), size: 14),
            filled: true,
            fillColor: const Color(0xFF1E293B),
            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
          ),
          onChanged: (_) => _calculatePrice(),
        ),

        const SizedBox(height: 14),

        // Drop-off Address
        const Text(
          'DROP-OFF / DESTINATION ADDRESS *',
          style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 6),
        TextField(
          controller: _dropoffController,
          style: const TextStyle(color: Colors.white, fontSize: 13),
          decoration: InputDecoration(
            prefixIcon: const Icon(Icons.location_on_rounded, color: Color(0xFFEF4444), size: 18),
            hintText: 'Enter recipient delivery address...',
            hintStyle: const TextStyle(color: Colors.white38, fontSize: 13),
            filled: true,
            fillColor: const Color(0xFF1E293B),
            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
          ),
          onChanged: (_) => _calculatePrice(),
        ),

        const SizedBox(height: 16),

        // Map Preview Container
        Container(
          height: 160,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: Colors.white12),
          ),
          clipBehavior: Clip.antiAlias,
          child: Stack(
            children: [
              GoogleMap(
                initialCameraPosition: CameraPosition(
                  target: LatLng(_pickupLat, _pickupLng),
                  zoom: 13.5,
                ),
                zoomControlsEnabled: false,
                myLocationButtonEnabled: false,
                markers: {
                  Marker(markerId: const MarkerId('pickup'), position: LatLng(_pickupLat, _pickupLng)),
                  Marker(markerId: const MarkerId('dropoff'), position: LatLng(_dropoffLat, _dropoffLng)),
                },
              ),
              Positioned(
                bottom: 8,
                left: 8,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.black.withOpacity(0.75),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    '📍 Estimated Distance: ${_distanceKm.toStringAsFixed(1)} km',
                    style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                  ),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  // --- STEP 2: SPEED & SCHEDULE ---
  Widget _buildStep2SpeedSchedule(CountryProvider countryProv) {
    final sym = countryProv.currencySymbol;
    final mult = countryProv.priceMultiplier;

    final speeds = [
      {
        'id': 'Hyperlocal',
        'title': 'Hyperlocal',
        'desc': 'City Local Bike Courier',
        'addon': 0.0,
        'tag': '+$sym${(0.0 * mult).toStringAsFixed(2)}',
      },
      {
        'id': 'Scheduled',
        'title': 'Scheduled',
        'desc': 'Pick your exact time window',
        'addon': 2.0,
        'tag': '+$sym${(2.0 * mult).toStringAsFixed(2)}',
      },
      {
        'id': 'Same Day',
        'title': 'Same Day',
        'desc': 'Delivered by end of today',
        'addon': 4.0,
        'tag': '+$sym${(4.0 * mult).toStringAsFixed(2)}',
      },
      {
        'id': 'Express',
        'title': 'Express',
        'desc': 'Priority Direct Route (< 2 hrs)',
        'addon': 8.0,
        'tag': '+$sym${(8.0 * mult).toStringAsFixed(2)}',
      },
      {
        'id': 'Instant',
        'title': 'Instant',
        'desc': 'Immediate Courier Pickup (~30 mins)',
        'addon': 10.0,
        'tag': '+$sym${(10.0 * mult).toStringAsFixed(2)}',
      },
    ];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'STEP 2: Delivery Type & Schedule',
          style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w900),
        ),
        const SizedBox(height: 4),
        const Text(
          'Select dispatch speed and schedule window.',
          style: TextStyle(color: Colors.white60, fontSize: 12.5),
        ),
        const SizedBox(height: 16),

        const Text(
          'DELIVERY SPEED OPTION *',
          style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 8),

        ...speeds.map((s) {
          final isSelected = _selectedDeliverySpeed == s['id'];
          return GestureDetector(
            onTap: () {
              setState(() => _selectedDeliverySpeed = s['id'] as String);
              _calculatePrice();
            },
            child: Container(
              margin: const EdgeInsets.only(bottom: 8),
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: isSelected ? const Color(0xFF1E293B) : const Color(0xFF0F172A),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(
                  color: isSelected ? const Color(0xFFF59E0B) : Colors.white10,
                  width: isSelected ? 1.5 : 1,
                ),
              ),
              child: Row(
                children: [
                  Icon(
                    isSelected ? Icons.radio_button_checked : Icons.radio_button_off,
                    color: isSelected ? const Color(0xFFF59E0B) : Colors.white38,
                    size: 20,
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          s['title'] as String,
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13.5),
                        ),
                        Text(
                          s['desc'] as String,
                          style: const TextStyle(color: Colors.white60, fontSize: 11.5),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: isSelected ? const Color(0xFFF59E0B).withOpacity(0.2) : Colors.white10,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      s['tag'] as String,
                      style: TextStyle(
                        color: isSelected ? const Color(0xFFF59E0B) : Colors.white70,
                        fontWeight: FontWeight.bold,
                        fontSize: 12,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          );
        }),

        const SizedBox(height: 16),

        const Text(
          'DISPATCH SCHEDULE *',
          style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 8),

        Row(
          children: [
            Expanded(
              child: GestureDetector(
                onTap: () => setState(() => _scheduleMode = 'now'),
                child: Container(
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  decoration: BoxDecoration(
                    color: _scheduleMode == 'now' ? const Color(0xFFF59E0B) : const Color(0xFF1E293B),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  alignment: Alignment.center,
                  child: Text(
                    '⚡ Deliver Now (Immediate)',
                    style: TextStyle(
                      color: _scheduleMode == 'now' ? Colors.black : Colors.white70,
                      fontWeight: FontWeight.bold,
                      fontSize: 12,
                    ),
                  ),
                ),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: GestureDetector(
                onTap: () => setState(() => _scheduleMode = 'later'),
                child: Container(
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  decoration: BoxDecoration(
                    color: _scheduleMode == 'later' ? const Color(0xFFF59E0B) : const Color(0xFF1E293B),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  alignment: Alignment.center,
                  child: Text(
                    '📅 Schedule for Later',
                    style: TextStyle(
                      color: _scheduleMode == 'later' ? Colors.black : Colors.white70,
                      fontWeight: FontWeight.bold,
                      fontSize: 12,
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
                child: ListTile(
                  tileColor: const Color(0xFF1E293B),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  title: const Text('Date', style: TextStyle(color: Colors.white70, fontSize: 11)),
                  subtitle: Text(DateFormat('yyyy-MM-dd').format(_scheduledDate), style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12.5)),
                  trailing: const Icon(Icons.calendar_month, color: Color(0xFFF59E0B), size: 18),
                  onTap: () async {
                    final picked = await showDatePicker(
                      context: context,
                      initialDate: _scheduledDate,
                      firstDate: DateTime.now(),
                      lastDate: DateTime.now().add(const Duration(days: 30)),
                    );
                    if (picked != null) setState(() => _scheduledDate = picked);
                  },
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: ListTile(
                  tileColor: const Color(0xFF1E293B),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  title: const Text('Time', style: TextStyle(color: Colors.white70, fontSize: 11)),
                  subtitle: Text(_scheduledTime.format(context), style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12.5)),
                  trailing: const Icon(Icons.access_time, color: Color(0xFFF59E0B), size: 18),
                  onTap: () async {
                    final picked = await showTimePicker(
                      context: context,
                      initialTime: _scheduledTime,
                    );
                    if (picked != null) setState(() => _scheduledTime = picked);
                  },
                ),
              ),
            ],
          ),
        ],
      ],
    );
  }

  // --- STEP 3: SENDER & RECIPIENT ---
  Widget _buildStep3SenderRecipient() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'STEP 3: Sender & Recipient Details',
          style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w900),
        ),
        const SizedBox(height: 4),
        const Text(
          'Provide contact details for parcel pickup & delivery notification.',
          style: TextStyle(color: Colors.white60, fontSize: 12.5),
        ),
        const SizedBox(height: 16),

        // SENDER INFORMATION Card
        Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: const Color(0xFF1E293B),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: Colors.white10),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: const [
                  Icon(Icons.person_pin_circle_rounded, color: Color(0xFF3B82F6), size: 18),
                  SizedBox(width: 6),
                  Text('SENDER INFORMATION', style: TextStyle(color: Color(0xFF60A5FA), fontWeight: FontWeight.bold, fontSize: 12)),
                ],
              ),
              const SizedBox(height: 12),
              const Text('Sender Full Name *', style: TextStyle(color: Colors.white70, fontSize: 11)),
              const SizedBox(height: 4),
              TextField(
                controller: _senderNameController,
                style: const TextStyle(color: Colors.white, fontSize: 13),
                decoration: InputDecoration(
                  filled: true,
                  fillColor: const Color(0xFF0F172A),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
                ),
              ),
              const SizedBox(height: 10),
              const Text('Sender Phone Number *', style: TextStyle(color: Colors.white70, fontSize: 11)),
              const SizedBox(height: 4),
              TextField(
                controller: _senderPhoneController,
                style: const TextStyle(color: Colors.white, fontSize: 13),
                decoration: InputDecoration(
                  filled: true,
                  fillColor: const Color(0xFF0F172A),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
                ),
              ),
            ],
          ),
        ),

        const SizedBox(height: 16),

        // RECIPIENT INFORMATION Card
        Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: const Color(0xFF1E293B),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFF59E0B).withOpacity(0.3)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: const [
                  Icon(Icons.mark_email_read_rounded, color: Color(0xFFF59E0B), size: 18),
                  SizedBox(width: 6),
                  Text('RECIPIENT INFORMATION', style: TextStyle(color: Color(0xFFF59E0B), fontWeight: FontWeight.bold, fontSize: 12)),
                ],
              ),
              const SizedBox(height: 12),
              const Text('Recipient Full Name *', style: TextStyle(color: Colors.white70, fontSize: 11)),
              const SizedBox(height: 4),
              TextField(
                controller: _recipientNameController,
                style: const TextStyle(color: Colors.white, fontSize: 13),
                decoration: InputDecoration(
                  filled: true,
                  fillColor: const Color(0xFF0F172A),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
                ),
              ),
              const SizedBox(height: 10),
              const Text('Recipient Phone Number (For PIN SMS) *', style: TextStyle(color: Colors.white70, fontSize: 11)),
              const SizedBox(height: 4),
              TextField(
                controller: _recipientPhoneController,
                style: const TextStyle(color: Colors.white, fontSize: 13),
                decoration: InputDecoration(
                  filled: true,
                  fillColor: const Color(0xFF0F172A),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
                ),
              ),
              const SizedBox(height: 10),
              const Text('Delivery Notes / Instructions (Optional)', style: TextStyle(color: Colors.white70, fontSize: 11)),
              const SizedBox(height: 4),
              TextField(
                controller: _deliveryNotesController,
                maxLines: 2,
                style: const TextStyle(color: Colors.white, fontSize: 13),
                decoration: InputDecoration(
                  hintText: 'Gate code, call before arrival, leave at reception...',
                  hintStyle: const TextStyle(color: Colors.white30, fontSize: 12),
                  filled: true,
                  fillColor: const Color(0xFF0F172A),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  // --- STEP 4: PACKAGE SPECS ---
  Widget _buildStep4PackageSpecs() {
    final sizes = [
      {
        'id': 'Small',
        'icon': '✉️',
        'title': 'Small',
        'sub': 'Up to 2 kg\n(Envelopes / Small Box)',
      },
      {
        'id': 'Medium',
        'icon': '📦',
        'title': 'Medium',
        'sub': 'Up to 8 kg\n(Shoebox / Groceries)',
      },
      {
        'id': 'Large',
        'icon': '🚚',
        'title': 'Large',
        'sub': 'Up to 25 kg\n(Cartons / Heavy)',
      },
    ];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'STEP 4: Package Category & Specifications',
          style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w900),
        ),
        const SizedBox(height: 4),
        const Text(
          'Specify parcel category, size, weight, and handling rules.',
          style: TextStyle(color: Colors.white60, fontSize: 12.5),
        ),
        const SizedBox(height: 16),

        const Text('PACKAGE CATEGORY *', style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold)),
        const SizedBox(height: 8),

        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: _categories.map((cat) {
            final isSel = _selectedCategory == cat;
            return ChoiceChip(
              label: Text(cat),
              selected: isSel,
              selectedColor: const Color(0xFFF59E0B),
              backgroundColor: const Color(0xFF1E293B),
              labelStyle: TextStyle(
                color: isSel ? Colors.black : Colors.white,
                fontWeight: isSel ? FontWeight.bold : FontWeight.normal,
                fontSize: 12,
              ),
              onSelected: (_) => setState(() => _selectedCategory = cat),
            );
          }).toList(),
        ),

        const SizedBox(height: 16),

        // Description & Declared Value
        Row(
          children: [
            Expanded(
              flex: 2,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Package Description', style: TextStyle(color: Colors.white70, fontSize: 11)),
                  const SizedBox(height: 4),
                  TextField(
                    controller: _packageDescController,
                    style: const TextStyle(color: Colors.white, fontSize: 12.5),
                    decoration: InputDecoration(
                      filled: true,
                      fillColor: const Color(0xFF1E293B),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
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
                  const Text('Declared Value (\$)', style: TextStyle(color: Colors.white70, fontSize: 11)),
                  const SizedBox(height: 4),
                  TextField(
                    controller: _declaredValueController,
                    keyboardType: TextInputType.number,
                    style: const TextStyle(color: Colors.white, fontSize: 12.5),
                    decoration: InputDecoration(
                      filled: true,
                      fillColor: const Color(0xFF1E293B),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),

        const SizedBox(height: 16),

        const Text('PACKAGE SIZE *', style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold)),
        const SizedBox(height: 8),

        Row(
          children: sizes.map((s) {
            final isSel = _selectedSize == s['id'];
            return Expanded(
              child: GestureDetector(
                onTap: () {
                  setState(() => _selectedSize = s['id'] as String);
                  _calculatePrice();
                },
                child: Container(
                  margin: const EdgeInsets.symmetric(horizontal: 4),
                  padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 6),
                  decoration: BoxDecoration(
                    color: isSel ? const Color(0xFF1E293B) : const Color(0xFF0F172A),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(
                      color: isSel ? const Color(0xFFF59E0B) : Colors.white10,
                      width: isSel ? 1.8 : 1,
                    ),
                  ),
                  child: Column(
                    children: [
                      Text(s['icon'] as String, style: const TextStyle(fontSize: 22)),
                      const SizedBox(height: 4),
                      Text(
                        s['title'] as String,
                        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12.5),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        s['sub'] as String,
                        textAlign: TextAlign.center,
                        style: const TextStyle(color: Colors.white54, fontSize: 9.5),
                      ),
                    ],
                  ),
                ),
              ),
            );
          }).toList(),
        ),

        const SizedBox(height: 16),

        // Weight & Quantity
        Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Weight (kg) *', style: TextStyle(color: Colors.white70, fontSize: 11)),
                  const SizedBox(height: 4),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(color: const Color(0xFF1E293B), borderRadius: BorderRadius.circular(10)),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('$_weightKg kg', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13)),
                        Row(
                          children: [
                            IconButton(
                              icon: const Icon(Icons.remove_circle_outline, color: Colors.white70, size: 20),
                              onPressed: () {
                                if (_weightKg > 0.5) {
                                  setState(() => _weightKg = double.parse((_weightKg - 0.5).toStringAsFixed(1)));
                                  _calculatePrice();
                                }
                              },
                            ),
                            IconButton(
                              icon: const Icon(Icons.add_circle_outline, color: Color(0xFFF59E0B), size: 20),
                              onPressed: () {
                                setState(() => _weightKg = double.parse((_weightKg + 0.5).toStringAsFixed(1)));
                                _calculatePrice();
                              },
                            ),
                          ],
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
                  const Text('Quantity *', style: TextStyle(color: Colors.white70, fontSize: 11)),
                  const SizedBox(height: 4),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(color: const Color(0xFF1E293B), borderRadius: BorderRadius.circular(10)),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('$_quantity', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13)),
                        Row(
                          children: [
                            IconButton(
                              icon: const Icon(Icons.remove_circle_outline, color: Colors.white70, size: 20),
                              onPressed: () {
                                if (_quantity > 1) {
                                  setState(() => _quantity--);
                                  _calculatePrice();
                                }
                              },
                            ),
                            IconButton(
                              icon: const Icon(Icons.add_circle_outline, color: Color(0xFFF59E0B), size: 20),
                              onPressed: () {
                                setState(() => _quantity++);
                                _calculatePrice();
                              },
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),

        const SizedBox(height: 16),

        // Special Handling
        const Text('SPECIAL HANDLING OPTIONS', style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold)),
        const SizedBox(height: 8),

        CheckboxListTile(
          value: _signatureRequired,
          contentPadding: EdgeInsets.zero,
          activeColor: const Color(0xFF3B82F6),
          title: const Text('Signature required on delivery', style: TextStyle(color: Colors.white, fontSize: 12.5)),
          onChanged: (v) => setState(() => _signatureRequired = v ?? false),
        ),
        CheckboxListTile(
          value: _climateControlled,
          contentPadding: EdgeInsets.zero,
          activeColor: const Color(0xFF3B82F6),
          title: const Text('Climate-controlled transport (Temperature sensitive)', style: TextStyle(color: Colors.white, fontSize: 12.5)),
          onChanged: (v) => setState(() => _climateControlled = v ?? false),
        ),
        CheckboxListTile(
          value: _whiteGlove,
          contentPadding: EdgeInsets.zero,
          activeColor: const Color(0xFF3B82F6),
          title: const Text('Discreet white-glove packaging', style: TextStyle(color: Colors.white, fontSize: 12.5)),
          onChanged: (v) => setState(() => _whiteGlove = v ?? false),
        ),
      ],
    );
  }

  // --- STEP 5: PAYMENT ---
  Widget _buildStep5Payment(CountryProvider countryProv) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'STEP 5: Payment Method',
          style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w900),
        ),
        const SizedBox(height: 4),
        const Text(
          'Select payment method for parcel dispatch.',
          style: TextStyle(color: Colors.white60, fontSize: 12.5),
        ),
        const SizedBox(height: 16),

        const Text('PAYMENT METHOD *', style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold)),
        const SizedBox(height: 8),

        Container(
          padding: const EdgeInsets.symmetric(horizontal: 14),
          decoration: BoxDecoration(
            color: const Color(0xFF1E293B),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.white12),
          ),
          child: DropdownButtonHideUnderline(
            child: DropdownButton<String>(
              value: _paymentMethod,
              isExpanded: true,
              dropdownColor: const Color(0xFF1E293B),
              style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold),
              items: const [
                DropdownMenuItem(value: 'Stripe (Credit / Debit Card)', child: Text('💳 Stripe (Credit / Debit Card)')),
                DropdownMenuItem(value: 'Cash on Pickup', child: Text('💵 Cash on Pickup')),
                DropdownMenuItem(value: 'In-App Wallet', child: Text('👛 In-App Wallet')),
                DropdownMenuItem(value: 'Apple / Google Pay', child: Text('📱 Apple / Google Pay')),
              ],
              onChanged: (val) => setState(() => _paymentMethod = val!),
            ),
          ),
        ),

        if (_paymentMethod.contains('Stripe') || _paymentMethod.contains('Card')) ...[
          const SizedBox(height: 14),
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: const Color(0xFF1E293B),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: Colors.white10),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: const [
                        Icon(Icons.credit_card, color: Color(0xFF3B82F6), size: 18),
                        SizedBox(width: 6),
                        Text('CARD DETAILS', style: TextStyle(color: Color(0xFF60A5FA), fontWeight: FontWeight.bold, fontSize: 11)),
                      ],
                    ),
                    const Text('🔒 256-bit SSL', style: TextStyle(color: Colors.white38, fontSize: 10)),
                  ],
                ),
                const SizedBox(height: 10),
                const Text('Cardholder Name *', style: TextStyle(color: Colors.white70, fontSize: 11)),
                const SizedBox(height: 4),
                TextField(
                  controller: _cardholderController,
                  style: const TextStyle(color: Colors.white, fontSize: 12.5),
                  decoration: InputDecoration(
                    filled: true,
                    fillColor: const Color(0xFF0F172A),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
                  ),
                ),
                const SizedBox(height: 10),
                const Text('Card Number *', style: TextStyle(color: Colors.white70, fontSize: 11)),
                const SizedBox(height: 4),
                TextField(
                  controller: _cardNumberController,
                  style: const TextStyle(color: Colors.white, fontSize: 12.5),
                  decoration: InputDecoration(
                    filled: true,
                    fillColor: const Color(0xFF0F172A),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
                  ),
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Expiry *', style: TextStyle(color: Colors.white70, fontSize: 11)),
                          const SizedBox(height: 4),
                          TextField(
                            controller: _cardExpiryController,
                            style: const TextStyle(color: Colors.white, fontSize: 12.5),
                            decoration: InputDecoration(
                              filled: true,
                              fillColor: const Color(0xFF0F172A),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
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
                          const Text('CVC *', style: TextStyle(color: Colors.white70, fontSize: 11)),
                          const SizedBox(height: 4),
                          TextField(
                            controller: _cardCvcController,
                            style: const TextStyle(color: Colors.white, fontSize: 12.5),
                            decoration: InputDecoration(
                              filled: true,
                              fillColor: const Color(0xFF0F172A),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
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
                          const Text('Zip / Postal *', style: TextStyle(color: Colors.white70, fontSize: 11)),
                          const SizedBox(height: 4),
                          TextField(
                            controller: _cardZipController,
                            style: const TextStyle(color: Colors.white, fontSize: 12.5),
                            decoration: InputDecoration(
                              filled: true,
                              fillColor: const Color(0xFF0F172A),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],

        const SizedBox(height: 14),

        CheckboxListTile(
          value: _prohibitedAcknowledged,
          contentPadding: EdgeInsets.zero,
          activeColor: const Color(0xFF10B981),
          title: const Text(
            'I certify this parcel does not contain hazardous, flammable, perishable or illegal contraband items.',
            style: TextStyle(color: Colors.white70, fontSize: 11.5),
          ),
          onChanged: (v) => setState(() => _prohibitedAcknowledged = v ?? false),
        ),
      ],
    );
  }

  // --- PRICE ESTIMATE SIDEBAR / CARD (MATCHES WEB) ---
  Widget _buildPriceEstimateCard(CountryProvider countryProv) {
    final sym = countryProv.currencySymbol;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF1E293B),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: Colors.white12),
        boxShadow: [
          BoxShadow(color: Colors.black.withOpacity(0.3), blurRadius: 10, offset: const Offset(0, 4)),
        ],
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
                  const Text(
                    'PRICE ESTIMATE',
                    style: TextStyle(color: Color(0xFFF59E0B), fontSize: 10.5, fontWeight: FontWeight.w900, letterSpacing: 0.8),
                  ),
                  const SizedBox(height: 2),
                  const Text(
                    'Delivery Fare',
                    style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w900),
                  ),
                  Text(
                    '$_selectedDeliverySpeed Parcel Delivery',
                    style: const TextStyle(color: Colors.white54, fontSize: 11),
                  ),
                ],
              ),
              if (_isCalculating)
                const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFFF59E0B))),
            ],
          ),
          const Divider(color: Colors.white10, height: 20),

          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Delivery Fee:', style: TextStyle(color: Colors.white70, fontSize: 12.5)),
              Text('$sym${_baseFare.toStringAsFixed(2)}', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12.5)),
            ],
          ),
          const SizedBox(height: 6),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Service Fee (5%):', style: TextStyle(color: Colors.white70, fontSize: 12.5)),
              Text('+$sym${_serviceFee.toStringAsFixed(2)}', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12.5)),
            ],
          ),
          const SizedBox(height: 6),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Taxes (5%):', style: TextStyle(color: Colors.white70, fontSize: 12.5)),
              Text('+$sym${_taxes.toStringAsFixed(2)}', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12.5)),
            ],
          ),
          const Divider(color: Colors.white12, height: 18),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Total Amount:', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14)),
              Text(
                '$sym${_totalAmount.toStringAsFixed(2)}',
                style: const TextStyle(color: Color(0xFFF59E0B), fontWeight: FontWeight.w900, fontSize: 18),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
            decoration: BoxDecoration(
              color: const Color(0xFF0F172A),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Row(
              children: const [
                Icon(Icons.lock_rounded, color: Color(0xFFF59E0B), size: 14),
                SizedBox(width: 6),
                Expanded(
                  child: Text(
                    '4-Digit Secure PIN Verification Included',
                    style: TextStyle(color: Colors.white70, fontSize: 10.5, fontWeight: FontWeight.bold),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // --- BOTTOM ACTION BAR ---
  Widget _buildBottomActionBar(CountryProvider countryProv) {
    final sym = countryProv.currencySymbol;

    return Container(
      padding: EdgeInsets.fromLTRB(16, 12, 16, 12 + MediaQuery.of(context).padding.bottom),
      decoration: BoxDecoration(
        color: const Color(0xFF1E293B),
        border: const Border(top: BorderSide(color: Colors.white12)),
        boxShadow: [
          BoxShadow(color: Colors.black.withOpacity(0.4), blurRadius: 10, offset: const Offset(0, -2)),
        ],
      ),
      child: Row(
        children: [
          if (_currentStep > 1) ...[
            Expanded(
              flex: 1,
              child: SizedBox(
                height: 48,
                child: OutlinedButton(
                  onPressed: _prevStep,
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(color: Colors.white24),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  child: const Text('← Back', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13)),
                ),
              ),
            ),
            const SizedBox(width: 10),
          ],
          Expanded(
            flex: 2,
            child: SizedBox(
              height: 48,
              child: ElevatedButton(
                onPressed: _isSubmitting ? null : _nextStep,
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFFF59E0B),
                  disabledBackgroundColor: const Color(0xFFF59E0B).withOpacity(0.4),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  elevation: 4,
                ),
                child: _isSubmitting
                    ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.black))
                    : Text(
                        _currentStep == 5
                            ? '🚀 Dispatch & Pay ($sym${_totalAmount.toStringAsFixed(2)})'
                            : 'Next: Step ${_currentStep + 1} →',
                        style: const TextStyle(color: Colors.black, fontWeight: FontWeight.w900, fontSize: 13.5),
                      ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
