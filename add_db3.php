<?php

$host = '192.9.200.25';
$dbname = 'certificados_aut';
$username = 'admindba';
$password = 'axw043';

// Crear conexión a MySQL
$pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$archivo = 'Downloads3/archivo.txt';
$nombre_archivo = basename($archivo);  
$fecha_procesamiento = date('Y-m-d H:i:s');  

// Abrir el archivo .txt
$lineas = file($archivo, FILE_IGNORE_NEW_LINES);

// Preparar la consulta de inserción
$sql = "INSERT INTO certificados (cuit, nombre, nombre_archivo, fecha_procesamiento, fecha_desde, fecha_hasta, porcentaje, es_cliente) 
            VALUES (:cuit, :nombre, :nombre_archivo, :fecha_procesamiento, :fecha_desde, :fecha_hasta, :porcentaje, :es_cliente)";

$stmt = $pdo->prepare($sql);

// Recorrer las líneas del archivo y procesarlas
foreach ($lineas as $key => $linea) {
    // Ignorar la primera línea (cabecera)
    if ($key == 0) {
        continue;
    }

    // Separar los datos por tabulaciones
    $columnas = explode("\t", $linea);

    // Verificar que la línea tiene al menos las columnas necesarias
    if (count($columnas) >= 10) {
        
        $CUIT = $columnas[0];
        $Nombre = $columnas[3];

        // Usar DateTime::createFromFormat() para convertir las fechas
        $Fecha_Desde = DateTime::createFromFormat('d/m/Y', $columnas[9]);  // Ajusta el formato según el formato de fecha en el archivo
        $Fecha_Hasta = DateTime::createFromFormat('d/m/Y', $columnas[10]);  // Ajusta el formato según el formato de fecha en el archivo

        // Verificar si la fecha fue creada correctamente
        if ($Fecha_Desde && $Fecha_Hasta) {
            // Formatear la fecha a 'Y-m-d'
            $Fecha_Desde = $Fecha_Desde->format('Y-m-d');
            $Fecha_Hasta = $Fecha_Hasta->format('Y-m-d');
        } else {
            // Si alguna fecha no es válida, se puede saltar esa fila o manejar el error
            echo "Fecha inválida en la línea: " . $linea . "\n";
            continue;
        }

        $Porcentaje = 0;
        $Cliente = 'si'; 

        // Bind de valores a los parámetros
        $stmt->bindParam(':cuit', $CUIT);
        $stmt->bindParam(':nombre', $Nombre);
        $stmt->bindParam(':nombre_archivo', $nombre_archivo);
        $stmt->bindParam(':fecha_procesamiento', $fecha_procesamiento);
        $stmt->bindParam(':fecha_desde', $Fecha_Desde);
        $stmt->bindParam(':fecha_hasta', $Fecha_Hasta);
        $stmt->bindParam(':porcentaje', $Porcentaje);
        $stmt->bindParam(':es_cliente', $Cliente);

        // Ejecutar la consulta
        $stmt->execute();
    }
}

echo "Datos insertados correctamente en la base de datos.";

?>
