<?php

namespace Database\Seeders;

use App\Models\Frame;
use App\Models\Project;
use App\Models\ProjectSetting;
use App\Models\User;
use App\Models\WelcomeScreenComponent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

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
     * Download file from URL to storage if not exists.
     * Returns storage path (e.g. "template-frame/template-1.png") so views can use Storage::url().
     * In development, use SEEDER_ASSET_BASE_URL so assets are fetched from a server that has them.
     */
    private function ensureFileFromUrl(string $url, string $storagePath): string
    {
        $fullPath = storage_path('app/public/' . $storagePath);
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!file_exists($fullPath)) {
            try {
                $response = Http::timeout(30)->get($url);
                if ($response->successful() && strlen($response->body()) > 0) {
                    file_put_contents($fullPath, $response->body());
                } else {
                    $this->writePlaceholderImage($fullPath);
                }
            } catch (\Exception $e) {
                $this->writePlaceholderImage($fullPath);
            }
        }

        return $storagePath;
    }

    /**
     * Write a minimal 1x1 PNG placeholder so storage path is always valid (views use Storage::url).
     */
    private function writePlaceholderImage(string $fullPath): void
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        if ($png !== false) {
            file_put_contents($fullPath, $png);
        }
    }

    /**
     * Seed 3 template frames (dengan slot foto) dan 1 default project untuk user.
     */
    public function seedDefaultFramesAndProject(User $user): void
    {
        // Use SEEDER_ASSET_BASE_URL in development so assets are downloaded from a server that has them.
        // In dev, APP_URL is often localhost and does not serve /template-frame/ or general_homescreen.
        $baseUrl = rtrim(env('SEEDER_ASSET_BASE_URL', 'https://receipt.photomate.id'), '/');

        // Download template frames if not exists
        $template1Path = $this->ensureFileFromUrl($baseUrl . '/template-frame/template-1.png', 'template-frame/template-1.png');
        $template2Path = $this->ensureFileFromUrl($baseUrl . '/template-frame/template-2.png', 'template-frame/template-2.png');
        $template3Path = $this->ensureFileFromUrl($baseUrl . '/template-frame/template-3.png', 'template-frame/template-3.png');
        $generalHomescreenPath = $this->ensureFileFromUrl($baseUrl . '/general_homescreen.png', 'general_homescreen.png');
        
        $templateFrames = [
            ['name' => 'Template 1', 'file' => $template1Path, 'key' => 'template-1'],
            ['name' => 'Template 2', 'file' => $template2Path, 'key' => 'template-2'],
            ['name' => 'Template 3', 'file' => $template3Path, 'key' => 'template-3'],
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
                'cover_image' => $generalHomescreenPath,
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
                'content' => ['path' => $generalHomescreenPath],
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
