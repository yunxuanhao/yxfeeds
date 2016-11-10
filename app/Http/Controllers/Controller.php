<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    //

    protected function getResult($data = true){
        if($data === true){
            $data = array(
                'result' => true,
            );
        }elseif($data === false){
            $data = array(
                'error' => '操作失败',
                'error_code' => 10000,
            );
        }elseif(empty($data)){
            $data = array(
                'error' => '空',
                'error_code' => 10001,
            );
        }
        return response()->json($data);
    }
}
