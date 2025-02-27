<?php

$host = '192.9.200.25';
$dbname = 'certificados_aut';
$username = 'admindba';
$password = 'axw043';

try {
    // Crear la conexión a la base de datos
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES 'utf8'");  // Asegurar que la conexión sea UTF-8
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Función para procesar las fechas y asegurarse de que sean válidas
function procesarFecha($fecha) {
    $fecha = preg_replace('/[^0-9\/]/', '', $fecha);  // Limpiar caracteres no numéricos
    if (empty($fecha) || $fecha == "-") {
        return null;  // Si la fecha es vacía o contiene un guion, la consideramos inválida
    }
    $fecha_obj = DateTime::createFromFormat('d/m/Y', $fecha);
    return $fecha_obj ? $fecha_obj->format('Y-m-d') : null;  // Convertir a formato Y-m-d
}

function procesarArchivo3($archivo)
{
    global $pdo;

    // SQL para insertar los datos en la db
    $stmt = $pdo->prepare("
        INSERT INTO certificados (cuit, nombre, nombre_archivo, fecha_procesamiento, fecha_desde, fecha_hasta, porcentaje, es_cliente)
        VALUES (:cuit, :nombre, :nombre_archivo, :fecha_procesamiento, :fecha_desde, :fecha_hasta, :porcentaje, :es_cliente)
    ");

    // Abre el archivo para leerlo línea por línea
    if (($file = fopen($archivo, 'r')) !== false) {
        fgetcsv($file, 1000, ";");  // Omite la primera fila (encabezados)
        
        while (($campos = fgetcsv($file, 1000, ";")) !== false) {
            if (count($campos) != 12) {  // Verificar la cantidad de columnas
                echo "Línea inválida (campo incorrecto): " . implode(";", $campos) . "<br>";
                continue;
            }

            // Extraemos los datos
            $cuit = $campos[0];
            $nombre = $campos[1];
            $fecha_desde = procesarFecha($campos[9]);  // Procesamos las fechas
            $fecha_hasta = procesarFecha($campos[10]);
            $fecha_procesamiento = date('Y-m-d H:i');
            $porcentaje = 100;  // Asumido como fijo
            $es_cliente = 1;  // Asumido como cliente

            // Verificar si las fechas son válidas
            if (!$fecha_desde || !$fecha_hasta) {
                echo "Fecha inválida para el CUIT $cuit.<br>";
                continue;
            }

            $nombre_archivo = basename($archivo); // Nombre del archivo actual

            try {
                // Insertar en la base de datos
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
                echo "Error al insertar datos para el CUIT $cuit: " . $e->getMessage() . "<br>";
            }
        }
        fclose($file);
        echo "Datos procesados exitosamente desde el archivo: $archivo <br>";
    } else {
        echo "Error al abrir el archivo $archivo.<br>";
    }
}

// Llamamos a la función
$archivo = 'Downloads3/archivo.txt';
procesarArchivo3($archivo);

?>
