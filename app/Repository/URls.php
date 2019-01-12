<?php
/**
 * Created by PhpStorm.
 * User: Sahand
 * Date: 12/17/18
 * Time: 11:46 AM
 */

namespace App\Repository;


class URls
{
// path : CmController@image
    static $image = 'http://hashbazaar.com/ContentManagerPanel/images';
    // path : UserController@handleProviderCallback
    static $googleLogin = 'http://hashbazaar.com/google/';
    static $googleRedirect = 'http://hashbazaar.com/googleLogin/';
    // path : RouteRedirectMiddleware
    static $myGoogleLogin = 'http://hashbazaar.com/login/google';
    static $myGoogleLoginCallback = 'https://hashbazaar.com/api/user/login/google/callback';
    static $excelImport = 'http://localhost/excel-import';
    static $excelExport = 'http://localhost/excel-export';
    //path PaymentController
    static $gate = 'http://hashbazaar.com/payment-gate';
    static $verify = 'http://hashbazaar.com/credit-verify';
    static $truePayment = 'http://hashbazaar.com/User/buy/completed';
    static $falsePayment = 'http://hashbazaar.com/User/buy/Uncompleted';

}