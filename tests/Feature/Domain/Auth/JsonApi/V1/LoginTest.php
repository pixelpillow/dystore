<?php

use Dystcz\LunarApi\Domain\Customers\Models\Customer;
use Dystcz\LunarApi\Domain\Users\Models\User;
use Dystcz\LunarApi\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(TestCase::class, RefreshDatabase::class)
    ->group('auth');

it('cannot log in a user with wrong credentials', function () {
    /** @var TestCase $this */
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $data = [
        'type' => 'auth',
        'attributes' => [
            'email' => $user->email,
            'password' => 'swag',
        ],
    ];

    $response = $this
        ->jsonApi()
        ->expects('users')
        ->withData($data)
        ->post(serverUrl('/auth/-actions/login'));

    $response
        ->assertStatus(422)
        ->assertErrorStatus([
            'detail' => __('lunar-api::validations.auth.attempt.failed'),
            'status' => '422',
        ]);
});

it('can log in a user', function () {
    /** @var TestCase $this */
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $data = [
        'type' => 'auth',
        'attributes' => [
            'email' => $user->email,
            'password' => 'password',
        ],
    ];

    $response = $this
        ->jsonApi()
        ->expects('users')
        ->withData($data)
        ->post(serverUrl('/auth/-actions/login'));

    $response->assertSuccessful();
});

it('can return a user with customers included after login', function () {
    /** @var TestCase $this */
    $user = User::factory()
        ->has(Customer::factory(), 'customers')
        ->create([
            'password' => Hash::make('password'),
        ]);

    $data = [
        'type' => 'auth',
        'attributes' => [
            'email' => $user->email,
            'password' => 'password',
        ],
    ];

    $response = $this
        ->jsonApi()
        ->expects('users')
        ->withData($data)
        ->includePaths('customers')
        ->post(serverUrl('/auth/-actions/login'));

    $response
        ->assertSuccessful()
        ->assertIsIncluded('customers', $user->customers->first());
});
