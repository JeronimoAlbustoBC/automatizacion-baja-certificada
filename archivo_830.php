<?php

$url_File = "https://servicioscf.afip.gob.ar/Publico/rg830/DownloadFile.aspx";


$dir_download = 'Downloads2/';

// si no existe la crea
if (!file_exists($dir_download)) {
    mkdir($dir_download, 0777, true);
}

// Descarga el archivo
$file = file_get_contents($url_File);

if ($file === false) {
    echo "Error al descargar el archivo.";
    exit;
}

// Guardar el archivo
$temp_zip_path = $dir_download . 'descarga17.zip';
$f = fopen($temp_zip_path, 'w+');
fwrite($f, $file);
fclose($f);

// Verifica si el archivo comprimido se guardo
if (file_exists($temp_zip_path)) {
    echo "Archivo comprimido descargado correctamente";

    // Descomprime el archivo ZIP
    $zip = new ZipArchive;
    if ($zip->open($temp_zip_path) === TRUE) {
        // Extrae el archivos y lo guarda en la carpeta downloads
        $zip->extractTo($dir_download);
        $zip->close();
        echo "Archivo descomprimido exitosamente en: $dir_download";

        // Elimina el archivo comprimido después de descomprimirlo
        unlink($temp_zip_path);
    } else {
        echo "Error al descomprimir el archivo.";
    }
} else {
    echo "Error al guardar el archivo comprimido.";
}
?>
