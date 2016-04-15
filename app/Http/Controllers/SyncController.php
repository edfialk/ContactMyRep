<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use StateAPI;
use App\Location;
use App\Representative;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class SyncController extends Controller
{

    public function openStates()
    {
        $reps = [];
        $count = 0;
        foreach(Location::states as $state=>$state_name){
            if ($count < 20){
                $count++;
                continue;
            }
            echo "$state \n";
            $request = StateAPI::state($state)->then(function($data) use (&$reps, &$count){
                foreach($data as $d){
                    if (!isset($d['state']) || !isset($d['chamber']) || !isset($d['district'])) continue;
                    $division = 'ocd-division/country:us/state:'.$d['state'].'/sld';
                    $division .= $d['chamber'] == 'upper' ? 'u' : 'l';
                    $division .= ':'.$d['district'];
                    $entry = [];

                    $rep = Representative::where('division',$division)->first();
                    $entry['old'] = $rep;
                    if (null === $rep){
                        echo "!! new division: $division \n";
                        $rep = Representative::create(['division' => $division]);
                    }
                    $rep->load($d);
                    $entry['change'] = [];
                    foreach($rep->toArray() as $k=>$v){
                        if (null === $entry['old'] || $entry['old']->$k !== $v){
                            $entry['change'][$k] = $v;
                        }
                    }
                    if (count($entry['change']) > 0)
                        $reps[] = $entry;
                }
            });
            $request->wait();
            $count++;
            if ($count > 30){
                break;
            }
        }
        return response()->json($reps);
    }

}
