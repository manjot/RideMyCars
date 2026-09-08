class VehicleModel {
  final int id;
  final String make;
  final String model;
  final String year;
  final String category;
  final String type;
  final double dailyRate;
  final bool isAvailable;
  final String transmission;
  final String fuelType;
  final int seats;
  final int luggage;
  final int doors;
  final int minDriverAge;
  final double securityDeposit;
  final int dailyMileageLimit;
  final String? imageUrl;
  final String currencySymbol;
  final String? ownerName;
  final String fuelPolicy;

  VehicleModel({
    required this.id,
    required this.make,
    required this.model,
    required this.year,
    required this.category,
    required this.type,
    required this.dailyRate,
    required this.isAvailable,
    required this.transmission,
    required this.fuelType,
    required this.seats,
    required this.luggage,
    required this.doors,
    required this.minDriverAge,
    required this.securityDeposit,
    required this.dailyMileageLimit,
    this.imageUrl,
    this.currencySymbol = '\$',
    this.ownerName,
    this.fuelPolicy = 'Full-to-Full',
  });

  String get fullName => '$year $make $model';

  factory VehicleModel.fromJson(Map<String, dynamic> json) {
    return VehicleModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      make: json['make']?.toString() ?? '',
      model: json['model']?.toString() ?? '',
      year: json['year']?.toString() ?? '2024',
      category: json['category']?.toString() ?? 'Sedan',
      type: json['type']?.toString() ?? 'Sedan',
      dailyRate: (json['daily_rate'] is num)
          ? (json['daily_rate'] as num).toDouble()
          : double.tryParse(json['daily_rate']?.toString() ?? '0') ?? 0.0,
      isAvailable: json['is_available'] == true || json['is_available'] == 1 || json['is_available'] == '1',
      transmission: json['transmission']?.toString() ?? 'Auto',
      fuelType: json['fuel_type']?.toString() ?? 'Petrol',
      seats: (json['seats'] is int) ? json['seats'] : int.tryParse(json['seats']?.toString() ?? '5') ?? 5,
      luggage: (json['luggage'] is int) ? json['luggage'] : int.tryParse(json['luggage']?.toString() ?? '2') ?? 2,
      doors: (json['doors'] is int) ? json['doors'] : int.tryParse(json['doors']?.toString() ?? '4') ?? 4,
      minDriverAge: (json['min_driver_age'] is int)
          ? json['min_driver_age']
          : int.tryParse(json['min_driver_age']?.toString() ?? '18') ?? 18,
      securityDeposit: (json['security_deposit_amount'] is num)
          ? (json['security_deposit_amount'] as num).toDouble()
          : double.tryParse(json['security_deposit_amount']?.toString() ?? '200') ?? 200.0,
      dailyMileageLimit: (json['daily_mileage_limit'] is int)
          ? json['daily_mileage_limit']
          : int.tryParse(json['daily_mileage_limit']?.toString() ?? '300') ?? 300,
      imageUrl: json['image_url']?.toString(),
      currencySymbol: json['currency_symbol']?.toString() ?? '\$',
      ownerName: json['owner_name']?.toString() ?? (json['owner'] is Map ? json['owner']['name']?.toString() : null),
      fuelPolicy: json['fuel_policy']?.toString() ?? 'Full-to-Full',
    );
  }
}
