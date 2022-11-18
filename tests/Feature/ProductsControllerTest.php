<?php

namespace Dystcz\LunarApi\Tests\Feature\Domain\Products\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Database\Factories\PriceFactory;
use Lunar\Database\Factories\ProductFactory;
use Lunar\Database\Factories\ProductVariantFactory;

uses(\Dystcz\LunarApi\Tests\TestCase::class, RefreshDatabase::class);

it('can list all products', function () {
    ProductFactory::new()
        ->has(
            ProductVariantFactory::new()->has(PriceFactory::new())->count(1),
            'variants'
        )
        ->count(3)
        ->create();

    $response = $this->get(Config::get('lunar-api.route_prefix').'/products');

    $response->assertStatus(200);

    expect($response->json('data'))->toHaveCount(3);
});

it('can read product detail', function () {
    $product = ProductFactory::new()->create();

    $response = $this->get(Config::get('lunar-api.route_prefix').'/products/'.$product->defaultUrl->slug);

    $response->assertStatus(200);

    expect($response->json('data.id'))->toBe((string) $product->id);
});
