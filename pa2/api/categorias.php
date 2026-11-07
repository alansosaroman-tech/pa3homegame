<?php
require __DIR__ . '/config/conexion.php';
require __DIR__ . '/models/CategoriaModel.php';
require __DIR__ . '/controllers/CategoriaController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responder(405, [
        'ok' => false,
        'error' => 'Método no permitido, use GET'
    ]);
}

$controlador = new CategoriaController(new CategoriaModel($pdo));
$controlador->listar();