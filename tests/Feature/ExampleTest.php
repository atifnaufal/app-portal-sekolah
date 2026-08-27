<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Halaman root meneruskan pengunjung ke dashboard.
     */
    public function test_root_redirects_to_dashboard(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
    }

    /**
     * Health check Railway tetap dapat diakses tanpa autentikasi.
     */
    public function test_health_check_endpoint_is_available(): void
    {
        $this->get('/up')->assertOk();
    }
}
