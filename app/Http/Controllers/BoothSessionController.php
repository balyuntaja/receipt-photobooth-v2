<?php

namespace App\Http\Controllers;

use App\Enums\MediaTypeEnum;
use App\Models\BoothSession;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * REST API for booth session operations.
 * Used by kiosk JavaScript to save frame, update state, and upload media.
 */
class BoothSessionController extends Controller
{
    /**
     * Save selected frame for session.
     * Validates frame belongs to project.
     */
    public function saveFrame(Request $request, BoothSession $session): JsonResponse
    {
        $validated = $request->validate([
            'frame_id' => ['required', 'integer', Rule::exists('frames', 'id')],
        ]);

        $project = $session->project;
        $frameIds = $project->frames()
            ->wherePivot('is_active', true)
            ->where('frames.is_active', true)
            ->pluck('frames.id');

        if (!$frameIds->contains($validated['frame_id'])) {
            return response()->json(['message' => 'Invalid frame for this project'], 422);
        }

        $session->update(['frame_id' => $validated['frame_id']]);

        return response()->json([
            'success' => true,
            'frame_id' => $session->frame_id,
        ]);
    }

    /**
     * Update session state (e.g. status).
     */
    public function update(Request $request, BoothSession $session): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'string', Rule::in(['in_progress', 'completed', 'cancelled'])],
        ]);

        if (isset($validated['status'])) {
            $session->update(['status' => $validated['status']]);
        }

        return response()->json([
            'success' => true,
            'session_id' => $session->id,
        ]);
    }

    /**
     * Save captured media (photo or photostrip).
     * Accepts base64 data URL or multipart file.
     */
    public function saveMedia(Request $request, BoothSession $session): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(['image', 'strip'])],
            'data' => ['required_without:file', 'string'], // base64 data URL
            'file' => ['required_without:data', 'file', 'mimes:png,jpg,jpeg', 'max:10240'],
            'index' => ['sometimes', 'integer', 'min:1'], // photo index (1, 2, 3) or omit for strip
        ]);

        $type = $validated['type'] === 'strip' ? MediaTypeEnum::STRIP : MediaTypeEnum::IMAGE;
        $index = $validated['index'] ?? null;

        $path = $this->storeMediaFile($validated, $session);

        if (!$path) {
            return response()->json(['message' => 'Failed to store media'], 500);
        }

        $media = Media::create([
            'session_id' => $session->id,
            'type' => $type,
            'file_path' => $path,
        ]);

        return response()->json([
            'success' => true,
            'media_id' => $media->id,
            'path' => $path,
        ]);
    }

    /**
     * Get media files for a session (for QR / softfile page).
     */
    public function getMedia(BoothSession $session): JsonResponse
    {
        $media = $session->media()->get()->map(function (Media $m) {
            return [
                'id' => $m->id,
                'type' => $m->type->value,
                'url' => Storage::url($m->file_path),
            ];
        });

        return response()->json([
            'success' => true,
            'files' => $media,
        ]);
    }

    private function storeMediaFile(array $validated, BoothSession $session): ?string
    {
        $sessionDir = "booth/{$session->id}";

        if (isset($validated['file'])) {
            $file = $validated['file'];
            $ext = $file->getClientOriginalExtension() ?: 'png';
            $name = ($validated['type'] ?? 'photo') . '-' . ($validated['index'] ?? 'strip') . '-' . time() . '.' . $ext;
            return $file->storeAs($sessionDir, $name, 'public');
        }

        if (isset($validated['data'])) {
            $data = $validated['data'];
            if (!preg_match('/^data:image\/(\w+);base64,/', $data, $m)) {
                return null;
            }
            $ext = $m[1] === 'jpeg' ? 'jpg' : $m[1];
            $base64 = substr($data, strpos($data, ',') + 1);
            $contents = base64_decode($base64);
            if ($contents === false) {
                return null;
            }
            $name = ($validated['type'] ?? 'photo') . '-' . ($validated['index'] ?? 'strip') . '-' . time() . '.' . $ext;
            $fullPath = $sessionDir . '/' . $name;
            Storage::disk('public')->put($fullPath, $contents);
            return $fullPath;
        }

        return null;
    }
}
