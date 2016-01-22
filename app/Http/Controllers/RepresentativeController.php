<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $ip = $request->ip();

        if (
            filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
            && stripos($request->header('User-Agent'), 'mobi') === false
        ){
            return view('pages.home', ['location' => IPInfo::getLocation($ip)]);
        }

        return view('pages.home');
    }


    public function district($state, $district)
    {
        $googReq = GoogleAPI::district($state, $district);
        $congReq = CongressAPI::district($state, $district);
        $stateReq = StateAPI::district($state, $district);
        $results = Promise\unwrap([$googReq, $congReq, $stateReq]);

        if (!$this->valid($results))
            return $this->error($results);

        return $this->success($results);
    }

    public function zipcode($zipcode)
    {
        $googReq = GoogleAPI::address($zipcode);
        $congReq = CongressAPI::zip($zipcode);
        $results = Promise\unwrap([$googReq, $congReq]);

        if (!$this->valid($results)){
            return $this->error($results);
        }

        //stateapi has no zip search, have to use district
        $rep = array_first($results[1], function($key, $val){
            return isset($val->district) && isset($val->state);
        });
        if (!is_null($rep)){
            $states = StateAPI::district($rep->state, $rep->district);
            $states->then(function($data) use (&$results){
                $results[] = $data;
            });
            $states->wait();
        }

        return $this->success($results);
    }

    public function gps($lat, $lng)
    {
        $googReq = GoogleAPI::address($lat.','.$lng);
        $congReq = CongressAPI::gps($lat, $lng);
        $stateReq = StateAPI::gps($lat, $lng);
        $results = Promise\unwrap([$googReq, $congReq, $stateReq]);

        if (!$this->valid($results))
            return $this->error($results);

        return $this->success($results);
    }

    public function address($address)
    {
        $geo = GoogleAPI::geocode($address);
        $gps = $geo->results[0]->geometry->location;
        //if its a street address, we can get district reps, otherwise just state reps
        foreach($geo->results[0]->types as $type){
            if ($type == 'street_address'){
                return $this->gps($gps->lat, $gps->lng);
            }
            if ($type == 'administrative_area_level_1'){
                $googReq = GoogleAPI::address($address);
                $congReq = CongressAPI::gps($gps->lat, $gps->lng);
                $results = Promise\unwrap([$googReq, $congReq]);

                if (!$this->valid($results))
                    return $this->error($results);

                $c = count($results[1]);
                for ($i = 0; $i < $c; $i++){
                    $rep = $results[1][$i];
                    if (!$rep->isStateLevel()){
                        unset($results[1][$i]);
                    }
                }
                $results[1] = array_values($results[1]);
                return $this->success($results);
            }
        }
    }

    public function success($data)
    {
        $response = (object) $data[0];
        if (!empty($data[1])){
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
        }

        if (!empty($data[2])){
            $response->reps = array_merge($response->reps, $data[2]);
        }

        usort($response->reps, function($a, $b){
            $ia = array_search($a->office, Representative::ranks);
            $ib = array_search($b->office, Representative::ranks);

            if ($ia === false) $ia = 6;
            if ($ib === false) $ib = 6;

            return $ia > $ib;
        });

        $response->reps = array_map(function($rep){
            $filename = $rep->imgFileName();
            if (\File::exists(public_path().$filename))
                $rep->photo = $filename;

            $db = Representative::where('name', $rep->name)->where('division_id', $rep->division_id)->get();
            if (count($db) == 1){
                $rep->load($db->first()->toArray());
            }

            return $rep;

        }, $response->reps);

        return response()->json($response);
    }

    public function valid($results)
    {
        if (isset($results[0]->status) && $results[0]->status == 'error')
            return false;
        if (isset($results[0]->error))
            return false;
        return true;
    }

    public function error($results)
    {
        if (isset($results[0]->status) && $results[0]->status == 'error'){
            return response()->json($results[0]);
        }
        if (isset($results[0]->error)){
            return response()->json(
                (object) [
                    'status' => 'error',
                    'message' => $results[0]->error
                ]
            );
        }
        return response()->json(['status' => 'error']);

    }
}
