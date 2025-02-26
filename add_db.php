<?php

$host = '192.9.200.25';
$dbname = 'certificados_aut';
$username = 'admindba';
$password = 'axw043';

// Creamos la conexión a la db certificados_aut
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}


// Función para procesar el primer archivo 1
function procesarArchivo1($archivo)
{
    global $pdo;

    $stmt = $pdo->prepare("
        INSERT INTO certificados (cuit, nombre, nombre_archivo, fecha_procesamiento, fecha_desde, fecha_hasta, porcentaje, es_cliente)
        VALUES (:cuit, :nombre, :nombre_archivo, :fecha_procesamiento, :fecha_desde, :fecha_hasta, :porcentaje, :es_cliente)
    ");

    $lineas = file($archivo, FILE_IGNORE_NEW_LINES);
    foreach ($lineas as $linea) {
        // Extraemos los datos de acuerdo con el formato del archivo
        $cuit = substr($linea, 0, 11);
        $nombre = trim(substr($linea, 12, 57));
        $fecha_procesamiento = date('Y-m-d H:i');
        $fecha_desde = trim(substr($linea, 73, 11));
        $fecha_hasta = trim(substr($linea, 84, 11));
        // Convertimos las fechas a formato yyyy-mm-dd
        $fecha_desde_obj = DateTime::createFromFormat('d/m/Y', $fecha_desde);
        $fecha_hasta_obj = DateTime::createFromFormat('d/m/Y', $fecha_hasta);
        // Verificamos si la conversión fue exitosa y obtenemos las fechas en formato adecuado
        if ($fecha_desde_obj && $fecha_hasta_obj) {
            $fecha_desde = $fecha_desde_obj->format('Y-m-d');
            $fecha_hasta = $fecha_hasta_obj->format('Y-m-d');
        } else {
            echo "Error al convertir las fechas.";
        }
        $porcentaje = 100;
        $es_cliente = 1;

        // Insertamos los datos en la base de datos
        try {
            $stmt->bindParam(':cuit', $cuit);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':nombre_archivo', $archivo);
            $stmt->bindParam(':fecha_procesamiento', $fecha_procesamiento);
            $stmt->bindParam(':fecha_desde', $fecha_desde);
            $stmt->bindParam(':fecha_hasta', $fecha_hasta);
            $stmt->bindParam(':porcentaje', $porcentaje);
            $stmt->bindParam(':es_cliente', $es_cliente, PDO::PARAM_BOOL);
            $stmt->execute();
        } catch (PDOException $e) {
            echo "Error al insertar datos: " . $e->getMessage() . "<br>";
        }
    }
    echo "Datos del archivo 1 procesados exitosamente: $archivo  <br>";
}





// Función para procesar el archivo .txt
function procesarArchivo2($archivo)
{
    global $pdo;

    // Verificamos si el archivo existe
    if (!file_exists($archivo)) {
        echo "El archivo no existe: $archivo <br>";
        return;
    }

    // Obtener la fecha de procesamiento
    $fecha_procesamiento = date('Y-m-d'); // Fecha actual

    // Abrir el archivo .txt
    if (($handle = fopen($archivo, 'r')) !== false) {
        // Leer cada línea del archivo
        $isFirstLine = true;
        while (($data = fgetcsv($handle, 1000, ';')) !== false) {
            // Saltar la primera línea que contiene los encabezados
            if ($isFirstLine) {
                $isFirstLine = false;
                continue;
            }

            // Asignar los valores a variables
            $cuit = $data[1];
            $nombre = $data[2];
            $nombre_archivo = basename($archivo); // Nombre del archivo
            $fecha_desde = date('Y-m-d', strtotime($data[6])); // Convertir la fecha al formato MySQL
            $fecha_hasta = date('Y-m-d', strtotime($data[7])); // Convertir la fecha al formato MySQL
            $porcentaje = $data[4];
            $es_cliente = ($data[4] == 100) ? 1 : 0; // Si el porcentaje es 100, se considera cliente

            // Usamos una sentencia preparada para evitar problemas de sintaxis
            $sql = "INSERT IGNORE INTO certificados (cuit, nombre, nombre_archivo, fecha_procesamiento, fecha_desde, fecha_hasta, porcentaje, es_cliente) 
            VALUES ('$cuit', '$nombre', '$nombre_archivo', '$fecha_procesamiento', '$fecha_desde', '$fecha_hasta', $porcentaje, $es_cliente)
            ON DUPLICATE KEY UPDATE 
            nombre = VALUES(nombre), 
            fecha_procesamiento = VALUES(fecha_procesamiento), 
            porcentaje = VALUES(porcentaje), 
            es_cliente = VALUES(es_cliente)";
    
            
            try {
                // Preparar la consulta SQL
                $stmt = $pdo->prepare($sql);
                
                // Bind de los parámetros
                $stmt->bindParam(':cuit', $cuit);
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':nombre_archivo', $nombre_archivo);
                $stmt->bindParam(':fecha_procesamiento', $fecha_procesamiento);
                $stmt->bindParam(':fecha_desde', $fecha_desde);
                $stmt->bindParam(':fecha_hasta', $fecha_hasta);
                $stmt->bindParam(':porcentaje', $porcentaje);
                $stmt->bindParam(':es_cliente', $es_cliente, PDO::PARAM_BOOL);

                // Ejecutar la consulta
                $stmt->execute();
                echo "Nuevo registro insertado correctamente para el CUIT: $cuit <br>";
            } catch (PDOException $e) {
                echo "Error al insertar datos: " . $e->getMessage() . "<br>";
            }
        }

        // Cerrar el archivo .txt
        fclose($handle);
    } else {
        echo "Error al abrir el archivo.";
    }
}


// Llamar la función procesarArchivo2 con la ruta del archivo
$archivo = 'Downloads2/RG830.txt'; // Cambia esto a la ruta de tu archivo .txt












// // Función para procesar el tercer archivo (formato 3)
// function procesarArchivo3($archivo)
// {
//     global $pdo;

//     $stmt = $pdo->prepare("
//         INSERT INTO certificados (cuit, nombre, nombre_archivo, fecha_procesamiento, fecha_desde, fecha_hasta, porcentaje, es_cliente)
//         VALUES (:cuit, :nombre, :nombre_archivo, :fecha_procesamiento, :fecha_desde, :fecha_hasta, :porcentaje, :es_cliente)
//     ");

//     $lineas = file($archivo, FILE_IGNORE_NEW_LINES); // Leer todas las líneas del archivo
//     foreach ($lineas as $linea) {
//         // Separamos los valores por tabuladores o espacios en el caso de un formato más complejo
//         $datos = preg_split('/\t+/', $linea); // Usamos preg_split para manejar tabulaciones

//         $cuit = $datos[0]; // CUIT
//         $nombre = trim($datos[1]); // Tipo de sujeto o nombre
//         $fecha_desde = $datos[8]; // Fecha desde
//         $fecha_hasta = $datos[9]; // Fecha hasta
//         $porcentaje = 100; // Asumido siempre 100
//         $es_cliente = 1; // Asumido siempre cliente

//         // Validar las fechas antes de insertarlas
//         if (empty($fecha_desde) || !preg_match('/\d{4}-\d{2}-\d{2}/', $fecha_desde)) {
//             $fecha_desde = '0000-00-00'; // O puedes usar NULL dependiendo de lo que prefieras en la base de datos
//         }
//         if (empty($fecha_hasta) || !preg_match('/\d{4}-\d{2}-\d{2}/', $fecha_hasta)) {
//             $fecha_hasta = '0000-00-00'; // O puedes usar NULL dependiendo de lo que prefieras en la base de datos
//         }

//         // Insertamos los datos en la base de datos
//         try {
//             $stmt->bindParam(':cuit', $cuit);
//             $stmt->bindParam(':nombre', $nombre);
//             $stmt->bindParam(':nombre_archivo', $archivo);
//             $stmt->bindParam(':fecha_procesamiento', date('Y-m-d'));
//             $stmt->bindParam(':fecha_desde', $fecha_desde);
//             $stmt->bindParam(':fecha_hasta', $fecha_hasta);
//             $stmt->bindParam(':porcentaje', $porcentaje);
//             $stmt->bindParam(':es_cliente', $es_cliente, PDO::PARAM_BOOL);
//             $stmt->execute();
//         } catch (PDOException $e) {
//             echo "Error al insertar datos: " . $e->getMessage() . "<br>";
//         }
//     }
//     echo "Datos del archivo 3 procesados exitosamente: $archivo<br>";
// }

// Función para procesar todos los archivos de una carpeta
function procesarArchivosDeCarpeta($carpeta)
{
    // Comprobar si la carpeta existe
    if (!is_dir($carpeta)) {
        echo "La carpeta $carpeta no existe.<br>";
        return;
    }

    // Obtener todos los archivos dentro de la carpeta
    $archivos = scandir($carpeta);

    foreach ($archivos as $archivo) {
        // Ignorar las entradas "." y ".."
        if ($archivo != "." && $archivo != "..") {
            $rutaArchivo = $carpeta . DIRECTORY_SEPARATOR . $archivo;
            // Procesar el archivo dependiendo de su nombre o extensión
            if (strpos($archivo, 'rg17.txt') !== false) {
                procesarArchivo1($rutaArchivo);
            } elseif (strpos($archivo, 'RG830.txt') !== false) {
                procesarArchivo2($rutaArchivo);
            // } elseif (strpos($archivo, 'archivo.txt') !== false) {
            //     procesarArchivo3($rutaArchivo);
            }
        }
    }
}

// Procesar los archivos en las carpetas
procesarArchivosDeCarpeta('Downloads1');
procesarArchivosDeCarpeta('Downloads2');
// procesarArchivosDeCarpeta('Downloads3');
