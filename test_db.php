<?php
header('Content-Type: application/json');

// Test específico con la configuración del phpMyAdmin
$configs = [
    // Configuración EXACTA del phpMyAdmin
    [
        'host' => 'mysql-sistemasic.alwaysdata.net',
        'dbname' => 'sistemasic_chat',
        'username' => 'jhonbrayanhuinchoquispe',
        'password' => 'brayan933783039'
    ],
    // Probando con usuario más corto
    [
        'host' => 'mysql-sistemasic.alwaysdata.net',
        'dbname' => 'sistemasic_chat',
        'username' => 'sistemasic',
        'password' => 'brayan933783039'
    ],
    // Probando sin mysql- prefix
    [
        'host' => 'sistemasic.alwaysdata.net',
        'dbname' => 'sistemasic_chat',
        'username' => 'jhonbrayanhuinchoquispe',
        'password' => 'brayan933783039'
    ],
    // Probando con puerto explícito
    [
        'host' => 'mysql-sistemasic.alwaysdata.net:3306',
        'dbname' => 'sistemasic_chat',
        'username' => 'jhonbrayanhuinchoquispe',
        'password' => 'brayan933783039'
    ]
];

$results = [];

foreach ($configs as $index => $config) {
    try {
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Obtener lista de tablas
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $results[] = [
            'config' => $index + 1,
            'host' => $config['host'],
            'dbname' => $config['dbname'],
            'username' => $config['username'],
            'status' => 'SUCCESS ✅',
            'tables' => $tables,
            'table_count' => count($tables)
        ];
    } catch (PDOException $e) {
        $results[] = [
            'config' => $index + 1,
            'host' => $config['host'],
            'dbname' => $config['dbname'],
            'username' => $config['username'],
            'status' => 'ERROR ❌',
            'error' => $e->getMessage()
        ];
    }
}

echo json_encode($results, JSON_PRETTY_PRINT);
?>