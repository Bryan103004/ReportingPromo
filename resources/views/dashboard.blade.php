@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50 py-8 px-4 sm:px-6 lg:px-8 font-sans" x-data="{ activeTab: 'rafaksi' }">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header Section -->
        <div class="mb-8 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl mb-2">
                    Dashboard Analisis
                </h1>
                <p class="text-sm text-slate-500 font-medium">
                    Pantau dan kelola antrean data expired secara terpusat.
                </p>
            </div>
            
            <div class="flex items-center gap-2 text-sm text-slate-500 bg-white px-4 py-2 rounded-lg border border-slate-200 shadow-sm">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span class="font-bold">{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</span>
            </div>
        </div>

        <!-- Wadah Badge (Dibungkus Grid 3 Kolom agar sejajar dengan Card di bawahnya) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <livewire:rafaksi-badge lazy />
            <livewire:jsm-badge lazy />
            <livewire:loc-badge lazy />
        </div>

        <!-- Wadah Cards (Dibungkus Grid 3 Kolom yang rapi) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <livewire:rafaksi-card lazy />
            <livewire:jsm-card lazy />
            <livewire:loc-card lazy />
        </div>

        <!-- Wadah Utama Dashboard (Tabs) -->
        <div class="mt-4 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            
            <!-- Header Navigasi Tabs -->
            <div class="border-b border-slate-200 bg-slate-50/50 px-6 pt-2">
                <nav class="flex space-x-8" aria-label="Tabs">
                    
                    <!-- Tab Rafaksi -->
                    <button @click="activeTab = 'rafaksi'" 
                        :class="activeTab === 'rafaksi' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-colors flex items-center gap-2 outline-none">
                        <span class="flex h-2.5 w-2.5 rounded-full transition-colors" :class="activeTab === 'rafaksi' ? 'bg-yellow-600' : 'bg-slate-300'"></span>
                        RAFAKSI
                    </button>

                    <!-- Tab JSM -->
                    <button @click="activeTab = 'jsm'" 
                        :class="activeTab === 'jsm' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-colors flex items-center gap-2 outline-none">
                        <span class="flex h-2.5 w-2.5 rounded-full transition-colors" :class="activeTab === 'jsm' ? 'bg-blue-500' : 'bg-slate-300'"></span>
                        JSM
                    </button>

                    <!-- Tab LOC -->
                    <button @click="activeTab = 'loc'" 
                        :class="activeTab === 'loc' ? 'border-green-500 text-green-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-colors flex items-center gap-2 outline-none">
                        <span class="flex h-2.5 w-2.5 rounded-full transition-colors" :class="activeTab === 'loc' ? 'bg-green-600' : 'bg-slate-300'"></span>
                        LOC
                    </button>
                    
                </nav>
            </div>

            <!-- Konten Tabs -->
            <div class="relative bg-white p-6">
                
                <!-- Konten Rafaksi -->
                <div x-show="activeTab === 'rafaksi'" x-cloak
                    x-transition:enter="transition ease-out duration-300" 
                    x-transition:enter-start="opacity-0 translate-y-2" 
                    x-transition:enter-end="opacity-100 translate-y-0">
                    <livewire:rafaksi-dashboard lazy />
                </div>

                <!-- Konten JSM -->
                <div x-show="activeTab === 'jsm'" x-cloak style="display: none;"
                    x-transition:enter="transition ease-out duration-300" 
                    x-transition:enter-start="opacity-0 translate-y-2" 
                    x-transition:enter-end="opacity-100 translate-y-0">
                    <livewire:jsm-dashboard lazy />
                </div>

                <!-- Konten LOC -->
                <div x-show="activeTab === 'loc'" x-cloak style="display: none;"
                    x-transition:enter="transition ease-out duration-300" 
                    x-transition:enter-start="opacity-0 translate-y-2" 
                    x-transition:enter-end="opacity-100 translate-y-0">
                    <livewire:loc-dashboard lazy />
                </div>
                
            </div>
        </div>
        
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection