<?php
// protegido.php

// --- INICIO DEL RATE LIMITER (El Escudo) ---
function checkRateLimit($ip) {
    $archivo_limite = 'limite_' . md5($ip) . '.json';
    $tiempo_ventana = 10; // segundos
    $max_peticiones = 5;  // máximo permitido

    $data = ['intentos' => 0, 'inicio' => time()];
    
    if (file_exists($archivo_limite)) {
        $data = json_decode(file_get_contents($archivo_limite), true);
    }

    // Reiniciar contador si pasó el tiempo
    if (time() - $data['inicio'] > $tiempo_ventana) {
        $data['intentos'] = 0;
        $data['inicio'] = time();
    }

    // BLOQUEO: Si supera el límite
    if ($data['intentos'] >= $max_peticiones) {
        return false; // Denegar acceso
    }

    // Incrementar y guardar
    $data['intentos']++;
    file_put_contents($archivo_limite, json_encode($data));
    return true; // Permitir acceso
}

// Aplicar el escudo antes de cargar nada
if (!checkRateLimit($_SERVER['REMOTE_ADDR'])) {
    http_response_code(429);
    die("<h1>⛔ ERROR 429: Demasiadas solicitudes.</h1><p>Has sido bloqueado temporalmente por el sistema de Rate Limiting.</p>");
}
// --- FIN DEL RATE LIMITER ---

// A partir de aquí, es el mismo código que el vulnerable
sleep(1); // Carga simulada
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentario'])) {
    file_put_contents('db_mensajes_seguros.txt', $_POST['comentario'] . "\n", FILE_APPEND);
}
$comentarios = file_exists('db_mensajes_seguros.txt') ? file('db_mensajes_seguros.txt') : [];
?>

<!DOCTYPE html>
<html>
<head><title>Sitio Protegido</title></head>
<body style="background-color: #e0f7fa;">
    <h1>Muro Blindado (Con Rate Limiting)</h1>
    <p>Este sitio detecta si envías tráfico demasiado rápido.</p>
    
    <form method="POST">
        <input type="text" name="comentario" placeholder="Escribe algo..." required>
        <button type="submit">Publicar</button>
    </form>

    <h3>Comentarios:</h3>
    <ul>
        <?php foreach($comentarios as $c): ?>
            <li><?= htmlspecialchars($c) ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>