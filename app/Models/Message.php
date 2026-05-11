<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    protected $fillable = [
        'channel_id', 'user_id', 'body',
        'attachment_url', 'attachment_type', 'attachment_name'
    ];

    protected $with = ['user'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function channel(): BelongsTo { return $this->belongsTo(Channel::class); }
    public function attachments(): HasMany { return $this->hasMany(Attachment::class); }
}
