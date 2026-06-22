<?php

namespace Database\Factories;

use App\Models\Artwork;
use App\Models\OrderRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderRequest>
 */
class OrderRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'artwork_id' => Artwork::factory(),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->optional()->phoneNumber(),
            'message' => fake()->optional()->sentence(),
            'status' => OrderRequest::STATUS_NEW,
        ];
    }
}
