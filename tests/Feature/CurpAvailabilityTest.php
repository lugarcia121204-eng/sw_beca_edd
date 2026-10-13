<?php

namespace Tests\Feature;

use App\Exceptions\CurpServiceUnavailableException;
use App\Services\CurpService;
use Illuminate\Support\Facades\Http;
use ReflectionMethod;
use Tests\TestCase;

class CurpAvailabilityTest extends TestCase
{
    public function test_connection_failure_is_not_treated_as_missing_curp(): void
    {
        Http::fake(['*' => Http::failedConnection()]);
        $this->expectException(CurpServiceUnavailableException::class);
        (new ReflectionMethod(CurpService::class, 'consultarApi'))->invoke(new CurpService(), 'AAAA000101HDFBBB00');
    }

    public function test_provider_failure_is_not_treated_as_missing_curp(): void
    {
        Http::fake(['*' => Http::response([], 500)]);
        $this->expectException(CurpServiceUnavailableException::class);
        (new ReflectionMethod(CurpService::class, 'consultarApi'))->invoke(new CurpService(), 'AAAA000101HDFBBB00');
    }

    public function test_lookup_returns_service_unavailable_message(): void
    {
        $this->mock(CurpService::class, function ($mock) {
            $mock->shouldReceive('buscarPorCurp')->once()->andThrow(new CurpServiceUnavailableException());
        });
        $this->postJson('/api/buscar-curp', ['curp' => 'AAAA000101HDFBBB00'])
            ->assertStatus(503)
            ->assertJsonPath('code', 'curp_service_unavailable')
            ->assertJsonPath('success', false);
    }

    public function test_invalid_provider_response_is_not_treated_as_missing_curp(): void
    {
        Http::fake(['*' => Http::response('Página de error', 200, ['Content-Type' => 'text/html'])]);
        $this->expectException(CurpServiceUnavailableException::class);
        (new ReflectionMethod(CurpService::class, 'consultarApi'))->invoke(new CurpService(), 'AAAA000101HDFBBB00');
    }

    public function test_missing_result_remains_distinct_from_connection_failure(): void
    {
        $this->mock(CurpService::class, function ($mock) {
            $mock->shouldReceive('buscarPorCurp')->once()->andReturn(null);
        });
        $this->postJson('/api/buscar-curp', ['curp' => 'AAAA000101HDFBBB00'])->assertNotFound();
    }
}
