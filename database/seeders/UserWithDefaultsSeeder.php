<?php

namespace Database\Seeders;

use App\Models\Frame;
use App\Models\Project;
use App\Models\ProjectSetting;
use App\Models\User;
use App\Models\WelcomeScreenComponent;
use Illuminate\Database\Seeder;

class UserWithDefaultsSeeder extends Seeder
{
    /**
     * Photo slot (x, y, width, height dalam px) per template.
     */
    private static function templatePhotoSlots(): array
    {
        return [
            'template-1' => [['id' => 1, 'x' => 475, 'y' => 400, 'width' => 875, 'height' => 670]],
            'template-2' => [['id' => 1, 'x' => 505, 'y' => 800, 'width' => 745, 'height' => 675]],
            'template-3' => [['id' => 1, 'x' => 475, 'y' => 432, 'width' => 875, 'height' => 610]],
        ];
    }

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
     * Seed 3 template frames (dengan slot foto) dan 1 default project untuk user.
     */
    public function seedDefaultFramesAndProject(User $user): void
    {
        $templateFrames = [
            ['name' => 'Template 1', 'file' => 'template-frame/template-1.png', 'key' => 'template-1'],
            ['name' => 'Template 2', 'file' => 'template-frame/template-2.png', 'key' => 'template-2'],
            ['name' => 'Template 3', 'file' => 'template-frame/template-3.png', 'key' => 'template-3'],
        ];

        $allSlots = self::templatePhotoSlots();
        $frames = [];

        foreach ($templateFrames as $template) {
            $photoSlots = $allSlots[$template['key']] ?? [];
            $frame = Frame::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'frame_file' => $template['file'],
                ],
                [
                    'name' => $template['name'],
                    'preview_image' => $template['file'],
                    'is_active' => true,
                    'photo_slots' => $photoSlots,
                ]
            );

            if (empty($frame->photo_slots)) {
                $frame->update(['photo_slots' => $photoSlots]);
            }

            $frames[] = $frame;
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

        // Homescreen kiosk: background = general_homescreen.png (public/storage/general_homescreen.png)
        WelcomeScreenComponent::updateOrCreate(
            [
                'project_id' => $project->id,
                'type' => 'background',
            ],
            [
                'content' => ['path' => 'general_homescreen.png'],
                'sort_order' => 0,
            ]
        );

        $project->setting()->firstOrCreate(
            ['project_id' => $project->id],
            [
                'price_per_session' => 10000,
                'copies' => 1,
                'max_retakes' => 3,
                'countdown_seconds' => 3,
                'auto_print' => true,
            ]
        );

        $project->frames()->sync(
            collect($frames)->mapWithKeys(fn ($frame) => [$frame->id => ['is_active' => true]])->toArray()
        );
    }
}
