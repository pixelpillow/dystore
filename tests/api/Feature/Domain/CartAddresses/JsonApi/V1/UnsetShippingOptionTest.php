<?php

use Dystore\Api\Domain\CartAddresses\Models\CartAddress;
use Dystore\Api\Domain\Carts\Models\Cart;
use Dystore\Tests\Api\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Lunar\Base\CartSessionInterface;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    /** @var TestCase $this */
    $this->cartSession = App::make(CartSessionInterface::class);

    $this->cart = Cart::factory()->create();

    $this->cartAddress = CartAddress::factory()->for($this->cart)->create();

    $this->data = [
        'id' => (string) $this->cartAddress->getRouteKey(),
        'type' => 'cart_addresses',
    ];
});

test('users can unset a shipping option from cart address', function () {
    /** @var TestCase $this */
    $this->cartSession->use($this->cart);

    $response = $this
        ->jsonApi()
        ->expects('cart_addresses')
        ->withData($this->data)
        ->patch(serverUrl("/cart_addresses/{$this->cartAddress->getRouteKey()}/-actions/unset-shipping-option"));

    $response->assertFetchedOne($this->cartAddress);

    $this->assertDatabaseHas($this->cartAddress->getTable(), [
        'shipping_option' => null,
    ]);

    expect($this->cartAddress->fresh()->shipping_option)->toBeNull();
})->group('cart_addresses', 'shipping_options');

test('only the user who owns the cart address can unset shipping option for it', function () {
    /** @var TestCase $this */
    $this->cartSession->forget();
    $response = $this
        ->jsonApi()
        ->expects('cart_addresses')
        ->withData($this->data)
        ->patch(serverUrl("/cart_addresses/{$this->cartAddress->getRouteKey()}/-actions/unset-shipping-option"));

    $response->assertErrorStatus([
        'detail' => 'Unauthenticated.',
        'status' => '401',
        'title' => 'Unauthorized',
    ]);
})->group('cart_addresses', 'shipping_options');
