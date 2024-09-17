<?php

namespace App\Http\Controllers;


use Carbon\Carbon;

abstract class Controller
{
    

    public function checkTime()
    {
        $current_time = Carbon::now();
        $start_time = Carbon::createFromTime(15, 45, 0);
        $end_time = Carbon::createFromTime(24, 30, 0); 
        if ($current_time->between($start_time, $end_time)) {
            return true;
        } else {
            return false;
        }
    }
}
