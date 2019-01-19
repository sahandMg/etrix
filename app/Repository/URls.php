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
    static $image = 'http://etrix.ir/ContentManagerPanel/images';
    // path : UserController@handleProviderCallback
    static $googleLogin = 'http://etrix.ir/google/';
    static $googleRedirect = 'http://etrix.ir/googleLogin/';
    // path : RouteRedirectMiddleware
    static $myGoogleLogin = 'http://etrix.ir/login/google';
    static $myGoogleLoginCallback = 'https://etrix.ir/api/user/login/google/callback';
    static $excelImport = 'http://localhost/excel-import';
    static $excelExport = 'http://localhost/excel-export';
    //path PaymentController
    static $gate = 'http://etrix.ir/payment-gate';
    static $verify = 'http://etrix.ir/credit-verify';
    static $truePayment = 'http://etrix.ir/User/buy/completed';
    static $falsePayment = 'http://etrix.ir/User/buy/Uncompleted';

}