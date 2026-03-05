<?php
// vulnerable.php
sleep(5); 
session_start();
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentario'])) {
    $nuevo_comentario = $_POST['comentario'] . "\n";
    file_put_contents('db_mensajes.txt', $nuevo_comentario, FILE_APPEND);
    $mensaje = "Comentario guardado.";
}

// CORRECCIÓN APLICADA AQUÍ TAMBIÉN
$comentarios = file_exists('db_mensajes.txt') ? file('db_mensajes.txt') : [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sitio Vulnerable</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body class="vulnerable">
    
    <div class="container">
        <h1>🔓 Muro Vulnerable</h1>
        <p>Sin protección. Lento y fácil de atacar.</p>
        
        <form method="POST">
            <input type="text" name="comentario" placeholder="Escribe algo..." required>
            <br>
            <button type="submit">Publicar</button>
        </form>
        
        <p class="success"><?= $mensaje ?></p>

        <h3>💬 Comentarios Recientes</h3>
        <ul>
            <?php foreach($comentarios as $c): ?>
                <li><?= htmlspecialchars($c) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

</body>
</html>