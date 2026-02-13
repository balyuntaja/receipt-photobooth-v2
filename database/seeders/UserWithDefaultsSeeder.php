<?php

namespace Database\Seeders;

use App\Models\Frame;
use App\Models\Project;
use App\Models\ProjectSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserWithDefaultsSeeder extends Seeder
{
    /**
     * Seed a user with default frames (from template-frame folder) and one default project.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'balyuntaja28@gmail.com'],
            [
                'name' => 'Balyuntaja',
                'password' => bcrypt('password'),
            ]
        );

        $this->seedDefaultFramesAndProject($user);
    }

    /**
     * Seed 3 template frames and 1 default project for a user.
     */
    public function seedDefaultFramesAndProject(User $user): void
    {
        $templateFrames = [
            ['name' => 'Frame Template 1', 'file' => 'template-frame/template-1.png'],
            ['name' => 'Frame Template 2', 'file' => 'template-frame/template-2.png'],
            ['name' => 'Frame Template 3', 'file' => 'template-frame/template-3.png'],
        ];

        $frames = [];

        foreach ($templateFrames as $template) {
            $frames[] = Frame::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'frame_file' => $template['file'],
                ],
                [
                    'name' => $template['name'],
                    'preview_image' => $template['file'],
                    'is_active' => true,
                ]
            );
        }

        $project = Project::firstOrCreate(
            [
                'user_id' => $user->id,
                'name' => 'Default Project',
            ],
            [
                'description' => 'Project default photobooth',
                'cover_image' => 'general_homescreen.png',
                'is_active' => true,
            ]
        );

        $project->setting()->firstOrCreate(
            ['project_id' => $project->id],
            [
                'price_per_session' => 0,
                'copies' => 1,
                'max_retakes' => 3,
                'auto_print' => true,
            ]
        );

        $project->frames()->sync(
            collect($frames)->mapWithKeys(fn ($frame) => [$frame->id => ['is_active' => true]])->toArray()
        );
    }
}
