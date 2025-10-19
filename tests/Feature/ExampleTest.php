<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    #[Test]
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }
}
