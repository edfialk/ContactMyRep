<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SiteTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testHome()
    {
    	$this->visit('/')->assertResponseOk();
    }

    public function testZip()
    {
    	$faker = \Faker\Factory::create();
    	$zip = $faker->postcode();
        $this->get($zip)->assertResponseOk();
    }

    public function testDistrict()
    {
    	$faker = \Faker\Factory::create();
    	$state = $faker->stateAbbr();
        $this->get($state.'/1')->assertResponseOk();
    }

    public function testGPS()
    {
        $faker = \Faker\Factory::create();
        $lat = $faker->latitude();
        $lng = $faker->longitude();
        $this->get($lat.'/'.$lng)->assertResponseOk();
    }
}
