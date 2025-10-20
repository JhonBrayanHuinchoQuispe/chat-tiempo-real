<?php
// Test de conexión a AlwaysData
header('Content-Type: application/json');

$configs = [
    [
        'host' => 'jhonbrayanhuinchoquispe.alwaysdata.net',
        'dbname' => 'jhonbrayanhuinchoquispe_sistemasic_chat'
    ],
    [
        'host' => 'mysql-jhonbrayanhuinchoquispe.alwaysdata.net', 
        'dbname' => 'jhonbrayanhuinchoquispe_sistemasic_chat'
    ],
    [
        'host' => 'jhonbrayanhuinchoquispe.alwaysdata.net',
        'dbname' => 'sistemasic_chat'
    ]
];

$results = [];

foreach ($configs as $i => $config) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
            'jhonbrayanhuinchoquispe',
            'brayan933783039',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Probar consulta
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $results[] = [
            'config' => $i + 1,
            'host' => $config['host'],
            'dbname' => $config['dbname'],
            'status' => 'SUCCESS',
            'tables' => $tables
        ];
        
    } catch (Exception $e) {
        $results[] = [
            'config' => $i + 1,
            'host' => $config['host'],
            'dbname' => $config['dbname'],
            'status' => 'ERROR',
            'error' => $e->getMessage()
        ];
    }
}

echo json_encode($results, JSON_PRETTY_PRINT);
?>