<?php

namespace App\Exports;

use App\Models\dibujos;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;


class dibujos_exports implements FromView, ShouldAutoSize, WithTitle
{
    use Exportable;
    public function view(): View
    {
        return view('modulos.exportaciones.dibujos_exports', [
            'dibujos' => dibujos::all()
        ]);
    }

    public function title(): string
    {
        return 'dibujos';
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return dibujos::all();
    }
}
