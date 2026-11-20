<?php

namespace App\Http\Controllers;

use App\Helpers\LogAktivitas;

class LogController extends Controller
{
    public function index()
    {
        return view('log.index', [
            'catatan' => LogAktivitas::baca(300),
            'ukuran'  => LogAktivitas::ukuran(),
        ]);
    }

    public function destroy()
    {
        LogAktivitas::kosongkan();
        LogAktivitas::catat('Kosongkan log aktivitas');

        return redirect()->route('log.index')->with('sukses', 'Log aktivitas sudah dikosongkan.');
    }
}
