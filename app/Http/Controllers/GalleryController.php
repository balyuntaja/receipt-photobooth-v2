<?php

namespace App\Http\Controllers;

use App\Models\BoothSession;

class GalleryController extends Controller
{
    /**
     * Show gallery search form. If session param provided and valid, redirect to result page.
     */
    public function index()
    {
        $sessionId = request()->query('session');

        if ($sessionId) {
            $sessionId = strtolower(trim($sessionId));
            $session = BoothSession::find($sessionId);

            if ($session) {
                return redirect()->route('booth.result', ['session' => $session]);
            }

            return view('gallery', [
                'error' => 'Session ID tidak ditemukan. Pastikan Anda memasukkan kode yang benar dari receipt atau QR.',
                'sessionId' => $sessionId,
            ]);
        }

        return view('gallery');
    }
}
