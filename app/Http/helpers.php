<?php
/**
 * Created by yunxuanhao@gmail.com
 * Date: 16/11/11
 * Time: 下午2:09
 */

/**
 * @param $error_code
 * @return array
 */
function getError($error_code){
    if(empty($error_code)){
        $error_code = 10000;
    }
    $error = config(sprintf('error.%s',$error_code));

    $data = array(
        'error_code' => $error_code,
        'error' => $error,
    );
    return $data;
}

/**
 * @param bool $data
 * @return \Illuminate\Http\JsonResponse
 */
function jsonResult($data = true){
    if(is_object($data)){
        $data = $data->toArray();
    }
    if($data === true){
        $data = array(
            'result' => true,
        );
    }elseif(empty($data) || is_numeric($data)){
        $data = getError($data);
    }
    return response()->json($data);
}