<?php

namespace Database\Factories;

use App\Models\MarketingLead;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MarketingLead> */
class MarketingLeadFactory extends Factory
{
    protected $model = MarketingLead::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->e164PhoneNumber(),
            'company' => fake()->company(),
            'business_type' => 'Commerce',
            'source' => 'whatsapp',
            'status' => 'new',
            'score' => 0,
        ];
    }
}
