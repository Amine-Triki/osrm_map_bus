<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DistanceController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/calculate-distance', [DistanceController::class, 'calculateDistance']);

Route::get("/GetDistances", [DistanceController::class, "GetDistances"]);
Route::get("/getDistance/{id}", [DistanceController::class, "GetDistance"]);
Route::put("/updateDistance/{id}", [DistanceController::class, "updateDistance"]);
Route::delete("/deleteDistance/{id}", [DistanceController::class, "DeleteDistance"]);
