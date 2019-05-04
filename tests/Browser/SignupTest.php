<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class SignupTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function test_signup_unsuccess()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->type('email','smg@gmail.com')
                ->type('password','1234596786')
                ->press('ثبت نام')
                ->pause(2000)
                ->assertSee('نام خود را وارد کنید');
        });
    }

    public function test_signup_success()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->type('email','smg@gmail.com')
                ->type('name','sahand')
                ->type('password','1234596786')
                ->press('ثبت نام')
                ->pause(2000)
                ->assertPathIs('/');
        });
    }
}
