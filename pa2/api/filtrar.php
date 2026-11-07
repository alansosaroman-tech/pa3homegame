<?php
require __DIR__ . '/config/conexion.php';
require __DIR__ . '/models/ProductoModel.php';
require __DIR__ . '/controllers/ProductoController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, [
        'ok' => false,
        'error' => 'Método no permitido, use POST'
    ]);
}

$controlador = new ProductoController(new ProductoModel($pdo));
$controlador->filtrar();