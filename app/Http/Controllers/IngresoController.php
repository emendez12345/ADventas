<?php

namespace sisVentas\Http\Controllers;

use Illuminate\Http\Request;

use sisVentas\Http\Requests;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use sisVentas\Http\Requests\IngresoFormRequest;
use sisVentas\Ingreso;
use sisVentas\DetalleIngreso;
use DB;

//para utiliza la fecha horaria se hace con carbon
use Carbon\Carbon;
use Response;
use Illuminate\Support\Collection;

class IngresoController extends Controller
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
          $ingresos=DB::table('ingreso as i')
          //DATOS AGRUPADOS CON JOIN
          ->join('persona as p','i.idproveedor','=','p.idpersona')
          ->join('detalle_ingreso as di','i.idingreso','=','di.idingreso')
          ->select('i.idingreso','i.fecha_hora','p.nombre','i.tipo_comprobante',
          'i.serie_comprobante','i.num_comprobante',
          'i.impuesto','i.estado',DB::raw('sum(di.cantidad*precio_compra)as total'))
          //BUSQUEDA
          ->where('i.num_comprobante','LIKE','%'.$query.'%')
          //ORDENAR DE MANERA DESCENDETE
          ->orderBy('i.idingreso','desc')
          //AGRUPAR LOS DATOS
          ->groupBy('i.idingreso','i.fecha_hora','p.nombre','i.tipo_comprobante',
          'i.serie_comprobante','i.num_comprobante','i.impuesto','i.estado')
          //PAGINAR
          ->paginate(7);

          //RETONAR
          return view('compras.ingresos.index',["ingresos"=>$ingresos,"searchText"=>$query]);

        }
    }

    public function create()
    {
        $personas=DB::table('persona')->where('tipo_persona','=','Proveedor')->get();
        //CONSULTA A LA BD ARTICULO
        $articulos=DB::table('articulo as art')
        ->select(DB::raw('CONCAT(art.codigo," ",art.nombre) AS articulo'),'art.idarticulo')
        //SOLO MOSTRAR LOS ACTIVOS
        ->where('art.estado','=','Activo')
        ->get();
        //RETORNAMOS A LA VISTA
        return view("compras.ingresos.create",["personas"=>$personas,"articulos"=>$articulos]);
    }
    //FUNCION PARA ALMACENAR LO QUE TRAE EL CREATE (LOS INGRESOS Y LOS DETALLES DE INGRESOS)
    
    public function store(IngresoFormRequest $request)
    {
        try {
            DB::beginTransaction();
            // SE CREA EL OBJETO
            $ingreso=new Ingreso;
            //SE ENVIA A CADA UNO DE LOS DATOS
            $ingreso->idproveedor=$request->get('idproveedor');
            $ingreso->tipo_comprobante=$request->get('tipo_comprobante');
            $ingreso->serie_comprobante=$request->get('serie_comprobante');
            $ingreso->num_comprobante=$request->get('num_comprobante');
            //SE ENVIA LA FECHA Y HORA ACTUAL DEL SISTEMA
            $mytime=Carbon::now('America/Bogota');
            $ingreso->fecha_hora=$mytime->toDateTimeString();
            //CAMPOS INGRESADOS POR DEFECTO
            $ingreso->impuesto='19';
            $ingreso->estado='A';

            //SE GUARDA EL INGRESO
            $ingreso->save();
             

            //RECIBIR LOS DATOS DE DETALLE DE INGRESO O VENTA
            $idarticulo=$request->get('idarticulo');
            $cantidad=$request->get('cantidad');
            $precio_compra=$request->get('precio_compra');
            $precio_venta=$request->get('precio_venta');

   

            $cont=0;
            while ($cont < count($idarticulo)) {
              $detalle=new DetalleIngreso();
              $detalle->idingreso=$ingreso->idingreso;
              $detalle->idarticulo=$idarticulo[$cont];
              $detalle->cantidad=$cantidad[$cont];
              $detalle->precio_compra=$precio_compra[$cont];
              $detalle->precio_venta=$precio_venta[$cont];
              $detalle->save();
             $cont=$cont+1;
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
        }
        return Redirect::to('compras/ingreso');
    } 
    public function show($id)
    {
      $ingreso=DB::table('ingreso as i')
      //DATOS AGRUPADOS CON JOIN
      ->join('persona as p','i.idproveedor','=','p.idpersona')
      ->join('detalle_ingreso as di','i.idingreso','=','di.idingreso')
      ->select('i.idingreso','i.fecha_hora','p.nombre','i.tipo_comprobante','i.serie_comprobante',
      'i.num_comprobante','i.impuesto','i.estado',DB::raw('sum(di.cantidad*precio_compra)as total'))
      ->where('i.idingreso','=',$id)
      ->first();

      $detalles=DB::table('detalle_ingreso as d')
          ->join('articulo as a','d.idarticulo','=','a.idarticulo')
          ->select('a.nombre as articulo','d.cantidad','d.precio_compra','d.precio_venta')
          ->where('d.idingreso','=',$id)
          ->get();

          return view("compras.ingresos.show",["ingreso"=>$ingreso,"detalles"=>$detalles]);

    }

    public function destroy($id)
    {
        $ingreso = Ingreso::findOrFail($id);
        $ingreso->Estado='C';
        $ingreso->update();
        return Redirect::to('compras/ingreso');
    }
}
