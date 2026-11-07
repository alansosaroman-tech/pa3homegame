<?php
require __DIR__ . '/config/conexion.php';
require __DIR__ . '/models/ProductoModel.php';
require __DIR__ . '/controllers/ProductoController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responder(405, [
        'ok' => false,
        'error' => 'Método no permitido, use GET'
    ]);
}

$controlador = new ProductoController(new ProductoModel($pdo));
$controlador->detalle($_GET['id'] ?? null);