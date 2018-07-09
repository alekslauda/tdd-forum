<?php

namespace App\Http\Controllers;

use App\Thread;
use Illuminate\Http\Request;

class RepliesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store($channelSlug, Thread $thread, Request $request)
    {
        $this->validate($request, ['body' => 'required', 'thread_id' => 'required|exists:threads,id', 'user_id' => 'required|exists:users,id']);

        $thread->addReply([
            'user_id' => auth()->id(),
            'body' => request('body')
        ]);

        return redirect()->back();
    }
}
