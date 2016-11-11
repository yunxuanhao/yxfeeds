<?php

namespace App\Jobs;

class UnFollow extends Job
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
        $follow_model = \App\Models\Follow::getInstance();
        return $follow_model->deleteFollow($this->follow_data);
    }
}
