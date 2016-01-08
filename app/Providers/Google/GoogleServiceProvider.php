<?php

namespace App\Providers\Google;

use Illuminate\Support\ServiceProvider;

use GoogleAPI;

class GoogleServiceProvider extends ServiceProvider
{

    protected $defer = true;

    public function boot()
    {
        $this->setupConfig();
    }

    protected function setupConfig()
    {
/*        $source = realpath(__DIR__.'/../config/sunlightapi.php');
        $this->publishes([$source => config_path('sunlightapi.php')]);
        $this->mergeConfigFrom($source, 'sunlightapi');*/
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(GoogleAPI::class, function($app){
            return new GoogleAPI();
        });
    }

    public function provides()
    {
        return [
            GoogleAPI::class
        ];
    }
}
