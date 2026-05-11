<?php
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Presence channel for chat rooms – returns user info so all members can see who is online
Broadcast::channel('channel.{channelId}', function ($user, $channelId) {
    $channel = \App\Models\Channel::find($channelId);
    if (!$channel) return false;

    // Allow if user is a member (or auto-join public channels)
    $isMember = $channel->members()->where('user_id', $user->id)->exists();
    if (!$isMember && !$channel->is_private) {
        $channel->members()->attach($user->id);
        $isMember = true;
    }

    if ($isMember) {
        // Mark online
        cache()->put("user-online-{$user->id}", true, now()->addMinutes(5));
        return [
            'id'     => $user->id,
            'name'   => $user->name,
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=6366f1&color=fff',
        ];
    }
    return false;
});
