<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testRegisterMemberSuccess()
    {
        $response = $this->post('/api/users/register/members',[
            'name' => 'jhondoe',
            'username' => 'jhondoe',
            'password' => 'default',
            'is_admin' => 2
        ]);

        $response->assertStatus(201)
        ->assertJson([
            "data" => [
                "name" => "jhondoe",
                "username" => "jhondoe",
                "is_admin" => 2
            ]
        ]);
    }

    public function testRegisterMemberIsFailed(){
        $response = $this->post('/api/users/register/members',[
            'name' => '',
            'username' => '',
            'password' => '',
            'is_admin' => 2
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
        $response = $this->post('/api/users/register/members',[
            'name' => 'jhondoe benz',
            'username' => 'jhondoe',
            'password' => 'default',
            'is_admin' => 2
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
}
