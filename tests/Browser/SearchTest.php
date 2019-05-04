<?php

namespace Tests\Browser;

use Facebook\WebDriver\WebDriverBy;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class SearchTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function test_keyword_search()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->pause(1000);



            $browser->driver->findElement( WebDriverBy::xpath("//*[@id=\"desktopSearch\"]/div[2]/input") )
                ->sendKeys('stm32');
            $browser->driver->findElement( WebDriverBy::xpath('//*[@id="desktopSearch"]/div[1]/button/span'))->click();
            $browser->pause(3000)->assertSee('سازنده');
        });



    }

    public function test_multi_category(){

        $this->browse(function (Browser $browser){

            $browser->driver->findElement( WebDriverBy::xpath("//*[@id=\"desktopSearch\"]/div[2]/input") )
                ->clear()->sendKeys('st');
            $browser->driver->findElement( WebDriverBy::xpath('//*[@id="desktopSearch"]/div[1]/button/span'))->click();
            $browser->pause(2000)->assertSee('جست و جوی شما در دسته های زیر پیدا شده است');
        });
    }

    public function test_category(){

        $this->browse(function (Browser $browser){
            $browser->driver->findElement( WebDriverBy::xpath("//*[@id=\"desktopSearch\"]/div[2]/input") )
                ->clear();
            $browser->select('category', 'Capacitors');
            $browser->driver->findElement( WebDriverBy::xpath('//*[@id="desktopSearch"]/div[1]/button/span'))->click();
            $browser->pause(2000)->assertSee('جست و جوی شما در دسته های زیر پیدا شده است');
        });
    }


}
