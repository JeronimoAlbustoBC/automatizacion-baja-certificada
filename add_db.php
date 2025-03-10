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
      
        if ($archivo != "." && $archivo != "..") {
            $rutaArchivo = $carpeta . DIRECTORY_SEPARATOR . $archivo;
            // Procesar el archivo dependiendo de su nombre o extensión
            if (strpos($archivo, 'rg17.txt') !== false) {
                procesarArchivo1($rutaArchivo);
            }
        }
    }
}

// Procesar los archivos en las carpetas
procesarArchivosDeCarpeta('Downloads1');


$pdo = null; // Esto cierra la conexión con la base de datos
?>
