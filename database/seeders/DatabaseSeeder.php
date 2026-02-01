<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use App\Models\BoothSession;
use App\Models\Transaction;
use App\Models\Media;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ambil / buat user
        $user = User::updateOrCreate(
            ['email' => 'balyuntaja28@gmail.com'],
            [
                'name' => 'Balyuntaja',
                'password' => bcrypt('password'), // Password default
            ]
        );

        // Project milik user
        Project::factory()
            ->count(3)
            ->create([
                'user_id' => $user->id,
            ])
            ->each(function ($project) {

                BoothSession::factory()
                    ->count(10)
                    ->create([
                        'project_id' => $project->id,
                    ])
                    ->each(function ($session) {

                        Transaction::factory()->create([
                            'session_id' => $session->id,
                        ]);

                        Media::factory()
                            ->count(rand(1, 3))
                            ->create([
                                'session_id' => $session->id,
                            ]);
                    });
            });
    }
}
