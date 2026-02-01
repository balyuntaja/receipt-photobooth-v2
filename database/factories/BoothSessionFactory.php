<?php

namespace Database\Factories;

use App\Models\BoothSession;
use App\Models\Project;
use App\Enums\SessionStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BoothSessionFactory extends Factory
{
    protected $model = BoothSession::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-2 days', 'now');
        $end   = (clone $start)->modify('+' . rand(60, 300) . ' seconds');

        return [
            'id'           => strtolower(Str::random(6)),
            'project_id'   => Project::factory(),
            'started_at'   => $start,
            'ended_at'     => $end,
            'duration_sec' => $end->getTimestamp() - $start->getTimestamp(),
            'status'       => SessionStatusEnum::COMPLETED,
        ];
    }
}
