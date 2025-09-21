<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "name" => $this->faker->name,
            "email" => $this->faker->unique()->safeEmail,
            "phone" => $this->faker->phoneNumber,
            "company" => $this->faker->company,
            "address" => $this->faker->address,
            "status_id" => 1,
            "user_id" => 1,
            "is_email_valid" => true,
            "converted" => $this->faker->boolean(30), // 30% chance of being converted (client)
        ];
    }
}
