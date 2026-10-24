<?php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';

final class Product
{
    public function __construct(
        private PDO $db
    ) {
    }

    public function getAll(): array
    {
        $sql = "
            SELECT id, name, description, price, stock
            FROM products
            WHERE stock >= 0
            ORDER BY id DESC
        ";

        return $this->db->query($sql)->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, name, description, price, stock
             FROM products
             WHERE id = :id'
        );

        $stmt->execute(['id' => $id]);

        $product = $stmt->fetch();

        return $product ?: null;
    }
}
