<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//use App\Http\Requests;
use App\Http\Controllers\Controller;
use GuzzleHttp\Promise;

use App\Representative;
use App\Providers\IPInfo\IPInfo;

use GoogleAPI;
use CongressAPI;
use StateAPI;

class RepresentativeController extends Controller
{

    public function view()
    {
        return view('pages.home');
    }

    public function index(Request $request)
    {
        $data = ['reps' => []];
        $ip = $request->ip();

        if ($ip == '192.168.10.1') $ip = '73.157.212.42';

        if (
            filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) &&
            stripos($request->header('User-Agent'), 'mobi') === false
        ){
            $data['location'] = IPInfo::getLocation($ip);
        }

        return view('pages.home', $data);
    }


    public function jsonDistrict($state, $district)
    {
        $googReq = GoogleAPI::districtAsync($state, $district);
        $congReq = CongressAPI::districtAsync($state, $district);
        $results = Promise\unwrap([$googReq, $congReq]);

        if (isset($results[0]->status) && $results[0]->status == 'error'){
            return response()->json($results[0]);
        }

        $resp = $this->buildResponse($results[0], $results[1]);

        return response()->json($resp);
    }

    public function jsonZipcode($zipcode)
    {
        $googReq = GoogleAPI::zip($zipcode);
        $congReq = CongressAPI::zip($zipcode);

        $results = Promise\unwrap([$googReq, $congReq]);

        if (isset($results[0]->status) && $results[0]->status == 'error'){
            return response()->json($results[0]);
        }

        $resp = $this->buildResponse($results);
        return response()->json($resp);
    }

    public function jsonGPS($lat, $lng)
    {
        $googReq = GoogleAPI::gps($lat, $lng);
        $congReq = CongressAPI::gps($lat, $lng);
        $stateReq = StateAPI::gps($lat, $lng);

        $results = Promise\unwrap([$googReq, $congReq, $stateReq]);
        if (isset($results[0]->status) && $results[0]->status == 'error'){
            return response()->json($results[0]);
        }
        if (isset($results[0]->error)){
            return response()->json((object)['status' => 'error', 'message' => $results[0]->error]);
        }

        $resp = $this->buildResponse($results);
        return response()->json($resp);
    }

    public function buildResponse($data)
    {
        $response = (object)$data[0];
        $congress = $data[1];
        foreach($response->reps as &$rep){
            $congressIndex = $rep->isIn($congress);
            if ($congressIndex !== false){
                $rep->load($congress[$congressIndex]);
                unset($congress[$congressIndex]);
            }
            $congress = array_values($congress);
        }

        foreach($congress as $cdata){
            $response->reps[] = $cdata;
        }

        if (isset($data[2])){
            foreach($data[2] as $state){
                $response->reps[] = $state;
            }
        }

        usort($response->reps, function($a, $b){
            $ia = array_search($a->office, Representative::ranks);
            $ib = array_search($b->office, Representative::ranks);

            if ($ia === false) $ia = 6;
            if ($ib === false) $ib = 6;

            return $ia > $ib;
        });

        return $response;
    }
}
