<?php
header('Content-Type: application/json');

// Configuraciones a probar basadas en diferentes formatos de AlwaysData
$configs = [
    // Formato estándar sin mysql- prefix
    [
        'host' => 'jhonbrayanhuinchoquispe.alwaysdata.net',
        'dbname' => 'jhonbrayanhuinchoquispe_sistemasic_chat',
        'username' => 'jhonbrayanhuinchoquispe',
        'password' => 'brayan933783039'
    ],
    // Formato con mysql- prefix (recomendado por AlwaysData)
    [
        'host' => 'mysql-jhonbrayanhuinchoquispe.alwaysdata.net',
        'dbname' => 'jhonbrayanhuinchoquispe_sistemasic_chat',
        'username' => 'jhonbrayanhuinchoquispe',
        'password' => 'brayan933783039'
    ],
    // Probando con nombre de BD más corto
    [
        'host' => 'mysql-jhonbrayanhuinchoquispe.alwaysdata.net',
        'dbname' => 'sistemasic_chat',
        'username' => 'jhonbrayanhuinchoquispe',
        'password' => 'brayan933783039'
    ],
    // Probando con cuenta más corta (posible)
    [
        'host' => 'mysql-jhonbrayan.alwaysdata.net',
        'dbname' => 'jhonbrayan_sistemasic_chat',
        'username' => 'jhonbrayan',
        'password' => 'brayan933783039'
    ],
    // Probando con formato de cuenta ID (si el nombre es muy largo)
    [
        'host' => 'mysql-jhonbrayanhuinchoquispe.alwaysdata.net',
        'dbname' => 'jhonbrayanhuinchoquispe_sistemasic',
        'username' => 'jhonbrayanhuinchoquispe',
        'password' => 'brayan933783039'
    ],
    // Probando con puerto explícito
    [
        'host' => 'mysql-jhonbrayanhuinchoquispe.alwaysdata.net:3306',
        'dbname' => 'jhonbrayanhuinchoquispe_sistemasic_chat',
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