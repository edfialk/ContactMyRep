<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Representative;

class RepresentativeController extends Controller
{

    protected $sunlight;
    protected $opensecrets;

    public function viewZipcode($zipcode)
    {
        $reps = Representative::atZip($zipcode);
        $districts = [];
        $state;
        foreach($reps as $rep){
            if (isset($rep->district) && !in_array($rep->district, $districts)){
                array_push($districts, $rep->district);
            }
            if (isset($rep->state)){
                $state = $rep->state;
            }
        }
        if ($state && count($districts) > 0){
            foreach($districts as $d){
                $reps = array_merge($reps, Representative::atDistrict($state, $d));
            }
        }

        $multiple_districts = count($districts) > 1;

        return view('pages.zip', [
            'reps' => $reps,
            'multiple_districts' => $multiple_districts
        ]);
    }

    public function byDistrict($state, $district)
    {
        $reps = Representative::atDistrict($state, $district);
        return response()->json($reps);
    }

    public function byZipcode($zipcode)
    {
        $reps = Representative::atZip($zipcode);
        return response()->json($reps);
    }

}
