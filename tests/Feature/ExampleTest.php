<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Prueba de ejemplo de Laravel. Solo confirma que la página de inicio responde. */
class ExampleTest extends TestCase
{
    /** La ruta de bienvenida del backend responde correctamente. */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
