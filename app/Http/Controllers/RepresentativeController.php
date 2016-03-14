<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp\Promise;
use App\Location;
use App\Representative;
use App\Report;
use GoogleAPI;
use StateAPI;

class RepresentativeController extends Controller
{

    /**
     * Any query page view (/zip, /state, etc.)
     * @return view
     */
    public function view(Request $request)
    {
        return view('pages.home');
    }

    /**
     *  General string query (not zip or lat/lng) - could be address, city, state, or name
     * @param  string $query text input from homepage
     * @return json
     */
    public function query($query)
    {
        $res = new \stdClass();

        //check if query is a state
        $state = Location::isState($query);
        if (null !== $state) {
            $res->reps = Representative::state($state)->get()->all();
            $res->location = (object) [
                'state' => $state,
                'state_name' => Location::states[$state]
            ];
            $res->reps[] = Representative::where('office', 'President')->first();
            usort($res->reps, 'rankSort');
            return response()->json($res);
        }

        //if query has number try address
        if (preg_match('/[0-9,]/', $query)) {
            $address = $this->address($query);
            if (isset($address->getData()->status) && $address->getData()->status == "error") {
                return $this->error($address->getData()->message);
            }
            if (count($address->getData()->reps) > 0) {
                return $address;
            }
        }

        //if query doesnt have number try name
        $reps = Representative::name($query)->orderBy('name')->get()->all();
        if (count($reps) > 0) {
            $res->reps = $reps;
            return response()->json($res);
        }

        //if no name, or state, try address
        $address = $this->address($query);
        if (isset($address->getData()->reps) && count($address->getData()->reps) > 0) {
            return $address;
        }

        return $this->error('No results.');
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
        if (null === $l) {
            return [
                'status' => 'error',
                'message' => 'zipcode not found'
            ];
        }

        $reps = Representative::atLocation($l);
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
        $stateReq = StateAPI::gps($lat, $lng, ['boundary_id']);
        $resp = new \stdClass();
        $results = Promise\unwrap([$googReq, $stateReq]);

        if (isset($results[0]->status) && $results[0]->status == "error") {
            return $this->error($results[0]->message);
        }

        $divisions = array_unique(array_merge(
            $results[0]->divisions,
            array_pluck($results[1], 'division')
        ));

        $reps = Representative::whereIn('division', $divisions)->get()->all();

        usort($reps, 'rankSort');

        $resp->reps = $reps;

        if (isset($results[0]->location)) {
            $resp->location = $results[0]->location;

            if (isset($resp->location->state) && isset(Location::states[$resp->location->state])) {
                $resp->location->state_name = Location::states[$resp->location->state];
            }
        }

        return response()->json($resp);
    }

    /**
     * Query by address
     * @param  string $address any google-able address (street + zip, state, zip, etc.)
     * @return json
     */
    public function address($address)
    {
        $geo = GoogleAPI::geocode($address);
        if (count($geo->results) == 0) {
            return $this->error('No results.');
        }
        $result = $geo->results[0]; //first is always most "accurate" says google
        $gps = $result->geometry->location;
        return $this->gps(round($gps->lat, 4), round($gps->lng, 4));
    }

    /**
     * GET /{$id}
     */
    public function show($id)
    {
        return Representative::where('_id', $id)->first();
    }

    /**
     * GET /edit/{$id}
     */
    public function edit(Request $request, $id)
    {
        $q = Representative::where('_id', $id)->first();
        if ($request->has('redirect')) {
            $request->session()->put('redirect', $request->input('redirect'));
        }

        return view('pages.edit', ['rep' => $q]);
    }

    /**
     * POST /edit/{$id}
     */
    public function store(Request $request, $id)
    {
        $q = Representative::where('_id', $id)->first();
        if (null === $q) {
            return $this->error("no representative with id: $id");
        }
        //todo: validator
        foreach ($request->all() as $key => $value) {
            if (in_array($key, ['redirect', 'token'])) {
                continue;
            }
            if ($key == 'clear_reports' && $value === 'yes') {
                $q->reports()->delete();
            }
            if (is_array($value)) {
                $value = array_filter($value, function ($a) {
                    return !empty($a);
                });
            } else {
                $value = trim($value);
            }
            $q->$key = $value;
        }

        $q->save();

        if ($request->session()->has('redirect')) {
            return redirect($request->session()->get('redirect'));
        }

        return redirect('/')->with('status', 'Saved!');
    }

    /**
     * GET /rep/{id}/flag
     */
    public function flag(Request $request, $id)
    {
        $rep = Representative::where('_id', $id)->first();
        if (null === $rep) {
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
