<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Lista todos os utilizadores
    public function index()
    {
        return response()->json(User::with('role')->get());
    }

    // Mostra um utilizador específico
    public function show($id)
    {
        return response()->json(User::with('role')->findOrFail($id));
    }

    // Cria novo utilizador
    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password_hash' => 'required|string',
            'role_id' => 'required|exists:roles,id',
            'slug' => 'required|string|unique:users'
        ]);

        $user = User::create($data);
        return response()->json($user, 201);
    }

    // Atualiza utilizador
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'username' => 'string|unique:users,username,' . $id,
            'email' => 'email|unique:users,email,' . $id,
            'password_hash' => 'string|nullable',
            'role_id' => 'exists:roles,id',
            'slug' => 'string|unique:users,slug,' . $id,
        ]);

        $user->update($data);
        return response()->json($user);
    }

    // Apaga utilizador
    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return response()->json(['message' => 'User deleted']);
    }
}
