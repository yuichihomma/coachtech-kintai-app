<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_サンプルを表示できる(): void
{
    $response = $this->get('/');

    $response->assertRedirect('/login');
}

}
