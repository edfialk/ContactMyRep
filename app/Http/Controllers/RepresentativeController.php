<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp\Promise;
use App\Location;
use App\Representative;
use App\Report;
use App\Providers\IPInfo\IPInfo;
use GoogleAPI;
use StateAPI;

class RepresentativeController extends Controller
{

    public function test()
    {
        $reps = Representative::where('photo', 'like', 'http://contactmyreps.org%')->get()->each(Function($rep){
            $photo = str_replace('http://contactmyreps.org', '', $rep->photo);
            $rep->photo = $photo;
            $rep->save();
        });
    }

    /**
     * Home Page View
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
        if (\Auth::check())
            $request->session()->put('backUrl', $request->fullUrl()); //for post edit redirect

        return view('pages.home');
    }

    /**
     * Query by zipcode
     * @param  string $zipcode 5 digit zipcode - validated in route
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

        if (isset($results[0]->location)){
            $resp->location = $results[0]->location;

            if (isset($resp->location->state))
                $resp->location->state_name = Location::states[$resp->location->state];
        }

        return response()->json($resp);
    }

    public function query($query)
    {
        $res = new \stdClass();

        //check if query is a state
        $states = Location::states;
        $state_names = array_map('strtolower', array_values($states));
        $state_abbrevs = array_keys($states);

        if (in_array(strtolower($query), $state_names)){
            $state = $state_abbrevs[array_search(strtolower($query), $state_names)];
        }else if (in_array(strtoupper($query), $state_abbrevs)){
            $state = strtoupper($query);
        }

        if (isset($state)){
            $res->reps = Representative::state($state);
            $res->location = (object) [
                'state' => $state,
                'state_name' => Location::states[$state]
            ];
            $res->reps[] = Representative::where('office','President')->first();
            usort($res->reps, 'rankSort');
            return response()->json($res);
        }

        //if query has a number, try address
        if (preg_match('/[0-9]/', $query)){
            $address = $this->address($query);
            if (isset($address->getData()->status) && $address->getData()->status == "error"){
                return $this->error($address->getData()->message);
            }
            if (count($address->getData()->reps) > 0){
                return $address;
            }
        }

        $reps = Representative::name($query)->orderBy('name')->get()->all();
        if (count($reps) > 0){
            $res->reps = $reps;
            return response()->json($res);
        }

        return $this->error('No results.');
    }

    /**
     * Query by address
     * @param  string $address any google-able address (street + zip, state, zip, etc.)
     * @return json
     */
    public function address($address)
    {
        $geo = GoogleAPI::geocode($address);
        if (count($geo->results) == 0){
            return $this->error('No results.');
        }
        $result = $geo->results[0]; //first is always most "accurate" says google
        $gps = $result->geometry->location;
        return $this->gps($gps->lat, $gps->lng);
    }

    /**
     * GET /{$id}
     */
    public function show($id)
    {
        return Representative::where('_id',$id)->first();
    }

    /**
     * GET /edit/{$id}
     */
    public function edit($id)
    {
        $q = Representative::where('_id',$id)->first();
        return view('pages.edit', ['rep' => $q] );
    }

    /**
     * POST /edit/{$id}
     */
    public function store(Request $request, $id)
    {
        $q = Representative::where('_id',$id)->first();
        if (is_null($q)){
            return $this->error("no representative with id: $id");
        }
        //todo: validator
        foreach($request->all() as $key=>$value){
            if ($key == '_token') continue;
            if ($key == 'clear_reports' && $value === 'yes'){
                $q->reports()->delete();
            }
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
     * GET /rep/{id}/flag
     */
    public function flag(Request $request, $id)
    {
        $rep = Representative::where('_id', $id)->first();
        if (is_null($rep)){
            return $this->error("no representative with id: $id");
        }

        $rep->reports()->create([
            'text' => $request->input('text', '')
        ]);
    }
    /**
     * give json error
     * @param  string $error message
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
