<?php

namespace App\Jobs;

class DeleteFeed extends Job
{
    protected $id;

    /**
     * FollowJob constructor.
     * @param array $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $id = $this->id;
        app('db')->beginTransaction();
        if(app('db')->table('feed')->where('noisy_id',$id)->delete() && app('db')->table('noisy_delete')->where('noisy_id',$id)->update(['status' => 1])){
            app('db')->commit();
        }else{
            app('db')->rollback();
        }
    }
}
