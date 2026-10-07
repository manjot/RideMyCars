import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import '../core/constants/api_constants.dart';

class PlacePrediction {
  final String placeId;
  final String description;
  final String mainText;
  final String secondaryText;
  final double? lat;
  final double? lng;

  PlacePrediction({
    required this.placeId,
    required this.description,
    required this.mainText,
    required this.secondaryText,
    this.lat,
    this.lng,
  });

  factory PlacePrediction.fromJson(Map<String, dynamic> json) {
    final structured = json['structured_formatting'] ?? {};
    return PlacePrediction(
      placeId: json['place_id'] ?? '',
      description: json['description'] ?? '',
      mainText: structured['main_text'] ?? json['description'] ?? '',
      secondaryText: structured['secondary_text'] ?? '',
      lat: json['lat'] != null ? double.tryParse(json['lat'].toString()) : null,
      lng: json['lng'] != null ? double.tryParse(json['lng'].toString()) : null,
    );
  }
}

class PlaceDetails {
  final double lat;
  final double lng;
  final String formattedAddress;
  final String name;

  PlaceDetails({
    required this.lat,
    required this.lng,
    required this.formattedAddress,
    required this.name,
  });
}

class PlacesService {
  static final Dio _dio = Dio(
    BaseOptions(
      connectTimeout: const Duration(seconds: 8),
      receiveTimeout: const Duration(seconds: 8),
      headers: {'User-Agent': 'RideMyCars-App/1.0'},
    ),
  );

  /// Get autocomplete predictions for user query with Google Places + OpenStreetMap fallback
  static Future<List<PlacePrediction>> getAutocomplete(String query, {double? lat, double? lng}) async {
    final cleanQuery = query.trim();
    if (cleanQuery.isEmpty) return [];

    // 1. Try Google Places Autocomplete API
    try {
      String url =
          'https://maps.googleapis.com/maps/api/place/autocomplete/json?input=${Uri.encodeComponent(cleanQuery)}&key=${ApiConstants.googleMapsApiKey}';

      if (lat != null && lng != null) {
        url += '&location=$lat,$lng&radius=50000';
      }

      final res = await _dio.get(url);
      if (res.statusCode == 200 && res.data['status'] == 'OK') {
        final List list = res.data['predictions'] ?? [];
        if (list.isNotEmpty) {
          return list.map((e) => PlacePrediction.fromJson(e)).toList();
        }
      }
    } catch (e) {
      debugPrint('Google Places autocomplete warning: $e');
    }

    // 2. High-reliability fallback: OpenStreetMap Nominatim
    try {
      final osmUrl =
          'https://nominatim.openstreetmap.org/search?format=json&q=${Uri.encodeComponent(cleanQuery)}&limit=6&addressdetails=1';
      final osmRes = await _dio.get(osmUrl);
      if (osmRes.statusCode == 200 && osmRes.data is List) {
        final List list = osmRes.data;
        if (list.isNotEmpty) {
          return list.map((item) {
            final disp = item['display_name']?.toString() ?? '';
            final parts = disp.split(',');
            final main = item['name']?.toString().isNotEmpty == true
                ? item['name'].toString()
                : (parts.isNotEmpty ? parts[0].trim() : disp);
            final sec = parts.length > 1 ? parts.sublist(1).take(3).join(', ').trim() : '';
            final pLat = double.tryParse(item['lat']?.toString() ?? '');
            final pLng = double.tryParse(item['lon']?.toString() ?? '');

            return PlacePrediction(
              placeId: 'osm_${item['place_id'] ?? main}',
              description: disp,
              mainText: main,
              secondaryText: sec,
              lat: pLat,
              lng: pLng,
            );
          }).toList();
        }
      }
    } catch (e) {
      debugPrint('Nominatim autocomplete fallback error: $e');
    }

    return [];
  }

  /// Get lat/lng coordinates and formatted address for selected place
  static Future<PlaceDetails?> getPlaceDetails(String placeId, {PlacePrediction? prediction}) async {
    // If prediction already has coordinates (e.g. from Nominatim), return immediately
    if (prediction != null && prediction.lat != null && prediction.lng != null) {
      return PlaceDetails(
        lat: prediction.lat!,
        lng: prediction.lng!,
        formattedAddress: prediction.description.isNotEmpty ? prediction.description : prediction.mainText,
        name: prediction.mainText,
      );
    }

    if (placeId.isEmpty) return null;

    if (!placeId.startsWith('osm_')) {
      try {
        final url =
            'https://maps.googleapis.com/maps/api/place/details/json?place_id=$placeId&fields=geometry,formatted_address,name&key=${ApiConstants.googleMapsApiKey}';
        final res = await _dio.get(url);

        if (res.statusCode == 200 && res.data['status'] == 'OK') {
          final result = res.data['result'];
          final loc = result?['geometry']?['location'];
          if (loc != null) {
            return PlaceDetails(
              lat: (loc['lat'] as num).toDouble(),
              lng: (loc['lng'] as num).toDouble(),
              formattedAddress: result['formatted_address'] ?? result['name'] ?? '',
              name: result['name'] ?? '',
            );
          }
        }
      } catch (e) {
        debugPrint('Place details error: $e');
      }
    }

    return null;
  }

  /// Reverse geocode GPS coordinates to a detailed, human-readable street address
  static Future<String?> getAddressFromCoordinates(double lat, double lng) async {
    // 1. Google Geocoding
    try {
      final url =
          'https://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$lng&key=${ApiConstants.googleMapsApiKey}';
      final res = await _dio.get(url);

      if (res.statusCode == 200 && res.data['status'] == 'OK') {
        final List results = res.data['results'] ?? [];
        if (results.isNotEmpty) {
          final first = results.first;
          final formatted = first['formatted_address'] as String?;
          if (formatted != null && formatted.isNotEmpty) {
            return formatted;
          }
        }
      }
    } catch (e) {
      debugPrint('Geocoding reverse lookup error: $e');
    }

    // 2. Nominatim Reverse Lookup Fallback
    try {
      final osmUrl =
          'https://nominatim.openstreetmap.org/reverse?format=json&lat=$lat&lon=$lng&zoom=18&addressdetails=1';
      final osmRes = await _dio.get(osmUrl);
      if (osmRes.statusCode == 200 && osmRes.data != null && osmRes.data['display_name'] != null) {
        return osmRes.data['display_name'].toString();
      }
    } catch (e) {
      debugPrint('Nominatim reverse lookup fallback error: $e');
    }

    return null;
  }

  /// Forward geocode an address string to lat/lng coordinates
  static Future<PlaceDetails?> getCoordinatesFromAddress(String address) async {
    final clean = address.trim();
    if (clean.isEmpty) return null;

    // 1. Google Forward Geocoding
    try {
      final url =
          'https://maps.googleapis.com/maps/api/geocode/json?address=${Uri.encodeComponent(clean)}&key=${ApiConstants.googleMapsApiKey}';
      final res = await _dio.get(url);

      if (res.statusCode == 200 && res.data['status'] == 'OK') {
        final List results = res.data['results'] ?? [];
        if (results.isNotEmpty) {
          final first = results.first;
          final loc = first['geometry']?['location'];
          if (loc != null) {
            return PlaceDetails(
              lat: (loc['lat'] as num).toDouble(),
              lng: (loc['lng'] as num).toDouble(),
              formattedAddress: first['formatted_address'] ?? clean,
              name: first['formatted_address'] ?? clean,
            );
          }
        }
      }
    } catch (e) {
      debugPrint('Forward geocoding error: $e');
    }

    // 2. Nominatim Forward Geocoding Fallback
    try {
      final osmUrl =
          'https://nominatim.openstreetmap.org/search?format=json&q=${Uri.encodeComponent(clean)}&limit=1';
      final osmRes = await _dio.get(osmUrl);
      if (osmRes.statusCode == 200 && osmRes.data is List && (osmRes.data as List).isNotEmpty) {
        final first = (osmRes.data as List).first;
        final pLat = double.tryParse(first['lat']?.toString() ?? '');
        final pLng = double.tryParse(first['lon']?.toString() ?? '');
        if (pLat != null && pLng != null) {
          return PlaceDetails(
            lat: pLat,
            lng: pLng,
            formattedAddress: first['display_name'] ?? clean,
            name: first['display_name'] ?? clean,
          );
        }
      }
    } catch (e) {
      debugPrint('Nominatim forward geocoding fallback error: $e');
    }

    return null;
  }
}
