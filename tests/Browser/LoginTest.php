<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class LoginTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */


    public function test_Login_unsuccess()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email','smg@gmail.com')
                ->type('password','1234596786')
                ->press('ورود')
                ->pause(2000)
                ->assertSee('ایمیل یا رمز خود را اشتباه وارد کرده اید');
        });
    }

    public function test_Login_success()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email','smg@gmail.com')
                ->type('password','12345678')
                ->press('ورود')
                ->pause(2000)
                ->assertPathIs('/');
        });
    }


}
