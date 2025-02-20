<?php
$servername = "localhost";  // Tu servidor MySQL
$username = "root";         // Tu usuario de MySQL
$password = "";             // Tu contraseña de MySQL
$dbname = "certificados_aut";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Función para insertar los datos en la base de datos
function insertarCertificado($conn, $data, $nombre_archivo) {
    $stmt = $conn->prepare("INSERT INTO certificados (cuit, nombre, nombre_archivo, fecha_procesamiento, fecha_desde, fecha_hasta, porcentaje, es_cliente) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssdi", $data['cuit'], $data['nombre'], $nombre_archivo, $data['fecha_procesamiento'], $data['fecha_desde'], $data['fecha_hasta'], $data['porcentaje'], $data['es_cliente']);
    $stmt->execute();
    $stmt->close();
}

// Función para procesar el archivo de acuerdo a la estructura
function procesarArchivo($conn, $archivo) {
    $handle = fopen($archivo, "r");
    while (($line = fgets($handle)) !== false) {
        // Extraer los datos de la línea con substr()
        $data = [];
        $data['cuit'] = trim(substr($line, 0, 11)); // CUIT (primeros 11 caracteres)
        $data['nombre'] = trim(substr($line, 11, 50)); // Nombre (siguientes 50 caracteres)
        $data['fecha_desde'] = date('Y-m-d', strtotime(trim(substr($line, 61, 10)))); // Fecha Desde (formato dd/mm/yyyy)
        $data['fecha_hasta'] = date('Y-m-d', strtotime(trim(substr($line, 71, 10)))); // Fecha Hasta (formato dd/mm/yyyy)
        $data['porcentaje'] = (float)trim(substr($line, 81, 5)); // Porcentaje (siguiente campo)
        $data['es_cliente'] = 1; // Suponiendo que todos los registros son clientes, si hay un campo adicional que indique lo contrario, se puede ajustar
        
        // Fecha de procesamiento
        $data['fecha_procesamiento'] = date('Y-m-d H:i:s');
        
        // Insertar en la base de datos
        insertarCertificado($conn, $data, $archivo);
    }
    fclose($handle);
}

// Procesar el archivo
procesarArchivo($conn, "archivo.txt"); // Cambiar "archivo.txt" por el nombre del archivo

// Cerrar la conexión
$conn->close();
?>
