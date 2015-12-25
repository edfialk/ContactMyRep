<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Representative;

use App\Providers\IPInfo\IPInfo;

class RepresentativeController extends Controller
{

    public function viewIndex(Request $request)
    {
        $ip = $request->ip();
        $location = IPInfo::getLocation($ip);
        $gps = explode(",", $location->loc);
        $lat = $gps[0];
        $lng = $gps[1];

        $reps = Representative::getAllAtGPS($lat, $lng);

        return view('pages.results', [
            'reps' => $reps,
            'location' => $gps
        ]);
    }

    public function viewDistrict($state, $district)
    {
        $reps = Representative::getAllAtDistrict($state, $district);

        return view('pages.results', [
            'reps' => $reps
        ]);
    }

    public function viewGPS($lat, $lng)
    {
        $reps = Representative::getAllAtGPS($lat, $lng);

        return view('pages.results', [
            'reps' => $reps
        ]);
    }

    public function viewZipcode($zipcode)
    {

        $reps = Representative::getAllAtZip($zipcode);

        return view('pages.results', [
            'reps' => $reps
        ]);
    }

    public function jsonDistrict($state, $district)
    {
        $reps = Representative::getAllAtDistrict($state, $district);

        return response()->json($reps);
    }

    public function jsonZipcode($zipcode)
    {
        //not set up for 9 digit zip yet
        if (strlen($zipcode) > 5){
            $zipcode = substr($zipcode, 0, 5);
        }
        $reps = Representative::getAllAtZip($zipcode);

        return response()->json($reps);
    }

    public function jsonGPS($lat, $lng)
    {
        $reps = Representative::getAllAtGPS($lat, $lng);

        return response()->json($reps);
    }

}
