<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;
class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'team_id' => 'nullable|exists:teams,id',
            'team_name' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => false,
        ]);

        if ($request->filled('team_name')) {
            $team = \App\Models\Team::create([
                'name' => $request->team_name,
                'captain_id' => $user->id,
            ]);
            $user->update(['team_id' => $team->id]);
        } elseif ($request->filled('team_id')) {
            $user->update(['team_id' => $request->team_id]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully.',
            'access_token' => $token,
            'user' => $user->load('team'),
        ], 201);
    }

    
}