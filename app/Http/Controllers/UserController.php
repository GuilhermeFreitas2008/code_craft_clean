<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    // Lista todos os utilizadores
    public function index()
    {
        return response()->json(User::with('role')->get());
    }

    // Mostra um utilizador específico - CORRIGIDO: Route Model Binding
    public function show(User $user)
    {
        return response()->json($user->load('role'));
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

        return response()->json($user->load('role'), 201);
    }

    // Atualiza um utilizador existente - CORRIGIDO: Route Model Binding
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'username' => 'string|unique:users,username,' . $user->id,
            'email' => 'email|unique:users,email,' . $user->id,
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

        return response()->json($user->load('role'));
    }

    // Apaga um utilizador - CORRIGIDO: Route Model Binding
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $user = $request->user();
        
        // Apagar avatar antigo se existir
        if ($user->avatar) {
            $oldPath = str_replace('/storage/', '', $user->avatar);
            Storage::disk('public')->delete($oldPath);
        }
        
        // Guardar novo avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = '/storage/' . $path;
        $user->save();
        
        return response()->json([
            'success' => true,
            'avatar_url' => $user->avatar
        ]);
    }

    public function removeAvatar(Request $request)
    {
        $user = $request->user();
        
        if ($user->avatar) {
            $oldPath = str_replace('/storage/', '', $user->avatar);
            Storage::disk('public')->delete($oldPath);
            $user->avatar = null;
            $user->save();
        }
        
        return response()->json(['success' => true]);
    }
}