<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $role = Role::firstOrCreate(['name' => 'admisi_kasir'], ['display_name' => 'Admisi & Kasir']);
    $user = User::create([
        'name' => 'Petugas Admisi Test',
        'username' => 'admisi.test',
        'email' => 'admisi@test.id',
        'phone' => '08123456789',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $user->roles()->attach($role->id);
});

test('login page is accessible for guests', function () {
    $response = $this->get(route('login'));
    $response->assertStatus(200);
    $response->assertSee('RME KLINIK');
});

test('user can authenticate using valid username and password', function () {
    $response = $this->post(route('login.post'), [
        'login' => 'admisi.test',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
});

test('user can authenticate using valid email and password', function () {
    $response = $this->post(route('login.post'), [
        'login' => 'admisi@test.id',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
});

test('user cannot authenticate with invalid password', function () {
    $response = $this->post(route('login.post'), [
        'login' => 'admisi.test',
        'password' => 'wrongpassword',
    ]);

    $response->assertSessionHasErrors('login');
    $this->assertGuest();
});

test('inactive user is blocked from logging in', function () {
    User::where('username', 'admisi.test')->update(['is_active' => false]);

    $response = $this->post(route('login.post'), [
        'login' => 'admisi.test',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('login');
    $this->assertGuest();
});

test('authenticated user can log out successfully', function () {
    $user = User::where('username', 'admisi.test')->first();

    $response = $this->actingAs($user)->post(route('logout'));
    $response->assertRedirect(route('login'));
    $this->assertGuest();
});
