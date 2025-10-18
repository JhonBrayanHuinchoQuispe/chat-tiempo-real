<?php
// Configuración de Pusher
$pusher_app_id = "TU_APP_ID";
$pusher_key = "TU_PUSHER_KEY";
$pusher_secret = "TU_PUSHER_SECRET";
$pusher_cluster = "mt1";
$port = 8000;

// Configuración de PostgreSQL
$db_host = "localhost";
$db_name = "chat_tiempo_real";
$db_user = "postgres";
$db_pass = "";
$db_port = 5432;

// Cargar variables del archivo .env
function loadEnv() {
    global $pusher_app_id, $pusher_key, $pusher_secret, $pusher_cluster, $port;
    global $db_host, $db_name, $db_user, $db_pass, $db_port;
    
    if (file_exists('.env')) {
        $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                switch ($key) {
                    case 'PUSHER_APP_ID':
                        $pusher_app_id = $value;
                        break;
                    case 'PUSHER_KEY':
                        $pusher_key = $value;
                        break;
                    case 'PUSHER_SECRET':
                        $pusher_secret = $value;
                        break;
                    case 'PUSHER_CLUSTER':
                        $pusher_cluster = $value;
                        break;
                    case 'PORT':
                        $port = (int)$value;
                        break;
                    case 'DB_HOST':
                        $db_host = $value;
                        break;
                    case 'DB_NAME':
                        $db_name = $value;
                        break;
                    case 'DB_USER':
                        $db_user = $value;
                        break;
                    case 'DB_PASS':
                        $db_pass = $value;
                        break;
                    case 'DB_PORT':
                        $db_port = (int)$value;
                        break;
                }
            }
        }
    }
}

loadEnv();

// Definir constantes de Pusher
define('PUSHER_APP_ID', $pusher_app_id);
define('PUSHER_KEY', $pusher_key);
define('PUSHER_SECRET', $pusher_secret);
define('PUSHER_CLUSTER', $pusher_cluster);
define('PORT', $port);

// Definir constantes de PostgreSQL
define('DB_HOST', $db_host);
define('DB_NAME', $db_name);
define('DB_USER', $db_user);
define('DB_PASS', $db_pass);
define('DB_PORT', $db_port);

// Función para conectar a la base de datos
function getDBConnection() {
    try {
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Error de conexión a la base de datos: " . $e->getMessage());
        return null;
    }
}
?>