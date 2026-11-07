<?php
class ProductoController {
    private ProductoModel $modelo;

    public function __construct(ProductoModel $modelo) {
        $this->modelo = $modelo;
    }

    public function detalle($idCrudo): void {
        $id = filter_var($idCrudo, FILTER_VALIDATE_INT);

        if ($id === false || $id <= 0) {
            responder(400, [
                'ok' => false,
                'error' => 'El parámetro id es obligatorio y debe ser un entero positivo'
            ]);
        }

        try {
            $producto = $this->modelo->obtenerPorId($id);

            if (!$producto) {
                responder(404, [
                    'ok' => false,
                    'error' => 'Producto no encontrado'
                ]);
            }

            responder(200, [
                'ok' => true,
                'producto' => $producto
            ]);
        } catch (PDOException $e) {
            responder(500, [
                'ok' => false,
                'error' => 'Error al consultar el producto'
            ]);
        }
    }

    public function filtrar(): void {
        $tipoContenido = $_SERVER['CONTENT_TYPE'] ?? '';
        $contenido = file_get_contents('php://input');

        if (stripos($tipoContenido, 'application/json') !== false) {
            $entrada = json_decode($contenido, true);

            if (!is_array($entrada)) {
                responder(400, [
                    'ok' => false,
                    'error' => 'El cuerpo JSON no es válido'
                ]);
            }
        } else {
            $entrada = $_POST;
        }

        $errores = [];
        $filtros = [];

        if (isset($entrada['nombre'])) {
            if (!is_scalar($entrada['nombre'])) {
                $errores[] = 'nombre no es válido';
            } else {
                $nombre = trim((string) $entrada['nombre']);

                if (mb_strlen($nombre) > 120) {
                    $errores[] = 'nombre no puede superar 120 caracteres';
                } elseif ($nombre !== '') {
                    $filtros['nombre'] = $nombre;
                }
            }
        }

        if (isset($entrada['categoria_id']) && $entrada['categoria_id'] !== '') {
            $categoriaId = filter_var($entrada['categoria_id'], FILTER_VALIDATE_INT);

            if ($categoriaId === false || $categoriaId <= 0) {
                $errores[] = 'categoria_id debe ser un entero positivo';
            } else {
                $filtros['categoria_id'] = $categoriaId;
            }
        }

        foreach (['precio_min', 'precio_max'] as $campo) {
            if (isset($entrada[$campo]) && $entrada[$campo] !== '') {
                $valor = filter_var($entrada[$campo], FILTER_VALIDATE_FLOAT);

                if ($valor === false || $valor < 0) {
                    $errores[] = "$campo debe ser un número mayor o igual a 0";
                } else {
                    $filtros[$campo] = $valor;
                }
            }
        }

        if (
            isset($filtros['precio_min'], $filtros['precio_max']) &&
            $filtros['precio_min'] > $filtros['precio_max']
        ) {
            $errores[] = 'precio_min no puede ser mayor que precio_max';
        }

        if (isset($entrada['stock_min']) && $entrada['stock_min'] !== '') {
            $stockMin = filter_var($entrada['stock_min'], FILTER_VALIDATE_INT);

            if ($stockMin === false || $stockMin < 0) {
                $errores[] = 'stock_min debe ser un entero mayor o igual a 0';
            } else {
                $filtros['stock_min'] = $stockMin;
            }
        }

        $pagina = 1;
        if (isset($entrada['pagina']) && $entrada['pagina'] !== '') {
            $pagina = filter_var($entrada['pagina'], FILTER_VALIDATE_INT);

            if ($pagina === false || $pagina < 1) {
                $errores[] = 'pagina debe ser un entero mayor o igual a 1';
            }
        }

        $porPagina = 6;
        if (isset($entrada['por_pagina']) && $entrada['por_pagina'] !== '') {
            $porPagina = filter_var($entrada['por_pagina'], FILTER_VALIDATE_INT);

            if ($porPagina === false || $porPagina < 1 || $porPagina > 50) {
                $errores[] = 'por_pagina debe ser un entero entre 1 y 50';
            }
        }

        if ($errores) {
            responder(400, [
                'ok' => false,
                'errores' => $errores
            ]);
        }

        try {
            $total = $this->modelo->contarFiltrado($filtros);
            $productos = $this->modelo->listarFiltrado($filtros, $pagina, $porPagina);

            responder(200, [
                'ok' => true,
                'productos' => $productos,
                'paginacion' => [
                    'pagina_actual' => $pagina,
                    'por_pagina' => $porPagina,
                    'total_registros' => $total,
                    'total_paginas' => max(1, (int) ceil($total / $porPagina))
                ]
            ]);
        } catch (PDOException $e) {
            responder(500, [
                'ok' => false,
                'error' => 'Error al filtrar los productos'
            ]);
        }
    }
}