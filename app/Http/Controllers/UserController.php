<?php

namespace App\Http\Controllers;

use App\Jobs\Follow;
use App\Jobs\UnFollow;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{

    /**
     * 返回用户个人信息
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id){
        $results = app('db')->table('user')->where('id',$id)->first();
        return jsonResult($results);
    }

    public function timeline($id){
        $user_model = User::getInstance();
        $data = $user_model->getUserTimeline($id);
        return jsonResult($data);
    }

    public function feed(){
        $user = app('auth')->user();
        $follow_where = array(
            'uid' => $user->id,
            'status' => 0,
        );
        $follow_undone = app('db')->table('follow')->where($follow_where)->get();
        if(!$follow_undone->isEmpty()){
            $follow_model = \App\Models\Follow::getInstance();
            foreach ($follow_undone as $follow_data){
                $follow_model->addFollow($follow_data);
            }
        }
        $noisy_ids = app('db')->table('feed')->where('uid',$user->id)->pluck('noisy_id');
        $delete_ids = app('db')->table('noisy_delete')->where('status',0)->pluck('noisy_id');
        $noisy_ids = array_diff($noisy_ids,$delete_ids);
        $data = app('db')->table('noisy_index')->whereIn('noisy.id',$noisy_ids)->leftJoin('noisy','noisy_index.id','=','noisy.id')->get();

        return jsonResult($data);
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
            return jsonResult(false);
        }

        app('db')->beginTransaction();
        if(app('db')->table('follow')->insert($follow_data) && app('db')->table('followed')->insert($followed_data)){
            app('db')->commit();
            $this->dispatch(new Follow($follow_data));
            return jsonResult();
        }else{
            app('log')->error('follow fail || '.json_encode($follow_data));
            app('db')->rollback();
            return jsonResult(false);
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

            return jsonResult();
        }else{
            app('log')->info('unfollow fail || '.json_encode($follow_data));
            app('db')->rollback();
            return jsonResult(false);
        }
    }
}
