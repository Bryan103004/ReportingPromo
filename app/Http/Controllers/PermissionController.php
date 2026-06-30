<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Services\ActivityLogger;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Permission::query();
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

        $permissions = $query->orderBy('name','asc')->paginate($number);
        
        // Safety Net: Jika page ketinggian dan data kosong, balikkan ke page 1
        if ($permissions->isEmpty() && $request->page > 1) {
            return redirect()->route('permission.index', array_merge($request->except('page'), ['page' => 1]));
        }

        $permissions->appends($request->all());
        return view('permission.index', compact('permissions', 'number_paginate', 'number'));
    }

    public function create()
    {
        return view('permission.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);

        $permission = Permission::create([
            'name' => $validatedData['name'],
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        ActivityLogger::logCreate(
            $permission,
            $permission->id,
            ['name' => $permission->name],
            "Created Permission #{$permission->id}: {$permission->name}"
        );

        return redirect()->route('permission.index')->with('success', 'Permission created successfully.');
    }

    public function show($id)
    {
        $permission = Permission::findOrFail($id);
        return view('permission.show', compact('permission'));
    }

    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        return view('permission.edit', compact('permission'));
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
        ]);

        $oldName = $permission->name;
        $permission->update(['name' => $validatedData['name']]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        ActivityLogger::logUpdate(
            $permission,
            $permission->id,
            ['old_name' => $oldName, 'new_name' => $permission->name],
            "Updated Permission #{$permission->id}: {$oldName} → {$permission->name}"
        );

        return redirect()->route('permission.index')->with('success', 'Permission updated successfully.');
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);

        ActivityLogger::logDelete(
            $permission,
            $permission->id,
            ['name' => $permission->name],
            "Deleted Permission #{$permission->id}: {$permission->name}"
        );

        $permission->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('permission.index')->with('success', 'Permission deleted successfully.');
    }
}