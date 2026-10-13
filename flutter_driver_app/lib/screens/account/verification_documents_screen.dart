import 'dart:io';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import '../../core/constants/api_constants.dart';
import '../../core/constants/app_colors.dart';
import '../../services/verification_service.dart';

class VerificationDocumentsScreen extends StatefulWidget {
  final int initialTabIndex;

  const VerificationDocumentsScreen({
    super.key,
    this.initialTabIndex = 0,
  });

  @override
  State<VerificationDocumentsScreen> createState() => _VerificationDocumentsScreenState();
}

class _VerificationDocumentsScreenState extends State<VerificationDocumentsScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final ImagePicker _picker = ImagePicker();

  bool _isLoading = true;
  bool _isSubmitting = false;

  bool _isChauffeur = false;

  // 1. Vehicle Certificates State
  final _insurancePolicyCtrl = TextEditingController();
  final _insuranceExpiryCtrl = TextEditingController();
  final _roadworthyNumberCtrl = TextEditingController();
  final _roadworthyExpiryCtrl = TextEditingController();
  XFile? _insuranceFile;
  XFile? _roadworthyFile;
  String? _existingInsuranceUrl;
  String? _existingRoadworthyUrl;
  String _vehicleCertStatus = 'pending';
  String? _vehicleCertRejection;

  // 2. ID Card State
  String _idType = 'Ghana Card';
  final _idNumberCtrl = TextEditingController();
  final _idExpiryCtrl = TextEditingController();
  XFile? _idFrontFile;
  XFile? _idBackFile;
  String? _existingIdFrontUrl;
  String? _existingIdBackUrl;
  String _idCardStatus = 'pending';
  String? _idCardRejection;

  // 3. Driver License State
  final _licenseNumberCtrl = TextEditingController();
  final _licenseCountryCtrl = TextEditingController(text: 'Ghana');
  final _licenseExpiryCtrl = TextEditingController();
  XFile? _licenseFrontFile;
  XFile? _licenseBackFile;
  String? _existingLicenseFrontUrl;
  String? _existingLicenseBackUrl;
  String _licenseStatus = 'pending';
  String? _licenseRejection;

  // 4. Guarantor State (Chauffeur Only)
  final _guarantorNameCtrl = TextEditingController();
  final _guarantorPhoneCtrl = TextEditingController();
  final _guarantorRelCtrl = TextEditingController();
  final _guarantorAddressCtrl = TextEditingController();
  final _guarantorWorkplaceCtrl = TextEditingController();
  XFile? _guarantorIdFile;
  String? _existingGuarantorIdUrl;
  String _guarantorStatus = 'not_submitted';

  @override
  void initState() {
    super.initState();
    _tabController = TabController(
      length: 4,
      vsync: this,
      initialIndex: widget.initialTabIndex.clamp(0, 3),
    );
    _loadVerificationData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    _insurancePolicyCtrl.dispose();
    _insuranceExpiryCtrl.dispose();
    _roadworthyNumberCtrl.dispose();
    _roadworthyExpiryCtrl.dispose();

    _idNumberCtrl.dispose();
    _idExpiryCtrl.dispose();

    _licenseNumberCtrl.dispose();
    _licenseCountryCtrl.dispose();
    _licenseExpiryCtrl.dispose();

    _guarantorNameCtrl.dispose();
    _guarantorPhoneCtrl.dispose();
    _guarantorRelCtrl.dispose();
    _guarantorAddressCtrl.dispose();
    _guarantorWorkplaceCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadVerificationData() async {
    setState(() => _isLoading = true);
    try {
      final data = await VerificationService.getVerificationStatus();
      if (data != null && mounted) {
        final profile = data['profile'] ?? {};
        final verifs = data['verifications'] ?? {};

        _isChauffeur = data['is_chauffeur'] == true ||
            profile['is_chauffeur'] == true ||
            data['driver_type'] == 'chauffeur';

        // Vehicle Certificates
        final vCert = verifs['vehicle_certificates'] ?? {};
        _vehicleCertStatus = vCert['status'] ?? profile['insurance_status'] ?? 'pending';
        _vehicleCertRejection = vCert['rejection_reason'] ?? profile['insurance_rejection_reason'];
        _insurancePolicyCtrl.text = vCert['insurance_policy_number'] ?? profile['insurance_policy_number'] ?? '';
        _insuranceExpiryCtrl.text = vCert['insurance_expiry'] ?? profile['insurance_expiry'] ?? '';
        _roadworthyNumberCtrl.text = vCert['roadworthy_number'] ?? profile['roadworthy_number'] ?? '';
        _roadworthyExpiryCtrl.text = vCert['roadworthy_expiry'] ?? profile['roadworthy_expiry'] ?? '';
        _existingInsuranceUrl = _resolveStorageUrl(vCert['insurance_scan'] ?? profile['insurance_certificate']);
        _existingRoadworthyUrl = _resolveStorageUrl(vCert['roadworthy_scan'] ?? profile['roadworthy_certificate']);

        // ID Card
        final idData = verifs['id_card'] ?? {};
        _idCardStatus = idData['status'] ?? profile['id_card_status'] ?? 'pending';
        _idCardRejection = idData['rejection_reason'] ?? profile['id_card_rejection_reason'];
        _idType = idData['id_type'] ?? profile['id_card_type'] ?? 'Ghana Card';
        _idNumberCtrl.text = idData['id_number'] ?? profile['id_card_number'] ?? '';
        _idExpiryCtrl.text = idData['expiry_date'] ?? profile['id_card_expiry'] ?? '';
        _existingIdFrontUrl = _resolveStorageUrl(idData['front_image'] ?? profile['id_card_front_url']);
        _existingIdBackUrl = _resolveStorageUrl(idData['back_image'] ?? profile['id_card_back_url']);

        // Driver License
        final licData = verifs['driver_license'] ?? {};
        _licenseStatus = licData['status'] ?? profile['license_status'] ?? 'pending';
        _licenseRejection = licData['rejection_reason'] ?? profile['license_rejection_reason'];
        _licenseNumberCtrl.text = licData['license_number'] ?? profile['license_number'] ?? '';
        _licenseCountryCtrl.text = licData['issuing_country'] ?? profile['issuing_country'] ?? profile['country'] ?? 'Ghana';
        _licenseExpiryCtrl.text = licData['expiry_date'] ?? profile['license_expiry'] ?? '';
        _existingLicenseFrontUrl = _resolveStorageUrl(licData['front_image'] ?? profile['license_front_url']);
        _existingLicenseBackUrl = _resolveStorageUrl(licData['back_image'] ?? profile['license_back_url']);

        // Guarantor (Chauffeur Only)
        final guarData = verifs['guarantor'] ?? data['guarantor'] ?? {};
        if (guarData is Map && guarData.isNotEmpty) {
          _guarantorStatus = guarData['status'] ?? 'under_review';
          _guarantorNameCtrl.text = guarData['full_name'] ?? guarData['name'] ?? '';
          _guarantorPhoneCtrl.text = guarData['phone'] ?? '';
          _guarantorRelCtrl.text = guarData['relationship'] ?? '';
          _guarantorAddressCtrl.text = guarData['address'] ?? '';
          _guarantorWorkplaceCtrl.text = guarData['workplace'] ?? '';
          _existingGuarantorIdUrl = _resolveStorageUrl(guarData['id_card_url'] ?? guarData['id_card_image']);
        }
      }
    } catch (e) {
      debugPrint('Failed to load verification info: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  String? _resolveStorageUrl(dynamic raw) {
    if (raw == null) return null;
    final str = raw.toString().trim();
    if (str.isEmpty) return null;
    if (str.startsWith('http://') || str.startsWith('https://')) return str;
    final cleaned = str.replaceFirst(RegExp(r"^storage/"), "");
    return '${ApiConstants.storageBaseUrl}/$cleaned';
  }

  Future<void> _pickDate(TextEditingController controller) async {
    final now = DateTime.now();
    DateTime initial = now.add(const Duration(days: 365));

    if (controller.text.isNotEmpty) {
      try {
        initial = DateFormat('yyyy-MM-dd').parse(controller.text);
      } catch (_) {}
    }

    final picked = await showDatePicker(
      context: context,
      initialDate: initial,
      firstDate: DateTime(2020),
      lastDate: DateTime(2040),
      builder: (context, child) {
        return Theme(
          data: ThemeData.dark().copyWith(
            colorScheme: const ColorScheme.dark(
              primary: AppColors.primary,
              onPrimary: AppColors.backgroundDark,
              surface: AppColors.surfaceDark,
              onSurface: AppColors.textLight,
            ),
            dialogBackgroundColor: AppColors.surfaceDark,
          ),
          child: child!,
        );
      },
    );

    if (picked != null) {
      controller.text = DateFormat('yyyy-MM-dd').format(picked);
    }
  }

  void _showImageSourcePicker(Function(XFile) onPicked) {
    showModalBottomSheet(
      context: context,
      backgroundColor: AppColors.surfaceDark,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 16),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 36,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.white24,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const SizedBox(height: 14),
              const Text(
                'Select Document Photo Source',
                style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 16),
              ),
              const SizedBox(height: 12),
              ListTile(
                leading: Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: AppColors.primary.withOpacity(0.15),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.camera_alt_rounded, color: AppColors.primary),
                ),
                title: const Text('Take Photo with Camera', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w600)),
                subtitle: const Text('Ensure document is flat and readable', style: TextStyle(color: AppColors.textMuted, fontSize: 12)),
                onTap: () async {
                  Navigator.pop(ctx);
                  try {
                    final photo = await _picker.pickImage(
                      source: ImageSource.camera,
                      imageQuality: 85,
                      maxWidth: 1600,
                    );
                    if (photo != null) onPicked(photo);
                  } catch (e) {
                    debugPrint('Camera error: $e');
                  }
                },
              ),
              const Divider(color: Colors.white10),
              ListTile(
                leading: Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: AppColors.primary.withOpacity(0.15),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.photo_library_rounded, color: AppColors.primary),
                ),
                title: const Text('Upload from Gallery', style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w600)),
                subtitle: const Text('Pick clear scan or photo from device', style: TextStyle(color: AppColors.textMuted, fontSize: 12)),
                onTap: () async {
                  Navigator.pop(ctx);
                  try {
                    final photo = await _picker.pickImage(
                      source: ImageSource.gallery,
                      imageQuality: 85,
                      maxWidth: 1600,
                    );
                    if (photo != null) onPicked(photo);
                  } catch (e) {
                    debugPrint('Gallery error: $e');
                  }
                },
              ),
            ],
          ),
        ),
      ),
    );
  }

  // Submit Vehicle Certificates
  Future<void> _submitVehicleCertificates() async {
    if (_insuranceFile == null && _existingInsuranceUrl == null) {
      _showToast('Please upload your Insurance Certificate scan.', isError: true);
      return;
    }

    setState(() => _isSubmitting = true);
    final res = await VerificationService.uploadVehicleCertificates(
      insurancePolicyNumber: _insurancePolicyCtrl.text.trim(),
      insuranceExpiry: _insuranceExpiryCtrl.text.trim(),
      insuranceScan: _insuranceFile,
      roadworthyNumber: _roadworthyNumberCtrl.text.trim(),
      roadworthyExpiry: _roadworthyExpiryCtrl.text.trim(),
      roadworthyScan: _roadworthyFile,
    );
    setState(() => _isSubmitting = false);

    _showToast(res['message'], isError: !res['success']);
    if (res['success']) {
      _loadVerificationData();
    }
  }

  // Submit ID Card
  Future<void> _submitIdCard() async {
    if (_idNumberCtrl.text.trim().isEmpty) {
      _showToast('Please enter your ID Number.', isError: true);
      return;
    }
    if (_idFrontFile == null && _existingIdFrontUrl == null) {
      _showToast('Please upload the front photo of your ID card.', isError: true);
      return;
    }

    setState(() => _isSubmitting = true);
    final res = await VerificationService.uploadIdCard(
      idType: _idType,
      idNumber: _idNumberCtrl.text.trim(),
      expiryDate: _idExpiryCtrl.text.trim(),
      frontImage: _idFrontFile,
      backImage: _idBackFile,
    );
    setState(() => _isSubmitting = false);

    _showToast(res['message'], isError: !res['success']);
    if (res['success']) {
      _loadVerificationData();
    }
  }

  // Submit Driver License
  Future<void> _submitDriverLicense() async {
    if (_licenseNumberCtrl.text.trim().isEmpty) {
      _showToast('Please enter your Driver License number.', isError: true);
      return;
    }
    if (_licenseFrontFile == null && _existingLicenseFrontUrl == null) {
      _showToast('Please upload the front photo of your Driver License.', isError: true);
      return;
    }

    setState(() => _isSubmitting = true);
    final res = await VerificationService.uploadDriverLicense(
      licenseNumber: _licenseNumberCtrl.text.trim(),
      issuingCountry: _licenseCountryCtrl.text.trim(),
      expiryDate: _licenseExpiryCtrl.text.trim(),
      frontImage: _licenseFrontFile,
      backImage: _licenseBackFile,
    );
    setState(() => _isSubmitting = false);

    _showToast(res['message'], isError: !res['success']);
    if (res['success']) {
      _loadVerificationData();
    }
  }

  // Submit Guarantor (Chauffeur Only)
  Future<void> _submitGuarantor() async {
    if (_guarantorNameCtrl.text.trim().isEmpty || _guarantorPhoneCtrl.text.trim().isEmpty) {
      _showToast('Please provide your guarantor full name and phone number.', isError: true);
      return;
    }
    if (_guarantorRelCtrl.text.trim().isEmpty || _guarantorAddressCtrl.text.trim().isEmpty) {
      _showToast('Please provide your relationship and residential address.', isError: true);
      return;
    }

    setState(() => _isSubmitting = true);
    final res = await VerificationService.submitGuarantor(
      name: _guarantorNameCtrl.text.trim(),
      phone: _guarantorPhoneCtrl.text.trim(),
      relationship: _guarantorRelCtrl.text.trim(),
      address: _guarantorAddressCtrl.text.trim(),
      workplace: _guarantorWorkplaceCtrl.text.trim(),
      idCardImage: _guarantorIdFile,
    );
    setState(() => _isSubmitting = false);

    _showToast(res['message'], isError: !res['success']);
    if (res['success']) {
      _loadVerificationData();
    }
  }

  // Toggle Chauffeur Status
  Future<void> _toggleChauffeurRole(bool enable) async {
    setState(() => _isSubmitting = true);
    final res = await VerificationService.toggleChauffeurRole(enable);
    setState(() => _isSubmitting = false);

    _showToast(res['message'], isError: !res['success']);
    if (res['success']) {
      setState(() {
        _isChauffeur = enable;
      });
      _loadVerificationData();
    }
  }

  void _showToast(String message, {bool isError = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? AppColors.danger : AppColors.success,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.backgroundDark,
      appBar: AppBar(
        backgroundColor: AppColors.surfaceDark,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: AppColors.textLight, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Verification Documents',
          style: TextStyle(color: AppColors.textLight, fontWeight: FontWeight.w800, fontSize: 18),
        ),
        bottom: TabBar(
          controller: _tabController,
          isScrollable: true,
          indicatorColor: AppColors.primary,
          indicatorWeight: 3,
          labelColor: AppColors.primary,
          unselectedLabelColor: AppColors.textMuted,
          labelStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
          tabs: [
            const Tab(text: 'Vehicle Certs'),
            const Tab(text: 'ID Card'),
            const Tab(text: 'Driver License'),
            Tab(
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Text('Guarantor'),
                  const SizedBox(width: 4),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                    decoration: BoxDecoration(
                      color: _isChauffeur ? AppColors.primary : Colors.white12,
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Text(
                      'CHAUFFEUR',
                      style: TextStyle(
                        fontSize: 8.5,
                        fontWeight: FontWeight.w900,
                        color: _isChauffeur ? AppColors.backgroundDark : AppColors.textMuted,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : TabBarView(
              controller: _tabController,
              children: [
                _buildVehicleCertificatesTab(),
                _buildIdCardTab(),
                _buildDriverLicenseTab(),
                _buildGuarantorTab(),
              ],
            ),
    );
  }

  // -------------------------------------------------------------
  // TAB 1: VEHICLE CERTIFICATES (INSURANCE & ROADWORTHY/FITNESS)
  // -------------------------------------------------------------
  Widget _buildVehicleCertificatesTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          _buildStatusBanner(
            title: 'Vehicle Roadworthiness & Insurance Verification',
            status: _vehicleCertStatus,
            rejectionReason: _vehicleCertRejection,
            verifiedDesc: 'Your vehicle insurance & roadworthiness certificates are fully verified and active.',
            pendingDesc: 'Certificates are currently under compliance review by the safety dispatch team.',
            actionDesc: 'Upload current insurance policy & roadworthy certificates for vehicle dispatch approval.',
          ),
          const SizedBox(height: 20),

          // Insurance Section
          _buildSectionHeader(Icons.security_rounded, 'Motor Insurance Policy'),
          const SizedBox(height: 12),
          _buildTextField(
            controller: _insurancePolicyCtrl,
            label: 'Insurance Policy Number',
            hint: 'e.g. POL-849204-GH',
            icon: Icons.tag_rounded,
          ),
          const SizedBox(height: 12),
          _buildDateField(
            controller: _insuranceExpiryCtrl,
            label: 'Insurance Expiry Date',
            hint: 'YYYY-MM-DD',
            onTap: () => _pickDate(_insuranceExpiryCtrl),
          ),
          const SizedBox(height: 14),
          _buildImagePickerCard(
            title: 'Insurance Certificate Scan / Photo',
            subtitle: 'Upload a clear photo showing policy number, validity dates, and vehicle chassis/reg',
            file: _insuranceFile,
            existingUrl: _existingInsuranceUrl,
            onPick: () => _showImageSourcePicker((f) => setState(() => _insuranceFile = f)),
            onRemove: () => setState(() {
              _insuranceFile = null;
              _existingInsuranceUrl = null;
            }),
          ),
          const SizedBox(height: 24),

          // Roadworthy / Fitness Section
          _buildSectionHeader(Icons.verified_rounded, 'Vehicle Fitness / Roadworthiness Certificate'),
          const SizedBox(height: 12),
          _buildTextField(
            controller: _roadworthyNumberCtrl,
            label: 'Roadworthiness Certificate Number',
            hint: 'e.g. RW-2026-99120',
            icon: Icons.confirmation_number_outlined,
          ),
          const SizedBox(height: 12),
          _buildDateField(
            controller: _roadworthyExpiryCtrl,
            label: 'Roadworthiness Expiry Date',
            hint: 'YYYY-MM-DD',
            onTap: () => _pickDate(_roadworthyExpiryCtrl),
          ),
          const SizedBox(height: 14),
          _buildImagePickerCard(
            title: 'Roadworthy / Fitness Certificate Scan',
            subtitle: 'Clear photo of the official inspection / fitness sticker or certificate',
            file: _roadworthyFile,
            existingUrl: _existingRoadworthyUrl,
            onPick: () => _showImageSourcePicker((f) => setState(() => _roadworthyFile = f)),
            onRemove: () => setState(() {
              _roadworthyFile = null;
              _existingRoadworthyUrl = null;
            }),
          ),
          const SizedBox(height: 28),

          _buildSubmitButton(
            title: 'Submit Vehicle Certificates',
            isSubmitting: _isSubmitting,
            onPressed: _submitVehicleCertificates,
          ),
          const SizedBox(height: 30),
        ],
      ),
    );
  }

  // -------------------------------------------------------------
  // TAB 2: NATIONAL ID CARD VERIFICATION
  // -------------------------------------------------------------
  Widget _buildIdCardTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          _buildStatusBanner(
            title: 'National Identity Verification',
            status: _idCardStatus,
            rejectionReason: _idCardRejection,
            verifiedDesc: 'Your national identity document is verified.',
            pendingDesc: 'Your ID card is currently being reviewed for KYC compliance.',
            actionDesc: 'Submit a clear scan of your National ID (Ghana Card, Passport, or Voter ID).',
          ),
          const SizedBox(height: 20),

          _buildSectionHeader(Icons.badge_rounded, 'Government-Issued Photo ID'),
          const SizedBox(height: 12),

          // ID Type Dropdown
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
            decoration: BoxDecoration(
              color: AppColors.surfaceDark,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: Colors.white12),
            ),
            child: DropdownButtonHideUnderline(
              child: DropdownButton<String>(
                value: ['Ghana Card', 'National Identity Card', 'Passport', 'Voter ID', 'Driver Residence Permit'].contains(_idType)
                    ? _idType
                    : 'Ghana Card',
                dropdownColor: AppColors.surfaceDark,
                style: const TextStyle(color: AppColors.textLight, fontSize: 15, fontWeight: FontWeight.w600),
                icon: const Icon(Icons.arrow_drop_down, color: AppColors.primary),
                isExpanded: true,
                items: const [
                  DropdownMenuItem(value: 'Ghana Card', child: Text('Ghana Card (National ID)')),
                  DropdownMenuItem(value: 'National Identity Card', child: Text('National Identity Card (ECOWAS / Other)')),
                  DropdownMenuItem(value: 'Passport', child: Text('International Passport')),
                  DropdownMenuItem(value: 'Voter ID', child: Text('Voter Identification Card')),
                  DropdownMenuItem(value: 'Driver Residence Permit', child: Text('Driver Residence Permit / Green Card')),
                ],
                onChanged: (val) {
                  if (val != null) setState(() => _idType = val);
                },
              ),
            ),
          ),
          const SizedBox(height: 12),

          _buildTextField(
            controller: _idNumberCtrl,
            label: 'ID Card Number',
            hint: 'e.g. GHA-729103810-4',
            icon: Icons.credit_card_rounded,
          ),
          const SizedBox(height: 12),

          _buildDateField(
            controller: _idExpiryCtrl,
            label: 'ID Expiry Date',
            hint: 'YYYY-MM-DD',
            onTap: () => _pickDate(_idExpiryCtrl),
          ),
          const SizedBox(height: 16),

          Row(
            children: [
              Expanded(
                child: _buildImagePickerCard(
                  title: 'Front of ID Card',
                  subtitle: 'Photo & details visible',
                  file: _idFrontFile,
                  existingUrl: _existingIdFrontUrl,
                  onPick: () => _showImageSourcePicker((f) => setState(() => _idFrontFile = f)),
                  onRemove: () => setState(() {
                    _idFrontFile = null;
                    _existingIdFrontUrl = null;
                  }),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _buildImagePickerCard(
                  title: 'Back of ID Card',
                  subtitle: 'Barcode & signature',
                  file: _idBackFile,
                  existingUrl: _existingIdBackUrl,
                  onPick: () => _showImageSourcePicker((f) => setState(() => _idBackFile = f)),
                  onRemove: () => setState(() {
                    _idBackFile = null;
                    _existingIdBackUrl = null;
                  }),
                ),
              ),
            ],
          ),
          const SizedBox(height: 28),

          _buildSubmitButton(
            title: 'Submit ID Card for Verification',
            isSubmitting: _isSubmitting,
            onPressed: _submitIdCard,
          ),
          const SizedBox(height: 30),
        ],
      ),
    );
  }

  // -------------------------------------------------------------
  // TAB 3: DRIVER LICENSE VERIFICATION
  // -------------------------------------------------------------
  Widget _buildDriverLicenseTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          _buildStatusBanner(
            title: 'Driver License Verification',
            status: _licenseStatus,
            rejectionReason: _licenseRejection,
            verifiedDesc: 'Your professional driver license is verified and valid for ride dispatch.',
            pendingDesc: 'Your license is currently undergoing authority and credential checks.',
            actionDesc: 'Provide your driver license details along with front and back photos.',
          ),
          const SizedBox(height: 20),

          _buildSectionHeader(Icons.airline_seat_recline_extra_rounded, 'Commercial / Private Driver License'),
          const SizedBox(height: 12),

          _buildTextField(
            controller: _licenseNumberCtrl,
            label: 'Driver License Number',
            hint: 'e.g. DL-82910398-2',
            icon: Icons.badge_outlined,
          ),
          const SizedBox(height: 12),

          _buildTextField(
            controller: _licenseCountryCtrl,
            label: 'Issuing Authority / Country',
            hint: 'e.g. Ghana (DVLA) / Nigeria (FRSC) / USA',
            icon: Icons.public_rounded,
          ),
          const SizedBox(height: 12),

          _buildDateField(
            controller: _licenseExpiryCtrl,
            label: 'License Expiry Date',
            hint: 'YYYY-MM-DD',
            onTap: () => _pickDate(_licenseExpiryCtrl),
          ),
          const SizedBox(height: 16),

          Row(
            children: [
              Expanded(
                child: _buildImagePickerCard(
                  title: 'License Front',
                  subtitle: 'License photo & classes',
                  file: _licenseFrontFile,
                  existingUrl: _existingLicenseFrontUrl,
                  onPick: () => _showImageSourcePicker((f) => setState(() => _licenseFrontFile = f)),
                  onRemove: () => setState(() {
                    _licenseFrontFile = null;
                    _existingLicenseFrontUrl = null;
                  }),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _buildImagePickerCard(
                  title: 'License Back',
                  subtitle: 'Endorsements & barcode',
                  file: _licenseBackFile,
                  existingUrl: _existingLicenseBackUrl,
                  onPick: () => _showImageSourcePicker((f) => setState(() => _licenseBackFile = f)),
                  onRemove: () => setState(() {
                    _licenseBackFile = null;
                    _existingLicenseBackUrl = null;
                  }),
                ),
              ),
            ],
          ),
          const SizedBox(height: 28),

          _buildSubmitButton(
            title: 'Submit Driver License',
            isSubmitting: _isSubmitting,
            onPressed: _submitDriverLicense,
          ),
          const SizedBox(height: 30),
        ],
      ),
    );
  }

  // -------------------------------------------------------------
  // TAB 4: GUARANTOR VERIFICATION (STRICTLY CHAUFFEUR ONLY)
  // -------------------------------------------------------------
  Widget _buildGuarantorTab() {
    // STRICT CHAUFFEUR CHECK: Non-chauffeur drivers see a locked screen explaining it's chauffeur-only
    if (!_isChauffeur) {
      return Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
          child: Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: AppColors.surfaceDark,
              borderRadius: BorderRadius.circular(24),
              border: Border.all(color: AppColors.primary.withOpacity(0.3), width: 1.5),
              boxShadow: [
                BoxShadow(
                  color: AppColors.primary.withOpacity(0.08),
                  blurRadius: 20,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  padding: const EdgeInsets.all(18),
                  decoration: BoxDecoration(
                    color: AppColors.primary.withOpacity(0.12),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.lock_person_rounded,
                    color: AppColors.primary,
                    size: 48,
                  ),
                ),
                const SizedBox(height: 18),
                const Text(
                  'Reserved for VIP Chauffeur Partners',
                  style: TextStyle(
                    color: AppColors.textLight,
                    fontSize: 20,
                    fontWeight: FontWeight.w900,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 12),
                const Text(
                  'Guarantor verification information is strictly required for Chauffeur drivers who provide high-tier executive rides, hourly private bookings, and VIP services.\n\nStandard drivers do not need a guarantor to operate on RideMyCars.',
                  style: TextStyle(
                    color: AppColors.textMuted,
                    fontSize: 14,
                    height: 1.5,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 24),
                ElevatedButton.icon(
                  onPressed: _isSubmitting ? null : () => _toggleChauffeurRole(true),
                  icon: const Icon(Icons.stars_rounded, color: AppColors.backgroundDark),
                  label: _isSubmitting
                      ? const SizedBox(
                          height: 18,
                          width: 18,
                          child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.backgroundDark),
                        )
                      : const Text(
                          'Apply as Professional Chauffeur',
                          style: TextStyle(fontWeight: FontWeight.w900, fontSize: 14),
                        ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    foregroundColor: AppColors.backgroundDark,
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
              ],
            ),
          ),
        ),
      );
    }

    // Chauffeur is enabled: Show full guarantor form
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Chauffeur Active Badge & Description
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
            decoration: BoxDecoration(
              color: AppColors.primary.withOpacity(0.15),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.primary.withOpacity(0.3)),
            ),
            child: Row(
              children: [
                const Icon(Icons.stars_rounded, color: AppColors.primary, size: 20),
                const SizedBox(width: 8),
                const Expanded(
                  child: Text(
                    'CHAUFFEUR VERIFICATION SUITE ACTIVE',
                    style: TextStyle(
                      color: AppColors.primary,
                      fontSize: 12,
                      fontWeight: FontWeight.w900,
                      letterSpacing: 0.5,
                    ),
                  ),
                ),
                TextButton(
                  onPressed: _isSubmitting ? null : () => _toggleChauffeurRole(false),
                  style: TextButton.styleFrom(
                    foregroundColor: AppColors.textMuted,
                    padding: EdgeInsets.zero,
                    minimumSize: const Size(50, 30),
                  ),
                  child: const Text('Opt Out', style: TextStyle(fontSize: 11)),
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),

          _buildStatusBanner(
            title: 'Chauffeur Guarantor Verification',
            status: _guarantorStatus,
            rejectionReason: null,
            verifiedDesc: 'Your Chauffeur guarantor has been verified and approved by RideMyCars Admin.',
            pendingDesc: 'Guarantor details submitted. Our verification team is reaching out for confirmation.',
            actionDesc: 'Please provide a reputable guarantor (family elder, employer, or professional reference) who can vouch for your integrity.',
          ),
          const SizedBox(height: 20),

          _buildSectionHeader(Icons.person_pin_rounded, 'Guarantor Personal Details'),
          const SizedBox(height: 12),

          _buildTextField(
            controller: _guarantorNameCtrl,
            label: 'Guarantor Full Legal Name',
            hint: 'e.g. Dr. Kwame Mensah',
            icon: Icons.person_outline_rounded,
          ),
          const SizedBox(height: 12),

          _buildTextField(
            controller: _guarantorPhoneCtrl,
            label: 'Guarantor Phone Number',
            hint: 'e.g. +233 24 123 4567',
            keyboardType: TextInputType.phone,
            icon: Icons.phone_outlined,
          ),
          const SizedBox(height: 12),

          _buildTextField(
            controller: _guarantorRelCtrl,
            label: 'Relationship to Driver',
            hint: 'e.g. Employer, Uncle, Mentor, Colleague',
            icon: Icons.people_outline_rounded,
          ),
          const SizedBox(height: 12),

          _buildTextField(
            controller: _guarantorAddressCtrl,
            label: 'Residential / Office Address',
            hint: 'e.g. House No. 14, Ring Road Central, Accra',
            icon: Icons.location_on_outlined,
          ),
          const SizedBox(height: 12),

          _buildTextField(
            controller: _guarantorWorkplaceCtrl,
            label: 'Place of Work / Occupation (Optional)',
            hint: 'e.g. Ghana Health Service / Senior Accountant',
            icon: Icons.work_outline_rounded,
          ),
          const SizedBox(height: 16),

          _buildImagePickerCard(
            title: 'Guarantor ID Card or Reference Letter',
            subtitle: 'Upload a scan/photo of the guarantor\'s ID card or stamped reference',
            file: _guarantorIdFile,
            existingUrl: _existingGuarantorIdUrl,
            onPick: () => _showImageSourcePicker((f) => setState(() => _guarantorIdFile = f)),
            onRemove: () => setState(() {
              _guarantorIdFile = null;
              _existingGuarantorIdUrl = null;
            }),
          ),
          const SizedBox(height: 28),

          _buildSubmitButton(
            title: 'Submit Chauffeur Guarantor Info',
            isSubmitting: _isSubmitting,
            onPressed: _submitGuarantor,
          ),
          const SizedBox(height: 30),
        ],
      ),
    );
  }

  // -------------------------------------------------------------
  // REUSABLE UI BUILDERS
  // -------------------------------------------------------------
  Widget _buildStatusBanner({
    required String title,
    required String status,
    String? rejectionReason,
    required String verifiedDesc,
    required String pendingDesc,
    required String actionDesc,
  }) {
    final isVerified = status == 'verified' || status == 'active';
    final isRejected = status == 'rejected';
    final isUnderReview = status == 'under_review' || status == 'pending';

    Color bannerColor = AppColors.warning;
    IconData bannerIcon = Icons.pending_actions_rounded;
    String badgeText = 'PENDING';
    String desc = actionDesc;

    if (isVerified) {
      bannerColor = AppColors.success;
      bannerIcon = Icons.verified_user_rounded;
      badgeText = 'VERIFIED & ACTIVE';
      desc = verifiedDesc;
    } else if (isRejected) {
      bannerColor = AppColors.danger;
      bannerIcon = Icons.cancel_rounded;
      badgeText = 'ACTION REQUIRED';
      desc = rejectionReason ?? 'Document rejected. Please review requirements and upload a clearer photo.';
    } else if (isUnderReview) {
      bannerColor = AppColors.info;
      bannerIcon = Icons.hourglass_top_rounded;
      badgeText = 'UNDER REVIEW';
      desc = pendingDesc;
    }

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: bannerColor.withOpacity(0.12),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: bannerColor.withOpacity(0.35)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(bannerIcon, color: bannerColor, size: 22),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  title,
                  style: TextStyle(
                    color: bannerColor,
                    fontWeight: FontWeight.w900,
                    fontSize: 14,
                  ),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: bannerColor,
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  badgeText,
                  style: const TextStyle(
                    color: AppColors.backgroundDark,
                    fontWeight: FontWeight.w900,
                    fontSize: 10,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            desc,
            style: const TextStyle(color: AppColors.textLight, fontSize: 13, height: 1.4),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionHeader(IconData icon, String title) {
    return Row(
      children: [
        Icon(icon, color: AppColors.primary, size: 20),
        const SizedBox(width: 8),
        Text(
          title,
          style: const TextStyle(
            color: AppColors.textLight,
            fontSize: 15,
            fontWeight: FontWeight.w800,
          ),
        ),
      ],
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String label,
    required String hint,
    IconData? icon,
    TextInputType keyboardType = TextInputType.text,
  }) {
    return TextField(
      controller: controller,
      keyboardType: keyboardType,
      style: const TextStyle(color: AppColors.textLight, fontSize: 14),
      decoration: InputDecoration(
        labelText: label,
        hintText: hint,
        prefixIcon: icon != null ? Icon(icon, color: AppColors.textMuted, size: 20) : null,
        labelStyle: const TextStyle(color: AppColors.textMuted, fontSize: 13),
        hintStyle: const TextStyle(color: Colors.white24, fontSize: 13),
        filled: true,
        fillColor: AppColors.surfaceDark,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Colors.white12)),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Colors.white12)),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.primary)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      ),
    );
  }

  Widget _buildDateField({
    required TextEditingController controller,
    required String label,
    required String hint,
    required VoidCallback onTap,
  }) {
    return TextField(
      controller: controller,
      readOnly: true,
      onTap: onTap,
      style: const TextStyle(color: AppColors.textLight, fontSize: 14),
      decoration: InputDecoration(
        labelText: label,
        hintText: hint,
        prefixIcon: const Icon(Icons.calendar_today_rounded, color: AppColors.primary, size: 18),
        suffixIcon: const Icon(Icons.arrow_drop_down, color: AppColors.textMuted),
        labelStyle: const TextStyle(color: AppColors.textMuted, fontSize: 13),
        hintStyle: const TextStyle(color: Colors.white24, fontSize: 13),
        filled: true,
        fillColor: AppColors.surfaceDark,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Colors.white12)),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Colors.white12)),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.primary)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      ),
    );
  }

  Widget _buildImagePickerCard({
    required String title,
    required String subtitle,
    XFile? file,
    String? existingUrl,
    required VoidCallback onPick,
    required VoidCallback onRemove,
  }) {
    final hasImage = file != null || (existingUrl != null && existingUrl.isNotEmpty);

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.surfaceDark,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: hasImage ? AppColors.primary.withOpacity(0.4) : Colors.white12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  title,
                  style: const TextStyle(color: AppColors.textLight, fontWeight: FontWeight.bold, fontSize: 13),
                ),
              ),
              if (hasImage)
                GestureDetector(
                  onTap: onRemove,
                  child: const Padding(
                    padding: EdgeInsets.all(4),
                    child: Text('Clear', style: TextStyle(color: AppColors.danger, fontSize: 11, fontWeight: FontWeight.bold)),
                  ),
                ),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            subtitle,
            style: const TextStyle(color: AppColors.textMuted, fontSize: 11),
          ),
          const SizedBox(height: 10),

          if (hasImage) ...[
            ClipRRect(
              borderRadius: BorderRadius.circular(10),
              child: SizedBox(
                height: 110,
                width: double.infinity,
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    if (file != null)
                      (kIsWeb
                          ? Image.network(file.path, fit: BoxFit.cover)
                          : Image.file(File(file.path), fit: BoxFit.cover))
                    else if (existingUrl != null)
                      Image.network(
                        existingUrl,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => const Center(
                          child: Icon(Icons.broken_image_rounded, color: AppColors.textMuted),
                        ),
                      ),
                    Positioned(
                      bottom: 6,
                      right: 6,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: AppColors.backgroundDark.withOpacity(0.75),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.check_circle_rounded, color: AppColors.success, size: 12),
                            SizedBox(width: 4),
                            Text('Attached', style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold)),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 8),
            OutlinedButton.icon(
              onPressed: onPick,
              icon: const Icon(Icons.refresh_rounded, size: 14),
              label: const Text('Change Document Photo', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
              style: OutlinedButton.styleFrom(
                foregroundColor: AppColors.primary,
                side: const BorderSide(color: AppColors.primary),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                padding: const EdgeInsets.symmetric(vertical: 8),
              ),
            ),
          ] else ...[
            ElevatedButton.icon(
              onPressed: onPick,
              icon: const Icon(Icons.add_a_photo_rounded, size: 16),
              label: const Text('Take / Choose Photo', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary.withOpacity(0.15),
                foregroundColor: AppColors.primary,
                elevation: 0,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                padding: const EdgeInsets.symmetric(vertical: 12),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildSubmitButton({
    required String title,
    required bool isSubmitting,
    required VoidCallback onPressed,
  }) {
    return ElevatedButton(
      onPressed: isSubmitting ? null : onPressed,
      style: ElevatedButton.styleFrom(
        backgroundColor: AppColors.primary,
        foregroundColor: AppColors.backgroundDark,
        padding: const EdgeInsets.symmetric(vertical: 16),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        elevation: 2,
      ),
      child: isSubmitting
          ? const SizedBox(
              height: 22,
              width: 22,
              child: CircularProgressIndicator(strokeWidth: 2.5, color: AppColors.backgroundDark),
            )
          : Text(
              title,
              style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 15, letterSpacing: 0.3),
            ),
    );
  }
}
