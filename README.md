# Automatizacion con php



### Enlaces
link: https://serviciosweb.afip.gob.ar/genericos/rg_17/Consulta.aspx

link: https://servicioscf.afip.gob.ar/Publico/rg830/rg830.aspx



### Diagrama de Flujo



<img src="./flujo proceso.png" alt="diagrama de flujo" width="300" />


crear una funcion que capture una url y que se contatene con una url(https://datospymes.produccion.gob.ar/api/resultadocondicion) y traiga una respuesta en json de esa url que consulta




nombre base de datos: certificados_aut

crear una tabla que tenga el cuit; nombre; nombre de archivo; fecha-procesamiento; fecha desde;  fecha hasta; porcentaje; si es cliente o no;

y crear un script que le agrege los datos que vienen de los archivos en esta tabla creada


'certificados', 'CREATE TABLE `certificados` 
(\n  `cuit` varchar(12) NOT NULL,\n  `nombre` varchar(255) NOT NULL,\n  `nombre_archivo` varchar(255) NOT NULL,\n  `fecha_procesamiento` datetime NOT NULL,\n  `fecha_desde` date NOT NULL,\n  `fecha_hasta` date NOT NULL,\n  `porcentaje` decimal(5,2) NOT NULL,\n  `es_cliente` tinyint(1) NOT NULL,\n  PRIMARY KEY (`cuit`)\n) ENGINE=MyISAM DEFAULT CHARSET=latin1'


cuit, nombre de archivo, fecha desde, fecha hasta


<?php
// Configuración de la conexión a la base de datos
$servername = "localhost";
$username = "root"; // Reemplaza con tu usuario
$password = ""; // Reemplaza con tu contraseña
$dbname = "certificados_aut";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Función para procesar el archivo .txt
function procesarArchivo2($archivo)
{
    global $conn; // Usamos la conexión global $conn

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

            // Preparar la consulta SQL para insertar los datos
            $sql = "INSERT INTO certificados (cuit, nombre, nombre_archivo, fecha_procesamiento, fecha_desde, fecha_hasta, porcentaje, es_cliente) 
                    VALUES ('$cuit', '$nombre', '$nombre_archivo', '$fecha_procesamiento', '$fecha_desde', '$fecha_hasta', $porcentaje, $es_cliente)";

            // Ejecutar la consulta
            if ($conn->query($sql) === TRUE) {
                echo "Nuevo registro insertado correctamente para el CUIT: $cuit <br>";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }

        // Cerrar el archivo .txt
        fclose($handle);
    } else {
        echo "Error al abrir el archivo.";
    }
}

// Llamar la función procesarArchivo2 con la ruta del archivo
$archivo = 'ruta_del_archivo.txt'; // Cambia esto a la ruta de tu archivo .txt
procesarArchivo2($archivo);

// Cerrar la conexión
$conn->close();
?>
