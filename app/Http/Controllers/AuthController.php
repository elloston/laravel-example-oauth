<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSocialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'name' => 'sometimes|string',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        $token = $user->createToken($request->header('User-Agent', 'unknown'))->plainTextToken;

        return response()->json(['token' => $token], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Wrong email or password'],
            ]);
        }

        $token = $user->createToken($request->header('User-Agent', 'unknown'))->plainTextToken;

        return response()->json(['token' => $token], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response(null, 204);
    }

    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback(Request $request, $provider)
    {
        try {
            $socialiteUser = Socialite::driver($provider)->user();

            $account = UserSocialAccount::where([
                'provider_name' => $provider,
                'provider_id' => $socialiteUser->getId(),
            ])->first();

            if (!$account) {
                $fakeUsername = 'user_' . $socialiteUser->getId();
                $fakeEmail = $provider . '_user_' . $socialiteUser->getId() . '@example.com';

                $user = User::firstOrCreate(
                    [
                        'email' => $socialiteUser->getEmail() ?? $fakeEmail,
                    ],
                    [
                        'name' => $fakeUsername,
                        'password' => Hash::make(Str::random(24)),
                        'avatar' => null,
                    ]
                );

                $account = UserSocialAccount::create(
                    [
                        'provider_name' => $provider,
                        'provider_id' => $socialiteUser->getId(),
                        'token' => $socialiteUser->token,
                        'user_id' => $user->id
                    ],
                );
            }

            $token = $account->user->createToken($request->header('User-Agent', 'unknown'))->plainTextToken;

            return redirect(env('FRONTEND_URL') . '/oauth/' .  '?token=' . $token);
        } catch (\Throwable $th) {
            Log::error('OAuth callback error: ' . $th);

            return redirect(env('FRONTEND_URL') . '/error/' . '?error=oauth_error');
        }
    }
}
