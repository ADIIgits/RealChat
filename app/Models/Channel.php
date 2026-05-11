<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Channel extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'is_private', 'created_by'];

    protected static function booted(): void
    {
        static::creating(function (Channel $channel) {
            $channel->slug = Str::slug($channel->name) . '-' . Str::random(4);
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'channel_members')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->latest();
    }

    public function unreadCount(int $userId): int
    {
        $pivot = $this->members()->where('user_id', $userId)->first()?->pivot;
        if (!$pivot || !$pivot->last_read_at) {
            return $this->messages()->count();
        }
        return $this->messages()->where('created_at', '>', $pivot->last_read_at)->count();
    }
}
