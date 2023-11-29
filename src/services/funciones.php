<?php

// Función para retornar las fotos de perfiles codificadas
function codificarIMG(string $nombreIMG): string
{
    $carpetaImagenes = dirname(dirname(__DIR__)) . '/imagenes/';
    $rutaCompletaImagen = $carpetaImagenes . $nombreIMG;
    if (file_exists($rutaCompletaImagen)) {
        $tipoContenido = mime_content_type($rutaCompletaImagen);
        $imagenBase64 = base64_encode(file_get_contents($rutaCompletaImagen));
        $nuevaImg = 'data:' . $tipoContenido . ';base64,' . $imagenBase64;
        return $nuevaImg;
    }
}
