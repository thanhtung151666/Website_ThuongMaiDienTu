<?php
declare(strict_types=1);

final class Cart
{
    public static function getItems(): array
    {
        return $_SESSION['cart'] ?? [];
    }

    public static function add(int $productId, int $quantity = 1): void
    {
        if ($productId <= 0 || $quantity <= 0) {
            throw new InvalidArgumentException('Sản phẩm không hợp lệ.');
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $_SESSION['cart'][$productId] =
            ($_SESSION['cart'][$productId] ?? 0) + $quantity;
    }

    public static function update(int $productId, int $quantity): void
    {
        if ($productId <= 0) {
            return;
        }

        if ($quantity <= 0) {
            unset($_SESSION['cart'][$productId]);
            return;
        }

        $_SESSION['cart'][$productId] = $quantity;
    }

    public static function remove(int $productId): void
    {
        unset($_SESSION['cart'][$productId]);
    }

    public static function clear(): void
    {
        unset($_SESSION['cart']);
    }
}
