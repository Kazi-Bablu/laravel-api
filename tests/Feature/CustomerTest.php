<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_customer_list()
    {

        $response = $this->get('/api/customers');

        $response->assertStatus(401);

    }
}
