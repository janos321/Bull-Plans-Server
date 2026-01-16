<?php

namespace Tests\Support;

use Illuminate\Testing\TestResponse;

trait ApiTestHelpers
{
    protected function authPayload(array $overrides = []): array
    {
        return array_merge(['biztonsagiKod' => 'TEST_CODE'], $overrides);
    }

    protected function postAuthJson(string $url, array $data = []): TestResponse
    {
        return $this->postJson($url, $this->authPayload($data));
    }

    protected function putAuthJson(string $url, array $data = []): TestResponse
    {
        return $this->putJson($url, $this->authPayload($data));
    }

    protected function assertApiSuccess(TestResponse $res, string $message): TestResponse
    {
        return $res->assertOk()
            ->assertJsonStructure(['success', 'message', 'data'])
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', $message);
    }

    protected function assertApiValidation(TestResponse $res): TestResponse //kliens oldali bemeneti hiba
    {
        return $res->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation error');
    }

    protected function assertApiUnauthorized(TestResponse $res): TestResponse //401 Invalid security code
    {
        return $res->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid security code');
    }
}
