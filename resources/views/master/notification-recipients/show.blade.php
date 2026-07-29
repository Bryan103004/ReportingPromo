@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 uppercase tracking-tight">Detail Penerima</h1>
        <div class="flex gap-2">
            <a href="{{ route('notification-recipients.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors uppercase">Kembali</a>
            <a href="{{ route('notification-recipients.edit', $notificationRecipient->id) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-700 shadow-md transition-all uppercase tracking-widest">Edit</a>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm p-8">
        <div class="flex items-center gap-6 mb-8">
            <div class="h-20 w-20 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-3xl font-black">
                {{ strtoupper(substr($notificationRecipient->name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-xl font-black text-gray-900 uppercase tracking-tight">{{ $notificationRecipient->name }}</h2>
                <p class="text-gray-500 font-mono">{{ $notificationRecipient->email }}</p>
                <div class="mt-2">
                    @if($notificationRecipient->is_active)
                        <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-800 uppercase tracking-tighter">Status: Aktif</span>
                    @else
                        <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-bold text-red-800 uppercase tracking-tighter">Status: Non-Aktif</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="border-t border-gray-100 pt-6">
            <h3 class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-4">Modul yang Diikuti</h3>
            <div class="grid grid-cols-1 gap-2">
                @foreach($notificationRecipient->modules as $modKey)
                    <div class="flex items-center p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <svg class="h-5 w-5 text-indigo-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-sm font-bold text-gray-700 uppercase">{{ $modules[$modKey] ?? $modKey }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection