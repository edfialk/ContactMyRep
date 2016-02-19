<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Report;
use App\Representative;

class AdminController extends Controller
{

    public function reports()
    {
        $reps = Representative::has('reports')->get();
        return view('admin.reports', compact('reps') );
    }

}
