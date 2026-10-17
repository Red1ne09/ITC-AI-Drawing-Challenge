<?php

namespace Tests\Feature;

use Tests\TestCase;

class GameTest extends TestCase
{
    public function test_game_page_loads()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('AI Draw Challenge');
    }
}
