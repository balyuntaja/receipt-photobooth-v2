<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'user_id' => User::factory(), // ⬅️ PENTING
            'name' => $this->faker->company . ' Booth',
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
