<?php

namespace App\Http\Controllers;

use App\Project;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;


class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }
/*
 *  Required Params => token, name
 */
    public function createProject(Request $request){
//        $validator = Validator::make($request->all(),['name'=>'unique:projects']);
//        if($validator->fails()){
//           return json_decode($validator->errors(),true)['name'];
//        }

        $prjNames = DB::table('projects')->where('user_id',Auth::id())->pluck('name');
        if(sizeof($prjNames) > 0 ){
            if(in_array($request->name,$prjNames->toArray())){
                return 'نام پروژه تکراری است';
            }
        }
        $project = new Project();
        $project->name = $request->name;
        $project->user_id = Auth::id();
        $project->status = 0;
        $project->price = 0;
        $project->save();
        return 'پروژه شما ایجاد شد';
    }
    // Sends back project detail for given token
    /*
     *  Required Params => token,project
     *
     */
    public function detail(Request $request){

        $token = $request->token;
        try{
            $id = DB::table('projects')->where('name',$request->project)->first()->id;
            $cart = unserialize(DB::table('carts')->where('project_id',$id)->first()->name);
        }catch (\Exception $exception){

            return $exception;
        }

        return $cart;
    }
    // TODO Add read cart to send user project names

   /*
    *  Required Params => token,project
    *   deletes user project
    */
    public function deleteProject(Request $request){

        try{
            DB::table('projects')->where('name',$request->project)->first()->id;
        }catch (\Exception $e){

            return 'project not found';
        }

        DB::table('projects')->where('name',$request->project)->delete();

        return 200;
    }
    /*
     *  Required Params => token
     */
    public function readProject(Request $request){

        $token = $request->token;

        $prjs =  User::where('token',$token)->first()->projects;
        foreach ($prjs as $prj){

            $prj['created_at'] = Jalalian::fromCarbon($prj->created_at) ;
        }
        return $prjs;
    }
}
