<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Product.php';

$db = Database::connect();
$productModel = new Product($db);
$products = $productModel->getAll();

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function formatPrice(int|float|string $price): string
{
    return number_format((float) $price, 0, ',', '.') . ' VNĐ';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TunShop - Cửa hàng trực tuyến</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="header">
    <div class="container header-content">
        <h1>TunShop</h1>
        <nav>
            <a href="index.php">Trang chủ</a>
            <a href="#products">Sản phẩm</a>
            <a href="cart.php">Giỏ hàng</a>
            <a href="orders.php">Đơn hàng</a>
        </nav>
    </div>
</header>

<main class="container">
    <section class="hero">
        <h2>Chào mừng đến với TunShop</h2>
        <p>Mua sắm thiết bị công nghệ trực tuyến.</p>
    </section>

    <section id="products">
        <h2>Danh sách sản phẩm</h2>

        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <article class="product-card">
                    <div class="product-icon"🛍/div>

                    <h3><?= e($product['name']) ?></h3>

                    <p><?= e($product['description'] ?? '') ?></p>

                    <p class="price">
                        <?= formatPrice($product['price']) ?>
                    </p>

                    <p>Còn lại: <?= (int) $product['stock'] ?></p>

                    <?php if ((int) $product['stock'] > 0): ?>
                        <form action="cart.php" method="post">
                            <input type="hidden" name="product_id"
                                   value="<?= (int) $product['id'] ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit">Thêm vào giỏ hàng</button>
                        </form>
                    <?php else: ?>
                        <p>Hết hàng</p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<footer class="footer">
    <p>TunShop - Website thương mại điện tử</p>
</footer>
</body>
</html>
