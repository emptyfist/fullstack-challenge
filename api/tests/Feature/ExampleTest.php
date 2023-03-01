<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_the_weather_returns_a_successful_response()
    {
        User::factory(20)->create();
        $user = User::first();

        $response = $this->get('/' . $user->latitude . '/' . $user->longitude);

        $response->assertStatus(200);

        $content = $response->decodeResponseJson();

        $this->assertEquals($content['status'], 'success');
    }

    public function test_database_works()
    {
        User::factory(20)->create();

        $this->assertEquals(20, User::all()->count());
    }
}
