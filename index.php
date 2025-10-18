<?php
require_once 'config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$pusher_key = PUSHER_KEY ?? '';
$pusher_cluster = PUSHER_CLUSTER ?? 'mt1';
$pusher_configurado = !empty(PUSHER_APP_ID) && !empty(PUSHER_KEY) && !empty(PUSHER_SECRET);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat en Tiempo Real</title>
    <meta name="pusher-key" content="<?php echo htmlspecialchars($pusher_key); ?>">
    <meta name="pusher-cluster" content="<?php echo htmlspecialchars($pusher_cluster); ?>">
    <link rel="stylesheet" href="static/css/style.css">
    <link rel="icon" href="static/favicon.ico" type="image/x-icon">
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
</head>
<body>
    <div class="contenedor-chat">
        <div class="encabezado-chat">
            <h1>Chat en Tiempo Real</h1>
        </div>
        
        <div class="area-mensajes" id="mensajes">
        </div>
        
        <div class="area-entrada">
            <input type="text" id="usuario" placeholder="Tu nombre" maxlength="50">
            <input type="text" id="texto" placeholder="Escribe un mensaje" maxlength="500">
            <button id="enviar">Enviar</button>
        </div>
    </div>

    <script src="static/js/app.js"></script>
</body>
</html>