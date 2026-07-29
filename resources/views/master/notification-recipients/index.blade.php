@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 uppercase tracking-tight">Master Penerima Notifikasi</h1>
            <p class="mt-1 text-md text-black font-semibold italic">Daftar personel yang menerima email pengingat otomatis.</p>
        </div>
        <a href="{{ route('notification-recipients.create') }}" class="inline-flex items-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all uppercase tracking-widest active:scale-95">
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Penerima
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-green-100 bg-green-50 p-4 text-sm text-green-700">
            {!! session('success') !!}
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 font-black uppercase tracking-wider text-slate-400 text-[10px]">Nama & Email</th>
                        <th class="px-6 py-4 font-black uppercase tracking-wider text-slate-400 text-[10px]">Modul Notifikasi</th>
                        <th class="px-6 py-4 font-black uppercase tracking-wider text-slate-400 text-[10px] text-center">Status</th>
                        <th class="px-6 py-4 font-black uppercase tracking-wider text-slate-400 text-[10px] text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recipients as $recipient)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-bold text-gray-900">{{ $recipient->name }}</p>
                                <p class="text-xs text-gray-500 font-mono">{{ $recipient->email }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($recipient->modules as $modKey)
                                        <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-[10px] font-black text-indigo-700 border border-indigo-100 uppercase">
                                            {{ $modules[$modKey] ?? $modKey }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($recipient->is_active)
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-800 uppercase tracking-tighter">Aktif</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-bold text-red-800 uppercase tracking-tighter">Non-Aktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('notification-recipients.edit', $recipient->id) }}" class="rounded-lg border border-gray-200 bg-white p-2 text-gray-400 hover:text-indigo-600 hover:border-indigo-200 transition-all">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </a>
                                    <form action="{{ route('notification-recipients.destroy', $recipient->id) }}" method="POST" onsubmit="return confirm('Hapus penerima ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-gray-200 bg-white p-2 text-gray-400 hover:text-red-600 hover:border-red-200 transition-all">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400 italic">Belum ada penerima notifikasi terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($recipients->hasPages())
            <div class="border-t border-gray-100 bg-slate-50 px-6 py-4">
                {{ $recipients->links() }}
            </div>
        @endif
    </div>
</div>
@endsection