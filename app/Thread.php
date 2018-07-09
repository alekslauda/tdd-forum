<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    protected $guarded = [];

    protected $with = ['creator', 'channel'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('replyCount', function($builder){
           $builder->withCount('replies');
        });

        static::deleting(function($thread) {
            $thread->replies()->delete();
        });

        static::created(function($thread){
            Activity::create([
                'user_id' => auth()->id(),
                'type' => 'created_thread',
                'subject_id' => $thread->id,
                'subject_type' => 'App\Thread'
            ]);
        });

        /* using with global scope */

        /**
            This can have use case for example if we are writting api and we dont want to eager load something
         * Model::withoutGlobalScopes()->first()
         *
            static::addGlobalScope('creator', function($builder){
            $builder->withCount('creator');
            });
         */
    }

    public function path()
    {
        return "/threads/{$this->channel->slug}/{$this->id}";
    }

    public function replies()
    {
        return $this->hasMany('App\Reply');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function addReply($reply)
    {
        $this->replies()->create($reply);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function scopeFilter($query, $filters)
    {
        return $filters->apply($query);
    }
}
