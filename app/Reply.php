<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use RecordActivity;

    protected $guarded = [];
    protected $appends = ['favoritesCount', 'isFavorited', 'isBest'];

    protected $with = ['owner', 'favorites'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function favorites()
    {
        return $this->morphMany(Favourite::class, 'favorited');
    }

    public function favorite()
    {
        $attributes = ['user_id'=>auth()->id()];
        if (! $this->favorites()->where(['user_id'=>auth()->id()])->exists()) {
            $this->favorites()->create($attributes);
        }
    }

    public function unfavorite()
    {
        $attributes = ['user_id'=>auth()->id()];
        $this->favorites()->where($attributes)->get()->each->delete();
    }

    public function isFavorited()
    {
        return (bool) $this->favorites->where('user_id', auth()->id())->count();
    }

    public function getisFavoritedAttribute()
    {
        return $this->isFavorited();
    }

    public function getFavoritesCountAttribute()
    {
        return $this->favorites->count();
    }

    public function isBest()
    {
        return $this->thread->best_reply_id == $this->id;
    }

    public function getisBestAttribute()
    {
        return $this->isBest();
    }

    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    public function path()
    {
         $perPage = config('forum.pagination.perPage');
         $replyPosition = $this->thread->replies()->pluck('id')->search($this->id) + 1;
         $page = ceil($replyPosition / $perPage);
         return $this->thread->path()."?page={$page}#reply-{$this->id}";
    }

    public function wasJustPublished()
    {
        return $this->created_at->gt(Carbon::now()->subMinute());
    }

    public function mentionedUsers()
    {
        preg_match_all('/@([\w\-]+)/', $this->body, $matches);

        return $matches[1];
    }

    public function setBodyAttribute($body)
    {
        $this->attributes['body'] = preg_replace(
            '/@([\w\-]+)/',
            '<a href="/profiles/$1">$0</a>',
            $body
        );
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($reply) {
            $reply->thread->increment('replies_count');
            Reputation::award($reply->owner, Reputation::Reply_Has_Made);
        });

        static::deleting(function ($reply) {
            $reply->thread->decrement('replies_count');
            Reputation::reduce($reply->owner, Reputation::Reply_Has_Made);
        });

        static::deleting(function ($model) {
            $model->favorites->each->delete();
        });
    }
}
