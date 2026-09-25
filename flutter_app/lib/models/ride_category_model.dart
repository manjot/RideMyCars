import 'package:flutter/material.dart';

class RideCategoryModel {
  final String id;
  final String slug;
  final String categoryKey;
  final String name;
  final String icon;
  final String capacity;
  final String luggage;
  final int etaMinutes;
  final double baseFare;
  final double perKmRate;
  final double perMinuteRate;
  final double minimumFare;
  final double multiplier;
  final double fare;
  final String fareFormatted;
  final String description;
  final String target;

  const RideCategoryModel({
    required this.id,
    required this.slug,
    required this.categoryKey,
    required this.name,
    required this.icon,
    required this.capacity,
    this.luggage = '2 Bags',
    this.etaMinutes = 4,
    this.baseFare = 4.5,
    this.perKmRate = 1.1,
    this.perMinuteRate = 0.2,
    this.minimumFare = 8.5,
    this.multiplier = 1.0,
    required this.fare,
    required this.fareFormatted,
    this.description = '',
    this.target = '',
  });

  factory RideCategoryModel.fromJson(Map<String, dynamic> json, {String defaultSymbol = '\$'}) {
    final fareVal = (json['fare'] as num?)?.toDouble() ??
        (json['default_fare'] as num?)?.toDouble() ??
        (json['minimum_fare'] as num?)?.toDouble() ??
        10.0;

    final symbol = json['currency_symbol']?.toString() ?? defaultSymbol;
    final formatted = json['fare_formatted']?.toString() ??
        json['default_fare_formatted']?.toString() ??
        '$symbol${fareVal.toStringAsFixed(2)}';

    return RideCategoryModel(
      id: json['id']?.toString() ?? json['slug']?.toString() ?? 'economy',
      slug: json['slug']?.toString() ?? json['category_key']?.toString() ?? 'economy',
      categoryKey: json['category_key']?.toString() ?? json['slug']?.toString() ?? 'economy',
      name: json['name']?.toString() ?? 'Standard',
      icon: json['icon']?.toString() ?? '🚗',
      capacity: json['capacity']?.toString() ?? json['seats']?.toString() ?? '1–4 seats',
      luggage: json['luggage']?.toString() ?? '2 Bags',
      etaMinutes: (json['eta_minutes'] as num?)?.toInt() ?? 4,
      baseFare: (json['base_fare'] as num?)?.toDouble() ?? 4.5,
      perKmRate: (json['per_km_rate'] as num?)?.toDouble() ?? 1.1,
      perMinuteRate: (json['per_minute_rate'] as num?)?.toDouble() ?? 0.2,
      minimumFare: (json['minimum_fare'] as num?)?.toDouble() ?? 8.5,
      multiplier: (json['multiplier'] as num?)?.toDouble() ?? 1.0,
      fare: fareVal,
      fareFormatted: formatted,
      description: json['description']?.toString() ?? '',
      target: json['target']?.toString() ?? '',
    );
  }

  IconData get displayIcon {
    final lower = (slug.isNotEmpty ? slug : name).toLowerCase();
    if (lower.contains('bus') || lower.contains('group')) {
      return Icons.directions_bus_filled_rounded;
    } else if (lower.contains('van') || lower.contains('xl')) {
      return Icons.airport_shuttle_rounded;
    } else if (lower.contains('vip') || lower.contains('chauffeur')) {
      return Icons.military_tech_rounded;
    } else if (lower.contains('suv') || lower.contains('luxury')) {
      return Icons.directions_car_filled_rounded;
    } else if (lower.contains('standard') || lower.contains('comfort')) {
      return Icons.drive_eta_rounded;
    } else {
      return Icons.directions_car_rounded;
    }
  }

  RideCategoryModel copyWith({
    double? fare,
    String? fareFormatted,
    int? etaMinutes,
  }) {
    return RideCategoryModel(
      id: id,
      slug: slug,
      categoryKey: categoryKey,
      name: name,
      icon: icon,
      capacity: capacity,
      luggage: luggage,
      etaMinutes: etaMinutes ?? this.etaMinutes,
      baseFare: baseFare,
      perKmRate: perKmRate,
      perMinuteRate: perMinuteRate,
      minimumFare: minimumFare,
      multiplier: multiplier,
      fare: fare ?? this.fare,
      fareFormatted: fareFormatted ?? this.fareFormatted,
      description: description,
      target: target,
    );
  }
}
