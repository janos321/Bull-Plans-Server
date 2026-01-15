<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityCodeMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_security_code_returns_401(): void
    {
        config()->set('app.security_code', 'TEST_CODE');

        $res = $this->postJson('/api/user/get', ['email' => 'x@test.com']);
        $res->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid security code');
    }

    public function test_wrong_security_code_returns_401(): void
    {
        config()->set('app.security_code', 'TEST_CODE');

        $res = $this->postJson('/api/user/get', [
            'biztonsagiKod' => 'WRONG',
            'email' => 'x@test.com',
        ]);

        $res->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid security code');
    }
}
