<?php
/**
 * Created by yunxuanhao@gmail.com
 * Date: 16/11/11
 * Time: 下午2:09
 */

/**
 * 错误码获取错误信息
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
 * json格式返回结果
 * @param bool $data
 * @return \Illuminate\Http\JsonResponse
 */
function jsonResult($data = true){
    if(is_object($data)){
        $data = json_decode(json_encode($data),true);
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

/**
 * 验证邮箱格式
 * @param $email
 * @return bool
 */
function is_email($email){
    return preg_match('/^[_.0-9a-z-a-z-]+@([0-9a-z][0-9a-z-]+.)+[a-z]{2,4}$/',$email);
}

/**
 * 验证url格式
 * @param $url
 * @return int
 */
function is_url($url){
    return preg_match('/(http|https):\/\/[a-zA-Z0-9\-]+(\.[a-zA-Z0-9]+)+([-A-Z0-9a-z_\$\.\+\!\*\(\)\/\,\:;@&=\?~#%]*)*/i', $url);
}