<?php
// protegido.php

// --- RATE LIMITER ---
function checkRateLimit($ip) {
    $archivo_limite = 'limite_' . md5($ip) . '.json';
    $tiempo_ventana = 10; 
    $max_peticiones = 5; 

    $data = ['intentos' => 0, 'inicio' => time()];
    
    if (file_exists($archivo_limite)) {
        $data = json_decode(file_get_contents($archivo_limite), true);
    }

    if (time() - $data['inicio'] > $tiempo_ventana) {
        $data['intentos'] = 0;
        $data['inicio'] = time();
    }

    if ($data['intentos'] >= $max_peticiones) {
        return false; 
    }

    $data['intentos']++;
    file_put_contents($archivo_limite, json_encode($data));
    return true; 
}

// BLOQUEO CON ESTILO
if (!checkRateLimit($_SERVER['REMOTE_ADDR'])) {
    http_response_code(429);
    // Imprimimos HTML completo para que el error se vea bien con el CSS
    echo '<link rel="stylesheet" href="estilos.css">';
    echo '<body class="vulnerable" style="flex-direction:column">';
    echo '<div class="container error-page">';
    echo '<h1>⛔ ALTO AHÍ</h1>';
    echo '<h3>Error 429: Demasiadas Solicitudes</h3>';
    echo '<p>El sistema de Rate Limiting ha detectado tráfico inusual desde tu IP.</p>';
    echo '<p>Espera 10 segundos e intenta de nuevo.</p>';
    echo '</div>';
    echo '</body>';
    exit(); // Detiene la ejecución
}
// --- FIN RATE LIMITER ---

sleep(1); 
session_start();
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentario'])) {
    file_put_contents('db_mensajes_seguros.txt', $_POST['comentario'] . "\n", FILE_APPEND);
    $mensaje = "Mensaje seguro guardado.";
}
$comentarios = file_exists('db_mensajes_seguros.txt') ? file('db_mensajes_seguros.txt') : [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sitio Protegido</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body class="protegido">

    <div class="container">
        <h1>🛡️ Muro Protegido</h1>
        <p>Con Rate Limiting activo. Tráfico seguro.</p>
        
        <form method="POST">
            <input type="text" name="comentario" placeholder="Escribe algo..." required>
            <br>
            <button type="submit">Publicar Seguro</button>
        </form>
        
        <p class="success"><?= $mensaje ?></p>

        <h3>💬 Comentarios</h3>
        <ul>
            <?php foreach($comentarios as $c): ?>
                <li><?= htmlspecialchars($c) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

</body>
</html>