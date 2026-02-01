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
    public function start(Project $project)
    {
        // 1️⃣ Validasi project
        abort_unless($project->is_active, 403);
        abort_unless($project->user_id !== null, 404);

        // 2️⃣ Ambil setting pricing
        $setting = $project->setting;
        abort_if(!$setting, 403, 'Project not configured');

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

        $frames = $project->frames()
            ->wherePivot('is_active', true)
            ->where('frames.is_active', true)
            ->get();

        // 5️⃣ Masuk kiosk
        return view('booth.kiosk', [
            'project' => $project,
            'session' => $session,
            'setting' => $setting,
            'frames' => $frames,
            'initialState' => 'IDLE',
        ]);

    }
}
