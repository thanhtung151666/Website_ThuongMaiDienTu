<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Product.php';
require_once __DIR__ . '/../src/Cart.php';

$db = Database::connect();
$productModel = new Product($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';

    $productId = filter_var(
        $_POST['product_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    $quantity = filter_var(
        $_POST['quantity'] ?? 1,
        FILTER_VALIDATE_INT
    );

    if ($productId === false || $productId === null || $productId <= 0) {
        http_response_code(400);
        exit('Mã sản phẩm không hợp lệ.');
    }

    if ($action === 'remove') {
        Cart::remove($productId);
    } elseif ($action === 'update') {
        if ($quantity === false || $quantity === null || $quantity < 0) {
            http_response_code(400);
            exit('Số lượng không hợp lệ.');
        }

        Cart::update($productId, $quantity);
    } else {
        if ($quantity === false || $quantity === null || $quantity <= 0) {
            http_response_code(400);
            exit('Số lượng phải lớn hơn 0.');
        }

        $product = $productModel->getById($productId);

        if (!$product) {
            http_response_code(404);
            exit('Không tìm thấy sản phẩm.');
        }

        $currentQuantity = Cart::getItems()[$productId] ?? 0;

        if ($currentQuantity + $quantity > (int) $product['stock']) {
            http_response_code(400);
            exit('Số lượng vượt quá tồn kho.');
        }

        Cart::add($productId, $quantity);
    }

    header('Location: cart.php');
    exit;
}

$items = [];
$total = 0;

foreach (Cart::getItems() as $productId => $quantity) {
    $product = $productModel->getById((int) $productId);

    if (!$product) {
        continue;
    }

    $quantity = (int) $quantity;
    $subtotal = (float) $product['price'] * $quantity;

    $items[] = [
        'product' => $product,
        'quantity' => $quantity,
        'subtotal' => $subtotal
    ];

    $total += $subtotal;
}

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
    <title>Giỏ hàng - TechShop</title>
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
        <h2>Giỏ hàng của bạn</h2>

        <?php if (!$items): ?>
            <p>Giỏ hàng đang trống.</p>
            <a href="index.php">Tiếp tục mua sắm</a>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <article class="product-card">
                    <h3><?= e($item['product']['name']) ?></h3>

                    <p>
                        Đơn giá:
                        <?= number_format((float) $item['product']['price'], 0, ',', '.') ?>
                        VNĐ
                    </p>

                    <p>
                        Thành tiền:
                        <?= number_format($item['subtotal'], 0, ',', '.') ?>
                        VNĐ
                    </p>

                    <form method="post" action="cart.php">
                        <input type="hidden" name="action" value="update">
                        <input
                            type="hidden"
                            name="product_id"
                            value="<?= (int) $item['product']['id'] ?>"
                        >
                        <label>
                            Số lượng:
                            <input
                                type="number"
                                name="quantity"
                                min="0"
                                max="<?= (int) $item['product']['stock'] ?>"
                                value="<?= $item['quantity'] ?>"
                                required
                            >
                        </label>
                        <button type="submit">Cập nhật</button>
                    </form>

                    <form method="post" action="cart.php">
                        <input type="hidden" name="action" value="remove">
                        <input
                            type="hidden"
                            name="product_id"
                            value="<?= (int) $item['product']['id'] ?>"
                        >
                        <button type="submit">Xóa sản phẩm</button>
                    </form>
                </article>
            <?php endforeach; ?>

            <h2>
                Tổng tiền:
                <?= number_format($total, 0, ',', '.') ?> VNĐ
            </h2>

            <p>
                <a href="index.php">Tiếp tục mua hàng</a>
            </p>
            <p>
                <a href="checkout.php">Tiến hành đặt hàng</a>
            </p>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
