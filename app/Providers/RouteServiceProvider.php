<?php

namespace App\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot(Router $router)
    {
        $router->pattern('zipcode', '\d{5}([\-]?\d{4})?');
        $router->pattern('state', '[a-zA-Z]{2}');
        $router->pattern('district', '\d{1,3}');
        $router->pattern('lat', '^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)');
        $router->pattern('lng', '[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)');

        parent::boot($router);
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function map(Router $router)
    {
        $router->group(['namespace' => $this->namespace], function ($router) {
            require app_path('Http/routes.php');
        });
    }
}
