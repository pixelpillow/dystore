<?php

namespace Dystcz\LunarApi\Tests\Feature\Cart;

use Dystcz\LunarApi\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Facades\CartSession;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;

uses(TestCase::class, RefreshDatabase::class);

it('can empty the cart', function () {
    $currency = Currency::factory()->create([
        'decimal_places' => 2,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create([
        'unit_quantity' => 1,
    ]);

    Price::factory()->create([
        'price' => 100,
        'tier' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasable),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasable),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    CartSession::use($cart);

    expect(CartSession::current()->lines->count())->toBe(1);

    $response = $this
        ->jsonApi()
        ->delete('/api/v1/carts/-actions/clear');

    $response->assertNoContent();

    expect(CartSession::current()->lines->count())->toBe(0);
});
