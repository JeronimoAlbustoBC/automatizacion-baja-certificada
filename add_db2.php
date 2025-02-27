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

    //  SQL para insertar los datos en la db
    $stmt = $pdo->prepare("
        INSERT INTO certificados (cuit, nombre, nombre_archivo, fecha_procesamiento, fecha_desde, fecha_hasta, porcentaje, es_cliente)
        VALUES (:cuit, :nombre, :nombre_archivo, :fecha_procesamiento, :fecha_desde, :fecha_hasta, :porcentaje, :es_cliente)
    ");

    // Abre el archivo para leerlo línea por línea
    $file = fopen($archivo, 'r');
    if ($file) {
        fgetcsv($file, 1000, ";");
        // Leer el archivo línea por línea
        while (($linea = fgets($file)) !== false) {
            $linea = trim($linea);

            if (empty($linea)) {
                continue;
            }

            // Verificamos si la línea tiene el formato esperado, por ejemplo, separada por punto y coma
            $campos = explode(";", $linea); 

            // Si la cantidad de campos no es la esperada, marcamos como inválida la línea
            if (count($campos) != 8) {
                echo "Línea inválida (campo incorrecto): $linea<br>";
                continue;
            }

            // Extraemos los datos
            $cuit = $campos[1];
            $nombre = $campos[2];
            $fecha_desde = $campos[6]; 
            $fecha_hasta = $campos[7]; 

            $fecha_procesamiento = date('Y-m-d H:i'); 
            $porcentaje = 100; 
            $es_cliente = 1; 

            // Limpiamos fechas de caracteres no numéricos
            $fecha_desde = preg_replace('/[^0-9\/]/', '', $fecha_desde);
            $fecha_hasta = preg_replace('/[^0-9\/]/', '', $fecha_hasta);

            // Validamos que las fechas sean válidas
            if (empty($fecha_desde) || empty($fecha_hasta)) {
                echo "Fecha vacía en el archivo para el CUIT $cuit.<br>";
                continue; 
            }

            // Convertimos las fechas
            $fecha_desde_obj = DateTime::createFromFormat('d/m/Y', $fecha_desde);
            $fecha_hasta_obj = DateTime::createFromFormat('d/m/Y', $fecha_hasta);

            // Verificamos si las fechas son válidas
            if (!$fecha_desde_obj || !$fecha_hasta_obj) {
                echo "Error al convertir las fechas para el CUIT $cuit: $fecha_desde - $fecha_hasta.<br>";
                continue;
            }

            $fecha_desde = $fecha_desde_obj->format('Y-m-d');
            $fecha_hasta = $fecha_hasta_obj->format('Y-m-d');

            $nombre_archivo = 'rg830.txt';

            // Insertamos los datos en la db
            try {
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
        fclose($file); 
    } else {
        echo "Error al abrir el archivo $archivo.<br>";
    }
    echo "Datos del archivo procesados exitosamente: $archivo <br>";
}

$archivo = 'Downloads2/RG830.txt';

procesarArchivo2($archivo);
?>
