<?php

use App\Http\Livewire\Traits\MensajesErrorUsuario;
use Illuminate\Support\Facades\Log;

it('returns user-friendly error message without raw exception text', function () {
    Log::shouldReceive('error')->once();

    $testClass = new class
    {
        use MensajesErrorUsuario;

        public function testMensaje(\Throwable $e, string $contexto): string
        {
            return $this->mensajeErrorUsuario($e, $contexto);
        }
    };

    $exception = new \Exception('Database connection failed at host 192.168.1.1:5432');
    $result = $testClass->testMensaje($exception, 'guardar la venta');

    expect($result)->toBe('Ocurrió un error al guardar la venta. Intente nuevamente o contacte al administrador.');
    expect($result)->not->toContain('Database connection failed');
    expect($result)->not->toContain('192.168.1.1');
});

it('logs the error with context via Log facade', function () {
    Log::shouldReceive('error')
        ->once()
        ->with('Error en buscar cargas: Something went wrong');

    $testClass = new class
    {
        use MensajesErrorUsuario;

        public function testMensaje(\Throwable $e, string $contexto): string
        {
            return $this->mensajeErrorUsuario($e, $contexto);
        }
    };

    $exception = new \Exception('Something went wrong');
    $result = $testClass->testMensaje($exception, 'buscar cargas');

    expect($result)->toContain('buscar cargas');
});

it('handles different contexts correctly', function () {
    Log::shouldReceive('error')->times(3);

    $testClass = new class
    {
        use MensajesErrorUsuario;

        public function testMensaje(\Throwable $e, string $contexto): string
        {
            return $this->mensajeErrorUsuario($e, $contexto);
        }
    };

    $contexts = ['crear la tarea rápida', 'liquidar los pagos', 'iniciar la operación'];

    foreach ($contexts as $contexto) {
        $result = $testClass->testMensaje(new \Exception('err'), $contexto);
        expect($result)->toContain($contexto);
        expect($result)->toContain('Intente nuevamente');
    }
});
