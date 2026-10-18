<?php
try {
    $pdo = new PDO('sqlsrv:Server=127.0.0.1,1433;Database=sithemr', 'root', 'P@ssw0rd');
    $stmt = $pdo->query('SELECT id, schedule_code FROM delivery_schedules ORDER BY id');
    foreach ($stmt as $row) {
        echo $row['id'] . ' => ' . $row['schedule_code'] . PHP_EOL;
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
