<?php

namespace App\Http\Controllers;

use App\Models\Toko;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Support\ManagesPublicFiles;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    use ManagesPublicFiles;

    public function index(Request $request)
    {

        if (Auth::user()->hasRole('superadmin')) {
            // Mengambil semua user dan diurutkan berdasarkan username
            $query = User::orderBy('username', 'asc');
        } else {
            // Mengambil data user yang sedang login saja (dikembalikan sebagai Collection)
            $query = User::where('id', Auth::user()->id); 
        }
        
        $number_paginate = [10, 25, 50, 100, 300, 999999999];
        $number = $request->input('number', 10);

        // 2. Filter Lanjutan: Nama User
        // Ini menangani input dari id="name" di form filter lanjutan
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // 3. Filter Lanjutan: Username
        // Ini menangani input dari id="username" di form filter lanjutan
        if ($request->filled('username')) {
            $query->where('username', 'like', '%' . $request->username . '%');
        }

        $users = $query->paginate($number);

        // Safety Net: Jika page ketinggian dan data kosong, balikkan ke page 1
        if ($users->isEmpty() && $request->page > 1) {
            return redirect()->route('user.index', array_merge($request->except('page'), ['page' => 1]));
        }
        
        $users->appends($request->all());
        return view('user.index', compact('users', 'number_paginate', 'number'));
    }


    public function create()
    {
        $roles = Role::whereNotIn('name', ['superadmin'])->get();
        $tokos = Toko::all();
        return view('user.create', compact('roles','tokos'));
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|alpha_dash|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'is_type' => 'nullable|boolean',
            'is_rafaksi_spv' => 'nullable|boolean',
            'is_jsm_spv' => 'nullable|boolean',
            'is_pwp_spv' => 'nullable|boolean',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'toko_ids' => 'required|array|min:1',
            'toko_ids.*' => 'exists:tokos,id',
            'ttd' => 'nullable|file|mimes:pdf,png,jpg,jpeg',
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);
        $validatedData['email'] = $validatedData['email'] ?? $validatedData['username'] . '@local.invalid';

        $userPayload = collect($validatedData)->all();

        if ($request->hasFile('ttd')) {
            $file = $request->file('ttd');
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('signatures', $filename, 'local');
            $userPayload['signature_path'] = $path;
            $userPayload['ttd'] = 'storage/' . $path;
        }

        $user = User::create($userPayload);

        $selectedTokoIds = collect($validatedData['toko_ids'])->map(static function ($id) {
            return (int) $id;
        })->unique()->values();

        $user->tokos()->sync($selectedTokoIds->all());

        $roleNames = Role::whereIn('id', $validatedData['roles'] ?? [])->pluck('name')->toArray();
        $user->syncRoles($roleNames);

        ActivityLogger::logCreate(
            $user,
            $user->id,
            ['name' => $user->name, 'username' => $user->username, 'roles' => $roleNames],
            "Created User #{$user->id}: {$user->username}"
        );

        return redirect()->route('user.index')->with('success', 'User created successfully.');
    }

    public function edit($id){
        $user = User::findOrFail($id);
        $roles = Role::orderBy('name')->get();
        $tokos = Toko::all();

        return view('user.edit', compact('user', 'roles', 'tokos'));
    }

    public function update(Request $request, $id){
        $user = User::findOrFail($id);
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'is_type' => 'nullable|boolean',
            'is_rafaksi_spv' => 'nullable|boolean',
            'is_jsm_spv' => 'nullable|boolean',
            'is_pwp_spv' => 'nullable|boolean',
            'username' => 'required|string|max:255|alpha_dash|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'toko_ids' => 'required|array|min:1',
            'toko_ids.*' => 'exists:tokos,id',
            'ttd' => 'nullable|file|mimes:pdf,png,jpg,jpeg',
        ]);

        if(!empty($validatedData['password'])){
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']);
        }

        $selectedTokoIds = collect($validatedData['toko_ids'])->map(static function ($id) {
            return (int) $id;
        })->unique()->values();

        $user->tokos()->sync($selectedTokoIds->all());

        $roleNames = [];
        if (isset($validatedData['roles'])) {
            $roleNames = Role::whereIn('id', $validatedData['roles'])->pluck('name')->toArray();
            $user->syncRoles($roleNames);
        } else {
            $user->syncRoles([]);
        }

        $userPayload = collect($validatedData)->except(['toko_ids', 'perusahaan_ids', 'roles'])->all();

        if ($request->hasFile('ttd')) {
            $oldSignaturePath = $user->signature_path;

            $file = $request->file('ttd');
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('signatures', $filename, 'local');

            // delete old signature file from storage disk (unless it's somehow the same path as the new one)
            if ($oldSignaturePath && $oldSignaturePath !== $path) {
                Storage::delete($oldSignaturePath);
            }
            $userPayload['signature_path'] = $path;
            $userPayload['ttd'] = 'storage/' . $path;
        }

        $user->update($userPayload);



        ActivityLogger::logUpdate(
            $user,
            $user->id,
            ['name' => $user->name, 'username' => $user->username, 'roles' => $roleNames],
            "Updated User #{$user->id}: {$user->username}"
        );

        return redirect()->route('user.index')->with('success', 'User updated successfully.');
    }

    public function destroy($id){
        $user = User::findOrFail($id);

        if($user){
            ActivityLogger::logDelete(
                $user,
                $user->id,
                ['name' => $user->name, 'username' => $user->username],
                "Deleted User #{$user->id}: {$user->username}"
            );

            $user->delete();


            return redirect()->route('user.index')->with('success', 'User deleted successfully.');
        } else {
            return redirect()->route('user.index')->with('error', 'User not found.');
        }
    }
}