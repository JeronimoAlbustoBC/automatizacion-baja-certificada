<?php

// URL de la página donde buscar el archivo
$url = 'https://serviciosweb.afip.gob.ar/genericos/rg_17/Consulta.aspx';

// Obtener el contenido HTML de la página
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$htmlContent = curl_exec($ch);
curl_close($ch);

// Buscar enlace de archivo comprimido
preg_match('/href="([^"]+\.(zip|tar\.gz|rar))"/i', $htmlContent, $matches);

// Si se encuentra un archivo para descargar
if (isset($matches[1])) {
    $fileUrl = $matches[1];
    $fileName = basename($fileUrl);
    $downloadPath = 'descargas/' . $fileName;

    // Crear carpeta si no existe
    if (!is_dir('descargas')) mkdir('descargas', 0777, true);

    // Descargar archivo
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fileUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $fileData = curl_exec($ch);
    curl_close($ch);

    // Guardar el archivo
    file_put_contents($downloadPath, $fileData);

    // Detectar y descomprimir según extensión
    $ext = pathinfo($downloadPath, PATHINFO_EXTENSION);
    $extractPath = 'descargas/extraido/';

    if (!is_dir($extractPath)) mkdir($extractPath, 0777, true);

    switch (strtolower($ext)) {
        case 'zip':
            $zip = new ZipArchive;
            if ($zip->open($downloadPath) === TRUE) {
                $zip->extractTo($extractPath);
                $zip->close();
            }
            break;
        case 'gz':
        case 'tar':
            $phar = new PharData($downloadPath);
            $phar->extractTo($extractPath);
            break;
        case 'rar':
            // Usar el comando `unrar` para extraer archivos RAR
            $command = "unrar x '$downloadPath' '$extractPath'";
            exec($command, $output, $status);

            if ($status === 0) {
                echo "Archivo RAR extraído correctamente.";
            } else {
                echo "Hubo un error al extraer el archivo RAR.";
                print_r($output);
            }
            break;
    }

    echo "Archivo descargado y descomprimido en '$extractPath'.";
} else {
    echo "No se encontró un archivo para descargar.";
}
