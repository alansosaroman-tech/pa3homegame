<?php
class CategoriaModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function listar(): array {
        $stmt = $this->pdo->prepare('SELECT id, nombre FROM categorias ORDER BY nombre');
        $stmt->execute();
        $filas = $stmt->fetchAll();

        foreach ($filas as &$fila) {
            $fila['id'] = (int) $fila['id'];
        }
        unset($fila);

        return $filas;
    }
}