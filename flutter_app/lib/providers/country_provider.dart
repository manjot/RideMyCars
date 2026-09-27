import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:geolocator/geolocator.dart';
import '../core/api/api_client.dart';
import '../core/constants/api_constants.dart';
import '../core/storage/token_storage.dart';

class SupportedCountry {
  final String name;
  final String code;
  final String symbol;
  final String currency;
  final String flag;
  final String phonePrefix;
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
    required this.phonePrefix,
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
      phonePrefix: '+1',
      defaultMultiplier: 1.0,
      defaultProtectionRate: 12.0,
      defaultExtraDriverRate: 10.0,
      defaultChildSeatRate: 8.0,
      defaultGpsRate: 5.0,
    ),
    SupportedCountry(
      name: 'Ghana',
      code: 'GHA',
      symbol: 'GH₵',
      currency: 'GHS',
      flag: '🇬🇭',
      phonePrefix: '+233',
      defaultMultiplier: 15.5,
      defaultProtectionRate: 25.0,
      defaultExtraDriverRate: 20.0,
      defaultChildSeatRate: 15.0,
      defaultGpsRate: 10.0,
    ),
    SupportedCountry(
      name: 'South Africa',
      code: 'ZAF',
      symbol: 'R',
      currency: 'ZAR',
      flag: '🇿🇦',
      phonePrefix: '+27',
      defaultMultiplier: 18.2,
      defaultProtectionRate: 220.0,
      defaultExtraDriverRate: 180.0,
      defaultChildSeatRate: 145.0,
      defaultGpsRate: 90.0,
    ),
    SupportedCountry(
      name: 'Nigeria',
      code: 'NGA',
      symbol: '₦',
      currency: 'NGN',
      flag: '🇳🇬',
      phonePrefix: '+234',
      defaultMultiplier: 1500.0,
      defaultProtectionRate: 18000.0,
      defaultExtraDriverRate: 15000.0,
      defaultChildSeatRate: 12000.0,
      defaultGpsRate: 7500.0,
    ),
    SupportedCountry(
      name: 'India',
      code: 'IND',
      symbol: '₹',
      currency: 'INR',
      flag: '🇮🇳',
      phonePrefix: '+91',
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
      phonePrefix: '+44',
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
      phonePrefix: '+1',
      defaultMultiplier: 1.35,
      defaultProtectionRate: 16.2,
      defaultExtraDriverRate: 13.5,
      defaultChildSeatRate: 10.8,
      defaultGpsRate: 6.75,
    ),
    SupportedCountry(
      name: 'Australia',
      code: 'AUS',
      symbol: 'A\$',
      currency: 'AUD',
      flag: '🇦🇺',
      phonePrefix: '+61',
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
      phonePrefix: '+971',
      defaultMultiplier: 3.67,
      defaultProtectionRate: 45.0,
      defaultExtraDriverRate: 36.0,
      defaultChildSeatRate: 29.0,
      defaultGpsRate: 18.0,
    ),
    SupportedCountry(
      name: 'Kenya',
      code: 'KEN',
      symbol: 'KSh',
      currency: 'KES',
      flag: '🇰🇪',
      phonePrefix: '+254',
      defaultMultiplier: 130.0,
      defaultProtectionRate: 1560.0,
      defaultExtraDriverRate: 1300.0,
      defaultChildSeatRate: 1040.0,
      defaultGpsRate: 650.0,
    ),
    SupportedCountry(
      name: 'Germany',
      code: 'DEU',
      symbol: '€',
      currency: 'EUR',
      flag: '🇩🇪',
      phonePrefix: '+49',
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
      phonePrefix: '+33',
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
      phonePrefix: '+65',
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
      phonePrefix: '+81',
      defaultMultiplier: 155.0,
      defaultProtectionRate: 1800.0,
      defaultExtraDriverRate: 1500.0,
      defaultChildSeatRate: 1200.0,
      defaultGpsRate: 750.0,
    ),
  ];

  String _selectedCountryCode = 'USA';
  String _selectedCountryName = 'United States';
  String _currencySymbol = '\$';
  String _currencyCode = 'USD';
  String _flag = '🇺🇸';
  String _phonePrefix = '+1';
  bool _isManual = false;
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
  String get phonePrefix => _phonePrefix;
  bool get isManual => _isManual;
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

  double get priceMultiplier => rentalMultiplier;
  double get deliveryMultiplier => rentalMultiplier;

  CountryProvider() {
    _initFromStorage();
  }

  Future<void> _initFromStorage() async {
    final savedCode = await TokenStorage.getSelectedCountryCode();
    final savedSym = await TokenStorage.getCurrencySymbol();
    final savedCurr = await TokenStorage.getCurrencyCode();
    final isManual = await TokenStorage.isCountryManual();

    if (savedCode != null && savedCode.isNotEmpty) {
      final country = supportedCountries.firstWhere(
        (c) => c.code.toUpperCase() == savedCode.toUpperCase(),
        orElse: () => supportedCountries.first,
      );
      _selectedCountryCode = country.code;
      _selectedCountryName = country.name;
      _currencySymbol = savedSym ?? country.symbol;
      _currencyCode = savedCurr ?? country.currency;
      _flag = country.flag;
      _phonePrefix = country.phonePrefix;
      _currentMultiplier = country.defaultMultiplier;
      _isManual = isManual;
      notifyListeners();
      fetchPricing(country.code);
    } else {
      fetchPricing('USA');
    }
  }

  /// Automatically detect user's country on launch using:
  /// 1. Saved manual preference (if user previously made a manual choice).
  /// 2. GPS Location (Geolocator coordinate bounding box).
  /// 3. Device System Locale / SIM Region.
  /// 4. Backend GeoIP / Visitor Location (GET /api/countries).
  Future<void> autoDetectCountry() async {
    // 1. If user previously manually picked a country, respect their manual preference!
    final savedCode = await TokenStorage.getSelectedCountryCode();
    final isManual = await TokenStorage.isCountryManual();

    if (savedCode != null && savedCode.isNotEmpty && isManual) {
      await setCountry(savedCode, isManual: true);
      return;
    }

    String? detectedCode;

    // 2. GPS Device Location Check (Bounding Boxes)
    try {
      final hasPermission = await Geolocator.checkPermission();
      if (hasPermission == LocationPermission.always || hasPermission == LocationPermission.whileInUse) {
        Position? pos = await Geolocator.getLastKnownPosition();
        pos ??= await Geolocator.getCurrentPosition(
          desiredAccuracy: LocationAccuracy.low,
          timeLimit: const Duration(seconds: 3),
        );
        detectedCode = _detectFromCoordinates(pos.latitude, pos.longitude);
        debugPrint('CountryProvider detected from GPS: $detectedCode');
      }
    } catch (e) {
      debugPrint('CountryProvider GPS check skipped: $e');
    }

    // 3. System Locale / SIM Region Check
    if (detectedCode == null) {
      try {
        final deviceCountry = PlatformDispatcher.instance.locale.countryCode;
        if (deviceCountry != null && deviceCountry.isNotEmpty) {
          detectedCode = normalizeCountryCode(deviceCountry);
          debugPrint('CountryProvider detected from Locale: $detectedCode');
        }
      } catch (e) {
        debugPrint('CountryProvider locale check error: $e');
      }
    }

    // 4. Backend GeoIP API Check (GET /api/countries)
    if (detectedCode == null || detectedCode == 'USA') {
      try {
        final res = await _dio.get('${ApiConstants.baseUrl}/api/countries');
        if (res.statusCode == 200 && res.data != null) {
          final visitor = res.data['visitor_location'];
          if (visitor != null && visitor['code'] != null) {
            final vCode = normalizeCountryCode(visitor['code'].toString());
            if (vCode != null) {
              detectedCode = vCode;
              debugPrint('CountryProvider detected from Backend GeoIP: $detectedCode');
            }
          }
        }
      } catch (e) {
        debugPrint('CountryProvider backend GeoIP check error: $e');
      }
    }

    // 5. Final fallback
    final finalCode = detectedCode ?? savedCode ?? 'USA';
    await setCountry(finalCode, isManual: false);
  }

  /// Sets the active country, updates state, fetches server pricing,
  /// saves to local storage, and synchronizes with the backend.
  Future<void> setCountry(String countryCode, {bool isManual = true}) async {
    final norm = normalizeCountryCode(countryCode) ?? 'USA';
    final country = supportedCountries.firstWhere(
      (c) => c.code.toUpperCase() == norm.toUpperCase(),
      orElse: () => supportedCountries.first,
    );

    _selectedCountryCode = country.code;
    _selectedCountryName = country.name;
    _currencySymbol = country.symbol;
    _currencyCode = country.currency;
    _flag = country.flag;
    _phonePrefix = country.phonePrefix;
    _currentMultiplier = country.defaultMultiplier;
    _currentProtectionRate = country.defaultProtectionRate;
    _currentExtraDriverRate = country.defaultExtraDriverRate;
    _currentChildSeatRate = country.defaultChildSeatRate;
    _currentGpsRate = country.defaultGpsRate;
    _isManual = isManual;
    _pricingData = null;
    notifyListeners();

    // Persist to local storage
    await TokenStorage.saveCountryPreference(
      code: country.code,
      name: country.name,
      currencyCode: country.currency,
      currencySymbol: country.symbol,
      isManual: isManual,
    );

    // Fetch dynamic rates from backend
    await fetchPricing(country.code);

    // Synchronize country selection to backend API
    _syncCountryToBackend(country.code, isManual);
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
        if (_pricingData!['country_name'] != null) {
          _selectedCountryName = _pricingData!['country_name'].toString();
        }
      }
    } catch (e) {
      debugPrint('Error fetching country pricing for $countryCode: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> _syncCountryToBackend(String code, bool isManual) async {
    try {
      await _dio.post(
        '${ApiConstants.baseUrl}/api/country/set',
        data: {
          'country': code,
          'country_code': code,
          'manual': isManual,
        },
      );
    } catch (_) {
      // Backend automatically respects X-Country headers via ApiClient
    }
  }

  static String? normalizeCountryCode(String? input) {
    if (input == null || input.trim().isEmpty) return null;
    final upper = input.trim().toUpperCase();
    const map = {
      'US': 'USA',
      'USA': 'USA',
      'UNITED STATES': 'USA',
      'UNITED STATES OF AMERICA': 'USA',
      'GH': 'GHA',
      'GHA': 'GHA',
      'GHANA': 'GHA',
      'ZA': 'ZAF',
      'ZAF': 'ZAF',
      'SOUTH AFRICA': 'ZAF',
      'NG': 'NGA',
      'NGA': 'NGA',
      'NIGERIA': 'NGA',
      'IN': 'IND',
      'IND': 'IND',
      'INDIA': 'IND',
      'GB': 'GBR',
      'GBR': 'GBR',
      'UK': 'GBR',
      'UNITED KINGDOM': 'GBR',
      'CA': 'CAN',
      'CAN': 'CAN',
      'CANADA': 'CAN',
      'AU': 'AUS',
      'AUS': 'AUS',
      'AUSTRALIA': 'AUS',
      'AE': 'ARE',
      'ARE': 'ARE',
      'UAE': 'ARE',
      'UNITED ARAB EMIRATES': 'ARE',
      'KE': 'KEN',
      'KEN': 'KEN',
      'KENYA': 'KEN',
      'DE': 'DEU',
      'DEU': 'DEU',
      'GERMANY': 'DEU',
      'FR': 'FRA',
      'FRA': 'FRA',
      'FRANCE': 'FRA',
      'SG': 'SGP',
      'SGP': 'SGP',
      'SINGAPORE': 'SGP',
      'JP': 'JPN',
      'JPN': 'JPN',
      'JAPAN': 'JPN',
    };
    return map[upper] ?? (upper.length == 3 ? upper : null);
  }

  static String? _detectFromCoordinates(double lat, double lng) {
    // Ghana: lat 4.5 to 11.5, lng -3.5 to 1.5
    if (lat >= 4.5 && lat <= 11.5 && lng >= -3.5 && lng <= 1.5) return 'GHA';
    // Nigeria: lat 4.0 to 14.0, lng 2.5 to 15.0
    if (lat >= 4.0 && lat <= 14.0 && lng >= 2.5 && lng <= 15.0) return 'NGA';
    // South Africa: lat -35.0 to -22.0, lng 16.0 to 33.0
    if (lat >= -35.0 && lat <= -22.0 && lng >= 16.0 && lng <= 33.0) return 'ZAF';
    // India: lat 8.0 to 37.0, lng 68.0 to 97.5
    if (lat >= 8.0 && lat <= 37.0 && lng >= 68.0 && lng <= 97.5) return 'IND';
    // United Kingdom: lat 49.8 to 60.8, lng -8.0 to 1.8
    if (lat >= 49.8 && lat <= 60.8 && lng >= -8.0 && lng <= 1.8) return 'GBR';
    // United Arab Emirates: lat 22.5 to 26.2, lng 51.0 to 56.5
    if (lat >= 22.5 && lat <= 26.2 && lng >= 51.0 && lng <= 56.5) return 'ARE';
    // Kenya: lat -4.7 to 5.5, lng 33.9 to 41.9
    if (lat >= -4.7 && lat <= 5.5 && lng >= 33.9 && lng <= 41.9) return 'KEN';
    // Canada: lat 42.0 to 83.0, lng -141.0 to -52.0
    if (lat >= 42.0 && lat <= 83.0 && lng >= -141.0 && lng <= -52.0) return 'CAN';
    // USA: lat 24.5 to 49.4, lng -125.0 to -66.9
    if (lat >= 24.5 && lat <= 49.4 && lng >= -125.0 && lng <= -66.9) return 'USA';
    // Australia: lat -44.0 to -10.0, lng 113.0 to 154.0
    if (lat >= -44.0 && lat <= -10.0 && lng >= 113.0 && lng <= 154.0) return 'AUS';
    // Singapore: lat 1.15 to 1.48, lng 103.6 to 104.1
    if (lat >= 1.15 && lat <= 1.48 && lng >= 103.6 && lng <= 104.1) return 'SGP';
    // Japan: lat 24.0 to 46.0, lng 122.0 to 154.0
    if (lat >= 24.0 && lat <= 46.0 && lng >= 122.0 && lng <= 154.0) return 'JPN';
    // Germany: lat 47.2 to 55.1, lng 5.8 to 15.1
    if (lat >= 47.2 && lat <= 55.1 && lng >= 5.8 && lng <= 15.1) return 'DEU';
    // France: lat 42.3 to 51.1, lng -4.8 to 8.3
    if (lat >= 42.3 && lat <= 51.1 && lng >= -4.8 && lng <= 8.3) return 'FRA';
    return null;
  }

  String formatPrice(double amount, {int decimals = 2}) {
    return '$_currencySymbol${amount.toStringAsFixed(decimals)}';
  }

  String formatAmount(double amount, {int decimals = 2}) {
    return formatPrice(amount, decimals: decimals);
  }
}
