<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', function () {
    return response()->json([
        'message' => 'all systems are a go',
        'users' => \App\Models\User::all(),
    ]);
});

/**
 * Get weather information with latitude and longitude
 * 
 * @param $latitude
 * @param $longitude
 * 
 * @return ['status' => '', 'data' => '']
 */
Route::get('/{latitude}/{longitude}', function (Request $request, $latitude, $longitude) {

    $cacheId = "location_{$latitude}_{$longitude}";
    $data = Cache::get($cacheId);
    if ($data) 
        return response()->json(['status' => 'success', 'data' => $data]);

    $apiUrl = env('APP_WEATHER_URL');
    $apiKey = env('APP_WEATHER_KEY');
    $url = "$apiUrl?lat=$latitude&lon=$longitude&appid=$apiKey";
    
    try {
        $response = Http::timeout(1)
            ->withHeaders([
                'Accept' => 'application/json',
            ])
            ->get($url);
        if ($response->ok()) {
            Cache::put($cacheId, $response->json(), now()->addMinutes(60));
            $result = ['status' => 'success', 'data' => $response->json()];
        } else {
            $result = ['status' => 'failed'];
        }
    } catch (ConnectionException $e) {
        $result = ['status' => 'failed'];
    }
    
    return response()->json($result);

    // $curl = curl_init();
    // $data = ['lat' => $latitude, 'lon' => $longitude, 'appid' => env('APP_WEATHER_KEY')];
    // $url = sprintf("%s?%s", env('APP_WEATHER_URL'), http_build_query($data));

    // curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
    // curl_setopt($curl, CURLOPT_URL, $url);
    // curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    // $result = curl_exec($curl);
    // $info = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    // if ($info !== 200) {
    //     curl_close($curl);
    //     return response()->json([
    //         'status' => 'failed',
    //     ]);
    // }

    // curl_close($curl);

    // return response()->json([
    //     'status' => 'success',
    //     'data' => json_decode($result)
    // ]);
});
