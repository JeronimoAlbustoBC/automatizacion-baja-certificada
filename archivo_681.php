<?php

$url_File = 'https://www.afip.gob.ar/genericos/exentas-rg2681/rg_Listado_Completo.asp';  

$dir_download = 'Downloads3/';  

// Verifica si el directorio de destino existe; si no, lo crea
if (!file_exists($dir_download)) {
    mkdir($dir_download, 0777, true);
}

// Ruta para guardar el archivo descargado
$temp_file_path = $dir_download . 'archivo.txt';  // Nombre del archivo de destino

// Iniciar una sesión de cURL
$ch = curl_init($url_File);

// Abrir el archivo de destino en modo escritura
$fp = fopen($temp_file_path, 'w+');

// Configuración de cURL para descargar el archivo
curl_setopt($ch, CURLOPT_FILE, $fp);  // Guardar la descarga en el archivo
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Seguir redirecciones si es necesario

// Ejecutar la descarga
if (curl_exec($ch) === false) {
    echo "Error al descargar el archivo: " . curl_error($ch);
} else {
    echo "Archivo descargado correctamente";
}

// Cerrar la sesión de cURL y el archivo
curl_close($ch);
fclose($fp);

?>
