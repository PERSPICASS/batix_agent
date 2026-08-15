<?php

namespace Database\Factories;

use App\Models\MarketingCampaign;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MarketingCampaign> */
class MarketingCampaignFactory extends Factory
{
    protected $model = MarketingCampaign::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'channel' => 'facebook',
            'status' => 'draft',
            'objective' => fake()->sentence(),
            'audience' => fake()->sentence(),
            'offer' => fake()->sentence(),
            'daily_budget' => 5000,
        ];
    }
}
