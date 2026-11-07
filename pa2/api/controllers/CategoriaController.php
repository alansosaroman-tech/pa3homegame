<?php
class CategoriaController {
    private CategoriaModel $modelo;

    public function __construct(CategoriaModel $modelo) {
        $this->modelo = $modelo;
    }

    public function listar(): void {
        try {
            $categorias = $this->modelo->listar();
            responder(200, [
                'ok' => true,
                'categorias' => $categorias
            ]);
        } catch (PDOException $e) {
            responder(500, [
                'ok' => false,
                'error' => 'Error al consultar las categorías'
            ]);
        }
    }
}