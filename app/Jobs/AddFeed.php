<?php

namespace App\Jobs;

use Illuminate\Support\Facades\DB;

class AddFeed extends Job
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
        $noisy_index = DB::table('noisy_index')->where('id',$this->id)->first();
        $uid = $noisy_index->uid;

        $followed = DB::table('followed')->where('uid',$uid)->pluck('followed_id');

        $feeds = array();
        foreach ($followed as $uid){
            $feed = array(
                'uid' => $uid,
                'noisy_id' => $this->id,
            );
            $feeds[] = $feed;
        }

        DB::table('feed')->insert($feeds);
    }
}
