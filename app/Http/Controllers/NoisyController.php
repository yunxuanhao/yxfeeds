<?php

namespace App\Http\Controllers;

use App\Jobs\AddFeed;
use App\Jobs\DeleteFeed;
use Illuminate\Http\Request;

class NoisyController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        //
    }

    public function create(Request $request){
        $user = app('auth')->user();

        $id = app('redis')->incr('noisy_id');

        $data['id'] = $id;
        $data['title'] = $request->input('title');
        $data['content'] = $request->input('content');

        $data_index['id'] = $id;
        $data_index['uid'] = $user->id;
        $data_index['create_at'] = time();

        app('db')->beginTransaction();
        if(app('db')->table('noisy')->insert($data) && app('db')->table('noisy_index')->insert($data_index)){
            app('db')->commit();
            dispatch(new AddFeed($id));

            return jsonResult();
        }else{
            app('log')->error('noisy create fail || '.json_encode(array_merge($data_index,$data)));
            app('db')->rollback();
            return jsonResult(false);
        }
    }

    public function delete(Request $request){
        $user = app('auth')->user();
        $id = $request->input('id');
        $noisy_index = app('db')->table('noisy_index')->where('id',$id)->first();
        if($user->id != $noisy_index->uid){
            return jsonResult(10001);
        }
        
        app('db')->beginTransaction();
        if(app('db')->table('noisy')->where('id',$id)->delete() && app('db')->table('noisy_index')->where('id',$id)->delete() && app('db')->table('noisy_delete')->insert(['noisy_id' => $id])){
            app('db')->commit();
            dispatch(new DeleteFeed($noisy_index));
            return jsonResult();
        }else{
            app('log')->error('noisy delete fail || '.json_encode($id));
            app('db')->rollback();
            return jsonResult(false);
        }
    }
}
