<?php
require_once 'config.php';

header('Content-Type: application/json');

// Verificar variables de entorno
$debug_info = [
    'variables_entorno' => [
        'DB_HOST' => DB_HOST,
        'DB_NAME' => DB_NAME,
        'DB_USER' => DB_USER,
        'DB_PASS' => DB_PASS ? 'SET' : 'NOT_SET',
        'DB_PORT' => DB_PORT
    ],
    'conexion_bd' => null,
    'tabla_existe' => false,
    'error' => null
];

// Probar conexión
try {
    $pdo = getDBConnection();
    if ($pdo) {
        $debug_info['conexion_bd'] = 'SUCCESS';
        
        // Verificar si la tabla existe
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'mensajes'");
        $stmt->execute();
        $tabla = $stmt->fetch();
        
        if ($tabla) {
            $debug_info['tabla_existe'] = true;
            
            // Contar registros
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM mensajes");
            $stmt->execute();
            $count = $stmt->fetch();
            $debug_info['registros_tabla'] = $count['total'];
        }
    } else {
        $debug_info['conexion_bd'] = 'FAILED';
    }
} catch (Exception $e) {
    $debug_info['conexion_bd'] = 'ERROR';
    $debug_info['error'] = $e->getMessage();
}

echo json_encode($debug_info, JSON_PRETTY_PRINT);
?>