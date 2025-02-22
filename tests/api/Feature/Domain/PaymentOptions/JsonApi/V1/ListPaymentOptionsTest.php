<?php

use Dystore\Api\Domain\Carts\Models\Cart;
use Dystore\Api\Domain\PaymentOptions\Entities\PaymentOption;
use Dystore\Api\Domain\PaymentOptions\Facades\PaymentManifest;
use Dystore\Tests\Api\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

uses(TestCase::class, RefreshDatabase::class);

it('can list payment options', function () {
    /** @var TestCase $this */
    $response = $this
        ->jsonApi()
        ->expects('payment_options')
        ->get(serverUrl('/payment_options'));

    $response->assertSuccessful();

    $options = PaymentManifest::getOptions(new Cart);

    $response->assertFetchedMany(
        $options->map(function (PaymentOption $paymentOption) {
            return [
                'type' => 'payment_options',
                'id' => Str::slug($paymentOption->getId()),
                'attributes' => [
                    'driver' => $paymentOption->getDriver(),
                    'name' => $paymentOption->getName(),
                    'description' => $paymentOption->getDescription(),
                    'default' => $paymentOption->isDefault(),
                    'currency' => Arr::only($paymentOption->getCurrency()->toArray(), ['code', 'name']),
                    'identifier' => $paymentOption->getIdentifier(),
                    'price' => [
                        'decimal' => $paymentOption->getPrice()->decimal,
                        'formatted' => $paymentOption->getPrice()->formatted,
                    ],
                ],
            ];
        })->toArray()
    );

})->group('payment_options');
