<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityCodeMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.security_code', 'TEST_CODE');
    }

    public function test_missing_security_code_returns_401(): void
    {
        $res = $this->postJson('/api/user/get', ['email' => 'x@test.com']);

        $this->assertApiUnauthorized($res);
    }

    public function test_wrong_security_code_returns_401(): void
    {
        $res = $this->postJson('/api/user/get', [
            'biztonsagiKod' => 'WRONG',
            'email' => 'x@test.com',
        ]);

        $this->assertApiUnauthorized($res);
    }
}
