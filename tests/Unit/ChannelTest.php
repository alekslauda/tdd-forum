<?php

namespace Tests\Unit;

use App\Channel;
use App\Reply;
use App\Thread;
use Tests\DBTestCase;

class ChannelTest extends DBTestCase
{
    /** @test */
    public function it_has_threads()
    {
        $channel = create(Channel::class);
        $thread = create(Thread::class, ['channel_id' => $channel->id]);

        $this->assertTrue($channel->threads->contains($thread));

    }
}
