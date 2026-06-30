<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Support\ManagesPublicFiles;
use App\Services\ActivityLogger;

class UserController extends Controller
{
    use ManagesPublicFiles;

    public function index(Request $request)
    {
        $query = User::orderBy('username','asc');
        $number_paginate = [10, 25, 50, 100, 300, 999999999];
        $number = $request->input('number', 10);

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
        $roles = Role::orderBy('name')->get();

        return view('user.create', compact('roles'));
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|alpha_dash|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);
        $validatedData['email'] = $validatedData['email'] ?? $validatedData['username'] . '@local.invalid';

        $userPayload = collect($validatedData)->all();
        $user = User::create($userPayload);

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

        return view('user.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id){
        $user = User::findOrFail($id);
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'username' => 'required|string|max:255|alpha_dash|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        if(!empty($validatedData['password'])){
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']);
        }

        $roleNames = [];
        if (isset($validatedData['roles'])) {
            $roleNames = Role::whereIn('id', $validatedData['roles'])->pluck('name')->toArray();
            $user->syncRoles($roleNames);
        } else {
            $user->syncRoles([]);
        }

        $userPayload = collect($validatedData)->except(['toko_ids', 'perusahaan_ids', 'roles'])->all();
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