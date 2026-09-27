import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/driver_model.dart';
import '../../providers/country_provider.dart';
import '../../services/driver_service.dart';
import 'driver_detail_screen.dart';

class HireDriverCatalogScreen extends StatefulWidget {
  final String? initialPickupLocation;
  final String? initialDropoffLocation;

  const HireDriverCatalogScreen({
    super.key,
    this.initialPickupLocation,
    this.initialDropoffLocation,
  });

  @override
  State<HireDriverCatalogScreen> createState() => _HireDriverCatalogScreenState();
}

class _HireDriverCatalogScreenState extends State<HireDriverCatalogScreen> {
  final _searchController = TextEditingController();
  List<DriverModel> _allDrivers = [];
  bool _isLoading = true;

  String _selectedFilter = 'All'; // 'All', 'Top Rated', 'City Duty', 'Executive'
  String _sortOption = 'rating_desc'; // 'rating_desc', 'price_asc', 'price_desc'

  final List<String> _filters = ['All', 'Top Rated', 'City Duty', 'Executive'];

  @override
  void initState() {
    super.initState();
    _fetchDrivers();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _fetchDrivers() async {
    setState(() => _isLoading = true);
    final country = Provider.of<CountryProvider>(context, listen: false).selectedCountryCode;
    try {
      final drivers = await DriverService.getDrivers(country: country);
      if (mounted) {
        setState(() {
          _allDrivers = drivers;
          _isLoading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  List<DriverModel> get _filteredDrivers {
    final query = _searchController.text.trim().toLowerCase();

    var list = _allDrivers.where((d) {
      if (query.isNotEmpty) {
        final matches = d.name.toLowerCase().contains(query) ||
            d.bio.toLowerCase().contains(query) ||
            d.licenseNumber.toLowerCase().contains(query);
        if (!matches) return false;
      }

      if (_selectedFilter == 'Top Rated' && d.rating < 4.8) {
        return false;
      } else if (_selectedFilter == 'Executive' && d.hourlyRate < 40.0) {
        return false;
      } else if (_selectedFilter == 'City Duty' && d.hourlyRate >= 40.0) {
        return false;
      }

      return true;
    }).toList();

    if (_sortOption == 'price_asc') {
      list.sort((a, b) => a.hourlyRate.compareTo(b.hourlyRate));
    } else if (_sortOption == 'price_desc') {
      list.sort((a, b) => b.hourlyRate.compareTo(a.hourlyRate));
    } else {
      list.sort((a, b) => b.rating.compareTo(a.rating));
    }

    return list;
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final drivers = _filteredDrivers;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0D1117) : const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text(
          'Hire a Chauffeur',
          style: TextStyle(fontWeight: FontWeight.w900, fontSize: 18),
        ),
        centerTitle: true,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Refresh',
            onPressed: _fetchDrivers,
          ),
        ],
      ),
      body: Column(
        children: [
          // Search & Filter Header
          Container(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 14),
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF161B26) : Colors.white,
              border: Border(bottom: BorderSide(color: isDark ? Colors.white10 : Colors.grey.shade200)),
            ),
            child: Column(
              children: [
                Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: _searchController,
                        onChanged: (_) => setState(() {}),
                        decoration: InputDecoration(
                          hintText: 'Search by chauffeur name, duty...',
                          prefixIcon: const Icon(Icons.search, size: 20),
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
                            DropdownMenuItem(value: 'rating_desc', child: Text('Rating', style: TextStyle(fontSize: 12))),
                            DropdownMenuItem(value: 'price_asc', child: Text('Rate: Low', style: TextStyle(fontSize: 12))),
                            DropdownMenuItem(value: 'price_desc', child: Text('Rate: High', style: TextStyle(fontSize: 12))),
                          ],
                          onChanged: (val) {
                            if (val != null) setState(() => _sortOption = val);
                          },
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),

                // Duty Chips
                Row(
                  children: _filters.map((f) {
                    final isSel = _selectedFilter == f;
                    return Padding(
                      padding: const EdgeInsets.only(right: 8),
                      child: ChoiceChip(
                        label: Text(f),
                        selected: isSel,
                        selectedColor: const Color(0xFF0EA5E9),
                        backgroundColor: isDark ? Colors.white10 : Colors.grey.shade100,
                        labelStyle: TextStyle(
                          fontSize: 12,
                          fontWeight: isSel ? FontWeight.w800 : FontWeight.w600,
                          color: isSel ? Colors.white : (isDark ? Colors.white70 : Colors.black87),
                        ),
                        onSelected: (val) => setState(() => _selectedFilter = f),
                      ),
                    );
                  }).toList(),
                ),
              ],
            ),
          ),

          // Drivers List
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : drivers.isEmpty
                    ? _buildEmptyState(isDark)
                    : RefreshIndicator(
                        onRefresh: _fetchDrivers,
                        child: ListView.separated(
                          padding: const EdgeInsets.all(16),
                          itemCount: drivers.length,
                          separatorBuilder: (_, __) => const SizedBox(height: 14),
                          itemBuilder: (context, index) {
                            return _buildDriverCard(drivers[index], isDark);
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildDriverCard(DriverModel d, bool isDark) {
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
                builder: (_) => DriverDetailScreen(
                  driver: d,
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
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Avatar with badge
                    Stack(
                      children: [
                        CircleAvatar(
                          radius: 28,
                          backgroundColor: const Color(0xFF0EA5E9).withOpacity(0.15),
                          backgroundImage: d.avatarUrl != null && d.avatarUrl!.isNotEmpty
                              ? NetworkImage(d.avatarUrl!)
                              : null,
                          child: (d.avatarUrl == null || d.avatarUrl!.isEmpty)
                              ? Text(
                                  d.name.isNotEmpty ? d.name.substring(0, 1).toUpperCase() : 'D',
                                  style: const TextStyle(
                                    fontSize: 20,
                                    fontWeight: FontWeight.bold,
                                    color: Color(0xFF0EA5E9),
                                  ),
                                )
                              : null,
                        ),
                        if (d.isVerified)
                          Positioned(
                            bottom: 0,
                            right: 0,
                            child: Container(
                              padding: const EdgeInsets.all(2),
                              decoration: const BoxDecoration(
                                color: Colors.blue,
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.check, size: 12, color: Colors.white),
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(width: 14),

                    // Driver Name & Stats
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Expanded(
                                child: Text(
                                  d.name,
                                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(
                                  color: Colors.amber.withOpacity(0.14),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Row(
                                  children: [
                                    const Icon(Icons.star_rounded, size: 14, color: Colors.amber),
                                    const SizedBox(width: 3),
                                    Text(
                                      d.rating.toStringAsFixed(1),
                                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w900),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 3),
                          Text(
                            '${d.experienceYears} Years Exp • ${d.totalTrips}+ Trips • ${d.licenseNumber}',
                            style: TextStyle(fontSize: 11, color: isDark ? Colors.white60 : Colors.black54),
                          ),
                          const SizedBox(height: 4),
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                decoration: BoxDecoration(
                                  color: Colors.green.withOpacity(0.12),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: const Text(
                                  'VERIFIED CHAUFFEUR',
                                  style: TextStyle(
                                    fontSize: 9,
                                    fontWeight: FontWeight.w900,
                                    color: Colors.green,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),

                // Bio
                Text(
                  d.bio,
                  style: TextStyle(
                    fontSize: 12,
                    color: isDark ? Colors.white70 : Colors.black87,
                    height: 1.3,
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 14),

                // Rate & Action Row
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Text(
                              '${d.currencySymbol}${d.hourlyRate.toStringAsFixed(0)}',
                              style: const TextStyle(
                                fontSize: 18,
                                fontWeight: FontWeight.w900,
                                color: Color(0xFF0EA5E9),
                              ),
                            ),
                            const Text(
                              '/hr',
                              style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                            ),
                            const SizedBox(width: 8),
                            Text(
                              '• ${d.currencySymbol}${d.dailyRate.toStringAsFixed(0)}/day',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w600,
                                color: isDark ? Colors.white54 : Colors.grey.shade600,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                    ElevatedButton(
                      onPressed: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => DriverDetailScreen(
                              driver: d,
                              initialPickupLocation: widget.initialPickupLocation,
                              initialDropoffLocation: widget.initialDropoffLocation,
                            ),
                          ),
                        );
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF0EA5E9),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 10),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        elevation: 2,
                      ),
                      child: const Row(
                        children: [
                          Text('Book Driver', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
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

  Widget _buildEmptyState(bool isDark) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.person_off_outlined, size: 64, color: Colors.grey.shade400),
            const SizedBox(height: 14),
            const Text(
              'No Chauffeurs Found',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 6),
            Text(
              'Try changing your duty filter or search terms.',
              textAlign: TextAlign.center,
              style: TextStyle(color: isDark ? Colors.white54 : Colors.grey.shade600),
            ),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: () {
                setState(() {
                  _selectedFilter = 'All';
                  _searchController.clear();
                });
              },
              child: const Text('Reset Filters'),
            ),
          ],
        ),
      ),
    );
  }
}
