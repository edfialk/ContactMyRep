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
        $data = ['reps' => []];
        $ip = $request->ip();

        if ($ip == '192.168.10.1') $ip = '73.157.212.42';

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)){
            $location = IPInfo::getLocation($ip);
            $gps = explode(",", $location->loc);
            $data['reps'] = $this->repo->gps($gps[0], $gps[1]);
            $data['location'] = $location->city.', '.$location->region;
            $data['gps'] = $location->loc;
        }

        foreach($data['reps'] as $rep){
            if (!empty($rep->district)){
                $data['district'] = $rep->district;
                $data['location'] .= ' - District '.$rep->district;
                break;
            }
        }


        return view('pages.home', $data);
    }

    public function viewDistrict($state, $district)
    {
        return view('pages.home', [
            'reps' => $this->repo->district($state, $district),
            'location' => strtoupper($state).' - District '.$district
        ]);
    }

    public function viewGPS($lat, $lng)
    {
        return view('pages.home', [
            'reps' => $this->repo->gps($lat, $lng)
        ]);
    }

    public function viewZipcode($zip)
    {
        return view('pages.home', [
            'reps' => $this->repo->zip($zip),
            'location' => $zip
        ]);
    }

    public function jsonDistrict($state, $district)
    {
        return response()->json(
            $this->repo->district($state, $district)
        );
    }

    public function jsonZipcode($zipcode)
    {
        //not set up for 9 digit zip yet - i.e. OpenAPI doesn't use it
        if (strlen($zipcode) > 5) $zipcode = substr($zipcode, 0, 5);

        return response()->json(
            $this->repo->zip($zipcode)
        );
    }

    public function jsonGPS($lat, $lng)
    {
        return response()->json(
            $this->repo->gps($lat, $lng)
        );
    }

}
