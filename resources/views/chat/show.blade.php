<x-app-layout>
<div class="flex h-screen"
     x-data="chatApp({{ $channel->id }}, {{ auth()->id() }})"
     x-init="init()">

    {{-- Sidebar --}}
    <aside class="w-64 bg-gray-800 flex flex-col border-r border-gray-700 shrink-0">
        <div class="p-4 border-b border-gray-700">
            <span class="text-xl font-bold text-indigo-400">TeamChat</span>
            <p class="text-xs text-gray-400 mt-0.5">{{ auth()->user()->name }}</p>
        </div>

        <div class="flex-1 overflow-y-auto p-3">
            <div class="flex items-center justify-between mb-2 px-1">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Channels</span>
                <button onclick="document.getElementById('create-modal').classList.remove('hidden')"
                    class="text-gray-400 hover:text-white text-lg leading-none">+</button>
            </div>

            @foreach($myChannels as $ch)
            <a href="{{ route('channels.show', $ch) }}"
               class="flex items-center gap-2 px-2 py-1.5 rounded mb-0.5 text-sm
                      {{ $ch->id === $channel->id ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <span class="{{ $ch->id === $channel->id ? 'text-indigo-300' : 'text-gray-500' }}">#</span>
                <span class="truncate">{{ $ch->name }}</span>
            </a>
            @endforeach

            @if($channels->count() > $myChannels->count())
            <div class="mt-4 mb-2 px-1">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Browse</span>
            </div>
            @foreach($channels->whereNotIn('id', $myChannels->pluck('id')) as $ch)
            <div class="flex items-center justify-between px-2 py-1.5 rounded hover:bg-gray-700 mb-0.5">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="text-gray-500">#</span>
                    <span class="truncate text-sm text-gray-400">{{ $ch->name }}</span>
                </div>
                <form action="{{ route('channels.join', $ch) }}" method="POST">
                    @csrf
                    <button class="text-xs text-indigo-400 hover:text-indigo-300">Join</button>
                </form>
            </div>
            @endforeach
            @endif
        </div>

        {{-- Online users --}}
        <div class="p-3 border-t border-gray-700">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Online</p>
            <template x-for="u in onlineUsers" :key="u.id">
                <div class="flex items-center gap-2 mb-1">
                    <div class="relative">
                        <img :src="u.avatar" class="w-6 h-6 rounded-full">
                        <span class="absolute bottom-0 right-0 w-2 h-2 bg-green-400 rounded-full border border-gray-800"></span>
                    </div>
                    <span class="text-xs text-gray-300" x-text="u.name"></span>
                </div>
            </template>
            <template x-if="onlineUsers.length === 0">
                <p class="text-xs text-gray-500">No one online yet</p>
            </template>
        </div>

        {{-- User bar --}}
        <div class="p-3 border-t border-gray-700 flex items-center gap-2">
            <div class="relative">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=6366f1&color=fff"
                     class="w-8 h-8 rounded-full">
                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-400 rounded-full border border-gray-800"></span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-gray-400 hover:text-white text-xs">Logout</button>
            </form>
        </div>
    </aside>

    {{-- Chat main --}}
    <div class="flex-1 flex flex-col min-w-0">
        {{-- Header --}}
        <header class="bg-gray-800 border-b border-gray-700 px-6 py-3 flex items-center gap-3 shrink-0">
            <span class="text-gray-400 text-lg">#</span>
            <div>
                <h1 class="font-bold text-white">{{ $channel->name }}</h1>
                @if($channel->description)
                <p class="text-xs text-gray-400">{{ $channel->description }}</p>
                @endif
            </div>
            <div class="ml-auto flex items-center gap-2 text-sm text-gray-400">
                <span x-text="onlineUsers.length + ' online'"></span>
            </div>
        </header>

        {{-- Messages --}}
        <div id="messages-container"
             class="flex-1 overflow-y-auto p-6 space-y-4 scroll-smooth">

            {{-- Server-rendered messages --}}
            @foreach($messages as $msg)
            <div class="flex items-start gap-3 group">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($msg->user->name) }}&background=6366f1&color=fff"
                     class="w-9 h-9 rounded-full shrink-0 mt-0.5">
                <div class="min-w-0 flex-1">
                    <div class="flex items-baseline gap-2 mb-0.5">
                        <span class="font-semibold text-white text-sm">{{ $msg->user->name }}</span>
                        <span class="text-xs text-gray-500">{{ $msg->created_at->format('g:i A') }}</span>
                    </div>
                    @if($msg->body)
                    <p class="text-gray-200 text-sm leading-relaxed break-words">{{ $msg->body }}</p>
                    @endif
                    @if($msg->attachment_url)
                    @if($msg->attachment_type === 'image')
                    <img src="{{ $msg->attachment_url }}" alt="{{ $msg->attachment_name }}"
                         class="mt-2 max-w-xs rounded-lg border border-gray-700 cursor-pointer hover:opacity-90"
                         onclick="window.open('{{ $msg->attachment_url }}', '_blank')">
                    @else
                    <a href="{{ $msg->attachment_url }}" target="_blank"
                       class="mt-2 inline-flex items-center gap-2 bg-gray-700 hover:bg-gray-600 px-3 py-2 rounded-lg text-sm text-indigo-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                        </svg>
                        {{ $msg->attachment_name ?? 'Download file' }}
                    </a>
                    @endif
                    @endif
                </div>
            </div>
            @endforeach

            {{-- Real-time messages appended by Alpine --}}
            <template x-for="msg in realtimeMessages" :key="msg.id">
                <div class="flex items-start gap-3">
                    <img :src="msg.user.avatar" class="w-9 h-9 rounded-full shrink-0 mt-0.5">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-baseline gap-2 mb-0.5">
                            <span class="font-semibold text-white text-sm" x-text="msg.user.name"></span>
                            <span class="text-xs text-gray-500" x-text="formatTime(msg.created_at)"></span>
                        </div>
                        <p x-show="msg.body" class="text-gray-200 text-sm leading-relaxed break-words" x-text="msg.body"></p>
                        <template x-if="msg.attachment_url && msg.attachment_type === 'image'">
                            <img :src="msg.attachment_url" :alt="msg.attachment_name"
                                 class="mt-2 max-w-xs rounded-lg border border-gray-700 cursor-pointer hover:opacity-90"
                                 @click="window.open(msg.attachment_url, '_blank')">
                        </template>
                        <template x-if="msg.attachment_url && msg.attachment_type !== 'image'">
                            <a :href="msg.attachment_url" target="_blank"
                               class="mt-2 inline-flex items-center gap-2 bg-gray-700 hover:bg-gray-600 px-3 py-2 rounded-lg text-sm text-indigo-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                </svg>
                                <span x-text="msg.attachment_name || 'Download file'"></span>
                            </a>
                        </template>
                    </div>
                </div>
            </template>

            {{-- Typing indicator --}}
            <div x-show="typingUsers.length > 0" x-transition class="flex items-center gap-2 text-sm text-gray-400 italic">
                <div class="flex gap-1">
                    <span class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                    <span class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                    <span class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                </div>
                <span x-text="typingText"></span>
            </div>

            <div id="messages-end"></div>
        </div>

        {{-- Attachment preview --}}
        <div x-show="attachmentPreview" x-transition class="px-4 pb-0">
            <div class="bg-gray-700 rounded-lg px-4 py-2 flex items-center gap-3 text-sm">
                <template x-if="attachmentType === 'image'">
                    <img :src="attachmentPreview" class="h-16 w-16 object-cover rounded">
                </template>
                <template x-if="attachmentType !== 'image'">
                    <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </template>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-white truncate" x-text="attachmentName"></p>
                    <p class="text-gray-400 text-xs">Ready to send</p>
                </div>
                <button @click="clearAttachment()" class="text-gray-400 hover:text-red-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Message input --}}
        <div class="p-4 bg-gray-800 border-t border-gray-700">
            <form @submit.prevent="sendMessage()" class="flex items-end gap-3">
                {{-- Cloudinary upload button --}}
                <button type="button" @click="openUpload()"
                    class="p-2.5 rounded-lg bg-gray-700 hover:bg-gray-600 text-gray-400 hover:text-white shrink-0 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                </button>

                <div class="flex-1 relative">
                    <textarea x-model="body" @keydown.enter.prevent.exact="sendMessage()"
                        @keydown="onTyping()" @blur="stopTyping()"
                        rows="1" placeholder="Message #{{ $channel->name }}"
                        class="w-full bg-gray-700 border border-gray-600 rounded-xl px-4 py-3 text-white placeholder-gray-400
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none text-sm leading-relaxed"
                        style="max-height: 200px; overflow-y: auto;"
                        @input="$el.style.height='auto'; $el.style.height=$el.scrollHeight+'px'"></textarea>
                </div>

                <button type="submit" :disabled="sending"
                    class="p-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white shrink-0 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </form>
            <p class="text-xs text-gray-500 mt-1 px-1">Press Enter to send, Shift+Enter for new line</p>
        </div>
    </div>
</div>

{{-- Create Channel Modal --}}
<div id="create-modal" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50">
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

@push('scripts')
<script>
    // Cloudinary upload widget — loaded lazily
    let cloudinaryWidget = null;

    function chatApp(channelId, userId) {
        return {
            channelId,
            userId,
            body: '',
            sending: false,
            realtimeMessages: [],
            typingUsers: [],
            typingTimeout: null,
            onlineUsers: [],
            attachmentUrl: null,
            attachmentType: null,
            attachmentName: null,
            attachmentPreview: null,

            get typingText() {
                if (this.typingUsers.length === 1) return this.typingUsers[0] + ' is typing…';
                if (this.typingUsers.length === 2) return this.typingUsers.join(' and ') + ' are typing…';
                return 'Several people are typing…';
            },

            init() {
                this.scrollToBottom();
                this.listenToChannel();
            },

            scrollToBottom() {
                this.$nextTick(() => {
                    const el = document.getElementById('messages-end');
                    if (el) el.scrollIntoView({ behavior: 'smooth' });
                });
            },

            formatTime(iso) {
                const d = new Date(iso);
                return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            },

            listenToChannel() {
                // Presence channel — tracks who is online
                window.Echo.join('channel.' + this.channelId)
                    .here((users) => { this.onlineUsers = users; })
                    .joining((user) => {
                        if (!this.onlineUsers.find(u => u.id === user.id)) {
                            this.onlineUsers.push(user);
                        }
                    })
                    .leaving((user) => {
                        this.onlineUsers = this.onlineUsers.filter(u => u.id !== user.id);
                    })
                    // New message event
                    .listen('MessageSent', (e) => {
                        this.realtimeMessages.push(e);
                        this.scrollToBottom();
                    })
                    // Typing indicator
                    .listen('UserTyping', (e) => {
                        if (e.user_id === this.userId) return;
                        if (e.is_typing) {
                            if (!this.typingUsers.includes(e.user_name)) {
                                this.typingUsers.push(e.user_name);
                            }
                        } else {
                            this.typingUsers = this.typingUsers.filter(n => n !== e.user_name);
                        }
                    });
            },

            async sendMessage() {
                if (this.sending) return;
                if (!this.body.trim() && !this.attachmentUrl) return;

                this.sending = true;
                this.stopTyping();

                try {
                    const res = await fetch('/channels/' + this.channelId + '/messages', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            body:            this.body.trim() || null,
                            attachment_url:  this.attachmentUrl,
                            attachment_type: this.attachmentType,
                            attachment_name: this.attachmentName,
                        }),
                    });

                    const data = await res.json();
                    if (res.ok) {
                        // Add own message immediately (others get it via broadcast)
                        const msg = data.message;
                        msg.user.avatar = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(msg.user.name) + '&background=6366f1&color=fff';
                        this.realtimeMessages.push(msg);
                        this.body = '';
                        this.clearAttachment();
                        this.$nextTick(() => {
                            const ta = this.$el.querySelector('textarea');
                            if (ta) ta.style.height = 'auto';
                        });
                        this.scrollToBottom();
                    }
                } finally {
                    this.sending = false;
                }
            },

            onTyping() {
                clearTimeout(this.typingTimeout);
                this.broadcastTyping(true);
                this.typingTimeout = setTimeout(() => this.stopTyping(), 2500);
            },

            stopTyping() {
                clearTimeout(this.typingTimeout);
                this.broadcastTyping(false);
            },

            broadcastTyping(isTyping) {
                fetch('/channels/' + this.channelId + '/typing', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ is_typing: isTyping }),
                }).catch(() => {});
            },

            openUpload() {
                if (!window.cloudinary) {
                    const s = document.createElement('script');
                    s.src = 'https://upload-widget.cloudinary.com/global/all.js';
                    s.onload = () => this.launchWidget();
                    document.head.appendChild(s);
                } else {
                    this.launchWidget();
                }
            },

            launchWidget() {
                const cloud = document.querySelector('meta[name="cloudinary-cloud"]')?.content || 'YOUR_CLOUD_NAME';
                const widget = window.cloudinary.createUploadWidget(
                    {
                        cloudName: cloud,
                        uploadPreset: document.querySelector('meta[name="cloudinary-preset"]')?.content || 'unsigned_preset',
                        sources: ['local', 'url', 'camera'],
                        multiple: false,
                        styles: { palette: { window: '#1f2937', sourceBg: '#374151', windowBorder: '#4f46e5', tabIcon: '#6366f1', inactiveTabIcon: '#9ca3af', menuIcons: '#6366f1', link: '#6366f1', action: '#4f46e5', inProgress: '#6366f1', complete: '#10b981', error: '#ef4444', textDark: '#ffffff', textLight: '#9ca3af' } },
                    },
                    (error, result) => {
                        if (!error && result && result.event === 'success') {
                            const info = result.info;
                            this.attachmentUrl    = info.secure_url;
                            this.attachmentName   = info.original_filename + '.' + info.format;
                            const isImage         = info.resource_type === 'image';
                            this.attachmentType   = isImage ? 'image' : 'file';
                            this.attachmentPreview = isImage ? info.secure_url : null;
                        }
                    }
                );
                widget.open();
            },

            clearAttachment() {
                this.attachmentUrl    = null;
                this.attachmentType   = null;
                this.attachmentName   = null;
                this.attachmentPreview = null;
            },
        };
    }
</script>
@endpush
</x-app-layout>
