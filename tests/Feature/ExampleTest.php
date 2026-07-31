<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns a successful response', function () {
    $this->seed(\Database\Seeders\CategorySeeder::class);
    $this->seed(\Database\Seeders\ProductSeeder::class);

    $response = $this->get('/');
    $response->assertStatus(200);
});
