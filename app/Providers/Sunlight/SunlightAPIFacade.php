<?php

namespace App\Providers\Sunlight;

use Illuminate\Support\Facades\Facade;

class SunlightAPIFacade extends Facade {
    protected static function getFacadeAccessor() { return 'App\Providers\Sunlight\SunlightAPI'; }
}