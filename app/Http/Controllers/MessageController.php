<?php
namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Models\Channel;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Store a new message, then broadcast MessageSent via Reverb.
     */
    public function store(Request $request, Channel $channel): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'body'            => 'nullable|string|max:5000',
            'attachment_url'  => 'nullable|url',
            'attachment_type' => 'nullable|string|in:image,file',
            'attachment_name' => 'nullable|string|max:255',
        ]);

        // At least one of body or attachment required
        if (empty($data['body']) && empty($data['attachment_url'])) {
            return back()->withErrors(['body' => 'Message cannot be empty.']);
        }

        $message = Message::create([
            ...$data,
            'channel_id' => $channel->id,
            'user_id'    => Auth::id(),
        ]);

        // Update sender's last_read_at
        $channel->members()->updateExistingPivot(Auth::id(), ['last_read_at' => now()]);

        // Broadcast to all channel members in real time (non-fatal if Reverb is unavailable)
        try {
            broadcast(new MessageSent($message))->toOthers();
        } catch (\Throwable $e) {
            \Log::warning('Broadcast failed: ' . $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => $message->load('user')]);
        }

        return back();
    }

    /**
     * Broadcast typing indicator.
     */
    public function typing(Request $request, Channel $channel): JsonResponse
    {
        $data = $request->validate(['is_typing' => 'required|boolean']);

        broadcast(new UserTyping($channel->id, Auth::user(), $data['is_typing']))->toOthers();

        return response()->json(['ok' => true]);
    }
}
