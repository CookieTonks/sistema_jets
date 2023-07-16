<?php

namespace App\Exports;

use App\Models\Production;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class Produccion implements FromView, WithTitle
{
    public function view(): View
    {
        return view('modulos.exportaciones.production', [
            'Produccion' => Production::all()
        ]);
    }

    public function title(): string
    {
        return 'PRODUCCION';
    }
}
