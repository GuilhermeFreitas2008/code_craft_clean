<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        ]);

        // Define o papel automaticamente:
        // Se for o primeiro utilizador → admin, senão → user
        $role = User::count() === 0
            ? Role::where('name', 'admin')->first()
            : Role::where('name', 'user')->first();

        // Cria o utilizador com slug e password encriptada
        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => bcrypt($data['password_hash']),
            'role_id' => $role->id,
            'slug' => Str::slug($data['username']),
        ]);

        return response()->json($user, 201);
    }

    // Atualiza um utilizador existente
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'username' => 'string|unique:users,username,' . $id,
            'email' => 'email|unique:users,email,' . $id,
            'password_hash' => 'string|nullable',
            'role_id' => 'exists:roles,id|nullable',
        ]);

        // Atualiza apenas os campos enviados
        if (isset($data['password_hash'])) {
            $data['password_hash'] = bcrypt($data['password_hash']);
        }

        if (isset($data['username'])) {
            $data['slug'] = Str::slug($data['username']);
        }

        $user->update($data);

        return response()->json($user);
    }

    // Apaga um utilizador
    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return response()->json(['message' => 'User deleted']);
    }
}
