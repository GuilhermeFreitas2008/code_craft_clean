<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        return response()->json(Role::all());
    }

    public function show($id)
    {
        return response()->json(Role::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles',
        ]);

        $role = Role::create($data);
        return response()->json($role, 201);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $data = $request->validate([
            'name' => 'string|unique:roles,name,' . $id,
        ]);

        $role->update($data);
        return response()->json($role);
    }

    public function destroy($id)
    {
        Role::findOrFail($id)->delete();
        return response()->json(['message' => 'Role deleted']);
    }
}
