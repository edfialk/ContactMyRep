<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SiteTest extends TestCase
{

    public function testPages()
    {
        $this->visit('/')->assertResponseOk();

        //markdown
        $this->visit('api/page/about')->see('ABOUT US');
        $this->get('api/page/terms')->see('TERMS OF SERVICE');

        //js views
        $this->get('contact')->assertResponseOk();
    }
}
