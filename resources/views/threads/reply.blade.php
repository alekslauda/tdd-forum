<div class="panel panel-default">
    <div class="panel-heading">

        <div class="level">
            <h5 class="flex">
                <a href="/profiles/{{ $reply->owner->name }}">
                    {{ $reply->owner->name }}
                </a> said {{ $reply->created_at->diffForHumans()  }} ...
            </h5>
            <div>
                <form method="POST" action="/replies/{{ $reply->id }}/favorites">
                    {{ csrf_field() }}
                    @if(auth()->id() != $reply->owner->id)
                    <button type="submit" class="btn btn-default" {{ $reply->isFavorited() ? 'disabled' : '' }}>
                        {{ $reply->favorites_count }} {{ str_plural('Favorite', $reply->favorites_count) }}
                    </button>
                    @endif
                </form>
            </div>
        </div>

    </div>
    <div class="panel-body">
        {{ $reply->body }}
    </div>
</div>