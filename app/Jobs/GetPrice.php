<?php

namespace App\Jobs;

use App\Common;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GetPrice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $shopResp;
    protected $keyword;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($keyword)
    {
        $this->keyword = $keyword;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//        ------------- Get part price form shop ------------

        $stop = 0;
        $parts = [];

        $start = Carbon::now();
        $partClass = Common::where('manufacturer_part_number','like',"%$this->keyword%")->get();
        $componentIds = $partClass->pluck('component_id');
        foreach($componentIds as $componentId){
            $productId = DB::table('components')->where('id',$componentId)->first()->product_id;

            if($productId == 4 || $productId == 26){
                $recapsTable = 1;
            }
        }
        if(isset($recapsTable)){
            $recaps = DB::table('re_caps')->where('value','like',"%$this->keyword%")->get();
            $parts = $recaps->pluck('name');
        }else{

            $parts = $partClass->pluck('manufacturer_part_number');
        }


        Log::info($parts);

        for($i=0;$i<count($parts);$i++) {
            $output =[];
            $stop = 0;
            $path = public_path('storage/V1');
            $command = "cd $path && node index.js $parts[$i]";

            Log::info("Searching for $parts[$i] ...");
            while ($stop == 0) {

                exec($command, $output, $return);

                if (count($output) != 0) {
                    $stop = 1;
                } elseif ($start->diffInSeconds(Carbon::now()) > 5) {
                    $this->shopResp = '435';
                    Log::warning("Get price status: $parts[$i]".' --> '.'435 ' .' search stopped ...');
                    $stop = 1;
                }
            }
            if($this->shopResp != '435') {

                Log::warning($output[0]);
                if (isset($output) && $output[0] != 'not found') {


                    if (count($parts) == 0) {

                        $this->shopResp = '415';
                        Log::warning('Get price status:' . $parts[$i] . ' --> ' . '415');
                    }
                    $arr = explode(',',$output[0]);
                    $price = $arr[0];
                    if(isset($arr[1])&& sizeof($arr) > 0){

                        $quantity = ceil($arr[1]/2);
//                        $partClass[$i]->update(['quantity_available'=>$quantity]);
                        $partClass->orderBy('id','desc')->chunk(100,function ($queries) use($quantity,$price,$parts,$i){
                            foreach ($queries as $query){
                                $query->where('manufacturer_part_number',$parts[$i])->first()->update(['quantity_available'=>$quantity,'unit_price'=>$price]);

                            }
                        });
                    }
//                    $partClass[$i]->update(['unit_price'=>$price]);

                    Log::warning("Get price status: 200");
                } elseif (isset($output) && $output[0] == 'not found') {

                    $this->shopResp = $parts[$i] . ' --> ' . '404';
                    Log::warning('Get price status:' . $parts[$i] . ' --> ' . '404');
                } else {
                    $this->shopResp = $output[0];
                    Log::warning("Get price status: $output[0]");
                }
            }
//
//////                ------------------------------------------------
        }
    }
}
