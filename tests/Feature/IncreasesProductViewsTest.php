<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Dystcz\LunarApi\Domain\Products\Factories\ProductFactory;
use Illuminate\Support\Facades\Redis;

uses(\Dystcz\LunarApi\Tests\TestCase::class, RefreshDatabase::class);

it('records a view each time the product is shown', function () {
    $product = ProductFactory::new()->create();

    $self = 'http://localhost/api/v1/products/'.$product->getRouteKey();

    $this
        ->jsonApi()
        ->expects('products')
        ->get($self);

    $hits = Redis::zCount("product:views:{$product->id}", -INF, +INF);

    expect($hits)->toBe(1);

    $this
        ->jsonApi()
        ->expects('products')
        ->get($self);

    $hits = Redis::zCount("product:views:{$product->id}", -INF, +INF);

    expect($hits)->toBe(2);
});