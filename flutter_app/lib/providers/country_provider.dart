import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import '../core/api/api_client.dart';
import '../core/constants/api_constants.dart';

class SupportedCountry {
  final String name;
  final String code;
  final String symbol;
  final String currency;
  final String flag;
  final double defaultMultiplier;
  final double defaultProtectionRate;
  final double defaultExtraDriverRate;
  final double defaultChildSeatRate;
  final double defaultGpsRate;

  const SupportedCountry({
    required this.name,
    required this.code,
    required this.symbol,
    required this.currency,
    required this.flag,
    this.defaultMultiplier = 1.0,
    this.defaultProtectionRate = 12.0,
    this.defaultExtraDriverRate = 10.0,
    this.defaultChildSeatRate = 8.0,
    this.defaultGpsRate = 5.0,
  });
}

class CountryProvider extends ChangeNotifier {
  final Dio _dio = ApiClient().dio;

  static const List<SupportedCountry> supportedCountries = [
    SupportedCountry(
      name: 'United States',
      code: 'USA',
      symbol: '\$',
      currency: 'USD',
      flag: '🇺🇸',
      defaultMultiplier: 1.0,
      defaultProtectionRate: 12.0,
      defaultExtraDriverRate: 10.0,
      defaultChildSeatRate: 8.0,
      defaultGpsRate: 5.0,
    ),
    SupportedCountry(
      name: 'India',
      code: 'IND',
      symbol: '₹',
      currency: 'INR',
      flag: '🇮🇳',
      defaultMultiplier: 83.5,
      defaultProtectionRate: 450.0,
      defaultExtraDriverRate: 300.0,
      defaultChildSeatRate: 200.0,
      defaultGpsRate: 150.0,
    ),
    SupportedCountry(
      name: 'United Kingdom',
      code: 'GBR',
      symbol: '£',
      currency: 'GBP',
      flag: '🇬🇧',
      defaultMultiplier: 0.78,
      defaultProtectionRate: 9.5,
      defaultExtraDriverRate: 8.0,
      defaultChildSeatRate: 6.5,
      defaultGpsRate: 4.0,
    ),
    SupportedCountry(
      name: 'Canada',
      code: 'CAN',
      symbol: 'C\$',
      currency: 'CAD',
      flag: '🇨🇦',
      defaultMultiplier: 1.36,
      defaultProtectionRate: 16.0,
      defaultExtraDriverRate: 13.5,
      defaultChildSeatRate: 11.0,
      defaultGpsRate: 7.0,
    ),
    SupportedCountry(
      name: 'Australia',
      code: 'AUS',
      symbol: 'A\$',
      currency: 'AUD',
      flag: '🇦🇺',
      defaultMultiplier: 1.52,
      defaultProtectionRate: 18.0,
      defaultExtraDriverRate: 15.0,
      defaultChildSeatRate: 12.0,
      defaultGpsRate: 7.5,
    ),
    SupportedCountry(
      name: 'United Arab Emirates',
      code: 'ARE',
      symbol: 'AED',
      currency: 'AED',
      flag: '🇦🇪',
      defaultMultiplier: 3.67,
      defaultProtectionRate: 45.0,
      defaultExtraDriverRate: 36.0,
      defaultChildSeatRate: 29.0,
      defaultGpsRate: 18.0,
    ),
    SupportedCountry(
      name: 'Germany',
      code: 'DEU',
      symbol: '€',
      currency: 'EUR',
      flag: '🇩🇪',
      defaultMultiplier: 0.92,
      defaultProtectionRate: 11.0,
      defaultExtraDriverRate: 9.0,
      defaultChildSeatRate: 7.5,
      defaultGpsRate: 4.5,
    ),
    SupportedCountry(
      name: 'France',
      code: 'FRA',
      symbol: '€',
      currency: 'EUR',
      flag: '🇫🇷',
      defaultMultiplier: 0.92,
      defaultProtectionRate: 11.0,
      defaultExtraDriverRate: 9.0,
      defaultChildSeatRate: 7.5,
      defaultGpsRate: 4.5,
    ),
    SupportedCountry(
      name: 'Singapore',
      code: 'SGP',
      symbol: 'S\$',
      currency: 'SGD',
      flag: '🇸🇬',
      defaultMultiplier: 1.35,
      defaultProtectionRate: 16.0,
      defaultExtraDriverRate: 13.5,
      defaultChildSeatRate: 11.0,
      defaultGpsRate: 7.0,
    ),
    SupportedCountry(
      name: 'Japan',
      code: 'JPN',
      symbol: '¥',
      currency: 'JPY',
      flag: '🇯🇵',
      defaultMultiplier: 155.0,
      defaultProtectionRate: 1800.0,
      defaultExtraDriverRate: 1500.0,
      defaultChildSeatRate: 1200.0,
      defaultGpsRate: 750.0,
    ),
    SupportedCountry(
      name: 'Ghana',
      code: 'GHA',
      symbol: 'GH₵',
      currency: 'GHS',
      flag: '🇬🇭',
      defaultMultiplier: 18.2,
      defaultProtectionRate: 220.0,
      defaultExtraDriverRate: 180.0,
      defaultChildSeatRate: 145.0,
      defaultGpsRate: 90.0,
    ),
  ];

  String _selectedCountryCode = 'USA';
  String _selectedCountryName = 'United States';
  String _currencySymbol = '\$';
  String _currencyCode = 'USD';
  String _flag = '🇺🇸';
  double _currentMultiplier = 1.0;
  double _currentProtectionRate = 12.0;
  double _currentExtraDriverRate = 10.0;
  double _currentChildSeatRate = 8.0;
  double _currentGpsRate = 5.0;

  Map<String, dynamic>? _pricingData;
  bool _isLoading = false;

  String get selectedCountryCode => _selectedCountryCode;
  String get selectedCountryName => _selectedCountryName;
  String get currencySymbol => _currencySymbol;
  String get currencyCode => _currencyCode;
  String get flag => _flag;
  bool get isLoading => _isLoading;
  Map<String, dynamic>? get pricingData => _pricingData;

  double get rentalMultiplier =>
      (pricingData?['rental_price_multiplier'] as num?)?.toDouble() ?? _currentMultiplier;

  double get protectionDailyRate =>
      (pricingData?['rental_protection_daily_rate'] as num?)?.toDouble() ?? _currentProtectionRate;

  double get additionalDriverDailyRate =>
      (pricingData?['rental_additional_driver_rate'] as num?)?.toDouble() ?? _currentExtraDriverRate;

  double get childSeatDailyRate =>
      (pricingData?['rental_child_seat_rate'] as num?)?.toDouble() ?? _currentChildSeatRate;

  double get gpsDailyRate =>
      (pricingData?['rental_gps_rate'] as num?)?.toDouble() ?? _currentGpsRate;

  CountryProvider() {
    fetchPricing('USA');
  }

  Future<void> setCountry(String countryCode) async {
    final country = supportedCountries.firstWhere(
      (c) => c.code.toUpperCase() == countryCode.toUpperCase(),
      orElse: () => supportedCountries.first,
    );

    _selectedCountryCode = country.code;
    _selectedCountryName = country.name;
    _currencySymbol = country.symbol;
    _currencyCode = country.currency;
    _flag = country.flag;
    _currentMultiplier = country.defaultMultiplier;
    _currentProtectionRate = country.defaultProtectionRate;
    _currentExtraDriverRate = country.defaultExtraDriverRate;
    _currentChildSeatRate = country.defaultChildSeatRate;
    _currentGpsRate = country.defaultGpsRate;
    _pricingData = null;
    notifyListeners();

    await fetchPricing(country.code);
  }

  Future<void> fetchPricing(String countryCode) async {
    _isLoading = true;
    notifyListeners();

    try {
      final res = await _dio.get('${ApiConstants.baseUrl}/api/country-pricing', queryParameters: {
        'country': countryCode,
      });

      if (res.statusCode == 200 && res.data != null && res.data['data'] != null) {
        _pricingData = Map<String, dynamic>.from(res.data['data']);
        if (_pricingData!['currency_symbol'] != null) {
          _currencySymbol = _pricingData!['currency_symbol'].toString();
        }
        if (_pricingData!['currency_code'] != null) {
          _currencyCode = _pricingData!['currency_code'].toString();
        }
      }
    } catch (e) {
      debugPrint('Error fetching country pricing for $countryCode: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  double get priceMultiplier => rentalMultiplier;
  double get deliveryMultiplier => rentalMultiplier;

  String formatPrice(double amount, {int decimals = 2}) {
    return '$_currencySymbol${amount.toStringAsFixed(decimals)}';
  }

  String formatAmount(double amount, {int decimals = 2}) {
    return formatPrice(amount, decimals: decimals);
  }
}
