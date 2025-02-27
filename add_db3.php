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
        // Asignar valores a las variables
        $CUIT = $columnas[0];
        $Nombre = $columnas[1];
        $Fecha_Desde = date('Y-m-d', strtotime($columnas[9]));  // Convertir a formato DATE
        $Fecha_Hasta = date('Y-m-d', strtotime($columnas[10]));  // Convertir a formato DATE
        
        // Definir un valor para porcentaje, si no tienes esta información, puedes poner un valor predeterminado
        $Porcentaje = 0;  // Cambiar si es necesario
        
        // Definir si es cliente, basándonos en el campo 'Cod_Estado' (suposición: "CU" significa cliente)
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
