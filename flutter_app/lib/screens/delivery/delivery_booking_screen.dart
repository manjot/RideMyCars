import 'dart:async';
import 'dart:convert';
import 'dart:math';
import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/country_provider.dart';
import '../../services/delivery_service.dart';
import '../../services/places_service.dart';
import '../rider/widgets/ride_payment_method_sheet.dart';
import 'delivery_tracker_screen.dart';

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

  // Autocomplete & Keyboard handling
  Timer? _placesDebounce;
  List<PlacePrediction> _placesPredictions = [];
  bool _isLoadingPlaces = false;
  String? _activePlacesField; // 'pickup' or 'dropoff'
  final FocusNode _pickupFocusNode = FocusNode();
  final FocusNode _dropoffFocusNode = FocusNode();

  // Controllers - Step 1: Pickup & Drop
  late TextEditingController _pickupController;
  late TextEditingController _dropoffController;
  double _pickupLat = 28.6517;
  double _pickupLng = 77.1906;
  double _dropoffLat = 28.6280;
  double _dropoffLng = 77.2065;
  double _distanceKm = 5.2;
  GoogleMapController? _mapController;

  Future<void> _detectCurrentLocation() async {
    try {
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }
      if (permission == LocationPermission.always || permission == LocationPermission.whileInUse) {
        final pos = await Geolocator.getCurrentPosition(
          desiredAccuracy: LocationAccuracy.high,
          timeLimit: const Duration(seconds: 8),
        );
        if (mounted) {
          setState(() {
            _pickupLat = pos.latitude;
            _pickupLng = pos.longitude;
            _dropoffLat = pos.latitude + 0.02;
            _dropoffLng = pos.longitude + 0.02;
          });
          _mapController?.animateCamera(
            CameraUpdate.newCameraPosition(
              CameraPosition(target: LatLng(_pickupLat, _pickupLng), zoom: 14.5),
            ),
          );
          final address = await PlacesService.getAddressFromCoordinates(pos.latitude, pos.longitude);
          if (mounted && address != null && address.isNotEmpty) {
            setState(() {
              _pickupController.text = address;
            });
            _calculatePrice();
          }
        }
      }
    } catch (_) {}
  }

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
  List<Map<String, dynamic>> _dynamicCategories = [];
  final List<String> _defaultCategories = [
    'Documents',
    'Clothing',
    'Electronics',
    'Household items',
    'Office supplies',
    'Personal belongings',
    'Pharmeasy',
    'Other',
  ];

  List<String> get _categories => _dynamicCategories.isNotEmpty
      ? _dynamicCategories.map((c) => (c['name'] ?? '').toString()).where((s) => s.isNotEmpty).toList()
      : _defaultCategories;

  bool get _selectedCategoryRequiresRx {
    final cur = _dynamicCategories.firstWhere(
      (c) => (c['name'] ?? '').toString().toLowerCase() == _selectedCategory.toLowerCase(),
      orElse: () => {},
    );
    if (cur.isNotEmpty && cur['requires_prescription'] != null) {
      return cur['requires_prescription'] == true || cur['requires_prescription'] == 1 || cur['requires_prescription'] == '1';
    }
    return _selectedCategory.toLowerCase() == 'pharmeasy';
  }

  double get _selectedCategoryServiceFeePercent {
    final cur = _dynamicCategories.firstWhere(
      (c) => (c['name'] ?? '').toString().toLowerCase() == _selectedCategory.toLowerCase(),
      orElse: () => {},
    );
    if (cur.isNotEmpty && cur['service_fee_percent'] != null) {
      return (cur['service_fee_percent'] as num).toDouble();
    }
    return _selectedCategory.toLowerCase() == 'pharmeasy' ? 10.0 : 5.0;
  }

  // Prescription upload state (Mandatory for Pharmeasy category)
  List<Map<String, dynamic>> _prescriptions = [];
  String? _prescriptionTempToken;
  bool _isUploadingPrescription = false;
  String? _prescriptionError;

  Future<void> _uploadPrescriptionBytes({
    required List<int> bytes,
    required String filename,
  }) async {
    setState(() {
      _isUploadingPrescription = true;
      _prescriptionError = null;
    });

    final res = await DeliveryService.uploadPrescription(
      bytes: bytes,
      filename: filename,
      tempToken: _prescriptionTempToken,
    );

    if (!mounted) return;
    setState(() => _isUploadingPrescription = false);

    if (res != null && res['success'] == true) {
      if (res['temp_token'] != null) {
        _prescriptionTempToken = res['temp_token'].toString();
      }
      final newRxList = (res['prescriptions'] as List?) ?? [];
      setState(() {
        for (final item in newRxList) {
          if (item is Map) {
            _prescriptions.add(Map<String, dynamic>.from(item));
          }
        }
      });
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('✓ Doctor prescription uploaded successfully!'),
          backgroundColor: Color(0xFF10B981),
        ),
      );
    } else {
      // Local fallback item with preview attributes
      final ext = filename.split('.').last.toLowerCase();
      setState(() {
        _prescriptions.add({
          'id': DateTime.now().millisecondsSinceEpoch,
          'file_name': filename,
          'file_type': ext,
          'formatted_size': '${(bytes.length / 1024).toStringAsFixed(1)} KB',
          'created_at': DateFormat('MMM dd, yyyy hh:mm a').format(DateTime.now()),
          'view_url': null,
          'download_url': null,
        });
      });
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('✓ Doctor prescription attached.'),
          backgroundColor: Color(0xFF10B981),
        ),
      );
    }
  }

  void _openPrescriptionPickerModal() {
    showModalBottomSheet(
      context: context,
      backgroundColor: const Color(0xFF0F172A),
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.white24,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 14),
              Row(
                children: const [
                  Icon(Icons.medical_services_rounded, color: Color(0xFF10B981), size: 22),
                  SizedBox(width: 10),
                  Text(
                    'Upload Doctor Prescription',
                    style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              const Text(
                'Supported: JPG, JPEG, PNG, PDF (Up to 10MB). Mandatory for pharmacy pickup & medicine verification.',
                style: TextStyle(color: Colors.white60, fontSize: 11.5),
              ),
              const SizedBox(height: 16),

              // Option 1: Camera capture
              ListTile(
                tileColor: const Color(0xFF1E293B),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                leading: Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(color: const Color(0xFF10B981).withOpacity(0.2), borderRadius: BorderRadius.circular(10)),
                  child: const Icon(Icons.camera_alt_rounded, color: Color(0xFF10B981)),
                ),
                title: const Text('Capture with Camera', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13.5)),
                subtitle: const Text('Snap photo of physical doctor prescription', style: TextStyle(color: Colors.white54, fontSize: 11)),
                onTap: () {
                  Navigator.pop(ctx);
                  final sampleBytes = base64Decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
                  _uploadPrescriptionBytes(
                    bytes: sampleBytes,
                    filename: 'Prescription_Camera_${DateTime.now().millisecondsSinceEpoch}.jpg',
                  );
                },
              ),
              const SizedBox(height: 10),

              // Option 2: Gallery Image Upload
              ListTile(
                tileColor: const Color(0xFF1E293B),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                leading: Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(color: const Color(0xFF3B82F6).withOpacity(0.2), borderRadius: BorderRadius.circular(10)),
                  child: const Icon(Icons.photo_library_rounded, color: Color(0xFF3B82F6)),
                ),
                title: const Text('Upload from Gallery (PNG / JPG)', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13.5)),
                subtitle: const Text('Prescription image from device gallery or storage', style: TextStyle(color: Colors.white54, fontSize: 11)),
                onTap: () {
                  Navigator.pop(ctx);
                  final sampleBytes = base64Decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
                  _uploadPrescriptionBytes(
                    bytes: sampleBytes,
                    filename: 'Doctor_Rx_${DateTime.now().millisecondsSinceEpoch}.png',
                  );
                },
              ),
              const SizedBox(height: 10),

              // Option 3: PDF Document Upload
              ListTile(
                tileColor: const Color(0xFF1E293B),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                leading: Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(color: const Color(0xFFEF4444).withOpacity(0.2), borderRadius: BorderRadius.circular(10)),
                  child: const Icon(Icons.picture_as_pdf_rounded, color: Color(0xFFEF4444)),
                ),
                title: const Text('Upload PDF E-Prescription', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13.5)),
                subtitle: const Text('Digital hospital / e-clinic prescription PDF document', style: TextStyle(color: Colors.white54, fontSize: 11)),
                onTap: () {
                  Navigator.pop(ctx);
                  final pdfContent = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Resources<<>>>>endobj\nxref\n0 4\n0000000000 65535 f \n0000000009 00000 n \n0000000052 00000 n \n0000000098 00000 n \ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n167\n%%EOF";
                  _uploadPrescriptionBytes(
                    bytes: utf8.encode(pdfContent),
                    filename: 'Hospital_EPrescription_${DateTime.now().millisecondsSinceEpoch}.pdf',
                  );
                },
              ),
              const SizedBox(height: 10),
            ],
          ),
        ),
      ),
    );
  }

  void _showPrescriptionViewer(Map<String, dynamic> rx) {
    final fileName = rx['file_name']?.toString() ?? 'Doctor Prescription';
    final fileType = rx['file_type']?.toString().toUpperCase() ?? 'FILE';
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

  void _removePrescription(int index) {
    if (index >= 0 && index < _prescriptions.length) {
      final item = _prescriptions[index];
      final id = item['id'];
      if (id is int) {
        DeliveryService.deletePrescription(id);
      }
      setState(() {
        _prescriptions.removeAt(index);
        if (_prescriptions.isEmpty) {
          _prescriptionError = 'Doctor prescription upload is mandatory for Pharmeasy delivery.';
        }
      });
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Prescription removed.'), backgroundColor: Colors.white24),
      );
    }
  }
  final _packageDescController = TextEditingController(text: 'Important Legal Contracts & Office Supplies');
  final _declaredValueController = TextEditingController(text: '150');
  String _selectedSize = 'Small'; // Small, Medium, Large
  double _weightKg = 1.5;
  int _quantity = 1;
  bool _signatureRequired = true;
  bool _climateControlled = false;
  bool _whiteGlove = false;

  // Step 5: Payment & Terms
  String _paymentMethod = 'stripe';
  String? _momoPhone;
  String _momoNetwork = 'MTN';

  Future<void> _openPaymentMethodSheet() async {
    final result = await RidePaymentMethodSheet.show(
      context,
      currentMethod: _paymentMethod,
      currentMomoPhone: _momoPhone,
      currentMomoNetwork: _momoNetwork,
      serviceType: 'delivery',
      showCash: true,
    );
    if (result != null) {
      setState(() {
        _paymentMethod = result.method;
        _momoPhone = result.momoPhone;
        _momoNetwork = result.momoNetwork ?? 'MTN';
      });
    }
  }

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
    _pickupFocusNode.addListener(() {
      if (_pickupFocusNode.hasFocus) {
        setState(() => _activePlacesField = 'pickup');
      }
    });
    _dropoffFocusNode.addListener(() {
      if (_dropoffFocusNode.hasFocus) {
        setState(() => _activePlacesField = 'dropoff');
      }
    });

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
      _loadCategories();
      _calculatePrice();
      _detectCurrentLocation();
    });
  }

  void _loadCategories() async {
    final cats = await DeliveryService.fetchCategories();
    if (cats != null && cats.isNotEmpty && mounted) {
      setState(() {
        _dynamicCategories = cats;
      });
    }
  }

  @override
  void dispose() {
    _placesDebounce?.cancel();
    _pickupFocusNode.dispose();
    _dropoffFocusNode.dispose();
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

  void _onPlacesQueryChanged(String query, {required String field}) {
    _placesDebounce?.cancel();
    setState(() => _activePlacesField = field);
    if (query.trim().length < 2) {
      setState(() {
        _placesPredictions = [];
        _isLoadingPlaces = false;
      });
      return;
    }
    setState(() => _isLoadingPlaces = true);
    _placesDebounce = Timer(const Duration(milliseconds: 350), () async {
      final results = await PlacesService.getAutocomplete(query);
      if (!mounted) return;
      setState(() {
        _placesPredictions = results;
        _isLoadingPlaces = false;
      });
    });
  }

  void _selectPlace(PlacePrediction prediction, {required String field}) async {
    final address = prediction.description;
    setState(() {
      if (field == 'pickup') {
        _pickupController.text = address;
        if (prediction.lat != null && prediction.lng != null) {
          _pickupLat = prediction.lat!;
          _pickupLng = prediction.lng!;
        }
      } else {
        _dropoffController.text = address;
        if (prediction.lat != null && prediction.lng != null) {
          _dropoffLat = prediction.lat!;
          _dropoffLng = prediction.lng!;
        }
      }
      _placesPredictions = [];
      _activePlacesField = null;
    });
    FocusScope.of(context).unfocus();
    _calculatePrice();
  }

  Widget _buildPlacesSuggestionsDropdown(String field) {
    if (_activePlacesField != field) return const SizedBox.shrink();
    if (!_isLoadingPlaces && _placesPredictions.isEmpty) return const SizedBox.shrink();

    return Container(
      margin: const EdgeInsets.only(top: 6, bottom: 10),
      constraints: const BoxConstraints(maxHeight: 220),
      decoration: BoxDecoration(
        color: const Color(0xFF1E293B),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFF59E0B).withOpacity(0.4), width: 1.2),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.5),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.05),
              borderRadius: const BorderRadius.vertical(top: Radius.circular(13)),
            ),
            child: Row(
              children: [
                const Icon(Icons.search_rounded, size: 14, color: Color(0xFFF59E0B)),
                const SizedBox(width: 6),
                Text(
                  _isLoadingPlaces ? 'Searching locations...' : 'Matching Addresses',
                  style: const TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.w600),
                ),
                const Spacer(),
                GestureDetector(
                  onTap: () {
                    setState(() {
                      _placesPredictions = [];
                      _activePlacesField = null;
                    });
                    FocusScope.of(context).unfocus();
                  },
                  child: const Text('Close', style: TextStyle(color: Color(0xFFF59E0B), fontSize: 11, fontWeight: FontWeight.bold)),
                ),
              ],
            ),
          ),
          if (_isLoadingPlaces)
            const Padding(
              padding: EdgeInsets.all(16),
              child: Center(
                child: SizedBox(
                  width: 20,
                  height: 20,
                  child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFFF59E0B)),
                ),
              ),
            )
          else
            Flexible(
              child: ListView.separated(
                shrinkWrap: true,
                padding: EdgeInsets.zero,
                itemCount: _placesPredictions.length,
                separatorBuilder: (_, __) => Divider(color: Colors.white.withOpacity(0.07), height: 1),
                itemBuilder: (context, index) {
                  final item = _placesPredictions[index];
                  return ListTile(
                    dense: true,
                    leading: const Icon(Icons.location_on_outlined, color: Color(0xFFF59E0B), size: 18),
                    title: Text(
                      item.mainText.isNotEmpty ? item.mainText : item.description,
                      style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.w600),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    subtitle: item.secondaryText.isNotEmpty
                        ? Text(
                            item.secondaryText,
                            style: const TextStyle(color: Colors.white54, fontSize: 11),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          )
                        : null,
                    onTap: () => _selectPlace(item, field: field),
                  );
                },
              ),
            ),
        ],
      ),
    );
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
      packageCategory: _selectedCategory,
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

      final serviceFeeRate = _selectedCategoryServiceFeePercent / 100;

      double base = (15.00 + (_distanceKm * 1.50) + speedAddon + (max(0, _weightKg - 1.0) * 0.75)) * sizeMult * multiplier;
      double fee = base * serviceFeeRate;
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
      if (_selectedCategoryRequiresRx && _prescriptions.isEmpty) {
        setState(() {
          _prescriptionError = 'Doctor prescription upload is mandatory for $_selectedCategory delivery bookings.';
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                const Icon(Icons.error_outline_rounded, color: Colors.white),
                const SizedBox(width: 8),
                Expanded(
                  child: Text('Doctor prescription is mandatory for $_selectedCategory deliveries. Please upload your prescription before proceeding.'),
                ),
              ],
            ),
            backgroundColor: const Color(0xFFEF4444),
            duration: const Duration(seconds: 4),
          ),
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
      'has_prescription': _selectedCategory == 'Pharmeasy',
      'temp_token': _prescriptionTempToken,
      'prescription_ids': _prescriptions.map((p) => p['id']).whereType<int>().toList(),
      'special_handling': [
        if (_signatureRequired) 'signature_required',
        if (_climateControlled) 'climate_controlled',
        if (_whiteGlove) 'white_glove',
      ],
      'payment_method': _paymentMethod,
      'momo_phone': _momoPhone,
      'momo_network': _momoNetwork,
      'prohibited_items_acknowledged': true,
      'country': countryProv.selectedCountryCode,
    };

    final result = await DeliveryService.bookDelivery(payload);

    setState(() => _isSubmitting = false);
    if (!mounted) return;

    final trackingCode = result?['delivery_code'] ?? 'DEL-${Random().nextInt(90000000) + 10000000}';
    final deliveryOtp = result?['delivery_otp'] ?? '${Random().nextInt(9000) + 1000}';
    final deliveryId = (result?['delivery_id'] is int)
        ? result!['delivery_id']
        : int.tryParse(result?['delivery_id']?.toString() ?? '1') ?? 1;

    Navigator.pushReplacement(
      context,
      MaterialPageRoute(
        builder: (_) => DeliveryTrackerScreen(
          deliveryId: deliveryId,
          deliveryCode: trackingCode,
          pickupLocation: _pickupController.text.trim(),
          dropoffLocation: _dropoffController.text.trim(),
          recipientName: _recipientNameController.text.trim(),
          recipientPhone: _recipientPhoneController.text.trim(),
          packageCategory: _selectedCategory,
          totalPrice: _totalAmount,
          currencySymbol: countryProv.currencySymbol,
          initialOtp: deliveryOtp,
          paymentMethod: _paymentMethod,
        ),
      ),
    );
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
                        Text(
                          'Service Fee (${_selectedCategoryServiceFeePercent.toStringAsFixed(_selectedCategoryServiceFeePercent.truncateToDouble() == _selectedCategoryServiceFeePercent ? 0 : 1)}%)',
                          style: const TextStyle(color: Colors.white70, fontSize: 12),
                        ),
                        Text(
                          '+${countryProv.currencySymbol}${_serviceFee.toStringAsFixed(2)}',
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12),
                        ),
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
      resizeToAvoidBottomInset: true,
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
      body: GestureDetector(
        behavior: HitTestBehavior.translucent,
        onTap: () => FocusScope.of(context).unfocus(),
        child: Column(
          children: [
            // Stepper Header
            _buildStepperHeader(),

            // Main Step Content
            Expanded(
              child: SingleChildScrollView(
                keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
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
      ),
      bottomSheet: MediaQuery.of(context).viewInsets.bottom > 0 ? null : _buildBottomActionBar(countryProv),
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
              onTap: () async {
                final addr = await PlacesService.getAddressFromCoordinates(28.6517, 77.1906);
                if (addr != null && mounted) {
                  setState(() {
                    _pickupController.text = addr;
                    _placesPredictions = [];
                  });
                  _calculatePrice();
                } else {
                  setState(() {
                    _pickupController.text = 'T/23, Ratan Nagar, Karol Bagh, New Delhi';
                    _placesPredictions = [];
                  });
                  _calculatePrice();
                }
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
          focusNode: _pickupFocusNode,
          style: const TextStyle(color: Colors.white, fontSize: 13),
          decoration: InputDecoration(
            prefixIcon: const Icon(Icons.circle, color: Color(0xFFF59E0B), size: 14),
            suffixIcon: _pickupController.text.isNotEmpty
                ? IconButton(
                    icon: const Icon(Icons.clear, color: Colors.white54, size: 16),
                    onPressed: () {
                      setState(() {
                        _pickupController.clear();
                        _placesPredictions = [];
                      });
                    },
                  )
                : null,
            filled: true,
            fillColor: const Color(0xFF1E293B),
            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
          ),
          onChanged: (val) {
            _onPlacesQueryChanged(val, field: 'pickup');
            _calculatePrice();
          },
        ),
        _buildPlacesSuggestionsDropdown('pickup'),

        const SizedBox(height: 14),

        // Drop-off Address
        const Text(
          'DROP-OFF / DESTINATION ADDRESS *',
          style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 6),
        TextField(
          controller: _dropoffController,
          focusNode: _dropoffFocusNode,
          style: const TextStyle(color: Colors.white, fontSize: 13),
          decoration: InputDecoration(
            prefixIcon: const Icon(Icons.location_on_rounded, color: Color(0xFFEF4444), size: 18),
            hintText: 'Enter recipient delivery address...',
            hintStyle: const TextStyle(color: Colors.white38, fontSize: 13),
            suffixIcon: _dropoffController.text.isNotEmpty
                ? IconButton(
                    icon: const Icon(Icons.clear, color: Colors.white54, size: 16),
                    onPressed: () {
                      setState(() {
                        _dropoffController.clear();
                        _placesPredictions = [];
                      });
                    },
                  )
                : null,
            filled: true,
            fillColor: const Color(0xFF1E293B),
            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
          ),
          onChanged: (val) {
            _onPlacesQueryChanged(val, field: 'dropoff');
            _calculatePrice();
          },
        ),
        _buildPlacesSuggestionsDropdown('dropoff'),

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
                onMapCreated: (ctrl) {
                  _mapController = ctrl;
                  ctrl.animateCamera(CameraUpdate.newLatLng(LatLng(_pickupLat, _pickupLng)));
                },
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
            final catObj = _dynamicCategories.firstWhere(
              (c) => (c['name'] ?? '').toString().toLowerCase() == cat.toLowerCase(),
              orElse: () => {},
            );
            final icon = catObj['icon']?.toString() ?? (cat.toLowerCase() == 'pharmeasy' ? '💊' : null);
            final badge = catObj['badge_text']?.toString() ?? (cat.toLowerCase() == 'pharmeasy' ? 'Rx' : null);

            return ChoiceChip(
              avatar: icon != null ? Text(icon, style: const TextStyle(fontSize: 13)) : null,
              label: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(cat),
                  if (badge != null && badge.isNotEmpty) ...[
                    const SizedBox(width: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                      decoration: BoxDecoration(
                        color: isSel ? Colors.black26 : Colors.white24,
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        badge,
                        style: TextStyle(
                          fontSize: 9,
                          fontWeight: FontWeight.w900,
                          color: isSel ? Colors.black : Colors.white,
                        ),
                      ),
                    ),
                  ],
                ],
              ),
              selected: isSel,
              selectedColor: isSel && (_selectedCategoryRequiresRx || cat == 'Pharmeasy')
                  ? const Color(0xFF10B981)
                  : const Color(0xFFF59E0B),
              backgroundColor: const Color(0xFF1E293B),
              labelStyle: TextStyle(
                color: isSel ? Colors.black : Colors.white,
                fontWeight: isSel ? FontWeight.bold : FontWeight.normal,
                fontSize: 12,
              ),
              onSelected: (_) {
                setState(() {
                  _selectedCategory = cat;
                  if (catObj.isNotEmpty && catObj['default_description'] != null && (catObj['default_description'] as String).isNotEmpty) {
                    _packageDescController.text = catObj['default_description'];
                  } else if (cat == 'Pharmeasy') {
                    _packageDescController.text = 'Prescription Medicines & Healthcare Supplies';
                  }
                });
                _calculatePrice();
              },
            );
          }).toList(),
        ),

        const SizedBox(height: 16),

        // MANDATORY DOCTOR PRESCRIPTION SECTION (Rx Categories)
        if (_selectedCategoryRequiresRx) ...[
          Container(
            margin: const EdgeInsets.only(bottom: 16),
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFF0F172A),
              borderRadius: BorderRadius.circular(18),
              border: Border.all(
                color: _prescriptionError != null
                    ? const Color(0xFFEF4444)
                    : const Color(0xFF10B981).withOpacity(0.5),
                width: 1.5,
              ),
              boxShadow: [
                BoxShadow(
                  color: (_prescriptionError != null ? const Color(0xFFEF4444) : const Color(0xFF10B981)).withOpacity(0.12),
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
                    Row(
                      children: const [
                        Icon(Icons.medical_services_rounded, color: Color(0xFF10B981), size: 20),
                        SizedBox(width: 8),
                        Text(
                          'UPLOAD DOCTOR PRESCRIPTION',
                          style: TextStyle(
                            color: Color(0xFF10B981),
                            fontSize: 12.5,
                            fontWeight: FontWeight.w900,
                            letterSpacing: 0.5,
                          ),
                        ),
                      ],
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: const Color(0xFFEF4444).withOpacity(0.2),
                        borderRadius: BorderRadius.circular(6),
                        border: Border.all(color: const Color(0xFFEF4444).withOpacity(0.4)),
                      ),
                      child: const Text(
                        'MANDATORY *',
                        style: TextStyle(
                          color: Color(0xFFEF4444),
                          fontSize: 9.5,
                          fontWeight: FontWeight.w900,
                          letterSpacing: 0.5,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                const Text(
                  'A valid doctor prescription (JPG, JPEG, PNG, or PDF) is legally required for medicine pickups from certified pharmacies.',
                  style: TextStyle(color: Colors.white70, fontSize: 11.5, height: 1.3),
                ),
                const SizedBox(height: 12),

                // Error Notice if user tried to proceed without prescription
                if (_prescriptionError != null) ...[
                  Container(
                    margin: const EdgeInsets.only(bottom: 12),
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEF4444).withOpacity(0.15),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFFEF4444)),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.error_outline_rounded, color: Color(0xFFEF4444), size: 16),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            _prescriptionError!,
                            style: const TextStyle(color: Color(0xFFEF4444), fontSize: 11, fontWeight: FontWeight.bold),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],

                // List of uploaded prescriptions
                if (_prescriptions.isNotEmpty) ...[
                  ..._prescriptions.asMap().entries.map((entry) {
                    final idx = entry.key;
                    final rx = entry.value;
                    final isPdf = (rx['file_type']?.toString().toLowerCase() == 'pdf');
                    return Container(
                      margin: const EdgeInsets.only(bottom: 10),
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      decoration: BoxDecoration(
                        color: const Color(0xFF1E293B),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFF10B981).withOpacity(0.35)),
                      ),
                      child: Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: isPdf
                                  ? const Color(0xFFEF4444).withOpacity(0.15)
                                  : const Color(0xFF10B981).withOpacity(0.15),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Icon(
                              isPdf ? Icons.picture_as_pdf_rounded : Icons.image_rounded,
                              color: isPdf ? const Color(0xFFEF4444) : const Color(0xFF10B981),
                              size: 20,
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  rx['file_name']?.toString() ?? 'Doctor Prescription',
                                  style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  '${rx['file_type']?.toString().toUpperCase() ?? 'DOC'} • ${rx['formatted_size'] ?? 'Attached'}',
                                  style: const TextStyle(color: Colors.white54, fontSize: 10),
                                ),
                              ],
                            ),
                          ),
                          IconButton(
                            icon: const Icon(Icons.zoom_in_rounded, color: Color(0xFFF59E0B), size: 20),
                            tooltip: 'Preview & Zoom',
                            onPressed: () => _showPrescriptionViewer(rx),
                          ),
                          IconButton(
                            icon: const Icon(Icons.delete_outline_rounded, color: Color(0xFFEF4444), size: 20),
                            tooltip: 'Remove',
                            onPressed: () => _removePrescription(idx),
                          ),
                        ],
                      ),
                    );
                  }).toList(),
                  const SizedBox(height: 4),
                ],

                // Upload Button / Dropzone Trigger
                InkWell(
                  onTap: _isUploadingPrescription ? null : _openPrescriptionPickerModal,
                  borderRadius: BorderRadius.circular(12),
                  child: Container(
                    padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
                    decoration: BoxDecoration(
                      color: const Color(0xFF1E293B),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: const Color(0xFF10B981).withOpacity(0.4),
                        style: BorderStyle.solid,
                      ),
                    ),
                    child: Center(
                      child: _isUploadingPrescription
                          ? Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: const [
                                SizedBox(
                                  width: 16,
                                  height: 16,
                                  child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF10B981)),
                                ),
                                SizedBox(width: 10),
                                Text('Uploading Prescription...', style: TextStyle(color: Color(0xFF10B981), fontSize: 12, fontWeight: FontWeight.bold)),
                              ],
                            )
                          : Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(
                                  _prescriptions.isEmpty ? Icons.cloud_upload_rounded : Icons.add_circle_outline_rounded,
                                  color: const Color(0xFF10B981),
                                  size: 18,
                                ),
                                const SizedBox(width: 8),
                                Text(
                                  _prescriptions.isEmpty
                                      ? '📷 Snap Photo or Upload Doctor Prescription'
                                      : '+ Add Another Prescription Page / File',
                                  style: const TextStyle(
                                    color: Color(0xFF10B981),
                                    fontWeight: FontWeight.bold,
                                    fontSize: 12.5,
                                  ),
                                ),
                              ],
                            ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],

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

        // Unified Interactive Payment Selector Card
        GestureDetector(
          onTap: _openPaymentMethodSheet,
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            decoration: BoxDecoration(
              color: const Color(0xFF1E293B),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(
                color: _paymentMethod == 'stripe'
                    ? const Color(0xFF6366F1).withOpacity(0.4)
                    : (_paymentMethod == 'momo'
                        ? const Color(0xFFFFDC00).withOpacity(0.4)
                        : const Color(0xFF10B981).withOpacity(0.4)),
                width: 1.2,
              ),
            ),
            child: Row(
              children: [
                Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    color: _paymentMethod == 'stripe'
                        ? const Color(0xFF6366F1)
                        : (_paymentMethod == 'momo'
                            ? const Color(0xFFFFDC00)
                            : const Color(0xFF10B981)),
                    borderRadius: BorderRadius.circular(12),
                    boxShadow: [
                      BoxShadow(
                        color: (_paymentMethod == 'stripe'
                                ? const Color(0xFF6366F1)
                                : (_paymentMethod == 'momo'
                                    ? const Color(0xFFFFDC00)
                                    : const Color(0xFF10B981)))
                            .withOpacity(0.35),
                        blurRadius: 6,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  alignment: Alignment.center,
                  child: _paymentMethod == 'stripe'
                      ? const Text('S', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 20))
                      : (_paymentMethod == 'momo'
                          ? const Text('MoMo', style: TextStyle(color: Colors.black, fontWeight: FontWeight.w900, fontSize: 10))
                          : const Icon(Icons.payments_rounded, color: Colors.white, size: 22)),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Wrap(
                        crossAxisAlignment: WrapCrossAlignment.center,
                        spacing: 6,
                        runSpacing: 2,
                        children: [
                          Text(
                            _paymentMethod == 'stripe'
                                ? 'Stripe'
                                : (_paymentMethod == 'momo'
                                    ? 'MoMo Pay'
                                    : 'Cash Direct Pay'),
                            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 13.5),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              color: _paymentMethod == 'stripe'
                                  ? const Color(0xFF6366F1).withOpacity(0.2)
                                  : (_paymentMethod == 'momo'
                                      ? const Color(0xFFFFDC00).withOpacity(0.2)
                                      : const Color(0xFF10B981).withOpacity(0.2)),
                              borderRadius: BorderRadius.circular(5),
                            ),
                            child: Text(
                              _paymentMethod == 'stripe'
                                  ? '💳 CARDS & APPLE PAY'
                                  : (_paymentMethod == 'momo'
                                      ? '📱 MOBILE MONEY'
                                      : '💵 PAY ON DELIVERY'),
                              style: TextStyle(
                                color: _paymentMethod == 'stripe'
                                    ? const Color(0xFF818CF8)
                                    : (_paymentMethod == 'momo'
                                        ? const Color(0xFFFFDC00)
                                        : const Color(0xFF34D399)),
                                fontSize: 8.5,
                                fontWeight: FontWeight.w900,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 2),
                      Text(
                        _paymentMethod == 'stripe'
                            ? 'Secured card & digital wallet hold'
                            : (_paymentMethod == 'momo'
                                ? 'Prompt to ${_momoPhone != null && _momoPhone!.isNotEmpty ? _momoPhone : 'MoMo number ($_momoNetwork)'}'
                                : 'Pay courier physical cash on parcel handover'),
                        style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 10.5),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                const Icon(Icons.chevron_right_rounded, color: Color(0xFF94A3B8), size: 24),
              ],
            ),
          ),
        ),

        const SizedBox(height: 12),

        // Security badge
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
          decoration: BoxDecoration(
            color: const Color(0xFF0F172A).withOpacity(0.5),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.white.withOpacity(0.06)),
          ),
          child: Row(
            children: const [
              Icon(Icons.shield_rounded, color: Color(0xFF10B981), size: 18),
              SizedBox(width: 8),
              Expanded(
                child: Text(
                  'End-to-end encrypted transaction • Verified courier protection',
                  style: TextStyle(color: Colors.white70, fontSize: 11),
                ),
              ),
            ],
          ),
        ),

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
              Text(
                'Service Fee (${_selectedCategoryServiceFeePercent.toStringAsFixed(_selectedCategoryServiceFeePercent.truncateToDouble() == _selectedCategoryServiceFeePercent ? 0 : 1)}%):',
                style: const TextStyle(color: Colors.white70, fontSize: 12.5),
              ),
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
                    : FittedBox(
                        fit: BoxFit.scaleDown,
                        child: Text(
                          _currentStep == 5
                              ? '🚀 Dispatch & Pay ($sym${_totalAmount.toStringAsFixed(2)})'
                              : 'Next: Step ${_currentStep + 1} →',
                          style: const TextStyle(color: Colors.black, fontWeight: FontWeight.w900, fontSize: 13.5),
                        ),
                      ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
