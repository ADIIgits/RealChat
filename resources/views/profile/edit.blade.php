<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('channels.index') }}" class="text-gray-400 hover:text-white text-sm">← Back to Chat</a>
            <h2 class="font-semibold text-xl text-white leading-tight">Profile Settings</h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-2xl mx-auto px-6 space-y-6">
            <div class="p-6 bg-gray-800 rounded-xl border border-gray-700 shadow">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-6 bg-gray-800 rounded-xl border border-gray-700 shadow">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-6 bg-gray-800 rounded-xl border border-gray-700 shadow">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
