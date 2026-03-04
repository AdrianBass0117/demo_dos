<?php
// vulnerable.php

// 1. Simulación de carga pesada (Base de datos lenta)
// Esto hace que el servidor tarde 1 segundo en responder.
// Si llegan 100 peticiones, el servidor colapsará intentando procesarlas.
sleep(1); 

// 2. Lógica simple de Login y Guardado (CRUD sin BD)
session_start();
$mensaje = "";

// Guardar mensaje (Create)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentario'])) {
    $nuevo_comentario = $_POST['comentario'] . "\n";
    file_put_contents('db_mensajes.txt', $nuevo_comentario, FILE_APPEND);
    $mensaje = "Comentario guardado.";
}

// Leer mensajes (Read)
$comentarios = file_exists('db_mensajes.txt') ? file('db_mensajes.txt') : [];
?>

<!DOCTYPE html>
<html>
<head><title>Sitio Vulnerable</title></head>
<body>
    <h1>Muro de Comentarios (Vulnerable)</h1>
    <p>Este sitio no tiene protección. Cualquier script puede saturarlo.</p>
    
    <form method="POST">
        <input type="text" name="comentario" placeholder="Escribe algo..." required>
        <button type="submit">Publicar</button>
    </form>
    <p style="color:green"><?= $mensaje ?></p>

    <h3>Comentarios Recientes:</h3>
    <ul>
        <?php foreach($comentarios as $c): ?>
            <li><?= htmlspecialchars($c) ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>