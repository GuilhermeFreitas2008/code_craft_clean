<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function index()
    {
        return response()->json(User::with('role')->get());
    }

    public function show(User $user)
    {
        return response()->json($user->load('role'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password_hash' => 'required|string',
        ]);

        $role = User::count() === 0
            ? Role::where('name', 'admin')->first()
            : Role::where('name', 'user')->first();

        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => bcrypt($data['password_hash']),
            'role_id' => $role->id,
            'slug' => Str::slug($data['username']),
        ]);

        return response()->json($user->load('role'), 201);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'username' => 'string|unique:users,username,' . $user->id,
            'email' => 'email|unique:users,email,' . $user->id,
            'password_hash' => 'string|nullable',
            'role_id' => 'exists:roles,id|nullable',
        ]);

        if (isset($data['password_hash'])) {
            $data['password_hash'] = bcrypt($data['password_hash']);
        }

        if (isset($data['username'])) {
            $data['slug'] = Str::slug($data['username']);
        }

        $user->update($data);

        return response()->json($user->load('role'));
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        $user = $request->user();
        
        try {
            $user->uploadAvatar($request->file('avatar'));
            
            // Buscar o user atualizado com a URL fresca
            $user->refresh();
            
            return response()->json([
                'success' => true,
                'avatar_url' => $user->avatar_url,
                'message' => 'Avatar updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload avatar'
            ], 500);
        }
    }
    
    public function removeAvatar(Request $request)
    {
        $user = $request->user();
        
        try {
            $user->removeAvatar();
            
            return response()->json([
                'success' => true,
                'message' => 'Avatar removed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove avatar'
            ], 500);
        }
    }
}