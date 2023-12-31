<?php

namespace Tests\Feature;

use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testRegisterMemberSuccess()
    {
        $response = $this->post('/api/users/register',[
            'name' => 'jhondoe',
            'username' => 'jhondoe',
            'password' => 'default',
            'is_admin' => 0
        ]);

        $response->assertStatus(201)
        ->assertJson([
            "data" => [
                "name" => "jhondoe",
                "username" => "jhondoe",
                "is_admin" => 0
            ]
        ]);
    }

    public function testRegisterMemberIsFailed(){
        $response = $this->post('/api/users/register',[
            'name' => '',
            'username' => '',
            'password' => '',
            'is_admin' => 0
        ]);

        $response->assertStatus(400)
        ->assertJson([
            "errors" => [
                "name" => [
                    "The name field is required."
                ],
                "username" => [
                    "The username field is required."
                ],
                "password" => [
                    "The password field is required."
                ]
            ]
        ]);
    }

    public function testRegisterMemberIsUsernameAlreadyExists(){
        $this->testRegisterMemberSuccess();
        $response = $this->post('/api/users/register',[
            'name' => 'jhondoe',
            'username' => 'jhondoe',
            'password' => 'default',
            'is_admin' => 0
        ]);

        $response->assertStatus(400)
        ->assertJson([
            "errors" => [
                "username" => [
                    "username already exists"
                ]
            ]
        ]);
    }

    public function testRegisterAdminSuccess()
    {
        $response = $this->post("/api/users/register/admin", [
            'name' => 'admin',
            'username' => 'admin',
            'password' => 'admin123',
            'is_admin' => 1
        ]);
        $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'name' => 'admin',
                'username' => 'admin',
                'is_admin' => 1
            ]
        ]);
    }

    public function testRegisterAdminIsUsernameAlreadyExists()
    {
        $this->testRegisterAdminSuccess();
        $response = $this->post('/api/users/register/admin',[
            'name' => 'admin',
            'username' => 'admin',
            'password' => 'admin123',
            'is_admin' => 1
        ]);
        $response->assertStatus(400)
        ->assertJson([
            'errors' => [
                'username' => [
                    'username already exists.'
                ]
            ]
                ]);
    }

    public function testLoginSuccess()
    {
        $this->seed([UserSeeder::class]);
        $response = $this->post('/api/users/login',[
            'username' => 'test',
            'password' => 'testing123',
        ]);

        $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'test',
                'username' => 'test',
                'is_admin' => 0
            ]
        ]);

        $user = User::where('username', 'test')->first();
        self::assertNotNull($user->token);
    }

    public function testLoginFailed()
    {
        $response = $this->post('/api/users/login',[
            'username' => 'test',
            'password' => 'test12345'
        ]);
        $response->assertStatus(401)
        ->assertJson([
            'errors' => [
                'username' => [
                    'username or password wrong.'
                ]
            ]
                ]);
    }

    public function testLoginIsRequired()
    {
        $response = $this->post('/api/users/login',[
            'username' => '',
            'password' => ''
        ]);
        $response->assertStatus(400)
        ->assertJson([
            'errors' => [
                'username' => [
                    'The username field is required.'
                ],
                'password' => ['The password field is required.']
            ]
                ]);
    }

    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class]);
        $response = $this->get('/api/users/current',[
            'authorization' => 'test'
        ]);

        $response->assertStatus(200)
        ->assertJson([
            'data'=> [
                'name' => 'test',
                'username' => 'test',
                'is_admin' => 0
            ]
        ]);
    }

    public function testGetUnauthorized()
    {
        $this->seed([UserSeeder::class]);
        $response = $this->get('/api/users/current');
        $response->assertStatus(401)
        ->assertJson([
            'errors'=> [
                'message' => [
                    'unauthorized'
                ]
            ]
            ]);
    }

    public function testGetInvalidToken()
    {
        $this->seed([UserSeeder::class]);
        $response = $this->get('/api/users/current', [
            'authorization'=> 'salah'
        ]);
        $response->assertStatus(401)
        ->assertJson([
            'errors'=> [
                'message' => [
                    'unauthorized'
                ]
            ]
            ]);
    }

    public function testUpdateNameSuccess()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'test')->first();
        $response = $this->patch('/api/users/current', [
            'name' => 'baru'
        ], [
            'authorization' => 'test'
        ]);
        $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'baru',
                'username' => 'test',
                'is_admin' => 0
            ]
        ]);
        $newUser = User::where('username', 'test')->first();
        self::assertNotEquals($oldUser->name, $newUser->name);
    }

    public function testUpdatePasswordSuccess()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'test')->first();
        $response = $this->patch('/api/users/current', [
            'password' => 'baru12345'
        ], [
            'authorization' => 'test'
        ]);
        $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'test',
                'username' => 'test',
                'is_admin' => 0
            ]
        ]);
        $newUser = User::where('username', 'test')->first();
        self::assertNotEquals($oldUser->password, $newUser->password);
    }

    public function testLogoutSuccess()
    {
        $this->seed([UserSeeder::class]);
        $response= $this->delete('/api/users/logout',[],headers:[
            'authorization' => 'test'
        ]);
        $response->assertStatus(200)
        ->assertJson([
            'success' => true
        ]);

        $user = User::where('username', 'test')->first();
        self::assertNull($user->token);
    }

    public function testLogoutFailed()
    {
        $this->seed([UserSeeder::class]);
        $response= $this->delete('/api/users/logout', [], [
            'authorization' => 'salah'
        ]);
        $response->assertStatus(401)
        ->assertJson([
            'errors' => [
                'message' => [
                    'unauthorized'
                ]
            ]
        ]);
    }
}
