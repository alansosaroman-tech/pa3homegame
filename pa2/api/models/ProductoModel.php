<?php
class ProductoModel {
    private PDO $pdo;
    const SQL_BASE = "SELECT
        p.id,
        p.nombre,
        p.descripcion,
        p.precio,
        c.nombre AS categoria,
        p.categoria_id,
        p.stock,
        GROUP_CONCAT(i.url ORDER BY i.id SEPARATOR '||') AS imagenes
    FROM productos p
    INNER JOIN categorias c ON c.id = p.categoria_id
    LEFT JOIN imagenes i ON i.producto_id = p.id";
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function formatear(array $fila): array {
        $fila['id'] = (int) $fila['id'];
        $fila['precio'] = (float) $fila['precio'];
        $fila['categoria_id'] = (int) $fila['categoria_id'];
        $fila['stock'] = (int) $fila['stock'];
        
        $fila['imagenes'] = ($fila['imagenes'] !== null && $fila['imagenes'] !== '')
            ? explode('||', $fila['imagenes'])
            : [];
        return $fila;
    }

    public function obtenerPorId(int $id): ?array {
        $stmt = $this->pdo->prepare(
            self::SQL_BASE . ' WHERE p.id = :id GROUP BY p.id, c.nombre'
        );
        $stmt->execute([':id' => $id]);
        $fila = $stmt->fetch();
        return $fila ? $this->formatear($fila) : null;
    }

    private function construirWhere(array $f): array {
        $c = [];
        $p = [];

        if (isset($f['nombre'])) {
            $c[] = 'p.nombre LIKE :nombre';
            $p[':nombre'] = '%' . $f['nombre'] . '%';
        }
        if (isset($f['categoria_id'])) {
            $c[] = 'p.categoria_id = :categoria_id';
            $p[':categoria_id'] = $f['categoria_id'];
        }
        if (isset($f['precio_min'])) {
            $c[] = 'p.precio >= :precio_min';
            $p[':precio_min'] = $f['precio_min'];
        }
        if (isset($f['precio_max'])) {
            $c[] = 'p.precio <= :precio_max';
            $p[':precio_max'] = $f['precio_max'];
        }
        if (isset($f['stock_min'])) {
            $c[] = 'p.stock >= :stock_min';
            $p[':stock_min'] = $f['stock_min'];
        }

        return [$c ? ' WHERE ' . implode(' AND ', $c) : '', $p];
    }

    public function listarFiltrado(array $f, int $pagina, int $porPagina): array {
        [$where, $params] = $this->construirWhere($f);

        $stmt = $this->pdo->prepare(
            self::SQL_BASE . $where .
            ' GROUP BY p.id, c.nombre ORDER BY p.id LIMIT :limite OFFSET :desplazamiento'
        );
        foreach ($params as $clave => $valor) {
            $stmt->bindValue($clave, $valor);
        }
        $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':desplazamiento', ($pagina - 1) * $porPagina, PDO::PARAM_INT);
        $stmt->execute();

        return array_map([$this, 'formatear'], $stmt->fetchAll());
    }

    public function contarFiltrado(array $f): int {
        [$where, $params] = $this->construirWhere($f);

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM productos p' . $where);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }
}