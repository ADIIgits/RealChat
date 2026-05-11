<x-app-layout>
    <div class="flex h-screen">
        {{-- Sidebar --}}
        <aside class="w-64 bg-gray-800 flex flex-col border-r border-gray-700 shrink-0">
            <div class="p-4 border-b border-gray-700">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xl font-bold text-indigo-400">TeamChat</span>
                </div>
                <p class="text-xs text-gray-400">{{ auth()->user()->name }}</p>
            </div>

            {{-- Channels list --}}
            <div class="flex-1 overflow-y-auto p-3">
                <div class="flex items-center justify-between mb-2 px-1">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Channels</span>
                    <button onclick="document.getElementById('create-modal').classList.remove('hidden')"
                        class="text-gray-400 hover:text-white text-lg leading-none">+</button>
                </div>

                @foreach($myChannels as $ch)
                <a href="{{ route('channels.show', $ch) }}"
                   class="flex items-center gap-2 px-2 py-1.5 rounded hover:bg-gray-700 text-gray-300 hover:text-white mb-0.5 group">
                    <span class="text-gray-500">#</span>
                    <span class="truncate text-sm">{{ $ch->name }}</span>
                </a>
                @endforeach

                @if($channels->count() > $myChannels->count())
                <div class="mt-4 mb-2 px-1">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Browse Channels</span>
                </div>
                @foreach($channels->whereNotIn('id', $myChannels->pluck('id')) as $ch)
                <div class="flex items-center justify-between px-2 py-1.5 rounded hover:bg-gray-700 mb-0.5">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="text-gray-500">#</span>
                        <span class="truncate text-sm text-gray-400">{{ $ch->name }}</span>
                    </div>
                    <form action="{{ route('channels.join', $ch) }}" method="POST">
                        @csrf
                        <button class="text-xs text-indigo-400 hover:text-indigo-300 shrink-0">Join</button>
                    </form>
                </div>
                @endforeach
                @endif
            </div>

            {{-- User section --}}
            <div class="p-3 border-t border-gray-700 flex items-center gap-2">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=6366f1&color=fff"
                     class="w-8 h-8 rounded-full" alt="avatar">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-gray-400 hover:text-white text-xs">Logout</button>
                </form>
            </div>
        </aside>

        {{-- Main area --}}
        <main class="flex-1 flex flex-col items-center justify-center text-gray-400">
            <div class="text-center">
                <div class="text-6xl mb-4">#</div>
                <h2 class="text-2xl font-bold text-gray-200 mb-2">Welcome to TeamChat</h2>
                <p class="mb-6">Select a channel from the sidebar or create a new one.</p>
                <button onclick="document.getElementById('create-modal').classList.remove('hidden')"
                    class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2 rounded-lg font-medium">
                    Create a Channel
                </button>
            </div>
        </main>
    </div>

    {{-- Create Channel Modal --}}
    <div id="create-modal" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50" x-data>
        <div class="bg-gray-800 rounded-xl p-6 w-full max-w-md shadow-2xl border border-gray-700">
            <h3 class="text-lg font-bold mb-4">Create a Channel</h3>
            <form action="{{ route('channels.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-1">Channel Name</label>
                    <input type="text" name="name" required
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="e.g. general">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-300 mb-1">Description (optional)</label>
                    <input type="text" name="description"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="What is this channel about?">
                </div>
                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="document.getElementById('create-modal').classList.add('hidden')"
                        class="px-4 py-2 rounded-lg text-gray-400 hover:text-white">Cancel</button>
                    <button type="submit"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-medium">Create</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
