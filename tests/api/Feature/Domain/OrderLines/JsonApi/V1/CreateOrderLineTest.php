<?php

use Dystore\Api\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class);

test('users cannot create order lines', function () {
    /** @var TestCase $this */
    $response = $this->createTest('oder-lines', []);

    $response->assertErrorStatus([
        'status' => '404',
        'title' => 'Not Found',
    ]);
})->group('order_lines', 'policies');
