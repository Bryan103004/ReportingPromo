<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4">
    <div class="mb-8">
        {{ $logo }}
    </div>

    <div class="w-full sm:max-w-md bg-white shadow-2xl overflow-hidden sm:rounded-2xl border border-gray-100">
        <div class="px-8 py-10">
            {{ $slot }}
        </div>
    </div>
</div>
