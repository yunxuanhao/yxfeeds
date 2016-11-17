<?php

namespace App\Jobs;

class DeleteFeed extends Job
{
    protected $noisy_index;

    /**
     * FollowJob constructor.
     * @param array $noisy_index
     */
    public function __construct($noisy_index)
    {
        $this->noisy_index = $noisy_index;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $id = $this->noisy_index->id;
        app('db')->beginTransaction();
        if(app('db')->table('feed')->where('noisy_id',$id)->delete() && app('db')->table('noisy_delete')->where('noisy_id',$id)->update(['status' => 1])){
            app('db')->commit();
        }else{
            app('db')->rollback();
        }
    }
}
