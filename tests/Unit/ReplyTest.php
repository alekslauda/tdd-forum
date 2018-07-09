<?php

namespace Tests\Unit;

use App\Reply;
use Tests\DBTestCase;

class ReplyTest extends DBTestCase
{
    /** @test */
    public function it_has_owner()
    {
        $reply = create(Reply::class);

        $this->assertInstanceOf('App\User', $reply->owner);

    }
}
