<?php

namespace Tests\Feature;

use App\Channel;
use App\Reply;
use App\Thread;
use App\User;
use Tests\DBTestCase;

class ProfilesTest extends DBTestCase
{

    /** @test */
    public function a_user_has_a_profile()
    {
        $user = create(User::class);

        $this->get("profiles/{$user->name }")
            ->assertSee($user->name);
    }


    /** @test */
    public function an_authenticated_user_can_view_his_threads()
    {
        $user = create(User::class);

        $thread = create(Thread::class, ['user_id' => $user->id]);

        $this->signIn($user);

        $this->get("profiles/{$user->name}")
            ->assertSee($thread->title)
            ->assertSee($thread->body);
    }

}
