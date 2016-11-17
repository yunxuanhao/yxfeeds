<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * 前部分为必选参数,后部分为可选参数
     * @param Request $request
     * @param $params
     * @param $number
     * @return array
     */
    protected function getInput(Request $request,$params,$number = 0){
        $input = array();
        foreach ($params as $key => $name) {
            $input[$name] = $request->has($name) ? $request->input($name) : null;
            if(($key >= $number )&& ($input[$name] == null)) {
                return $name;
            }
        }
        return $input;
    }
}
