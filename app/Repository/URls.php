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
    static $image = 'http://localhost:80/ContentManagerPanel/images';
    // path : UserController@handleProviderCallback
    static $googleLogin = 'http://localhost/google/';
    static $googleRedirect = 'http://localhost/googleLogin/';
    // path : RouteRedirectMiddleware
    static $myGoogleLogin = 'http://localhost/login/google';
    static $myGoogleLoginCallback = 'http://localhost/api/user/login/google/callback';
    static $excelImport = 'http://localhost/excel-import';
    static $excelExport = 'http://localhost/excel-export';
    static $gate = 'https://etrix.ir/payment-gate';
    static $verify = 'https://etrix.ir/credit-verify';
}