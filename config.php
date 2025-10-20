<?php
// Configuración de Pusher
$pusher_app_id = "2062974";
$pusher_key = "ed728998da5272b7373e";
$pusher_secret = "49cbb5047f31a0b4f03c";
$pusher_cluster = "sa1";
$port = 8000;

// Configuración de MySQL
$db_host = "localhost";
$db_name = "sistemasic_chat";
$db_user = "root";
$db_pass = "";
$db_port = 3306;

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

// Definir constantes de MySQL
define('DB_HOST', $db_host);
define('DB_NAME', $db_name);
define('DB_USER', $db_user);
define('DB_PASS', $db_pass);
define('DB_PORT', $db_port);

// Función para conectar a la base de datos
function getDBConnection() {
    try {
        // Configuración específica para AlwaysData
        $host = DB_HOST;
        $port = 3306; // Puerto específico para AlwaysData
        $dbname = DB_NAME;
        
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 30,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Error de conexión a la base de datos: " . $e->getMessage());
        error_log("DSN usado: mysql:host=" . DB_HOST . ";port=3306;dbname=" . DB_NAME);
        error_log("Usuario: " . DB_USER);
        return null;
    }
}
?>