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


function procesarArchivo2($archivo)
{
    global $pdo;

    $stmt = $pdo->prepare("
        INSERT INTO certificados (cuit, nombre, nombre_archivo, fecha_procesamiento, fecha_desde, fecha_hasta, porcentaje, es_cliente)
        VALUES (:cuit, :nombre, :nombre_archivo, :fecha_procesamiento, :fecha_desde, :fecha_hasta, :porcentaje, :es_cliente)
    ");

    $lineas = file($archivo, FILE_IGNORE_NEW_LINES);
    foreach ($lineas as $linea) {
        // Extraemos los datos del archivo
        $cuit = substr($linea, 0, 11); // Extrae el CUIT
        $nombre = trim(substr($linea, 12, 57)); // Extrae el nombre
        $fecha_procesamiento = date('Y-m-d H:i'); // Fecha de procesamiento actual
        $fecha_desde = trim(substr($linea, 73, 11)); // Extrae la fecha desde
        $fecha_hasta = trim(substr($linea, 84, 11)); // Extrae la fecha hasta

        // Limpiar datos de fecha con texto extraño
        $fecha_desde = preg_replace('/[^0-9\/]/', '', $fecha_desde);
        $fecha_hasta = preg_replace('/[^0-9\/]/', '', $fecha_hasta);

        // Validamos que las fechas sean válidas
        if (empty($fecha_desde) || empty($fecha_hasta)) {
            echo "Fecha vacía en el archivo para el CUIT $cuit.<br>";
            continue; // Saltamos este registro
        }

        // Intentamos convertir las fechas
        $fecha_desde_obj = DateTime::createFromFormat('d/m/Y', $fecha_desde);
        $fecha_hasta_obj = DateTime::createFromFormat('d/m/Y', $fecha_hasta);

        // Verificamos si la conversión fue exitosa
        if ($fecha_desde_obj && $fecha_hasta_obj) {
            $fecha_desde = $fecha_desde_obj->format('Y-m-d');
            $fecha_hasta = $fecha_hasta_obj->format('Y-m-d');
        } else {
            echo "Error al convertir las fechas para el CUIT $cuit: $fecha_desde - $fecha_hasta.<br>";
            continue; // Saltamos este registro si las fechas no son válidas
        }

        // Porcentaje y cliente
        $porcentaje = 100;
        $es_cliente = 1;

        // Insertamos los datos en la base de datos
        try {
            // Asignamos el nombre de archivo de manera fija como 'rg830.txt'
            $nombre_archivo = 'rg830.txt';
            $stmt->bindParam(':cuit', $cuit);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':nombre_archivo', $nombre_archivo);
            $stmt->bindParam(':fecha_procesamiento', $fecha_procesamiento);
            $stmt->bindParam(':fecha_desde', $fecha_desde);
            $stmt->bindParam(':fecha_hasta', $fecha_hasta);
            $stmt->bindParam(':porcentaje', $porcentaje);
            $stmt->bindParam(':es_cliente', $es_cliente, PDO::PARAM_BOOL);
            $stmt->execute();
            echo "Nuevo registro insertado correctamente para el CUIT: $cuit <br>";
        } catch (PDOException $e) {
            echo "Error al insertar datos: " . $e->getMessage() . "<br>";
        }
    }
    echo "Datos del archivo 1 procesados exitosamente: $archivo <br>";
}



// Llamar la función procesarArchivo2 con la ruta del archivo
$archivo = 'RG830.txt';


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
            if (strpos($archivo, 'RG830.txt') !== false) {
                procesarArchivo2($rutaArchivo);
            }
        }
    }
}

procesarArchivosDeCarpeta('Downloads2');
