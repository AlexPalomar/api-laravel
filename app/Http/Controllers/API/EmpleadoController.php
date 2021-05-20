<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Empleado;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EmpleadoController extends Controller
{
    public $iterador = -6;
    public function getIterador(){
        return $this->iterador;
    }
    public function setIterador($iterador){
        $this->iterador = $iterador;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return Empleado::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $infoFile = $request->file('archivo');

        // asi se captura el nombre del archivo subido al espacio temporal de laravel
        $nameFile = $request->file('archivo')->getClientOriginalName();
        $pathFile = $request->file('archivo')->getRealPath();
        $fileName = $request->file('archivo')->getFileName();
        // dd("subido y guardado", $nameFile, $infoFile,$pathFile);
        // dd($infoFile);

        # Recomiendo poner la ruta absoluta si no está junto al script
        # Nota: no necesariamente tiene que tener la extensión XLSX
        $rutaArchivo = $pathFile;
        $documento = IOFactory::load($rutaArchivo);

        # Recuerda que un documento puede tener múltiples hojas
        # obtener conteo e iterar
        $totalDeHojas = $documento->getSheetCount();

        # Iterar hoja por hoja
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            # Obtener hoja en el índice que vaya del ciclo
            $hojaActual = $documento->getSheet($indiceHoja);
            // echo "<h3>Vamos en la hoja con índice $indiceHoja</h3>";

            # Calcular el máximo valor de la fila como entero, es decir, el
            # límite de nuestro ciclo
            $numeroMayorDeFila = $hojaActual->getHighestRow(); // Numérico
            $letraMayorDeColumna = $hojaActual->getHighestColumn(); // Letra
            # Convertir la letra al número de columna correspondiente
            $numeroMayorDeColumna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($letraMayorDeColumna);
            

            # Iterar filas con ciclo for e índices
            for ($indiceFila = 2; $indiceFila <= $numeroMayorDeFila; $indiceFila++) {
                $maxfila = $indiceFila-1; // obteniendo valor maximo de las filas
                
                for ($indiceColumna = 1; $indiceColumna <= $numeroMayorDeColumna; $indiceColumna++) {
                    # Obtener celda por columna y fila
                    $celda = $hojaActual->getCellByColumnAndRow($indiceColumna, $indiceFila);
                    # Y ahora que tenemos una celda trabajamos con ella igual que antes
                    # El valor, así como está en el documento
                    $valorRaw = $celda->getValue();

                    # Formateado por ejemplo como dinero o con decimales
                    $valorFormateado = $celda->getFormattedValue();

                    # Si es una fórmula y necesitamos su valor, llamamos a:
                    $valorCalculado = $celda->getCalculatedValue();

                    # Fila, que comienza en 1, luego 2 y así...
                    $fila = $celda->getRow();
                    # Columna, que es la A, B, C y así...
                    $columna = $celda->getColumn();

                    // echo "En <strong>$columna$fila</strong> tenemos el valor <strong>$valorRaw</strong>. ";
                    // echo "Formateado es: <strong>$valorFormateado</strong>. ";
                    // echo "Calculado es: <strong>$valorCalculado</strong><br><br>";

                    $item1[] =  $valorRaw;
                }
                
            }
            
        }
        
        $conteoSubElementos = count($item1)-1;

        for($index=-0; $index<=$conteoSubElementos; $index++){
            #se usa getter and setter del atributo iterador para iterar de 6 en 6 con cada ciclo
            $newI = $this->getIterador()+6;
            $this->setIterador($newI);

            # extrae de 6 en 6 los elementos del array $ultimoElemento con cada iteracion.
            $dato1[] = array_slice($item1, $this->getIterador(),6);
            
            // se valida donde el array esta vacio y finaliza la insercion a la base de datos.
            if($dato1[$index] != []){
                
                # validamos con elnumero de identificacion si el registro del excel ya esta en la base de datos, y si no esta crea un registro nuevo.
                #insercion de datos en campos en especifico en la base de datos.
                $newEmpleado   =   Empleado::where ( 'identificacion' , $  dato1 [ $  index ] [ 0 ]) ->  first ();
                
                switch(¡isset($newEmpleado->identificacion)){
                    case false:
                        $newEmpleado = new Empleado();
                        $newEmpleado->identificacion = $dato1[$index][0];
                        $newEmpleado->nombre = $dato1[$index][1]." ".$dato1[$index][2]." ".$dato1[$index][3];
                        $newEmpleado->cargo = $dato1[$index][4];
                        $newEmpleado->correo = $dato1[$index][5];
                        $newEmpleado->save();
                    
                    case true:
                        
                        # por medio de estos condicionales anidados validamos si algun campo del registro recuperado de la base de datos es diferente, incluyendo campos nulos y si son nulos o diferentes los va a actualizar con los datos extraidos del excel.
                        if($dato1[$index][1]." ".$dato1[$index][2]." ".$dato1[$index][3] != $newEmpleado['nombre']){
                            $newEmpleado->nombre = $dato1[$index][1]." ".$dato1[$index][2]." ".$dato1[$index][3];
                        }else if($dato1[$index][4] != $newEmpleado['cargo']){
                            $newEmpleado->cargo = $dato1[$index][4];
                        }else if($dato1[$index][5] != $newEmpleado['correo']){
                            $newEmpleado->correo = $dato1[$index][5];
                            $newEmpleado-> save ();
                        }

                        #De esta forma accedemos a los datos que me trae la consulta alamcenada en la variable $newEmpleado
                        // return $oldEmpleado['nombre'].'<br>'.$oldEmpleado['cargo'].'<br>'.$oldEmpleado['correo'];
                }
                
                
                // if(!isset($newEmpleado->identificacion)){
                //     $newEmpleado = new Empleado();
                //     $newEmpleado->identificacion = $dato1[$index][0];
                //     $newEmpleado->nombre = $dato1[$index][1]." ".$dato1[$index][2]." ".$dato1[$index][3];
                //     $newEmpleado->cargo = $dato1[$index][4];
                //     $newEmpleado->correo = $dato1[$index][5];
                //     $newEmpleado->save();
                // }
                // return $newEmpleado;
                
            }

        }
        // return $empleadoQuery[0][1];
        // return response()->json(array('msg'=>'ok'));
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
