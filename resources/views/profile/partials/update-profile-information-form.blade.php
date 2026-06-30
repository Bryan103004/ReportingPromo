<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and username.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="ttd" :value="__('Tanda Tangan')" />
            <input id="ttd" name="ttd" type="file" accept="image/png,image/jpeg,image/jpg,image/webp" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            <x-input-error class="mt-2" :messages="$errors->get('ttd')" />

            @if ($user->ttd)
                <div class="mt-3">
                    <p class="text-sm text-gray-600">TTD saat ini:</p>
                    <img src="{{ asset($user->ttd) }}" alt="Tanda Tangan" class="mt-2 h-20 w-auto rounded border bg-white p-2">
                </div>
            @endif

            <div class="mt-3 flex items-center gap-3">
                <button type="submit" name="ttd_action" value="delete" onclick="return confirm('Hapus tanda tangan saat ini?')" class="inline-flex items-center rounded-md border border-red-300 bg-white px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
                    Hapus TTD
                </button>
                <p class="text-xs text-gray-500">Klik ini untuk mengosongkan tanda tangan dari profil.</p>
            </div>
        </div>

        <div>
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input id="username" name="username" type="text" class="mt-1 block w-full" :value="old('username', $user->username)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('username')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
