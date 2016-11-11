<?php
/**
 * Created by yx1@meitu.com
 * Date: 16/11/11
 * Time: 下午4:41
 */

namespace App\Models;


class Follow extends Model
{
    public function addFollow($follow_data){
        $uid = $follow_data->uid;
        $follow_id = $follow_data->follow_id;
        $user_model = User::getInstance();
        $ids = $user_model->getUserTimeline($follow_id ,true);

        $follow_status = app('db')->table('follow')->where($follow_data)->pluck('status')->first();

        if($follow_status == 1){
            return false;
        }

        $data = array();
        foreach ($ids as $id){
            $item = array(
                'uid' => $uid,
                'noisy_id' => $id,
            );
            $data[] = $item;
        }
        app('db')->beginTransaction();
        if(app('db')->table('follow')->where($follow_data)->update(['status' => 1]) && app('db')->table('feed')->insert($data) ){
            app('db')->commit();
            return true;
        }else{
            app('db')->rollback();
            return false;
        }
    }
}