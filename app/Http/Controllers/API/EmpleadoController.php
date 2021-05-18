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
            // echo "<table border='true'>";

            # Iterar filas con ciclo for e índices
            for ($indiceFila = 2; $indiceFila <= $numeroMayorDeFila; $indiceFila++) {
                $maxfila = $indiceFila-1; // obteniendo valor maximo de las filas
                // echo    "<tr>";
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

                    // echo        "<td>$valorRaw</td>";

                    $item1[] =  $valorRaw;
                }
                // echo    "</tr>";
                $item2[] = array($indiceFila-2 => $item1);
            }
            // echo "</table>";
        }
       
        # extrae el ultimo elemento de array 
        $ultimoElemento = end($item2);
        # saca la cantidad de elmentos del array item2 y le resta uno para sacar la pocision del elemento
        $conteoElementos = count($item2)-1;
        $conteoSubElementos = count($ultimoElemento[$conteoElementos]);
        
        for($index=-0; $index<=$conteoSubElementos; $index++){

            if($index<=$conteoSubElementos){
                $newI = $this->getIterador()+6;
                $this->setIterador($newI);
            }
            # extrae de 6 en 6 los elementos del array $ultimoElemento con cada iteracion.
            $dato1[] = array_slice($ultimoElemento[$conteoElementos], $this->getIterador(),6);
            
            // se valida donde el array esta vacio y finaliza la insercion en la base de datos.
            if($dato1[$index] != []){

                $empleadoQuery = Empleado::all();
                # obtenemos cantidad de registros que hay en la base de datos
                $conteoRegistrosDB = count($empleadoQuery)-1;
                # validamos si el registro ya esta en la base de datos comparandolo con el excel.
                for($i=-0; $i<=$conteoRegistrosDB; $i++){

                    foreach ($empleadoQuery as $valor){
                        // echo $valor.'<br>';
                    }

                    if($empleadoQuery[0][$i] == $dato1[0][0]){
                        echo 'son iguales';
                    }
                }
                echo $empleadoQuery[0][1];
                // return response()->json($empleadoQuery[0][1]);
                
                #insercion de datos en campos en especifico en la base de datos.
                // $newEmpleado = new Empleado();
                // $newEmpleado->identificacion = $dato1[$index][0];
                // $newEmpleado->nombre = $dato1[$index][1]." ".$dato1[$index][2]." ".$dato1[$index][3];
                // $newEmpleado->cargo = $dato1[$index][4];
                // $newEmpleado->correo = $dato1[$index][5];
                // $newEmpleado->save();
            }

        }
        
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
