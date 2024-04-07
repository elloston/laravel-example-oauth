<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_signup()
    {
        $users = [
            [
                'name' => 'test',
                'email' => 'withusername@example.com',
                'password' => 'password',
                'password_confirmation' => 'password'
            ]
        ];

        foreach ($users as $user) {
            $response = $this->postJson('/api/signup', $user);

            $response->assertStatus(201);

            unset($user['password']);
            unset($user['password_confirmation']);

            $this->assertDatabaseHas('users', $user);
        }
    }

    public function test_user_login()
    {
        User::factory()->create([
            'email' => 'signin@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'signin@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token']);
    }

    public function test_user_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/logout');

        $response->assertStatus(204);
    }
}
