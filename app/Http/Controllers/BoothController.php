<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\BoothSession;
use App\Models\Transaction;
use App\Enums\SessionStatusEnum;
use App\Enums\TransactionStatusEnum;
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
            'auto_print' => true,
        ]);

        // 3️⃣ Buat session
        $session = BoothSession::create([
            'id' => strtolower(Str::random(6)),
            'project_id' => $project->id,
            'status' => SessionStatusEnum::IN_PROGRESS,
            'started_at' => now(),
        ]);

        // 4️⃣ Buat transaksi (sementara PAID)
        Transaction::create([
            'id' => uniqid('trx_'),
            'session_id' => $session->id,
            'amount' => $setting->price_per_session,
            'status' => TransactionStatusEnum::PAID,
        ]);

        // 5️⃣ Load frames aktif
        $frames = $project->frames()
            ->wherePivot('is_active', true)
            ->where('frames.is_active', true)
            ->get();

        // 6️⃣ Load welcome screen components (ordered by sort_order)
        $welcomeComponents = $project->welcomeScreenComponents()
            ->ordered()
            ->get();

        // 7️⃣ Return kiosk view with all data for JS
        return view('booth.kiosk', [
            'project' => $project,
            'session' => $session,
            'setting' => $setting,
            'frames' => $frames,
            'welcomeComponents' => $welcomeComponents,
            'initialState' => 'IDLE',
        ]);
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
