<?php

namespace App\Providers\Google;

use Illuminate\Support\Facades\Facade;

class GoogleAPIFacade extends Facade {
    protected static function getFacadeAccessor() { return 'App\Providers\Google\GoogleAPI'; }
}