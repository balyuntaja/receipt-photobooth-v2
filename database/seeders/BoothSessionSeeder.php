<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BoothSession;
use App\Models\Transaction;
use App\Models\Media;

class BoothSessionSeeder extends Seeder
{
    public function run(): void
    {
        BoothSession::factory()
            ->count(20)
            ->create()
            ->each(function ($session) {

                // 1 Transaction per session
                Transaction::factory()->create([
                    'session_id' => $session->id,
                ]);

                // 1–3 media per session
                Media::factory()
                    ->count(rand(1, 3))
                    ->create([
                        'session_id' => $session->id,
                    ]);
            });
    }
}
