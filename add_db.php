<?php

$host = '192.9.200.25'; 
$dbname = 'certificados_aut';
$username = 'admindba'; 
$password = 'axw043'; 

// Crear la conexión
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Función para procesar el primer archivo (formato 1)
function procesarArchivo1($archivo) {
    global $pdo;

    $stmt = $pdo->prepare("
        INSERT INTO certificados (cuit, nombre, nombre_archivo, fecha_procesamiento, fecha_desde, fecha_hasta, porcentaje, es_cliente)
        VALUES (:cuit, :nombre, :nombre_archivo, :fecha_procesamiento, :fecha_desde, :fecha_hasta, :porcentaje, :es_cliente)
    ");

    $lineas = file($archivo, FILE_IGNORE_NEW_LINES); // Leer todas las líneas del archivo
    foreach ($lineas as $linea) {
        // Extraemos los datos de acuerdo con el formato del archivo
        $cuit = substr($linea, 0, 11); // CUIT: 11 primeros caracteres
        $nombre = trim(substr($linea, 12, 50)); // Nombre de la empresa
        $fecha_desde = trim(substr($linea, 60, 10)); // Fecha desde
        $fecha_hasta = trim(substr($linea, 71, 10)); // Fecha hasta
        $porcentaje = 100; // Asumido siempre 100
        $es_cliente = 1; // Asumido siempre cliente

        // Validar las fechas antes de insertarlas
        if (empty($fecha_desde) || !preg_match('/\d{4}-\d{2}-\d{2}/', $fecha_desde)) {
            $fecha_desde = '0000-00-00'; // O puedes usar NULL dependiendo de lo que prefieras en la base de datos
        }
        if (empty($fecha_hasta) || !preg_match('/\d{4}-\d{2}-\d{2}/', $fecha_hasta)) {
            $fecha_hasta = '0000-00-00'; // O puedes usar NULL dependiendo de lo que prefieras en la base de datos
        }

        // Insertamos los datos en la base de datos
        $stmt->bindParam(':cuit', $cuit);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':nombre_archivo', $archivo);
        $stmt->bindParam(':fecha_procesamiento', date('Y-m-d'));
        $stmt->bindParam(':fecha_desde', $fecha_desde);
        $stmt->bindParam(':fecha_hasta', $fecha_hasta);
        $stmt->bindParam(':porcentaje', $porcentaje);
        $stmt->bindParam(':es_cliente', $es_cliente, PDO::PARAM_BOOL);

        $stmt->execute();
    }
    echo "Datos del archivo 1 procesados exitosamente: $archivo<br>";
}

// Función para procesar el segundo archivo (formato 2)
function procesarArchivo2($archivo) {
    global $pdo;

    $stmt = $pdo->prepare("
        INSERT INTO certificados (cuit, nombre, nombre_archivo, fecha_procesamiento, fecha_desde, fecha_hasta, porcentaje, es_cliente)
        VALUES (:cuit, :nombre, :nombre_archivo, :fecha_procesamiento, :fecha_desde, :fecha_hasta, :porcentaje, :es_cliente)
    ");

    $lineas = file($archivo, FILE_IGNORE_NEW_LINES); // Leer todas las líneas del archivo
    foreach ($lineas as $linea) {
        // Separamos los valores por el delimitador ";"
        $datos = explode(";", $linea);

        $cuit = $datos[1]; // CUIT
        $nombre = $datos[2]; // Razón social
        $fecha_desde = $datos[6]; // Fecha desde
        $fecha_hasta = $datos[7]; // Fecha hasta
        $porcentaje = $datos[4]; // Porcentaje
        $es_cliente = 1; // Asumido siempre cliente

        // Validar las fechas antes de insertarlas
        if (empty($fecha_desde) || !preg_match('/\d{4}-\d{2}-\d{2}/', $fecha_desde)) {
            $fecha_desde = '0000-00-00'; // O puedes usar NULL dependiendo de lo que prefieras en la base de datos
        }
        if (empty($fecha_hasta) || !preg_match('/\d{4}-\d{2}-\d{2}/', $fecha_hasta)) {
            $fecha_hasta = '0000-00-00'; // O puedes usar NULL dependiendo de lo que prefieras en la base de datos
        }

        // Insertamos los datos en la base de datos
        $stmt->bindParam(':cuit', $cuit);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':nombre_archivo', $archivo);
        $stmt->bindParam(':fecha_procesamiento', date('Y-m-d'));
        $stmt->bindParam(':fecha_desde', $fecha_desde);
        $stmt->bindParam(':fecha_hasta', $fecha_hasta);
        $stmt->bindParam(':porcentaje', $porcentaje);
        $stmt->bindParam(':es_cliente', $es_cliente, PDO::PARAM_BOOL);

        $stmt->execute();
    }
    echo "Datos del archivo 2 procesados exitosamente: $archivo<br>";
}

// Función para procesar el tercer archivo (formato 3)
function procesarArchivo3($archivo) {
    global $pdo;

    $stmt = $pdo->prepare("
        INSERT INTO certificados (cuit, nombre, nombre_archivo, fecha_procesamiento, fecha_desde, fecha_hasta, porcentaje, es_cliente)
        VALUES (:cuit, :nombre, :nombre_archivo, :fecha_procesamiento, :fecha_desde, :fecha_hasta, :porcentaje, :es_cliente)
    ");

    $lineas = file($archivo, FILE_IGNORE_NEW_LINES); // Leer todas las líneas del archivo
    foreach ($lineas as $linea) {
        // Separamos los valores por tabuladores o espacios en el caso de un formato más complejo
        $datos = preg_split('/\t+/', $linea); // Usamos preg_split para manejar tabulaciones

        $cuit = $datos[0]; // CUIT
        $nombre = trim($datos[1]); // Tipo de sujeto o nombre
        $fecha_desde = $datos[8]; // Fecha desde
        $fecha_hasta = $datos[9]; // Fecha hasta
        $porcentaje = 100; // Asumido siempre 100
        $es_cliente = 1; // Asumido siempre cliente

         // Validar las fechas antes de insertarlas
         if (empty($fecha_desde) || !preg_match('/\d{4}-\d{2}-\d{2}/', $fecha_desde)) {
            $fecha_desde = '0000-00-00'; // O puedes usar NULL dependiendo de lo que prefieras en la base de datos
        }
        if (empty($fecha_hasta) || !preg_match('/\d{4}-\d{2}-\d{2}/', $fecha_hasta)) {
            $fecha_hasta = '0000-00-00'; // O puedes usar NULL dependiendo de lo que prefieras en la base de datos
        }

        // Insertamos los datos en la base de datos
        $stmt->bindParam(':cuit', $cuit);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':nombre_archivo', $archivo);
        $stmt->bindParam(':fecha_procesamiento', date('Y-m-d'));
        $stmt->bindParam(':fecha_desde', $fecha_desde);
        $stmt->bindParam(':fecha_hasta', $fecha_hasta);
        $stmt->bindParam(':porcentaje', $porcentaje);
        $stmt->bindParam(':es_cliente', $es_cliente, PDO::PARAM_BOOL);

        $stmt->execute();
    }
    echo "Datos del archivo 3 procesados exitosamente: $archivo<br>";
}

// Función para procesar todos los archivos de una carpeta
function procesarArchivosDeCarpeta($carpeta) {
    // Obtener todos los archivos dentro de la carpeta
    $archivos = scandir($carpeta);

    foreach ($archivos as $archivo) {
        // Ignorar las entradas "." y ".."
        if ($archivo != "." && $archivo != "..") {
            // Crear la ruta completa del archivo
            $rutaArchivo = $carpeta . DIRECTORY_SEPARATOR . $archivo;

            // Verificar el formato del archivo y procesarlo
            if (preg_match('/rg17\.txt$/', $archivo)) {
                procesarArchivo1($rutaArchivo); // Procesar formato 1
            } elseif (preg_match('/RG830\.txt$/', $archivo)) {
                procesarArchivo2($rutaArchivo); // Procesar formato 2
            } elseif (preg_match('/archivo\.txt$/', $archivo)) {
                procesarArchivo3($rutaArchivo); // Procesar formato 3
            } else {
                echo "Archivo no reconocido: $archivo<br>";
            }
        }
    }
}

// Llamada a las funciones para procesar los archivos de las carpetas correspondientes
procesarArchivosDeCarpeta('Downloads1'); // Procesar archivos en la carpeta downloads1
procesarArchivosDeCarpeta('Downloads2'); // Procesar archivos en la carpeta downloads2
procesarArchivosDeCarpeta('Downloads3'); // Procesar archivos en la carpeta downloads3

?>
