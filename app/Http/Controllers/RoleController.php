<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Services\ActivityLogger;

class RoleController extends Controller
{
    public function index(Request $request){
        $query = Role::query()->orderBy("name","asc");
        $number_paginate = [10, 25, 50, 100, 300, 999999999];
        $number = $request->input('number', 10);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', '%' . $search . '%');
        }
        else if ($request->filled('name')) {
            $name = $request->name;
            $query->where('name', 'like', '%' . $name . '%');
        }

        $roles = $query->paginate($number);
        // Safety Net: Jika page ketinggian dan data kosong, balikkan ke page 1
        if ($roles->isEmpty() && $request->page > 1) {
            return redirect()->route('role.index', array_merge($request->except('page'), ['page' => 1]));
        }
        $roles->appends($request->all());
        return view('role.index', compact('roles','number','number_paginate'));
    }

    public function create(){
        $permissions = Permission::all();
        return view('role.create', compact('permissions'));
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create(['name' => $validatedData['name']]);

        $selectedPermissions = Permission::whereIn('id', $validatedData['permissions'] ?? [])->get();
        $role->syncPermissions($selectedPermissions);

        ActivityLogger::logCreate(
            $role,
            $role->id,
            ['name' => $role->name, 'permissions' => $selectedPermissions->pluck('name')->toArray()],
            "Created Role #{$role->id}: {$role->name}"
        );

        return redirect()->route('role.index')->with('success', 'Role created successfully.');
    }

    public function edit($id){
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::all();
        return view('role.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, $id){
        $role = Role::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update(['name' => $validatedData['name']]);

        $selectedPermissions = Permission::whereIn('id', $validatedData['permissions'] ?? [])->get();
        $role->syncPermissions($selectedPermissions);

        ActivityLogger::logUpdate(
            $role,
            $role->id,
            ['name' => $role->name, 'permissions' => $selectedPermissions->pluck('name')->toArray()],
            "Updated Role #{$role->id}: {$role->name}"
        );

        return redirect()->route('role.index')->with('success', 'Role updated successfully.');
    }

    public function destroy($id){
        $role = Role::findOrFail($id);

        if($role) {
            ActivityLogger::logDelete(
                $role,
                $role->id,
                ['name' => $role->name],
                "Deleted Role #{$role->id}: {$role->name}"
            );

            $role->delete();
            return redirect()->route('role.index')->with('success', 'Role deleted successfully.');
        } else {
            return redirect()->route('role.index')->with('error', 'Role not found.');
        }
    }
}