<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Distance;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class DistanceController extends Controller
{
    // create distance
    public function calculateDistance(Request $request)
{
    $validator = Validator::make($request->all(), [
        'points' => 'required|array',
        'coordinates' => 'required|array',
        'line_name' => 'required|string',

    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $points = $request->input('points');
    $line_name = $request->input('line_name');
    $coordinates = $request->input('coordinates');

    if (count($coordinates) < 2) {
        return response()->json(['error' => 'You must provide at least two points'], 400);
    }

    $coordinateStrings = array_map(function($coord) {
        return "{$coord[0]},{$coord[1]}";
    }, $coordinates);

    $coordinateString = implode(';', $coordinateStrings);

    $url = "http://router.project-osrm.org/route/v1/driving/{$coordinateString}?overview=full&geometries=geojson";

    try {
        $response = Http::get($url);

        if ($response->failed()) {
            throw new \Exception('Failed to connect to OSRM service');
        }

        $data = $response->json();

        if (!isset($data['routes']) || count($data['routes']) === 0) {
            return response()->json(['error' => 'The path cannot be calculated for these points'], 400);
        }

        $route = $data['routes'][0];
        $distance = $route['distance'] / 1000; // Distance in kilometers
        $geometry = $route['geometry']; // GeoJSON geometry

        $distanceRecord = Distance::create([
            'line_name' => $line_name,
            'points' => json_encode($points),
            'coordinates' => json_encode($coordinates),
            'distance' => $distance,
            'geometry' => json_encode($geometry),
        ]);

        return response()->json($distanceRecord, 201);

    } catch (\Exception $e) {
        return response()->json(['error' => 'An error occurred while calculating the distance: ' . $e->getMessage()], 500);
    }
}






// get all paths
function getDistances()
{
    $distances = Distance::all();
    return response()->json($distances, 200);
}


// get a specific path
function getDistance($id)
{
    $distance = Distance::find($id);
    if (!$distance) {
        return response()->json(['errors' => 'Distance not found'], 404);
    }
    return response()->json($distance, 200);
}




// Update distance
public function updateDistance(Request $request, $id)
{
    $distanceRecord = Distance::find($id);
    if (!$distanceRecord) {
        return response()->json(['errors' => 'Distance not found.'], 404);
    }

    $validator = Validator::make($request->all(), [
        'points' => 'required|array',
        'coordinates' => 'required|array',
        'line_name' => 'required|string',

    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $points = $request->input('points');
    $line_name = $request->input('line_name');
    $coordinates = $request->input('coordinates');

    if (count($coordinates) < 2) {
        return response()->json(['error' => 'You must provide at least two points'], 400);
    }

    $coordinateStrings = array_map(function($coord) {
        return "{$coord[0]},{$coord[1]}";
    }, $coordinates);

    $coordinateString = implode(';', $coordinateStrings);

    $url = "http://router.project-osrm.org/route/v1/driving/{$coordinateString}?overview=full&geometries=geojson";

    try {
        $response = Http::get($url);

        if ($response->failed()) {
            throw new \Exception('Failed to connect to OSRM service');
        }

        $data = $response->json();

        if (!isset($data['routes']) || count($data['routes']) === 0) {
            return response()->json(['error' => 'The path cannot be calculated for these points'], 400);
        }

        $route = $data['routes'][0];
        $distance = $route['distance'] / 1000; // Distance in kilometers
        $geometry = $route['geometry']; // GeoJSON geometry

        $distanceRecord ->update([
            'line_name' => $line_name,
            'points' => json_encode($points),
            'coordinates' => json_encode($coordinates),
            'distance' => $distance,
            'geometry' => json_encode($geometry),
        ]);

        return response()->json(['status' => 'Data updated successfully.', 'distance' => $distanceRecord], 201);
    } catch (\Exception $e) {
        return response()->json(['error' => 'An error occurred while calculating the distance: ' . $e->getMessage()], 500);
    }
}







// Delete a space
function deleteDistance($id)
{
    $distance = Distance::find($id);
    if ($distance) {
        $distance->delete();
        return response()->json(['status' => 'Distance deleted successfully.'], 200);
    } else {
        return response()->json(['errors' => 'Distance not found.'], 404);
    }
}


 // Display the map
 public function showMap()
 {
     $distances = Distance::all();
     return view('show_Map', compact('distances'));
 }

}
