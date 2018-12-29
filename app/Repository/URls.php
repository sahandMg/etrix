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
    static $image = 'https://etrix.ir/ContentManagerPanel/images';
    // path : UserController@handleProviderCallback
    static $googleLogin = 'https://etrix.ir/google/';
    static $googleRedirect = 'https://etrix.ir/googleLogin/';
    // path : RouteRedirectMiddleware
    static $myGoogleLogin = 'https://etrix.ir/login/google';
    static $myGoogleLoginCallback = 'https://etrix.ir/api/user/login/google/callback';
    static $excelImport = 'http://localhost/excel-import';
    static $excelExport = 'http://localhost/excel-export';
    //path PaymentController
    static $gate = 'https://etrix.ir/payment-gate';
    static $verify = 'https://etrix.ir/credit-verify';
    static $truePayment = 'https://etrix.ir/User/buy/completed';
    static $falsePayment = 'https://etrix.ir/User/buy/Uncompleted';

}