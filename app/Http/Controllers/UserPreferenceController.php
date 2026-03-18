<?php

namespace App\Http\Controllers;

use App\Models\UserPreference;
use Illuminate\Http\Request;

class UserPreferenceController extends Controller
{
    /**
     * Buscar preferências do utilizador
     */
    public function show(Request $request)
    {
        $user = $request->user();
        
        // Tenta buscar as preferências do user
        $preferences = $user->preferences;
        
        // Se não existirem, cria com valores padrão
        if (!$preferences) {
            $preferences = UserPreference::create([
                'user_id' => $user->id,
                'theme' => 'dark'
            ]);
        }
        
        return response()->json($preferences);
    }

    /**
     * Atualizar preferências do utilizador
     */
    public function update(Request $request)
    {
        $user = $request->user();
        
        // Validar os dados recebidos
        $data = $request->validate([
            'theme' => 'required|in:light,dark,system'
        ]);
        
        // Atualizar ou criar as preferências
        $preferences = UserPreference::updateOrCreate(
            ['user_id' => $user->id],  // Condição para encontrar
            $data                       // Dados para atualizar/criar
        );
        
        return response()->json($preferences);
    }
}