<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImagenProxyTest extends TestCase
{
    use RefreshDatabase;

    public function test_rechaza_path_traversal_hacia_el_env(): void
    {
        $this->getJson('/api/imagen-proxy?url=' . urlencode('/storage/../../../.env'))
            ->assertStatus(404);
    }

    public function test_rechaza_archivo_fuera_de_public(): void
    {
        $this->getJson('/api/imagen-proxy?url=' . urlencode('/../composer.json'))
            ->assertStatus(404);
    }

    public function test_rechaza_host_externo_no_permitido(): void
    {
        $this->getJson('/api/imagen-proxy?url=' . urlencode('http://169.254.169.254/latest/meta-data/'))
            ->assertStatus(404);
    }

    public function test_sirve_imagen_del_storage_propio(): void
    {
        $relative = 'testing/proxy-test.png';
        $full = storage_path('app/public/' . $relative);
        @mkdir(dirname($full), 0777, true);
        file_put_contents($full, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));

        try {
            $response = $this->get('/api/imagen-proxy?url=' . urlencode('/storage/' . $relative));

            $response->assertStatus(200);
            $this->assertStringStartsWith('image/', (string) $response->headers->get('Content-Type'));
        } finally {
            @unlink($full);
        }
    }
}
