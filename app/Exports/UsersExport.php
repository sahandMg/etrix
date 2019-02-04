<?php

namespace App\Exports;

use App\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class UsersExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {

        $nameArray = [];

        $columnArrays = DB::table('helpers')->get()->pluck('helper');
        foreach ($columnArrays as $key => $columnArray){

            $columnArrayU = unserialize($columnArray);

            for($i = 0 ; $i < count($columnArrayU); $i ++){
//                $nameArray[$columnArrayU[$i]] =  $tr->setSource('en')->setTarget('fa')->translate(str_replace('_',' ',$columnArrayU[$i]));
                $nameArray[$columnArrayU[$i]] =  str_replace('_',' ',$columnArrayU[$i]);

            }
        }
        return collect(array_keys($nameArray));


    }
}
