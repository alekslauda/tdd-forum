<?php

namespace Tests\Unit;

use App\Thread;
use Tests\DBTestCase;

class ThreadTest extends DBTestCase
{
    protected $thread;

    public function setUp()
    {
        parent::setUp();
        $this->thread = create(Thread::class);
    }

    /** @test */
    public function it_has_replies()
    {
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $this->thread->replies);
    }

    /** @test */
    public function it_has_a_creator()
    {
        $this->assertInstanceOf('App\User', $this->thread->creator);
    }

    /** @test */
    public function it_can_add_a_reply()
    {
        $this->thread->addReply([
            'user_id' => 1,
            'body' => 'test reply'
        ]);

        $this->assertCount(1, $this->thread->replies);
    }

    /** @test */
    public function it_has_a_channel()
    {
        $this->assertInstanceOf('App\Channel', $this->thread->channel);
    }

    /** @test */
    public function it_has_a_channel_slug_in_the_path()
    {
        $this->assertEquals("/threads/{$this->thread->channel->slug}/{$this->thread->channel->id}", $this->thread->path());
    }
}
