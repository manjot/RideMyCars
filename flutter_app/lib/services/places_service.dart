import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import '../core/constants/api_constants.dart';

class PlacePrediction {
  final String placeId;
  final String description;
  final String mainText;
  final String secondaryText;

  PlacePrediction({
    required this.placeId,
    required this.description,
    required this.mainText,
    required this.secondaryText,
  });

  factory PlacePrediction.fromJson(Map<String, dynamic> json) {
    final structured = json['structured_formatting'] ?? {};
    return PlacePrediction(
      placeId: json['place_id'] ?? '',
      description: json['description'] ?? '',
      mainText: structured['main_text'] ?? json['description'] ?? '',
      secondaryText: structured['secondary_text'] ?? '',
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
    ),
  );

  /// Get autocomplete predictions for user query
  static Future<List<PlacePrediction>> getAutocomplete(String query, {double? lat, double? lng}) async {
    if (query.trim().isEmpty) return [];

    try {
      String url =
          'https://maps.googleapis.com/maps/api/place/autocomplete/json?input=${Uri.encodeComponent(query)}&key=${ApiConstants.googleMapsApiKey}';

      if (lat != null && lng != null) {
        url += '&location=$lat,$lng&radius=50000';
      }

      final res = await _dio.get(url);
      if (res.statusCode == 200 && res.data['status'] == 'OK') {
        final List list = res.data['predictions'] ?? [];
        return list.map((e) => PlacePrediction.fromJson(e)).toList();
      }
    } catch (e) {
      debugPrint('Places autocomplete error: $e');
    }

    return [];
  }

  /// Get lat/lng coordinates and formatted address for selected place
  static Future<PlaceDetails?> getPlaceDetails(String placeId) async {
    if (placeId.isEmpty) return null;

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

    return null;
  }
}
