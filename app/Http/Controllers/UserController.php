<?php

namespace App\Http\Controllers;

use App\Jobs\Follow;
use App\Jobs\UnFollow;
use Illuminate\Http\Request;
use Illuminate\Queue\Queue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

    /**
     * 返回用户个人信息
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id){
        $results = app('db')->table('user')->where('id',$id)->first();
        return $this->getResult($results);
    }

    public function timeline($id){

    }

    public function feed($id){

    }

    public function follow(Request $request){
        $user = app('auth')->user();
        $uid = $user->id;
        $follow_id = $request->input('follow_id');
        $time = time();

        $follow_data = ['uid'=>$uid,'follow_id'=>$follow_id,'create_at' => $time];
        $followed_data = ['uid'=>$follow_id,'followed_id'=>$uid,'create_at' => $time];

        $follow_where = ['uid'=>$uid,'follow_id'=>$follow_id];
        $data = app('db')->table('follow')->where($follow_where)->first();
        if(!empty($data)){
            return $this->getResult(false);
        }

        app('db')->beginTransaction();
        if(app('db')->table('follow')->insert($follow_data) && app('db')->table('followed')->insert($followed_data)){
            app('db')->commit();
            $this->dispatch(new Follow($follow_data));
            return $this->getResult();
        }else{
            app('log')->error('follow fail || '.json_encode($follow_data));
            app('db')->rollback();
            return $this->getResult(false);
        }
    }

    public function unfollow(Request $request){
        $user = app('auth')->user();
        $uid = $user->id;
        $follow_id = $request->input('follow_id');

        $follow_data = [['uid',$uid],['follow_id',$follow_id]];
        $followed_data = [['uid',$follow_id],['followed_id',$uid]];

        app('db')->beginTransaction();
        if(app('db')->table('follow')->where($follow_data)->delete() && app('db')->table('followed')->where($followed_data)->delete()){
            app('db')->commit();
            dispatch(new UnFollow($follow_data));

            return $this->getResult();
        }else{
            app('log')->info('unfollow fail || '.json_encode($follow_data));
            app('db')->rollback();
            return $this->getResult(false);
        }
    }
}
