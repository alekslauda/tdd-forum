<?php

namespace Tests\Feature;

use App\Reply;
use App\Thread;
use App\User;
use Tests\DBTestCase;

class ParticipateInThreadsTest extends DBTestCase
{
    protected $thread;

    public function setUp()
    {
        parent::setUp();
        $this->thread = create(Thread::class);
    }

    /** @test */
    public function an_authenticated_user_can_participate_in_forum_thread()
    {
        $this->signIn();

        $reply = make(Reply::class);

        $this->post($this->thread->path() . '/replies', $reply->toArray());

        $this->get($this->thread->path())
            ->assertSee($reply->body);
    }

    /** @test */
    public function an_reply_has_a_body()
    {
        $this->publish(Reply::class,$this->thread->path() . '/replies', ['body' => null])
            ->assertSessionHasErrors('body');
    }

    /** @test */
    public function an_reply_has_a_valid_thread()
    {
        factory(Thread::class, 2)->create();

        $this->publish(Reply::class,$this->thread->path() . '/replies', ['thread_id' => 999])
            ->assertSessionHasErrors('thread_id');
    }

    /** @test */
    public function an_unauthenticated_user_try_to_participate_in_a_forum_thread()
    {
        $this->expectException('Illuminate\Auth\AuthenticationException');

        $this->post($this->thread->path() . '/replies', []);
    }

}
