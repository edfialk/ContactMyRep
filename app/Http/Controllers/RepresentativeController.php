<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Representative;
use App\Repositories\RepRepository;
use App\Providers\IPInfo\IPInfo;

class RepresentativeController extends Controller
{

    protected $repo;

    public function __construct(RepRepository $repo)
    {
        $this->repo = $repo;
    }

    public function viewIndex(Request $request)
    {
        $reps = [];
        $ip = $request->ip();

        if ($ip == '192.168.10.1') $ip = '73.157.212.42';

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)){
            $location = IPInfo::getLocation($ip);
            $gps = explode(",", $location->loc);
            $reps = $this->repo->gps($gps[0], $gps[1]);
        }

        $data = ['reps' => $reps];

        if (isset($gps)) $data['gps'] = $gps;

        return view('pages.results', $data);
    }

    public function viewDistrict($state, $district)
    {
        $reps = $this->repo->district($state, $district);

        return view('pages.results', [
            'reps' => $reps
        ]);
    }

    public function viewGPS($lat, $lng)
    {
        $reps = $this->repo->gps($lat, $lng);

        return view('pages.results', [
            'reps' => $reps
        ]);
    }

    public function viewZipcode($zip)
    {

        $reps = $this->repo->zip($zip);

        return view('pages.results', [
            'reps' => $reps
        ]);
    }

    public function jsonDistrict($state, $district)
    {
        $reps = $this->repo->district($state, $district);

        return response()->json($reps);
    }

    public function jsonZipcode($zipcode)
    {
        //not set up for 9 digit zip yet - i.e. OpenAPI doesn't use it
        if (strlen($zipcode) > 5) $zipcode = substr($zipcode, 0, 5);

        $reps = $this->repo->zip($zipcode);

        return response()->json($reps);
    }

    public function jsonGPS($lat, $lng)
    {
        $reps = $this->repo->gps($lat, $lng);

        return response()->json($reps);
    }

}
