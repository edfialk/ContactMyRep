<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp\Promise;
use App\Location;
use App\Representative;
use App\Providers\IPInfo\IPInfo;
use GoogleAPI;
use CongressAPI;
use StateAPI;

class RepresentativeController extends Controller
{

    /**
     * Home Page View
     * @param  Request $request Http Request
     * @return view
     */
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

    /**
     * Any query page view (/zip, /state, etc.)
     * @return view
     */
    public function view(Request $request)
    {
        $request->session()->put('backUrl', $request->fullUrl());
        return view('pages.home');
    }

    /**
     * Query by zipcode
     * @param  string $zipcode 5 digit zipcode
     * @return json
     */
    public function zipcode($zipcode)
    {
        $resp = new \stdClass();

        $l = Location::where('zip', intval($zipcode))->first();
        if (is_null($l)){
            return [
                'status' => 'error',
                'message' => 'zipcode not found'
            ];
        }

        $reps = Representative::atLocation($l);

        $updates = [];
        $keeps = [];
        foreach($reps as $rep){
            in_array('google', $rep->sources) ? array_push($keeps, $rep) : array_push($updates, $rep);
        }

        if (count($updates) > 0){
            GoogleAPI::update($updates);
            foreach($updates as &$u){
                $u = Representative::where('_id', $u->_id)->first(); //refresh from db, ->fresh() should work but doesnt
            }
        }

        $reps = array_merge($updates, $keeps);

        usort($reps, 'rankSort');

        $resp->reps = $reps;
        $resp->location = $l;

        return response()->json($resp);
    }

    /**
     * Query by gps
     * @param  float $lat latitude
     * @param  float $lng longitude
     * @return json
     */
    public function gps($lat, $lng)
    {
        $googReq = GoogleAPI::address($lat.','.$lng);
        $stateReq = StateAPI::gps($lat, $lng);
        $resp = new \stdClass();
        $results = Promise\unwrap([$googReq, $stateReq]);

        if (isset($results[0]->status) && $results[0]->status == "error")
            return $this->error($results[0]->message);

        $divisions = array_unique(array_merge(
            $results[0]->divisions,
            array_pluck($results[1], 'division')
        ));

        $reps = Representative::whereIn('division', $divisions)->get()->all();

        usort($reps, 'rankSort');

        $resp->reps = $reps;
        if (isset($results[0]->location))
            $resp->location = $results[0]->location;

        return response()->json($resp);
    }

    /**
     * Query by address
     * @param  string $address any google-able address (street + zip, state, zip, etc.)
     * @return json
     */
    public function address($address)
    {
        $resp = new \stdClass;

        //check if address is state -> get abbreviation
        $states = Location::states;
        $state_names = array_map('strtolower', array_values($states));
        $state_abbrevs = array_keys($states);

        if (in_array(strtolower($address), $state_names))
            $state = $state_abbrevs[array_search(strtolower($address), $state_names)];
        else if (in_array(strtoupper($address), $state_abbrevs))
            $state = strtoupper($address);

        if (isset($state)){
            $resp->reps = Representative::state($state);
            $resp->location = (object) [
                'state' => $state,
                'state_name' => Location::states[$state]
            ];
            $resp->reps[] = Representative::where('office','President')->first();
            usort($resp->reps, 'rankSort');
            return response()->json($resp);
        }

        $geo = GoogleAPI::geocode($address);
        if (count($geo->results) == 0){
            return $this->error('No results.');
        }
        $result = $geo->results[0]; //first is always most "accurate" says google
        $gps = $result->geometry->location;
        return $this->gps($gps->lat, $gps->lng);
    }

    public function show($id)
    {
        return Representative::where('_id',$id)->first();
    }

    public function edit($id)
    {
        $q = Representative::where('_id',$id)->first();
        // dd($q->getAttributes());
        return view('pages.edit', ['rep' => $q] );
    }

    public function store(Request $request, $id)
    {
        $q = Representative::where('_id',$id)->first();
        if (is_null($q)){
            return $this->error("can't find rep");
        }
        foreach($request->all() as $key=>$value){
            if ($key == '_token') continue;
            if (is_array($value)){
                $value = array_filter($value, function($a){
                    return !empty($a);
                });
            }else{
                $value = trim($value);
            }
            $q->$key = $value;
        }

        $q->save();

        if ($request->session()->has('backUrl'))
            return redirect($request->session()->get('backUrl'))->with('status', 'Saved!');

        return redirect('/')->with('status', 'Saved!');
    }
    /**
     * give json error
     * @param  array $results api response data
     * @return json
     */
    public function error($message)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message
        ]);
    }

}
