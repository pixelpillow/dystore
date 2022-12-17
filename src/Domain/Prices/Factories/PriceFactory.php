<?php

namespace Dystcz\LunarApi\Domain\Prices\Factories;

use Dystcz\LunarApi\Domain\Prices\Models\Price;
use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\Models\Currency;

class PriceFactory extends Factory
{
    protected $model = Price::class;

    public function definition(): array
    {
        return [
            'price' => $this->faker->numberBetween(1, 2500),
            'compare_price' => $this->faker->numberBetween(1, 2500),
            'currency_id' => Currency::factory(),
        ];
    }
}
