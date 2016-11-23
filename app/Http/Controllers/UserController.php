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
//        $follow_undone = app('db')->table('follow')->where($follow_where)->get();
//        if(!$follow_undone->isEmpty()){
//            $follow_model = \App\Models\Follow::getInstance();
//            foreach ($follow_undone as $follow_data){
//                $follow_model->addFollow($follow_data);
//            }
//        }
        $noisy_ids = app('db')->table('feed')->where('uid',$user->id)->pluck('noisy_id')->toArray();
        $delete_ids = app('db')->table('noisy_delete')->where('status',0)->pluck('noisy_id')->toArray();
        $noisy_ids = array_diff($noisy_ids,$delete_ids);
        $data = app('db')->table('noisy_index')->whereIn('noisy.id',$noisy_ids)->leftJoin('noisy','noisy_index.id','=','noisy.id')->get();

        return jsonResult($data);
    }

    public function follow(Request $request){
        $user = app('auth')->user();
        return jsonResult($user);
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
            dispatch(new Follow($follow_data));
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

    public function register(Request $request){
        $input = $this->getInput($request , ['email','name','avatar','password']);
        if(is_string($input)){
            return jsonResult(10002);
        }
        if(app('db')->table('user')->where('email',$input['email'])->first()){
            return jsonResult(10005);
        }


        $input['create_at'] = time();
        $input['password'] = encrypt($input['password']);
        if(!is_email($input['email'])) {
            return jsonResult(10003);
        }
        if(!is_url($input['avatar'])) {
            return jsonResult(10004);
        }

        if($uid = app('db')->table('user')->insertGetId($input)){
            $verify_token = encrypt($input['email'].'yxfeed');
            app('cache')->put($uid , $verify_token,300);
            $text = '感谢您的注册,请点击该链接以验证您的邮箱  '.config('constants.domain').'user/verify?uid='.$uid.'&token='.$verify_token;
            app('mailer')->raw($text, function($message) use ($input)
            {
                 $message->to($input['email'])->subject('邮箱验证');
            });
            return jsonResult();
        }

        return jsonResult(false);
    }

    public function verify(Request $request){
        $input = $this->getInput($request , ['uid','token']);
        $user = app('db')->table('user')->where('id',$input['uid'])->first();
        if($user->verifyed === 1){
            return '邮箱已经验证';
        }
        if (!app('cache')->has($input['uid'])) {
            return '链接已经失效';
        }
        $verify_token = app('cache')->get($input['uid']);

        if($verify_token === $input['token']){
            app('db')->table('user')->where('id',$input['uid'])->update(['verifyed' => 1]);
            app('cache')->forget($input['uid']);
            return '邮箱验证成功';
        }else{
            return '邮箱验证失败';
        }
    }

    public function login(Request $request){
        $input = $this->getInput($request , ['email','password']);

        $user = app('db')->table('user')->where('email',$input['email'])->first();

        if(empty($user)){
            return jsonResult(10006);
        }elseif(decrypt($user->password) != $input['password']){
            return jsonResult(10007);
        }

        $access_token = md5(base64_encode(md5($user->password . $user->email . time() .$user->id. rand(10000,99999))));
        app('cache')->put($user->id ,$access_token,1800);

        $arr_user = json_decode(json_encode($user),true);

        $arr_user['access_token'] = $access_token;

        return jsonResult($arr_user);
    }
}
