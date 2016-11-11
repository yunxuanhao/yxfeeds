<?php

namespace App\Jobs;

use App\Models\User;

class Follow extends Job
{
    protected $follow_data;

    /**
     * FollowJob constructor.
     * @param array $follow_data
     */
    public function __construct(array $follow_data)
    {
        $this->follow_data = $follow_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $uid = $this->follow_data->uid;
        $follow_id = $this->follow_data->follow_id;
        $user_model = User::getInstance();
        $ids = $user_model->getUserTimeline($follow_id ,true);

        $follow_status = app('db')->table('follow')->where($this->follow_data)->pluck('status')->first();

        if($follow_status == 1){
            exit();
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
        if(app('db')->table('feed')->insert($data) && app('db')->table('follow')->where($this->follow_data)->update(['status' => 1])){
            app('db')->commit();
        }else{
            app('db')->rollback();
        }

    }
}
