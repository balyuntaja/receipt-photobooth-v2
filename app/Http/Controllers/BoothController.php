<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\BoothSession;
use App\Models\Transaction;
use App\Enums\SessionStatusEnum;
use App\Enums\TransactionStatusEnum;
use App\Services\SettlementService;
use Illuminate\Support\Str;

class BoothController extends Controller
{
    /**
     * Start kiosk session: validate project, create session, return kiosk view.
     */
    public function start(Project $project)
    {
        // 1️⃣ Validasi project
        abort_unless($project->is_active, 403);
        abort_unless($project->user_id !== null, 404);

        // 2️⃣ Ambil atau buat setting pricing
        $setting = $project->setting ?? $project->setting()->create([
            'price_per_session' => 0,
            'copies' => 1,
            'max_retakes' => 3,
            'countdown_seconds' => 3,
            'auto_print' => true,
        ]);

        // 3️⃣ Buat session
        $session = BoothSession::create([
            'id' => strtolower(Str::random(6)),
            'project_id' => $project->id,
            'status' => SessionStatusEnum::IN_PROGRESS,
            'started_at' => now(),
        ]);

        // 4️⃣ Transaksi: hanya buat saat gratis (semua harga 0). Kalau berbayar, transaksi dibuat di payment (QRIS/voucher/confirm-free).
        $copyPriceOptions = $setting->getCopyPriceOptions();
        $minPrice = empty($copyPriceOptions) ? 0 : min($copyPriceOptions);
        $pricePerSession = (float) $minPrice;
        if ($pricePerSession <= 0) {
            $trx = Transaction::create([
                'id' => 'trx_' . $session->id . '_' . time(),
                'order_id' => 'FREE-' . $session->id . '-' . time(),
                'session_id' => $session->id,
                'owner_user_id' => $project->user_id,
                'amount' => 0,
                'status' => TransactionStatusEnum::PAID,
                'type' => 'photobooth_session',
            ]);
            app(SettlementService::class)->recordSettlement($trx, 0);
        }

        // 5️⃣ Load frames aktif (dengan photo_slots + ukuran template untuk overlay capture)
        $frames = $project->frames()
            ->wherePivot('is_active', true)
            ->where('frames.is_active', true)
            ->get();

        $framesForKiosk = $frames->map(function ($f) {
            $tw = 1920;
            $th = 1080;
            
            // Handle both file paths and full URLs
            $frameFileUrl = (strpos($f->frame_file, 'http') === 0)
                ? $f->frame_file
                : asset('storage/' . $f->frame_file);
            
            $previewUrl = (strpos($f->preview_image, 'http') === 0)
                ? $f->preview_image
                : asset('storage/' . $f->preview_image);
            
            // Try to get image size (only for local files)
            if (strpos($f->frame_file, 'http') !== 0) {
                $path = storage_path('app/public/' . $f->frame_file);
                if (is_file($path)) {
                    $size = @getimagesize($path);
                    if ($size) {
                        $tw = (int) $size[0];
                        $th = (int) $size[1];
                    }
                }
            }
            
            return [
                'id' => $f->id,
                'name' => $f->name,
                'preview' => $previewUrl,
                'frame_file' => $frameFileUrl,
                'photo_slots' => $f->photo_slots ?? [],
                'template_width' => $tw,
                'template_height' => $th,
            ];
        })->values();

        // 6️⃣ Load welcome screen components (ordered by sort_order)
        $welcomeComponents = $project->welcomeScreenComponents()
            ->ordered()
            ->get();

        // 7️⃣ Return kiosk view with all data for JS + CSP untuk Midtrans Snap
        $response = response()->view('booth.kiosk', [
            'project' => $project,
            'session' => $session,
            'setting' => $setting,
            'frames' => $frames,
            'framesForKiosk' => $framesForKiosk,
            'welcomeComponents' => $welcomeComponents,
            'initialState' => 'IDLE',
            'pricePerSession' => $pricePerSession,
            'copyPriceOptions' => $copyPriceOptions,
        ]);

        // CSP untuk Midtrans Snap (hanya saat popup dibuka, tidak perlu di halaman kiosk)
        // CSP akan ditambahkan via meta tag di kiosk.blade.php jika diperlukan, atau via middleware
        // Untuk sementara tidak set CSP di sini agar layout tidak rusak

        return $response;
    }

    /**
     * QR / softfile page: view captured photos for a session.
     * Public route (no auth) - users arrive via QR code.
     */
    public function result(BoothSession $session)
    {
        $project = $session->project;
        $media = $session->media()->get();

        return view('booth.result', [
            'project' => $project,
            'session' => $session,
            'media' => $media,
        ]);
    }
}
