<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;

class NotifikasiController extends Controller
{
    public function buka($id)
    {
        $n = Notifikasi::where('user_id', auth()->id())->findOrFail($id);
        $n->update(['dibaca' => true]);
        return redirect($n->link ?: '/');
    }

    public function bacaSemua()
    {
        Notifikasi::where('user_id', auth()->id())->where('dibaca', false)->update(['dibaca' => true]);
        return redirect()->back();
    }
}
