@extends('layouts.app')

@section('content')
<div class="min-height-screen bg-gray-50/50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header Section -->
        <div class="mb-8 border-b border-gray-200 pb-5 sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Analisis Dashboard
                </h1>
                <!-- <p class="mt-2 text-sm text-gray-500">
                    Pantau dan analisis performa data RAF, JSM, dan LOC secara real-time.
                </p> -->
            </div>
        </div>

        <!-- Main Dashboard Layout -->
        <div class="space-y-8">
            <!-- Komponen RAFAKSI -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 transition-all duration-200 hover:shadow-md">
                    <div class="flex items-center justify-between mb-4 border-b border-gray-50 pb-4">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            <span class="w-2 h-5 bg-blue-600 rounded-full"></span>
                            Dashboard RAFAKSI
                        </h2>
                    </div>
                    <livewire:rafaksi-dashboard lazy />
                </div>
                <!-- Komponen JSM -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 transition-all duration-200 hover:shadow-md">
                    <div class="flex items-center justify-between mb-4 border-b border-gray-50 pb-4">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            <span class="w-2 h-5 bg-emerald-600 rounded-full"></span>
                            Dashboard JSM
                        </h2>
                    </div>
                    <livewire:jsm-dashboard lazy />
                </div>

                <!-- Komponen LOC -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 transition-all duration-200 hover:shadow-md">
                    <div class="flex items-center justify-between mb-4 border-b border-gray-50 pb-4">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            <span class="w-2 h-5 bg-indigo-600 rounded-full"></span>
                            Dashboard LOC
                        </h2>
                    </div>
                    <livewire:loc-dashboard lazy />
                </div>

            </div>
            
        </div>
    </div>
</div>
@endsection
