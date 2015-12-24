<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Representative;

class RepresentativeController extends Controller
{

    public function viewZipcode($zipcode)
    {
        $reps = Representative::getAllAtZip($zipcode);

        return view('pages.results', [
            'reps' => $reps
        ]);
    }

    public function viewDistrict($state, $district)
    {
        $reps = Representative::getAllAtDistrict($state, $district);

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
        $reps = Representative::getAllAtZip($zipcode);

        return response()->json($reps);
    }

}
