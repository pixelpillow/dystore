<?php

namespace Dystore\Tests\Api\Feature\Domain\ProductVariants\JsonApi\V1;

use Dystore\Tests\Api\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class);

test('users cannot create new product variants', function () {
    /** @var TestCase $this */
    $response = $this->createTest('product_variants', []);

    $response->assertErrorStatus([
        'status' => '405',
        'title' => 'Method Not Allowed',
    ]);
})->group('variants', 'policies');
