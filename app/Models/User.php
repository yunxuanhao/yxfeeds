<?php
/**
 * Created by yx1@meitu.com
 * Date: 16/11/11
 * Time: 下午3:59
 */

namespace App\Models;


class User extends Model
{
    public function getUserTimeline($id ,$onlyIds = false){
        if($onlyIds){
            $data = app('db')->table('noisy_index')->where('uid',$id)->pluck('id');
        }else{
            $data = app('db')->table('noisy_index')->where('uid',$id)->leftJoin('noisy','noisy_index.id','=','noisy.id')->get();
        }
       return $data;
    }
}