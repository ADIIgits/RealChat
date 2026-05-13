<?php
namespace App\Http\Controllers;

use App\Models\Channel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChannelController extends Controller
{
    public function index(): View
    {
        $user     = Auth::user();
        $channels = Channel::withCount('members')->get();
        $myChannels = $user->channels()->withCount('members')->get();

        // Mark user as online
        cache()->put("user-online-{$user->id}", true, now()->addMinutes(5));

        return view('chat.index', compact('channels', 'myChannels'));
    }

    public function show(Channel $channel): View|RedirectResponse
    {
        $user = Auth::user();

        // Auto-join AND mark as read in one upsert query (replaces 3 separate queries)
        $channel->members()->syncWithoutDetaching([
            $user->id => ['last_read_at' => now()],
        ]);

        // Mark user as online
        cache()->put("user-online-{$user->id}", true, now()->addMinutes(5));

        // Load channel members once, reuse for online users (no extra query)
        $channelMembers = $channel->members()->get();
        $onlineUsers    = $channelMembers->filter(fn($m) => cache()->has("user-online-{$m->id}"));

        $messages   = $channel->messages()->with('user')->orderBy('created_at')->get();
        $myChannels = $user->channels()->withCount('members')->get();
        $channels   = Channel::withCount('members')->get();

        return view('chat.show', compact('channel', 'messages', 'myChannels', 'channels', 'onlineUsers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:80',
            'description' => 'nullable|string|max:255',
            'is_private'  => 'boolean',
        ]);

        $channel = Channel::create([...$data, 'created_by' => Auth::id()]);
        $channel->members()->attach(Auth::id());

        return redirect()->route('channels.show', $channel);
    }

    public function join(Channel $channel): RedirectResponse
    {
        $user = Auth::user();
        if (!$channel->members()->where('user_id', $user->id)->exists()) {
            $channel->members()->attach($user->id);
        }
        return redirect()->route('channels.show', $channel);
    }
}
