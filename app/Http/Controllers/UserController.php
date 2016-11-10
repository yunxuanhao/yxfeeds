<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $results = DB::table('user')->where('id',$id)->first();
        return $this->getResult($results);
    }

    public function timeline($id){

    }

    public function feed($id){

    }

    public function follow(Request $request){
        $uid = $request->input('uid');
        $follow_id = $request->input('follow_id');
        $time = time();

        $follow_data = ['uid'=>$uid,'follow_id'=>$follow_id,'create_at' => $time];
        $followed_data = ['uid'=>$follow_id,'followed_id'=>$uid,'create_at' => $time];

        $follow_where = ['uid'=>$uid,'follow_id'=>$follow_id];
        $data = DB::table('follow')->where($follow_where)->first();
        if(!empty($data)){
            return $this->getResult(false);
        }

        DB::beginTransaction();
        if(DB::table('follow')->insert($follow_data) && DB::table('followed')->insert($followed_data)){
            DB::commit();
            return $this->getResult();
        }else{
            Log::error('follow fail || '.json_encode($follow_data));
            DB::rollback();
            return $this->getResult(false);
        }
    }

    public function unfollow(Request $request){
        $uid = $request->input('uid');
        $follow_id = $request->input('follow_id');

        $follow_where = [['uid',$uid],['follow_id',$follow_id]];
        $followed_where = [['uid',$follow_id],['followed_id',$uid]];

        DB::beginTransaction();
        if(DB::table('follow')->where($follow_where)->delete() && DB::table('followed')->where($followed_where)->delete()){
            DB::commit();
            return $this->getResult();
        }else{
            Log::info('unfollow fail || '.json_encode($follow_where));
            DB::rollback();
            return $this->getResult(false);
        }
    }
}
