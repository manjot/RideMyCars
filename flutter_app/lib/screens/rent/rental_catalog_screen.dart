import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../core/constants/countries_data.dart';
import '../../models/vehicle_model.dart';
import '../../providers/country_provider.dart';
import '../../services/rental_service.dart';
import '../../widgets/country_picker_modal.dart';
import 'rental_detail_screen.dart';

class RentalCatalogScreen extends StatefulWidget {
  final String? initialPickupLocation;
  final String? initialDropoffLocation;

  const RentalCatalogScreen({
    super.key,
    this.initialPickupLocation,
    this.initialDropoffLocation,
  });

  @override
  State<RentalCatalogScreen> createState() => _RentalCatalogScreenState();
}

class _RentalCatalogScreenState extends State<RentalCatalogScreen> {
  final _searchController = TextEditingController();
  List<VehicleModel> _allVehicles = [];
  bool _isLoading = true;

  String _selectedCategory = 'All';
  String _selectedTransmission = 'All';
  String _selectedFuel = 'All';
  String _selectedSeats = 'All';
  String _sortOption = 'recommended'; // 'recommended', 'price_asc', 'price_desc'

  DateTime _pickupDate = DateTime.now();
  DateTime _returnDate = DateTime.now().add(const Duration(days: 3));

  final List<String> _categories = ['All', 'Economy', 'Compact', 'Sedan', 'SUV', 'Luxury', 'Van'];
  final List<String> _transmissions = ['All', 'Automatic', 'Manual'];
  final List<String> _fuels = ['All', 'Petrol', 'Diesel', 'Hybrid', 'Electric'];
  final List<String> _seatsList = ['All', '4+', '5+', '7+'];

  @override
  void initState() {
    super.initState();
    _fetchVehicles();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _fetchVehicles() async {
    setState(() => _isLoading = true);
    final country = Provider.of<CountryProvider>(context, listen: false).selectedCountryCode;
    try {
      final vehicles = await RentalService.getAvailableVehicles(country: country);
      if (mounted) {
        setState(() {
          _allVehicles = vehicles;
          _isLoading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  List<VehicleModel> get _filteredVehicles {
    final query = _searchController.text.trim().toLowerCase();

    var list = _allVehicles.where((v) {
      if (query.isNotEmpty) {
        final title = '${v.make} ${v.model} ${v.category} ${v.year}'.toLowerCase();
        if (!title.contains(query)) return false;
      }

      if (_selectedCategory != 'All') {
        final selected = _selectedCategory.toLowerCase();
        final vCat = v.category.toLowerCase();
        final vType = v.type.toLowerCase();
        final matches = vCat == selected || vCat.contains(selected) || vType.contains(selected) ||
            (selected == 'economy' && (vCat.contains('compact') || vType.contains('compact') || vType.contains('economy'))) ||
            (selected == 'compact' && (vCat.contains('economy') || vType.contains('compact') || vType.contains('hatchback')));
        if (!matches) return false;
      }

      if (_selectedTransmission != 'All') {
        final trans = v.transmission.toLowerCase();
        if (_selectedTransmission == 'Automatic' && !trans.contains('auto')) return false;
        if (_selectedTransmission == 'Manual' && !trans.contains('man')) return false;
      }

      if (_selectedFuel != 'All') {
        if (!v.fuelType.toLowerCase().contains(_selectedFuel.toLowerCase())) return false;
      }

      if (_selectedSeats != 'All') {
        final minS = int.tryParse(_selectedSeats.replaceAll('+', '')) ?? 0;
        if (v.seats < minS) return false;
      }

      return true;
    }).toList();

    if (_sortOption == 'price_asc') {
      list.sort((a, b) => a.dailyRate.compareTo(b.dailyRate));
    } else if (_sortOption == 'price_desc') {
      list.sort((a, b) => b.dailyRate.compareTo(a.dailyRate));
    }

    return list;
  }

  int get _rentalDays => _returnDate.difference(_pickupDate).inDays.clamp(1, 90);

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final vehicles = _filteredVehicles;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0D1117) : const Color(0xFFF8FAFC),
      resizeToAvoidBottomInset: true,
      appBar: AppBar(
        title: const Text(
          'Rent a Car',
          style: TextStyle(fontWeight: FontWeight.w900, fontSize: 18),
        ),
        centerTitle: true,
        elevation: 0,
        actions: [
          Consumer<CountryProvider>(
            builder: (context, countryProv, _) {
              return TextButton.icon(
                onPressed: () {
                  showCountryPickerModal(
                    context: context,
                    selectedCountry: CountriesData.findByCode(countryProv.selectedCountryCode),
                    onSelect: (c) async {
                      await countryProv.setCountry(c.code, isManual: true);
                      _fetchVehicles();
                    },
                  );
                },
                icon: Text(countryProv.flag, style: const TextStyle(fontSize: 18)),
                label: Text(
                  countryProv.currencySymbol,
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                ),
              );
            },
          ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Refresh',
            onPressed: _fetchVehicles,
          ),
        ],
      ),
      body: GestureDetector(
        behavior: HitTestBehavior.translucent,
        onTap: () => FocusScope.of(context).unfocus(),
        child: Column(
        children: [
          // Dates & Search Header
          Container(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 14),
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF161B26) : Colors.white,
              border: Border(bottom: BorderSide(color: isDark ? Colors.white10 : Colors.grey.shade200)),
            ),
            child: Column(
              children: [
                // Dates Summary Bar
                GestureDetector(
                  onTap: _showDateRangePicker,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    decoration: BoxDecoration(
                      color: const Color(0xFF8B5CF6).withOpacity(0.08),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: const Color(0xFF8B5CF6).withOpacity(0.25)),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.date_range_rounded, color: Color(0xFF8B5CF6), size: 18),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            '${DateFormat('MMM d').format(_pickupDate)} - ${DateFormat('MMM d, yyyy').format(_returnDate)} ($_rentalDays days)',
                            style: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w800,
                              color: Color(0xFF8B5CF6),
                            ),
                          ),
                        ),
                        const Text(
                          'Change',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF8B5CF6),
                            decoration: TextDecoration.underline,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 10),

                // Search Bar & Sort
                Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: _searchController,
                        onChanged: (_) => setState(() {}),
                        decoration: InputDecoration(
                          hintText: 'Search by make, model, type...',
                          prefixIcon: const Icon(Icons.search, size: 20),
                          suffixIcon: _searchController.text.isNotEmpty
                              ? IconButton(
                                  icon: const Icon(Icons.clear, size: 18),
                                  onPressed: () {
                                    setState(() => _searchController.clear());
                                  },
                                )
                              : null,
                          isDense: true,
                          filled: true,
                          fillColor: isDark ? Colors.black26 : Colors.grey.shade100,
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: BorderSide.none,
                          ),
                          contentPadding: const EdgeInsets.symmetric(vertical: 10),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8),
                      decoration: BoxDecoration(
                        color: isDark ? Colors.black26 : Colors.grey.shade100,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: DropdownButtonHideUnderline(
                        child: DropdownButton<String>(
                          value: _sortOption,
                          icon: const Icon(Icons.sort, size: 18),
                          items: const [
                            DropdownMenuItem(value: 'recommended', child: Text('Sort: Best', style: TextStyle(fontSize: 12))),
                            DropdownMenuItem(value: 'price_asc', child: Text('Price: Low', style: TextStyle(fontSize: 12))),
                            DropdownMenuItem(value: 'price_desc', child: Text('Price: High', style: TextStyle(fontSize: 12))),
                          ],
                          onChanged: (val) {
                            if (val != null) setState(() => _sortOption = val);
                          },
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // Categories Chip List
          Container(
            height: 46,
            padding: const EdgeInsets.symmetric(vertical: 6),
            child: ListView.separated(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              scrollDirection: Axis.horizontal,
              itemCount: _categories.length,
              separatorBuilder: (_, __) => const SizedBox(width: 8),
              itemBuilder: (context, i) {
                final cat = _categories[i];
                final isSel = _selectedCategory == cat;
                return ChoiceChip(
                  label: Text(cat),
                  selected: isSel,
                  selectedColor: const Color(0xFF8B5CF6),
                  backgroundColor: isDark ? Colors.white10 : Colors.grey.shade100,
                  labelStyle: TextStyle(
                    fontSize: 12,
                    fontWeight: isSel ? FontWeight.w800 : FontWeight.w600,
                    color: isSel ? Colors.white : (isDark ? Colors.white70 : Colors.black87),
                  ),
                  onSelected: (val) => setState(() => _selectedCategory = cat),
                );
              },
            ),
          ),

          // Secondary Filters (Transmission, Fuel, Seats)
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _buildFilterDropdown(
                    label: 'Trans: $_selectedTransmission',
                    items: _transmissions,
                    selected: _selectedTransmission,
                    onSelected: (v) => setState(() => _selectedTransmission = v),
                    isDark: isDark,
                  ),
                  const SizedBox(width: 8),
                  _buildFilterDropdown(
                    label: 'Fuel: $_selectedFuel',
                    items: _fuels,
                    selected: _selectedFuel,
                    onSelected: (v) => setState(() => _selectedFuel = v),
                    isDark: isDark,
                  ),
                  const SizedBox(width: 8),
                  _buildFilterDropdown(
                    label: 'Seats: $_selectedSeats',
                    items: _seatsList,
                    selected: _selectedSeats,
                    onSelected: (v) => setState(() => _selectedSeats = v),
                    isDark: isDark,
                  ),
                  if (_selectedCategory != 'All' || _selectedTransmission != 'All' || _selectedFuel != 'All' || _selectedSeats != 'All') ...[
                    const SizedBox(width: 8),
                    ActionChip(
                      label: const Text('Reset', style: TextStyle(fontSize: 11, color: Colors.red)),
                      backgroundColor: Colors.red.withOpacity(0.08),
                      onPressed: () {
                        setState(() {
                          _selectedCategory = 'All';
                          _selectedTransmission = 'All';
                          _selectedFuel = 'All';
                          _selectedSeats = 'All';
                          _searchController.clear();
                        });
                      },
                    ),
                  ],
                ],
              ),
            ),
          ),

          // Vehicle List
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : vehicles.isEmpty
                    ? _buildEmptyState(isDark)
                    : RefreshIndicator(
                        onRefresh: _fetchVehicles,
                        child: ListView.separated(
                          keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
                          padding: const EdgeInsets.all(16),
                          itemCount: vehicles.length,
                          separatorBuilder: (_, __) => const SizedBox(height: 16),
                          itemBuilder: (context, index) {
                            return _buildVehicleCard(vehicles[index], isDark);
                          },
                        ),
                      ),
          ),
        ],
      ),
      ),
    );
  }

  Widget _buildFilterDropdown({
    required String label,
    required List<String> items,
    required String selected,
    required ValueChanged<String> onSelected,
    required bool isDark,
  }) {
    return PopupMenuButton<String>(
      onSelected: onSelected,
      itemBuilder: (_) => items
          .map((item) => PopupMenuItem(
                value: item,
                child: Text(
                  item,
                  style: TextStyle(fontWeight: item == selected ? FontWeight.bold : FontWeight.normal),
                ),
              ))
          .toList(),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        decoration: BoxDecoration(
          color: selected != 'All' ? const Color(0xFF8B5CF6).withOpacity(0.12) : (isDark ? Colors.white10 : Colors.grey.shade200),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(
            color: selected != 'All' ? const Color(0xFF8B5CF6) : Colors.transparent,
          ),
        ),
        child: Row(
          children: [
            Text(
              label,
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.bold,
                color: selected != 'All' ? const Color(0xFF8B5CF6) : null,
              ),
            ),
            const Icon(Icons.arrow_drop_down, size: 16),
          ],
        ),
      ),
    );
  }

  Widget _buildVehicleCard(VehicleModel v, bool isDark) {
    return Container(
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF161B26) : Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: isDark ? Colors.white10 : Colors.grey.shade200),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 14,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(22),
        child: InkWell(
          borderRadius: BorderRadius.circular(22),
          onTap: () {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) => RentalDetailScreen(
                  vehicle: v,
                  initialPickupLocation: widget.initialPickupLocation,
                  initialDropoffLocation: widget.initialDropoffLocation,
                ),
              ),
            );
          },
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Image & Badge Row
                Stack(
                  children: [
                    ClipRRect(
                      borderRadius: BorderRadius.circular(16),
                      child: Container(
                        height: 160,
                        width: double.infinity,
                        color: isDark ? Colors.black38 : Colors.grey.shade100,
                        child: v.imageUrl != null && v.imageUrl!.isNotEmpty
                            ? Image.network(
                                v.imageUrl!,
                                fit: BoxFit.cover,
                                errorBuilder: (_, __, ___) => _buildCarPlaceholder(),
                              )
                            : _buildCarPlaceholder(),
                      ),
                    ),
                    Positioned(
                      top: 10,
                      left: 10,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.black.withOpacity(0.75),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          v.category.toUpperCase(),
                          style: const TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.w900,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ),
                    Positioned(
                      bottom: 10,
                      right: 10,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: const Color(0xFF8B5CF6),
                          borderRadius: BorderRadius.circular(10),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(0.2),
                              blurRadius: 6,
                            ),
                          ],
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(
                              '${v.currencySymbol}${v.dailyRate.toStringAsFixed(0)}',
                              style: const TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w900,
                                color: Colors.white,
                              ),
                            ),
                            const Text(
                              '/day',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                color: Colors.white70,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),

                // Make & Model
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Text(
                        v.fullName,
                        style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w900),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    if (v.ownerName != null)
                      Text(
                        'By ${v.ownerName}',
                        style: TextStyle(fontSize: 11, color: isDark ? Colors.white54 : Colors.grey.shade600),
                      ),
                  ],
                ),
                const SizedBox(height: 10),

                // Specs Pills
                Wrap(
                  spacing: 6,
                  runSpacing: 6,
                  children: [
                    _buildSpecPill(Icons.settings, v.transmission, isDark),
                    _buildSpecPill(Icons.people, '${v.seats} Seats', isDark),
                    _buildSpecPill(Icons.local_gas_station, v.fuelType, isDark),
                    _buildSpecPill(Icons.luggage, '${v.luggage} Bags', isDark),
                    _buildSpecPill(Icons.verified_user, v.fuelPolicy, isDark),
                  ],
                ),
                const SizedBox(height: 14),

                // Action Bar
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Estimated Total ($_rentalDays days)',
                          style: TextStyle(
                            fontSize: 10,
                            color: isDark ? Colors.white54 : Colors.grey.shade600,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        Text(
                          '${v.currencySymbol}${(v.dailyRate * _rentalDays).toStringAsFixed(2)}',
                          style: TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.w900,
                            color: const Color(0xFF8B5CF6),
                          ),
                        ),
                      ],
                    ),
                    ElevatedButton(
                      onPressed: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => RentalDetailScreen(
                              vehicle: v,
                              initialPickupLocation: widget.initialPickupLocation,
                              initialDropoffLocation: widget.initialDropoffLocation,
                            ),
                          ),
                        );
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF8B5CF6),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 10),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        elevation: 2,
                      ),
                      child: const Row(
                        children: [
                          Text('Rent Now', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                          SizedBox(width: 4),
                          Icon(Icons.arrow_forward_rounded, size: 16),
                        ],
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

  Widget _buildSpecPill(IconData icon, String label, bool isDark) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: isDark ? Colors.white.withOpacity(0.06) : Colors.grey.shade100,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 12, color: isDark ? Colors.white54 : Colors.grey.shade600),
          const SizedBox(width: 4),
          Text(
            label,
            style: TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.w600,
              color: isDark ? Colors.white70 : Colors.black87,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCarPlaceholder() {
    return Center(
      child: Icon(
        Icons.directions_car_rounded,
        size: 60,
        color: Colors.grey.shade400,
      ),
    );
  }

  Widget _buildEmptyState(bool isDark) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.car_crash_outlined, size: 64, color: Colors.grey.shade400),
            const SizedBox(height: 14),
            const Text(
              'No Vehicles Matching Filters',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 6),
            Text(
              'Try changing your category, transmission, or fuel filter.',
              textAlign: TextAlign.center,
              style: TextStyle(color: isDark ? Colors.white54 : Colors.grey.shade600),
            ),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: () {
                setState(() {
                  _selectedCategory = 'All';
                  _selectedTransmission = 'All';
                  _selectedFuel = 'All';
                  _selectedSeats = 'All';
                  _searchController.clear();
                });
              },
              child: const Text('Reset All Filters'),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _showDateRangePicker() async {
    final picked = await showDateRangePicker(
      context: context,
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365)),
      initialDateRange: DateTimeRange(start: _pickupDate, end: _returnDate),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: ColorScheme.fromSeed(
              seedColor: const Color(0xFF8B5CF6),
              brightness: Theme.of(context).brightness,
            ),
          ),
          child: child!,
        );
      },
    );

    if (picked != null) {
      setState(() {
        _pickupDate = picked.start;
        _returnDate = picked.end;
      });
    }
  }
}
