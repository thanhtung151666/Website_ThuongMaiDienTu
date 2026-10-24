<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Product.php';
require_once __DIR__ . '/../src/Cart.php';

$db = Database::connect();
$productModel = new Product($db);

$error = '';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

if (!Cart::getItems()) {
    header('Location: cart.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['customer_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === '' || $address === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Vui lòng nhập đầy đủ thông tin hợp lệ.';
    } else {
        try {
            $db->beginTransaction();

            $cartItems = Cart::getItems();
            $orderItems = [];
            $total = 0.0;

            foreach ($cartItems as $productId => $quantity) {
                $stmt = $db->prepare(
                    'SELECT id, name, price, stock
                     FROM products
                     WHERE id = ?
                     FOR UPDATE'
                );

                $stmt->execute([(int) $productId]);
                $product = $stmt->fetch();

                $quantity = (int) $quantity;

                if (
                    !$product ||
                    $quantity <= 0 ||
                    $quantity > (int) $product['stock']
                ) {
                    throw new RuntimeException(
                        'Sản phẩm không đủ tồn kho. Vui lòng kiểm tra lại giỏ hàng.'
                    );
                }

                $subtotal = (float) $product['price'] * $quantity;
                $total += $subtotal;

                $orderItems[] = [
                    'product_id' => (int) $product['id'],
                    'quantity' => $quantity,
                    'price' => (float) $product['price']
                ];
            }

            $stmt = $db->prepare(
                'INSERT INTO orders
                    (customer_name, email, address, total_amount)
                 VALUES (?, ?, ?, ?)'
            );

            $stmt->execute([$name, $email, $address, $total]);

            $orderId = (int) $db->lastInsertId();

            foreach ($orderItems as $item) {
                $stmt = $db->prepare(
                    'INSERT INTO order_items
                        (order_id, product_id, quantity, unit_price)
                     VALUES (?, ?, ?, ?)'
                );

                $stmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price']
                ]);

                $stmt = $db->prepare(
                    'UPDATE products
                     SET stock = stock - ?
                     WHERE id = ? AND stock >= ?'
                );

                $stmt->execute([
                    $item['quantity'],
                    $item['product_id'],
                    $item['quantity']
                ]);

                if ($stmt->rowCount() !== 1) {
                    throw new RuntimeException(
                        'Không thể cập nhật tồn kho. Vui lòng thử lại.'
                    );
                }
            }

            $db->commit();
            Cart::clear();

            header('Location: orders.php?created=' . $orderId);
            exit;
        } catch (Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            $error = $exception instanceof RuntimeException
                ? $exception->getMessage()
                : 'Không thể tạo đơn hàng. Vui lòng thử lại sau.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đặt hàng - TechShop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="header">
    <div class="container header-content">
        <h1>TechShop</h1>
        <nav>
            <a href="index.php">Trang chủ</a>
            <a href="cart.php">Giỏ hàng</a>
        </nav>
    </div>
</header>

<main class="container">
    <section class="hero">
        <h2>Thông tin giao hàng</h2>

        <?php if ($error !== ''): ?>
            <p><?= e($error) ?></p>
        <?php endif; ?>

        <form method="post" action="checkout.php">
            <p>
                <label>Họ tên khách hàng</label><br>
                <input name="customer_name" maxlength="150" required>
            </p>

            <p>
                <label>Email</label><br>
                <input type="email" name="email" maxlength="200" required>
            </p>

            <p>
                <label>Địa chỉ giao hàng</label><br>
                <textarea name="address" maxlength="500" required></textarea>
            </p>

            <button type="submit">Xác nhận đặt hàng</button>
        </form>
    </section>
</main>
</body>
</html>
