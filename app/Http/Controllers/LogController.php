<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class LogController extends Controller
{
    public function getLogs()
    {
      $logs = DB::table('logs')->orderBy('id','desc')->paginate(20);

      return view('logs',compact('logs'));

    }
}
