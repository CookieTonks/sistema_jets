<?php

namespace App\Exports;

use App\Models\orders;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;


class ordenes_exports implements FromView, ShouldAutoSize, WithTitle
{
    use Exportable;
    public function view(): View
    {
        return view('modulos.exportaciones.ordenes_exports', [
            'ordenes' => orders::all()
        ]);
    }

    public function title(): string
    {
        return 'ordernes';
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return orders::all();
    }
}
