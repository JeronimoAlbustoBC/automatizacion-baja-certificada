<?php

// funcion para obtener pymes

function getPyme($url_parameter) {
    // URL base de pymes.produccion
    $base_url = 'https://datospymes.produccion.gob.ar/api/resultadocondicion/';
    
    // creamos url full de concatenar url base y url pymes
    $url_full = $base_url . $url_parameter;
    
    // iniciamos curl y le pasamos la url full
    $ch = curl_init($url_full);
    
    // Configuramos cURL para que devuelva la respuesta
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Ejecutamos la solicitud de curl y obtenemos la response
    $response = curl_exec($ch);
    
    // Verificamos si hay un error en la solicitud
    if(curl_errno($ch)) {
        echo 'Error en cURL: ' . curl_error($ch);
        curl_close($ch);
        return null;
    }
    
    // Cerramos la sesión iniciada de cURL
    curl_close($ch);
    
    // Decodificamos la respuesta JSON en un array
    $data = json_decode($response, true);
    
    // Verificamos si la respuesta fue un json válido
    if ($data === null) {
        echo "Error: No se pudo decodificar la respuesta JSON.";
        return null;
    }
    
    // Devolvemos la respuesta como un array asociativo
    return $data;
}

// parámetro que se agrega a la URL base
$parameter = '30714110876';
// obtenemos el resultado de la funcion getPyme  
$result = getPyme($parameter);

// Mostramos el resultado de la funcion
if ($result !== null) {
    echo '<pre>';
    print_r($result);
    echo '</pre>';
}

?>
