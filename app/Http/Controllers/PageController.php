<?php


namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Http\Request;
use App\Providers\IPInfo\IPInfo;

class PageController extends Controller
{

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

    public function about()
    {
        return view('markdown.about');
    }

    public function terms()
    {
    	return view('markdown.terms');
    }

}
