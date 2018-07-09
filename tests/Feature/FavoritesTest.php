<?php

namespace Tests\Feature;

use App\Channel;
use App\Reply;
use App\Thread;
use App\User;
use Tests\DBTestCase;

class CreateThreadTest extends DBTestCase
{

    /** @test */
    public function guests_cannot_favorite_anything()
    {
        $reply = create(Reply::class);

        $this->withExceptionHandling();

        $this->post('replies/' . $reply->id . '/favorites', [])
        ->assertRedirect('/login');
    }

    /** @test */
    public function an_authenticated_user_can_favorite_a_reply()
    {
        $this->signIn();

        $reply = create(Reply::class);

        $this->post('replies/' . $reply->id . '/favorites');

        $this->assertCount(1, $reply->favorites);
    }

    /** @test */
    public function an_authenticated_user_can_favorite_a_reply_only_one_time()
    {
        $this->signIn();

        $reply = create(Reply::class);

        try {

            $this->post('replies/' . $reply->id . '/favorites');
            $this->post('replies/' . $reply->id . '/favorites');
        } catch (\Exception $ex) {
            $this->fail('Did not expect to add same record twice');
        }

        $this->assertCount(1, $reply->favorites);
    }
}
