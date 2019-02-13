<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use App\Log;
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
      $log = new Log();
       $log->message = $exception->getMessage();
       $log->extra = $exception->getFile(). $exception->getLine();
       $log->level = 'error';
       $log->context = json_encode($this->context());
       $log->env = env('LOG_CHANNEL');
       $log->save();
      parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception){
            // log the error
//            return response()->json([
//                'status' => $exception->getStatusCode(),
//                'error' => $exception->getMessage()
//            ]);

//            return ($exception->getMessage());

        }
        return parent::render($request, $exception);
    }
}
