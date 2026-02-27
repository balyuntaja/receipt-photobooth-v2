<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\Frame;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        return $data;
    }

    /**
     * Download file from URL to storage if not exists.
     * Returns storage path so views can use Storage::url(). Uses placeholder if download fails.
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
                    $this->writePlaceholderPng($fullPath);
                }
            } catch (\Exception $e) {
                $this->writePlaceholderPng($fullPath);
            }
        }

        return $storagePath;
    }

    private function writePlaceholderPng(string $fullPath): void
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        if ($png !== false) {
            file_put_contents($fullPath, $png);
        }
    }

    protected function afterCreate(): void
    {
        $this->record->setting()->create([
            'price_per_session' => 0,
            'copies' => 1,
            'max_retakes' => 3,
            'auto_print' => true,
        ]);

        // Default cover image for new projects (use SEEDER_ASSET_BASE_URL in dev so assets exist)
        $baseUrl = rtrim(env('SEEDER_ASSET_BASE_URL', config('app.url', 'https://receipt.photomate.id')), '/');
        $generalHomescreenPath = $this->ensureFileFromUrl($baseUrl . '/general_homescreen.png', 'general_homescreen.png');
        $this->record->update(['cover_image' => $generalHomescreenPath]);

        // Default frame using template-frame from production URL
        $template1Path = $this->ensureFileFromUrl($baseUrl . '/template-frame/template-1.png', 'template-frame/template-1.png');
        $defaultFrame = Frame::create([
            'user_id' => Auth::id(),
            'name' => 'Default Frame',
            'preview_image' => $template1Path,
            'frame_file' => $template1Path,
            'is_active' => true,
        ]);

        $this->record->frames()->attach($defaultFrame->id, ['is_active' => true]);
    }
}
