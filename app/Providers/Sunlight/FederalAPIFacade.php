<?php

namespace App\Providers\Sunlight;

use Illuminate\Support\Facades\Facade;

class FederalAPIFacade extends Facade {
    protected static function getFacadeAccessor() { return 'App\Providers\Sunlight\FederalAPI'; }
}