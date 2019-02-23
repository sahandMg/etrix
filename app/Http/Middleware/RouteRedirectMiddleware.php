<?php

namespace App\Http\Middleware;

use App\Repository\URls;
use Closure;

class RouteRedirectMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $url = new URls();
         if(explode('/',$request->path())[0] == 'api' ||
             $request->url() == $url::$myGoogleLogin ||
             $request->url() == $url::$myGoogleLoginCallback ||
             $request->url() == $url::$excelImport||
             $request->url() == $url::$excelExport ||
             $request->url() == $url::$gate ||
             $request->url() == $url::$verify ||
             $request->url() == $url::$sitemap
         ){

             return $next($request);
         }else{

             return response()->view('welcome');
         }
         /* uncomment this to deactivate api routes
         */

//        if(explode('/',$request->path())[0] != 'api'){
//
//            return $next($request);
//        }
    }
}
