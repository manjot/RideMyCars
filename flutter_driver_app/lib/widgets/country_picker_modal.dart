import 'package:flutter/material.dart';
import '../core/constants/app_colors.dart';
import '../core/constants/countries_data.dart';

void showCountryPickerModal({
  required BuildContext context,
  required Country selectedCountry,
  required ValueChanged<Country> onSelect,
}) {
  showModalBottomSheet(
    context: context,
    isScrollControlled: true,
    backgroundColor: AppColors.surfaceDark,
    shape: const RoundedRectangleBorder(
      borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
    ),
    builder: (ctx) {
      String query = '';
      return StatefulBuilder(
        builder: (context, setModalState) {
          final filtered = CountriesData.allCountries.where((c) {
            final q = query.toLowerCase().trim();
            if (q.isEmpty) return true;
            return c.name.toLowerCase().contains(q) ||
                c.dial.contains(q) ||
                c.code.toLowerCase().contains(q);
          }).toList();

          return SizedBox(
            height: MediaQuery.of(context).size.height * 0.75,
            child: Column(
              children: [
                const SizedBox(height: 12),
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
                Padding(
                  padding: const EdgeInsets.fromLTRB(20, 16, 20, 12),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Select Country',
                        style: TextStyle(
                          color: AppColors.textLight,
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.close_rounded, color: AppColors.textMuted),
                        onPressed: () => Navigator.pop(ctx),
                      ),
                    ],
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  child: TextField(
                    autofocus: false,
                    style: const TextStyle(color: AppColors.textLight, fontSize: 14),
                    onChanged: (val) => setModalState(() => query = val),
                    decoration: InputDecoration(
                      hintText: 'Search country name or code (+1, US)...',
                      hintStyle: const TextStyle(color: AppColors.textMuted, fontSize: 13),
                      prefixIcon: const Icon(Icons.search_rounded, color: AppColors.textMuted, size: 20),
                      filled: true,
                      fillColor: AppColors.backgroundDark,
                      contentPadding: const EdgeInsets.symmetric(vertical: 12),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(14),
                        borderSide: BorderSide.none,
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 10),
                Expanded(
                  child: ListView.separated(
                    itemCount: filtered.length,
                    separatorBuilder: (_, __) => Divider(
                      color: Colors.white.withValues(alpha: 0.05),
                      height: 1,
                    ),
                    itemBuilder: (context, idx) {
                      final item = filtered[idx];
                      final isSelected = item.code == selectedCountry.code && item.dial == selectedCountry.dial;
                      return ListTile(
                        onTap: () {
                          onSelect(item);
                          Navigator.pop(ctx);
                        },
                        leading: Text(item.flag, style: const TextStyle(fontSize: 26)),
                        title: Text(
                          item.name,
                          style: TextStyle(
                            color: isSelected ? AppColors.primary : AppColors.textLight,
                            fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                            fontSize: 15,
                          ),
                        ),
                        trailing: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(
                                color: isSelected
                                    ? AppColors.primary.withValues(alpha: 0.2)
                                    : Colors.white.withValues(alpha: 0.08),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                item.dial,
                                style: TextStyle(
                                  color: isSelected ? AppColors.primary : AppColors.textMuted,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 13,
                                ),
                              ),
                            ),
                            if (isSelected) ...[
                              const SizedBox(width: 8),
                              const Icon(Icons.check_rounded, color: AppColors.primary, size: 18),
                            ],
                          ],
                        ),
                      );
                    },
                  ),
                ),
              ],
            ),
          );
        },
      );
    },
  );
}
