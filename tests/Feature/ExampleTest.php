<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        Tenant::factory()->create(['subdomain' => 'demo']);

        $response = $this->get('http://demo.localhost/');

        $response->assertStatus(200);
    }
}
