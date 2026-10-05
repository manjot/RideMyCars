import 'package:flutter/material.dart';
import '../../../core/constants/app_colors.dart';
import '../../../services/places_service.dart';

class SavedLocationResult {
  final String locationType; // 'home' or 'office'
  final String address;
  final double? lat;
  final double? lng;

  SavedLocationResult({
    required this.locationType,
    required this.address,
    this.lat,
    this.lng,
  });
}

class SavedLocationModal extends StatefulWidget {
  final String locationType; // 'home' or 'office'
  final String? currentAddress;
  final double? currentLat;
  final double? currentLng;

  const SavedLocationModal({
    super.key,
    required this.locationType,
    this.currentAddress,
    this.currentLat,
    this.currentLng,
  });

  static Future<SavedLocationResult?> show(
    BuildContext context, {
    required String locationType,
    String? currentAddress,
    double? currentLat,
    double? currentLng,
  }) {
    return showModalBottomSheet<SavedLocationResult>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => SavedLocationModal(
        locationType: locationType,
        currentAddress: currentAddress,
        currentLat: currentLat,
        currentLng: currentLng,
      ),
    );
  }

  @override
  State<SavedLocationModal> createState() => _SavedLocationModalState();
}

class _SavedLocationModalState extends State<SavedLocationModal> {
  late TextEditingController _addressController;
  double? _selectedLat;
  double? _selectedLng;
  List<PlacePrediction> _predictions = [];
  bool _isSearching = false;

  @override
  void initState() {
    super.initState();
    _addressController = TextEditingController(text: widget.currentAddress ?? '');
    _selectedLat = widget.currentLat;
    _selectedLng = widget.currentLng;
  }

  @override
  void dispose() {
    _addressController.dispose();
    super.dispose();
  }

  Future<void> _searchPlaces(String val) async {
    if (val.trim().isEmpty) {
      setState(() => _predictions = []);
      return;
    }
    setState(() => _isSearching = true);
    final results = await PlacesService.getAutocomplete(val.trim());
    if (mounted) {
      setState(() {
        _predictions = results;
        _isSearching = false;
      });
    }
  }

  Future<void> _selectPrediction(PlacePrediction p) async {
    _addressController.text = p.description;
    final details = await PlacesService.getPlaceDetails(p.placeId);
    if (details != null && mounted) {
      setState(() {
        _selectedLat = details.lat;
        _selectedLng = details.lng;
        _predictions = [];
      });
    } else if (mounted) {
      setState(() => _predictions = []);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isHome = widget.locationType == 'home';
    final title = isHome ? 'Save Home Address' : 'Save Office Address';
    final emoji = isHome ? '🏠' : '🏢';

    return Container(
      padding: EdgeInsets.only(
        bottom: MediaQuery.of(context).viewInsets.bottom + 20,
        top: 18,
        left: 20,
        right: 20,
      ),
      decoration: const BoxDecoration(
        color: Color(0xFF0F172A),
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
        boxShadow: [
          BoxShadow(
            color: Colors.black54,
            blurRadius: 30,
            offset: Offset(0, -6),
          ),
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Drag handle
          Center(
            child: Container(
              width: 44,
              height: 4,
              margin: const EdgeInsets.only(bottom: 14),
              decoration: BoxDecoration(
                color: Colors.white24,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),

          // Header
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Text(emoji, style: const TextStyle(fontSize: 18)),
                  const SizedBox(width: 8),
                  Text(
                    title.toUpperCase(),
                    style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.w900,
                      fontSize: 13,
                      letterSpacing: 0.5,
                    ),
                  ),
                ],
              ),
              GestureDetector(
                onTap: () => Navigator.pop(context),
                child: Container(
                  padding: const EdgeInsets.all(6),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.08),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.close, color: Colors.white70, size: 16),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Address Input
          TextField(
            controller: _addressController,
            onChanged: _searchPlaces,
            style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold),
            decoration: InputDecoration(
              hintText: 'Enter street, building, or area...',
              hintStyle: const TextStyle(color: Colors.white38, fontSize: 12),
              filled: true,
              fillColor: const Color(0xFF1E293B),
              prefixIcon: Icon(isHome ? Icons.home_rounded : Icons.business_rounded, color: const Color(0xFFFFDC00), size: 20),
              suffixIcon: _isSearching
                  ? const Padding(
                      padding: EdgeInsets.all(12),
                      child: SizedBox(
                        width: 16,
                        height: 16,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFFFFDC00)),
                      ),
                    )
                  : _addressController.text.isNotEmpty
                      ? IconButton(
                          icon: const Icon(Icons.clear, color: Colors.white38, size: 18),
                          onPressed: () {
                            _addressController.clear();
                            setState(() {
                              _selectedLat = null;
                              _selectedLng = null;
                              _predictions = [];
                            });
                          },
                        )
                      : null,
              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: const BorderSide(color: Colors.white24),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: const BorderSide(color: Colors.white24),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: const BorderSide(color: Color(0xFFFFDC00), width: 1.5),
              ),
            ),
          ),

          // Autocomplete predictions list
          if (_predictions.isNotEmpty) ...[
            const SizedBox(height: 8),
            Container(
              constraints: const BoxConstraints(maxHeight: 180),
              decoration: BoxDecoration(
                color: const Color(0xFF1E293B),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: Colors.white10),
              ),
              child: ListView.separated(
                shrinkWrap: true,
                itemCount: _predictions.length,
                separatorBuilder: (_, __) => const Divider(color: Colors.white10, height: 1),
                itemBuilder: (ctx, i) {
                  final p = _predictions[i];
                  return ListTile(
                    dense: true,
                    leading: const Icon(Icons.location_on_outlined, color: Color(0xFFFFDC00), size: 18),
                    title: Text(
                      p.mainText,
                      style: const TextStyle(color: Colors.white, fontSize: 12.5, fontWeight: FontWeight.bold),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    subtitle: p.secondaryText.isNotEmpty
                        ? Text(
                            p.secondaryText,
                            style: const TextStyle(color: AppColors.textMuted, fontSize: 10.5),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          )
                        : null,
                    onTap: () => _selectPrediction(p),
                  );
                },
              ),
            ),
          ],
          const SizedBox(height: 16),

          // Save Button: Yellow with Black Text
          SizedBox(
            width: double.infinity,
            height: 48,
            child: ElevatedButton(
              onPressed: () {
                final addr = _addressController.text.trim();
                if (addr.isEmpty) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Please enter an address')),
                  );
                  return;
                }
                Navigator.pop(
                  context,
                  SavedLocationResult(
                    locationType: widget.locationType,
                    address: addr,
                    lat: _selectedLat,
                    lng: _selectedLng,
                  ),
                );
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFFFFDC00),
                foregroundColor: Colors.black,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(14),
                ),
                elevation: 3,
              ),
              child: const Text(
                'Save Address ✓',
                style: TextStyle(
                  color: Colors.black,
                  fontWeight: FontWeight.w900,
                  fontSize: 14.5,
                ),
              ),
            ),
          ),
          const SizedBox(height: 10),
        ],
      ),
    );
  }
}
