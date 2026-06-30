<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wide hover:from-blue-700 hover:to-indigo-700 active:from-blue-800 active:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150 shadow-lg hover:shadow-xl']) }}>
    {{ $slot }}
</button>
