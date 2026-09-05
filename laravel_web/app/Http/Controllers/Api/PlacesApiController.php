<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PlacesApiController extends Controller
{
    protected function getApiKey(): string
    {
        return config('services.google_maps.api_key') 
            ?: env('GOOGLE_MAPS_API_KEY', 'AIzaSyACN52o17kFjtg_K45rKU_ETTJ6WaXvkC0');
    }

    /**
     * Autocomplete search for places, addresses, and POIs.
     * GET /api/places/autocomplete?input=query&lat=...&lng=...
     */
    public function autocomplete(Request $request)
    {
        $input = trim($request->query('input', ''));
        if (strlen($input) < 2) {
            return response()->json(['success' => true, 'predictions' => []]);
        }

        $lat = $request->query('lat');
        $lng = $request->query('lng');
        $apiKey = $this->getApiKey();

        $cacheKey = 'places_auto_' . md5($input . '_' . $lat . '_' . $lng);
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return response()->json(['success' => true, 'predictions' => $cached]);
        }

        $predictions = [];

        // 1. Primary: Google Places Autocomplete API
        if (!empty($apiKey)) {
            try {
                $params = [
                    'input' => $input,
                    'key' => $apiKey,
                ];

                if (is_numeric($lat) && is_numeric($lng)) {
                    $params['location'] = "{$lat},{$lng}";
                    $params['radius'] = 50000; // 50km bias
                }

                $response = Http::timeout(6)->get('https://maps.googleapis.com/maps/api/place/autocomplete/json', $params);
                if ($response->successful()) {
                    $data = $response->json();
                    if (($data['status'] ?? '') === 'OK' && !empty($data['predictions'])) {
                        foreach ($data['predictions'] as $p) {
                            $structured = $p['structured_formatting'] ?? [];
                            $predictions[] = [
                                'place_id' => $p['place_id'] ?? '',
                                'description' => $p['description'] ?? '',
                                'main_text' => $structured['main_text'] ?? $p['description'] ?? '',
                                'secondary_text' => $structured['secondary_text'] ?? '',
                                'source' => 'google',
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Google Places Autocomplete API error: ' . $e->getMessage());
            }
        }

        // 2. Secondary Fallback: Photon Komoot / OSM if Google returned nothing
        if (empty($predictions)) {
            try {
                $photonParams = ['q' => $input, 'limit' => 6];
                if (is_numeric($lat) && is_numeric($lng)) {
                    $photonParams['lat'] = $lat;
                    $photonParams['lon'] = $lng;
                }
                $pResp = Http::timeout(5)->withHeaders([
                    'User-Agent' => 'RideMyCars/1.0 (contact@ridemycars.com)'
                ])->get('https://photon.komoot.io/api/', $photonParams);

                if ($pResp->successful()) {
                    $features = $pResp->json()['features'] ?? [];
                    foreach ($features as $f) {
                        $props = $f['properties'] ?? [];
                        $coords = $f['geometry']['coordinates'] ?? [0, 0];
                        $name = $props['name'] ?? '';
                        $subParts = array_filter([
                            $props['street'] ?? '',
                            $props['city'] ?? $props['town'] ?? $props['district'] ?? '',
                            $props['state'] ?? '',
                            $props['country'] ?? ''
                        ]);
                        $secondary = implode(', ', $subParts);
                        $desc = $name ? ($name . ($secondary ? ', ' . $secondary : '')) : $secondary;

                        $predictions[] = [
                            'place_id' => 'osm_' . ($props['osm_id'] ?? uniqid()),
                            'description' => $desc,
                            'main_text' => $name ?: $desc,
                            'secondary_text' => $secondary,
                            'lat' => $coords[1] ?? null,
                            'lng' => $coords[0] ?? null,
                            'source' => 'photon',
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Photon fallback autocomplete error: ' . $e->getMessage());
            }
        }

        // Cache valid predictions for 10 minutes
        if (!empty($predictions)) {
            Cache::put($cacheKey, $predictions, now()->addMinutes(10));
        }

        return response()->json([
            'success' => true,
            'predictions' => $predictions,
        ]);
    }

    /**
     * Get place coordinates and formatted address by place_id.
     * GET /api/places/details?place_id=...
     */
    public function details(Request $request)
    {
        $placeId = trim($request->query('place_id', ''));
        if (empty($placeId)) {
            return response()->json(['success' => false, 'message' => 'place_id is required'], 422);
        }

        $apiKey = $this->getApiKey();
        $cacheKey = 'places_det_' . md5($placeId);
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return response()->json(['success' => true, 'place' => $cached]);
        }

        // Check if this is a Photon / OSM simulated place_id with lat/lng passed
        if (str_starts_with($placeId, 'osm_')) {
            $lat = $request->query('lat');
            $lng = $request->query('lng');
            $name = $request->query('name', 'Selected Location');
            if ($lat && $lng) {
                $place = [
                    'place_id' => $placeId,
                    'name' => $name,
                    'formatted_address' => $name,
                    'lat' => (float) $lat,
                    'lng' => (float) $lng,
                    'source' => 'photon'
                ];
                return response()->json(['success' => true, 'place' => $place]);
            }
        }

        // 1. Google Place Details API
        if (!empty($apiKey)) {
            try {
                $response = Http::timeout(6)->get('https://maps.googleapis.com/maps/api/place/details/json', [
                    'place_id' => $placeId,
                    'fields' => 'geometry,formatted_address,name',
                    'key' => $apiKey,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (($data['status'] ?? '') === 'OK' && !empty($data['result'])) {
                        $res = $data['result'];
                        $loc = $res['geometry']['location'] ?? null;
                        if ($loc) {
                            $place = [
                                'place_id' => $placeId,
                                'name' => $res['name'] ?? '',
                                'formatted_address' => $res['formatted_address'] ?? $res['name'] ?? '',
                                'lat' => (float) $loc['lat'],
                                'lng' => (float) $loc['lng'],
                                'source' => 'google'
                            ];

                            Cache::put($cacheKey, $place, now()->addHours(24));
                            return response()->json(['success' => true, 'place' => $place]);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Google Place Details error: ' . $e->getMessage());
            }
        }

        return response()->json(['success' => false, 'message' => 'Could not resolve place details'], 404);
    }

    /**
     * Direct geocode for typed queries (e.g. on blur / enter).
     * GET /api/places/geocode?query=...
     */
    public function geocode(Request $request)
    {
        $query = trim($request->query('query', $request->query('address', '')));
        if (strlen($query) < 2) {
            return response()->json(['success' => false, 'message' => 'Query is required'], 422);
        }

        $apiKey = $this->getApiKey();
        $cacheKey = 'places_geo_' . md5($query);
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return response()->json(['success' => true, 'place' => $cached]);
        }

        // 1. Google Geocoding API
        if (!empty($apiKey)) {
            try {
                $response = Http::timeout(6)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'address' => $query,
                    'key' => $apiKey,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (($data['status'] ?? '') === 'OK' && !empty($data['results'])) {
                        $top = $data['results'][0];
                        $loc = $top['geometry']['location'] ?? null;
                        if ($loc) {
                            $addr = $top['formatted_address'] ?? $query;
                            $nameParts = explode(',', $addr);
                            $place = [
                                'place_id' => $top['place_id'] ?? '',
                                'name' => trim($nameParts[0] ?? $query),
                                'formatted_address' => $addr,
                                'lat' => (float) $loc['lat'],
                                'lng' => (float) $loc['lng'],
                                'source' => 'google'
                            ];

                            Cache::put($cacheKey, $place, now()->addHours(12));
                            return response()->json(['success' => true, 'place' => $place]);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Google Geocode API error: ' . $e->getMessage());
            }
        }

        // 2. Fallback: Photon Komoot
        try {
            $pResp = Http::timeout(5)->withHeaders([
                'User-Agent' => 'RideMyCars/1.0 (contact@ridemycars.com)'
            ])->get('https://photon.komoot.io/api/', ['q' => $query, 'limit' => 1]);

            if ($pResp->successful()) {
                $features = $pResp->json()['features'] ?? [];
                if (!empty($features)) {
                    $f = $features[0];
                    $props = $f['properties'] ?? [];
                    $coords = $f['geometry']['coordinates'] ?? [];
                    if (count($coords) >= 2) {
                        $name = $props['name'] ?? $query;
                        $place = [
                            'place_id' => 'osm_' . ($props['osm_id'] ?? uniqid()),
                            'name' => $name,
                            'formatted_address' => $name . (isset($props['city']) ? ', ' . $props['city'] : ''),
                            'lat' => (float) $coords[1],
                            'lng' => (float) $coords[0],
                            'source' => 'photon'
                        ];

                        Cache::put($cacheKey, $place, now()->addHours(6));
                        return response()->json(['success' => true, 'place' => $place]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Photon geocode fallback error: ' . $e->getMessage());
        }

        return response()->json(['success' => false, 'message' => 'Location not found'], 404);
    }

    /**
     * Reverse geocode coordinates to street address.
     * GET /api/places/reverse?lat=...&lng=...
     */
    public function reverse(Request $request)
    {
        $lat = $request->query('lat');
        $lng = $request->query('lng');

        if (!is_numeric($lat) || !is_numeric($lng)) {
            return response()->json(['success' => false, 'message' => 'Valid lat and lng required'], 422);
        }

        $lat = round((float) $lat, 6);
        $lng = round((float) $lng, 6);
        $apiKey = $this->getApiKey();

        $cacheKey = 'places_rev_' . md5("{$lat}_{$lng}");
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return response()->json(['success' => true, 'place' => $cached]);
        }

        // 1. Google Reverse Geocoding API
        if (!empty($apiKey)) {
            try {
                $response = Http::timeout(6)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'latlng' => "{$lat},{$lng}",
                    'key' => $apiKey,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (($data['status'] ?? '') === 'OK' && !empty($data['results'])) {
                        $results = $data['results'];
                        
                        // Look for street address or point_of_interest first, fallback to first result
                        $best = $results[0];
                        foreach ($results as $r) {
                            $types = $r['types'] ?? [];
                            if (in_array('street_address', $types) || in_array('premise', $types) || in_array('point_of_interest', $types)) {
                                $best = $r;
                                break;
                            }
                        }

                        $addr = $best['formatted_address'] ?? "{$lat}, {$lng}";
                        $parts = explode(',', $addr);
                        $shortName = trim($parts[0]);
                        if (count($parts) > 1 && strlen($shortName) < 6) {
                            $shortName = trim($parts[0]) . ', ' . trim($parts[1]);
                        }

                        $place = [
                            'place_id' => $best['place_id'] ?? '',
                            'formatted_address' => $addr,
                            'name' => $shortName,
                            'lat' => $lat,
                            'lng' => $lng,
                            'source' => 'google'
                        ];

                        Cache::put($cacheKey, $place, now()->addHours(24));
                        return response()->json(['success' => true, 'place' => $place]);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Google Reverse Geocode error: ' . $e->getMessage());
            }
        }

        // 2. Fallback: Nominatim OpenStreetMap
        try {
            $nomResp = Http::timeout(5)->withHeaders([
                'User-Agent' => 'RideMyCars/1.0 (contact@ridemycars.com)'
            ])->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'json',
                'lat' => $lat,
                'lon' => $lng,
                'zoom' => 18,
                'addressdetails' => 1
            ]);

            if ($nomResp->successful()) {
                $nData = $nomResp->json();
                $displayName = $nData['display_name'] ?? "{$lat}, {$lng}";
                $place = [
                    'place_id' => (string) ($nData['place_id'] ?? ''),
                    'formatted_address' => $displayName,
                    'name' => explode(',', $displayName)[0] ?? $displayName,
                    'lat' => $lat,
                    'lng' => $lng,
                    'source' => 'nominatim'
                ];

                Cache::put($cacheKey, $place, now()->addHours(6));
                return response()->json(['success' => true, 'place' => $place]);
            }
        } catch (\Exception $e) {
            Log::warning('Nominatim reverse geocode fallback error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'place' => [
                'place_id' => '',
                'formatted_address' => "{$lat}, {$lng}",
                'name' => "{$lat}, {$lng}",
                'lat' => $lat,
                'lng' => $lng,
                'source' => 'coords'
            ]
        ]);
    }
}
