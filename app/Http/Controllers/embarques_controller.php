<?php

namespace App\Http\Controllers;

use App\Models;
use App\Models\jets_rutas;
use Illuminate\Database\Eloquent\Model;
use PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Events\ModelsPruned;



class embarques_controller extends Controller
{

    public function dashboard_embarques()
    {
        // $ordenes = DB::table('orders')
        //     ->join('jets_rutas', 'orders.id', '=', 'jets_rutas.ot')
        //     ->where('jets_rutas.sistema_calidad', '=', 'DONE')
        //     ->where('jets_rutas.sistema_embarques', '=', '-')
        //     ->select('orders.*')
        //     ->get();


        $ordenes = models\salidas_produccion::where('estatus', '=', 'P/EMBARQUES')->get();

        $notificaciones = Models\notifications::all();



        $proveedor = models\proveedor::all();
        $ordenes_tratamientos = models\salidas_embarques::where('estatus', '=', 'Enviada a tratamiento')->orderBy('id', 'desc')->take(10)->get();
        $ordenes_salidas = models\salidas_embarques::where('estatus', '=', 'Enviada por facturación')
            ->orwhere('estatus', '=', 'Enviada por remisión')
            ->orderBy('id', 'desc')->take(10)->get();

        return view('modulos.embarques.dashboard_embarques', compact('notificaciones', 'proveedor', 'ordenes', 'ordenes_tratamientos', 'ordenes_salidas', 'notificaciones'));
    }

    public function buscador_embarques()
    {
        $notificaciones = Models\notifications::all();

        $ordenes_salidas = models\salidas_embarques::all();

        return view('modulos.embarques.buscador_embarques', compact('ordenes_salidas', 'notificaciones'));
    }

    public function salida_tratamiento(Request $request)
    {
        
        if ($request->tipo_salida == 'Remision') {
            $registro_jets = new models\jets_registros();
            $registro_jets->ot = $request->ot;
            $registro_jets->movimiento = 'EMBARQUES - REMISION';
            $registro_jets->responsable = Auth::user()->name;
            $registro_jets->save();

            $salida_embarques = new models\salidas_embarques();
            $salida_embarques->ot = $request->ot;
            $salida_embarques->tipo_salida = $request->tipo_salida;
            $salida_embarques->tipo_tratamiento = '-';
            $salida_embarques->proveedor = '-';
            $salida_embarques->cantidad = $request->cantidad;
            $salida_embarques->estatus = 'Enviada por remision';
            $salida_embarques->save();

            $orden = models\orders::where('id', '=', $request->ot)->first();
            $oc = $orden->cant_entregada;
            $piezas = $request->cantidad;
            $suma = $oc + $piezas;
            $orden->cant_entregada = $suma;
            $orden->save();

            $salida = models\salidas_produccion::where('id', '=', $request->id)->first();
            $salida->estatus = "L/FACTURAR";
            $salida->save();


            $pdf = PDF::loadView('modulos.embarques.remision_embarques', compact('orden', 'salida'));
            return $pdf->stream($request->ot . '.pdf');
        }

        if ($request->tipo_salida == 'Factura') {

            $registro_jets = new models\jets_registros();
            $registro_jets->ot = $request->ot;
            $registro_jets->movimiento = 'EMBARQUES - FACTURACIÓN';
            $registro_jets->responsable = Auth::user()->name;
            $registro_jets->save();

            $salida_embarques = new models\salidas_embarques();
            $salida_embarques->ot = $request->ot;
            $salida_embarques->tipo_salida = $request->tipo_salida;
            $salida_embarques->tipo_tratamiento = '-';
            $salida_embarques->proveedor = '-';
            $salida_embarques->cantidad = $request->cant_piezas;
            $salida_embarques->estatus = 'Enviada por facturación';
            $salida_embarques->save();
            



            $rutas_jets = models\jets_rutas::where('ot', '=', $request->ot)->first();
            $rutas_jets->sistema_embarques = 'DONE';
            $rutas_jets->save();

            $orden = models\orders::where('id', '=', $request->ot)->first();
            $oc = $orden->cant_entregada;
            $piezas = $request->cant_piezas;
            $suma = $oc + $piezas;
            $orden->cant_entregada = $suma;
            $orden->save();

                 $salida = models\salidas_produccion::where('id', '=', $request->id)->first();
            $salida->estatus = "L/FACTURAR";
            $salida->save();

            $evento_cliente = models\Events::where('title', '=', 'EC: ' . $request->ot)->delete();
            $evento_produccion = models\Events::where('title', '=', 'SP: ' . $request->ot)->delete();
            $salida = models\salidas_embarques::where('id', '=', $salida_embarques->id)->first();

            $pdf = PDF::loadView('modulos.embarques.facturacion_embarques', compact('orden', 'salida'));
            return $pdf->stream($request->ot . '.pdf');
        }


        // if ($request->tipo_salida == 'Tratamiento') {

        //     $registro_jets = new models\jets_registros();
        //     $registro_jets->ot = $request->ot;
        //     $registro_jets->movimiento = 'EMBARQUES - TRATAMIENTO';
        //     $registro_jets->responsable = Auth::user()->name;
        //     $registro_jets->save();

        //     $salida_embarques = new models\salidas_embarques();
        //     $salida_embarques->ot = $request->ot;
        //     $salida_embarques->tipo_salida = $request->tipo_salida;
        //     $salida_embarques->fecha_retorno = $request->fecha_retorno;
        //     $salida_embarques->tipo_tratamiento = $request->tipo_tratamiento;
        //     $salida_embarques->proveedor = $request->proveedor_tratamiento;
        //     $salida_embarques->cantidad = $request->cant_piezas;
        //     $salida_embarques->estatus = 'Enviada a tratamiento';
        //     $salida_embarques->save();

        //     $alta_material = new Models\materiales();
        //     $alta_material->ot = $request->ot;
        //     $alta_material->tipo = 'TRATAMIENTO';
        //     $alta_material->material = $request->tipo_salida;
        //     $alta_material->cantidad_solicitada = $request->cant_pieza;
        //     $alta_material->descripcion = $request->descripcion;
        //     $alta_material->proveedor = $request->proveedor_tratamiento;
        //     $alta_material->estatus = 'SOLICITADA';
        //     $alta_material->save();


        //     $orden = models\orders::where('id', '=', $request->ot)->first();
        //     $salida = models\salidas_embarques::where('id', '=', $salida_embarques->id)->first();

        //     $pdf = PDF::loadView('modulos.embarques.tratamiento_embarques', compact('orden', 'salida'));
        //     return $pdf->stream($request->ot . '.pdf');
        // }
    }

    public function regreso_tratamiento($orden_tratamiento)
    {
        $tratamiento = models\salidas_embarques::where('id', '=', $orden_tratamiento)->first();
        $tratamiento->estatus = 'Regreso de tratamiento';
        $tratamiento->save();


        $registro_jets = new models\jets_registros();
        $registro_jets->ot = $tratamiento->ot;
        $registro_jets->movimiento = 'TRATAMIENTO - EMBARQUES';
        $registro_jets->responsable = Auth::user()->name;
        $registro_jets->save();

        $regreso_calidad = models\jets_rutas::where('ot', '=', $tratamiento->ot)->first();
        $regreso_calidad->sistema_calidad = '-';
        $regreso_calidad->sistema_embarques = '-';
        $regreso_calidad->save();


        return back()->with('mensaje-success', '¡Regreso de tratamiento registrado!');
    }
}
