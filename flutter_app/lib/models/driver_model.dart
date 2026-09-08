class DriverModel {
  final int id;
  final int userId;
  final String name;
  final String email;
  final String? avatarUrl;
  final String licenseNumber;
  final double hourlyRate;
  final double dailyRate;
  final double rating;
  final int totalTrips;
  final int experienceYears;
  final String bio;
  final bool isAvailable;
  final bool isVerified;
  final double weeklyRate;
  final String? phone;
  final String country;
  final String currencySymbol;

  DriverModel({
    required this.id,
    required this.userId,
    required this.name,
    required this.email,
    this.avatarUrl,
    this.phone,
    required this.licenseNumber,
    required this.hourlyRate,
    required this.dailyRate,
    required this.weeklyRate,
    required this.rating,
    required this.totalTrips,
    required this.experienceYears,
    required this.bio,
    required this.isAvailable,
    required this.isVerified,
    required this.country,
    this.currencySymbol = '\$',
  });

  factory DriverModel.fromJson(Map<String, dynamic> json) {
    final user = json['user'] is Map<String, dynamic> ? json['user'] as Map<String, dynamic> : {};
    final hourly = (json['hourly_rate'] is num)
        ? (json['hourly_rate'] as num).toDouble()
        : (json['rate'] is num)
            ? (json['rate'] as num).toDouble()
            : double.tryParse(json['hourly_rate']?.toString() ?? json['rate']?.toString() ?? '35') ?? 35.0;
    final daily = (json['daily_rate'] is num)
        ? (json['daily_rate'] as num).toDouble()
        : double.tryParse(json['daily_rate']?.toString() ?? '') ?? (hourly * 8 * 0.85);
    final weekly = (json['weekly_rate'] is num)
        ? (json['weekly_rate'] as num).toDouble()
        : double.tryParse(json['weekly_rate']?.toString() ?? '') ?? (daily * 7 * 0.85);

    String symbol = json['currency_symbol']?.toString() ?? '\$';
    final countryStr = json['country']?.toString() ?? 'USA';
    if (countryStr == 'IND' || countryStr == 'India') {
      symbol = '₹';
    } else if (countryStr == 'GBR' || countryStr == 'United Kingdom') {
      symbol = '£';
    } else if (countryStr == 'ARE' || countryStr == 'United Arab Emirates') {
      symbol = 'AED';
    }

    return DriverModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      userId: json['user_id'] is int ? json['user_id'] : int.tryParse(json['user_id']?.toString() ?? '0') ?? 0,
      name: user['name']?.toString() ?? json['name']?.toString() ?? 'Chauffeur Partner',
      email: user['email']?.toString() ?? json['email']?.toString() ?? '',
      avatarUrl: user['avatar_url']?.toString() ?? json['avatar_url']?.toString() ?? json['photo_url']?.toString(),
      phone: user['phone']?.toString() ?? json['phone']?.toString(),
      licenseNumber: json['license_number']?.toString() ?? json['license']?.toString() ?? 'DL-VERIFIED',
      hourlyRate: hourly,
      dailyRate: daily,
      weeklyRate: weekly,
      rating: (json['rating'] is num)
          ? (json['rating'] as num).toDouble()
          : double.tryParse(json['rating']?.toString() ?? '4.9') ?? 4.9,
      totalTrips: (json['total_trips'] is int)
          ? json['total_trips']
          : int.tryParse(json['total_trips']?.toString() ?? '50') ?? 50,
      experienceYears: (json['experience_years'] is int)
          ? json['experience_years']
          : int.tryParse(json['experience_years']?.toString() ?? '5') ?? 5,
      bio: json['bio']?.toString() ?? 'Professional background-verified executive chauffeur.',
      isAvailable: json['is_available'] == null || json['is_available'] == true || json['is_available'] == 1,
      isVerified: json['is_verified'] == null || json['is_verified'] == true || json['is_verified'] == 1,
      country: countryStr,
      currencySymbol: symbol,
    );
  }
}
