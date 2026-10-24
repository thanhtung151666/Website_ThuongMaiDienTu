<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../src/Database.php';

$db = Database::connect();

$stmt = $db->query(
    'SELECT id, customer_name, email, address,
            total_amount, status, created_at
     FROM orders
     ORDER BY id DESC'
);

$orders = $stmt->fetchAll();

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đơn hàng - TechShop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="header">
    <div class="container header-content">
        <h1>TechShop</h1>
        <nav>
            <a href="index.php">Trang chủ</a>
            <a href="cart.php">Giỏ hàng</a>
            <a href="orders.php">Đơn hàng</a>
        </nav>
    </div>
</header>

<main class="container">
    <section class="hero">
        <h2>Danh sách đơn hàng</h2>

        <?php if (isset($_GET['created'])): ?>
            <p>
                Đặt hàng thành công! Mã đơn:
                <?= (int) $_GET['created'] ?>
            </p>
        <?php endif; ?>

        <?php if (!$orders): ?>
            <p>Chưa có đơn hàng nào.</p>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <article class="product-card">
                    <h3>Đơn hàng #<?= (int) $order['id'] ?></h3>

                    <p>
                        Khách hàng:
                        <?= e($order['customer_name']) ?>
                    </p>

                    <p>Email: <?= e($order['email']) ?></p>

                    <p>Địa chỉ: <?= e($order['address']) ?></p>

                    <p>
                        Tổng tiền:
                        <?= number_format(
                            (float) $order['total_amount'],
                            0,
                            ',',
                            '.'
                        ) ?> VNĐ
                    </p>

                    <p>Trạng thái: <?= e($order['status']) ?></p>

                    <p>Ngày tạo: <?= e($order['created_at']) ?></p>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
