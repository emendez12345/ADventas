<?php

namespace sisVentas\Http\Controllers;

use Illuminate\Http\Request;

use sisVentas\Http\Requests;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use sisVentas\Http\Requests\VentaFormRequest;
use sisVentas\Venta;
use sisVentas\DetalleVenta;
use DB;

use Carbon\Carbon;
use Response;
use Illuminate\Support\Collection;



class VentaController extends Controller
{
    public function __construct()
    {

    }

    public function index(Request $request)
    {
        if ($request)
        {
          //CAMPO QUE SE FILTRA
          $query=trim($request->get('searchText'));
          //CREA UNA VARIABLE LA CUAL LE PASA LOS DATOS AGRUPADOS CON JOIN
          $ventas=DB::table('venta as v')
          //DATOS AGRUPADOS CON JOIN
          ->join('persona as p','v.idcliente','=','p.idpersona')
          ->join('detalle_venta as dv','v.idventa','=','dv.idventa')
          ->select('v.idventa','v.fecha_hora','p.nombre','v.tipo_comprobante',
          'v.serie_comprobante','v.num_comprobante','v.impuesto','v.estado',
          'v.total_venta')
          //BUSQUEDA
          ->where('v.num_comprobante','LIKE','%'.$query.'%')
          //ORDENAR DE MANERA DESCENDETE
          ->orderBy('v.idventa','desc')
          //AGRUPAR LOS DATOS
          ->groupBy('v.idventa','v.fecha_hora','p.nombre','v.tipo_comprobante',
          'v.serie_comprobante','v.num_comprobante','v.impuesto','v.estado')
          //PAGINAR
          ->paginate(7);

          //RETONAR
          return view('ventas.venta.index',["ventas"=>$ventas,"searchText"=>$query]);

        }
    }
    
    public function create()
    {
        $personas=DB::table('persona')->where('tipo_persona','=','Cliente')->get();
        $articulos=DB::table('articulo as art')
        ->join('detalle_ingreso as di','art.idarticulo','=','di.idarticulo')
        ->select(DB::raw('CONCAT(art.codigo," ",art.nombre) AS articulo'),
        'art.idarticulo','art.stock',DB::raw('avg(di.precio_venta) as 
        precio_promedio'))
        //SOLO MOSTRAR LOS ACTIVOS
        ->where('art.estado','=','Activo')
        ->where('art.stock','>','0')
        ->groupBy('articulo','art.idarticulo','art.stock')
        ->get();
        //RETORNAMOS A LA VISTA
        return view("ventas.venta.create",["personas"=>$personas,"articulos"=>$articulos]);

    }

    public function store(VentaFormRequest $request)
    {
        try {
            DB::beginTransaction();
            // SE CREA EL OBJETO
            $venta=new Venta;
            //SE ENVIA A CADA UNO DE LOS DATOS
            $venta->idcliente=$request->get('idcliente');
            $venta->tipo_comprobante=$request->get('tipo_comprobante');
            $venta->serie_comprobante=$request->get('serie_comprobante');
            $venta->num_comprobante=$request->get('num_comprobante');
            $venta->total_venta=$request->get('total_venta');
            //SE ENVIA LA FECHA Y HORA ACTUAL DEL SISTEMA
            $mytime=Carbon::now('America/Bogota');
            $venta->fecha_hora=$mytime->toDateTimeString();
            //CAMPOS INGRESADOS POR DEFECTO
            $venta->impuesto='19';
            $venta->estado='A';

            //SE GUARDA EL venta
            $venta->save();
             

            //RECIBIR LOS DATOS DE DETALLE DE INGRESO O VENTA
            $idarticulo=$request->get('idarticulo');
            $cantidad=$request->get('cantidad');
            $descuento=$request->get('descuento');
            $precio_venta=$request->get('precio_venta');

            $cont=0;

            while ($cont < count($idarticulo)) {
              $detalle=new DetalleVenta();
              $detalle->idventa=$venta->idventa;
              $detalle->idarticulo=$idarticulo[$cont];
              $detalle->cantidad=$cantidad[$cont];
              $detalle->descuento=$descuento[$cont];
              $detalle->precio_venta=$precio_venta[$cont];
              $detalle->save();
                $cont=$cont+1;
            }

             DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
        }
        return Redirect::to('ventas/venta');
    } 

    public function show($id)
    {
      $venta=DB::table('venta as v')
      //DATOS AGRUPADOS CON JOIN
      ->join('persona as p','v.idcliente','=','p.idpersona')
      ->join('detalle_venta as dv','v.idventa','=','dv.idventa')
      ->select('v.idventa','v.fecha_hora','p.nombre','v.tipo_comprobante',
      'v.serie_comprobante','v.num_comprobante','v.impuesto','v.estado',
      'v.total_venta')
      ->where('v.idventa','=',$id)
      ->first();

      $detalles=DB::table('detalle_venta as d')
          ->join('articulo as a','d.idarticulo','=','a.idarticulo')
          ->select('a.nombre as articulo','d.cantidad','d.descuento','d.precio_venta')
          ->where('d.idventa','=',$id)
          ->get();

          return view("ventas.venta.show",["venta"=>$venta,"detalles"=>$detalles]);

    }

    public function destroy($id)
    {
        $venta = Venta::findOrFail($id);
        $venta->Estado='C';
        $venta->update();
        return Redirect::to('ventas/venta');
    }

}
