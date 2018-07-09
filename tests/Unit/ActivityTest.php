<?php

namespace Tests\Unit;

use App\Channel;
use App\Reply;
use App\Thread;
use App\User;
use Tests\DBTestCase;

class ActivityTest extends DBTestCase
{

    /** @test */
    public function it_records_activity_when_a_thread_is_created()
    {
        $this->signIn();

        $thread = create(Thread::class);

        $this->assertDatabaseHas(
            'activities', [
                'type' => 'created_thread',
                'user_id' => auth()->id(),
                'subject_id' => $thread->id,
                'subject_type' => Thread::class
            ]
        );
    }

}
