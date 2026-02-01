<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\BoothSession;
use App\Enums\MediaTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        return [
            'session_id' => null, // diisi dari seeder
            'type' => MediaTypeEnum::IMAGE,
            'file_path' => 'media/sample.jpg',
        ];
    }

}
