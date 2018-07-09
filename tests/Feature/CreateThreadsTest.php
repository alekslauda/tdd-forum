<?php

namespace Tests\Feature;

use App\Channel;
use App\Reply;
use App\Thread;
use App\User;
use Tests\DBTestCase;

class CreateThreadsTest extends DBTestCase
{

    /** @test */
    public function guests_cannot_see_the_create_page()
    {
        $this->withExceptionHandling();
        $this->get('/threads/create')
            ->assertRedirect('/login');

        $this->post('/threads')
            ->assertRedirect('/login');
    }

    /** @test  */
    public function unathorized_users_cannot_delete_thread()
    {
        $this->withExceptionHandling();

        $thread = create(Thread::class);

        $this->delete($thread->path())
            ->assertRedirect('/login');

        $this->signIn();

        $this->delete($thread->path())
            ->assertStatus(403);
    }

    /** @test  */
    public function authorized_users_can_delete_thread()
    {
        $this->signIn();

        $thread = create(Thread::class, ['user_id' => auth()->id()]);
        $reply = create(Reply::class, ['thread_id' => $thread->id]);

        $this->json('DELETE', $thread->path())
            ->assertStatus(204);

        $this->assertDatabaseMissing('threads', ['id' => $thread->id]);
        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);
    }

    /** @test */
    public function an_authenticated_user_can_create_a_thread()
    {
        $this->signIn();

        $thread = make(Thread::class);

        $response = $this->post('/threads', $thread->toArray());

        $this->get($response->headers->get('Location'))
            ->assertSee($thread->title)
            ->assertSee($thread->body);
    }

    /** @test  */
    public function a_thread_requires_a_title()
    {
        $this->publish(
            Thread::class,
            '/threads',
            ['title' => null]
        )
            ->assertSessionHasErrors('title');
    }

    /** @test  */
    public function a_thread_requires_a_body()
    {
        $this->publish(
            Thread::class,
            '/threads',
            ['body' => null]
        )
            ->assertSessionHasErrors('body');
    }

    /** @test */
    public function a_thread_requires_a_valid_channel()
    {
        $this->publish(Thread::class,'/threads',['channel_id' => null])
            ->assertSessionHasErrors('channel_id');

        factory(Channel::class, 2)->create();

        $this->publish(Thread::class,'/threads',['channel_id' => 999])
            ->assertSessionHasErrors('channel_id');

    }
}
