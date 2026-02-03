<?php
include 'db.php';
session_start();


if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'] ?? 'Геймер';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;

    if ($item_id > 0 && isset($_SESSION['cart'][$item_id])) {
        if ($action === 'increase') {
            $_SESSION['cart'][$item_id]['quantity']++;
        } elseif ($action === 'decrease') {
            $_SESSION['cart'][$item_id]['quantity']--;
            if ($_SESSION['cart'][$item_id]['quantity'] <= 0) {
                unset($_SESSION['cart'][$item_id]);
            }
        } elseif ($action === 'remove') {
            unset($_SESSION['cart'][$item_id]);
        }
    }

    header("Location: cart.php");
    exit;
}


$total = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина | IT-Universe</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #05060a;
            --surface: #0b0e14;
            --accent: #00e0ff;
            --text: #e0f0ff;
            --text-muted: #8b9db0;
            --border: rgba(0,224,255,0.12);
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        header {
            height: 80px;
            padding: 0 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(5,6,10,0.92);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
        }

        .logo-text {
            font-family: 'Orbitron';
            font-size: 1.8rem;
            font-weight: 900;
            background: linear-gradient(90deg, #fff, var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .container {
            max-width: 1200px;
            margin: 100px auto 40px;
            padding: 0 20px;
        }

        h1 {
            font-family: 'Orbitron';
            font-size: 2.8rem;
            margin-bottom: 30px;
            text-align: center;
        }

        .cart-empty {
            text-align: center;
            font-size: 1.4rem;
            color: var(--text-muted);
            margin-top: 100px;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--surface);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .cart-table th, .cart-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .cart-table th {
            background: rgba(0,224,255,0.1);
            font-family: 'Orbitron';
            font-size: 1.1rem;
        }

        .cart-item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .qty-btn {
            width: 32px;
            height: 32px;
            background: rgba(0,224,255,0.2);
            border: none;
            border-radius: 8px;
            color: var(--accent);
            font-size: 1.2rem;
            cursor: pointer;
            transition: 0.2s;
        }

        .qty-btn:hover {
            background: var(--accent);
            color: #000;
        }

        .qty-display {
            width: 50px;
            text-align: center;
            font-weight: bold;
        }

        .remove-btn {
            background: #ff4444;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
        }

        .total {
            margin-top: 40px;
            text-align: right;
            font-size: 1.8rem;
            font-family: 'Orbitron';
        }

        .total span {
            color: var(--accent);
        }
    </style>
</head>
<body>

<header>
    <?php include 'header.php'; ?>
</header>

<div class="container">
    <h1>Корзина</h1>

    <?php if (empty($_SESSION['cart'])): ?>
        <div class="cart-empty">
            <p>Ваша корзина пуста</p>
            <a href="catalog.php" style="color:var(--accent); font-size:1.2rem;">Перейти в каталог</a>
        </div>
    <?php else: ?>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Товар</th>
                    <th>Цена</th>
                    <th>Количество</th>
                    <th>Сумма</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                    <?php
                    $item_total = $item['price'] * $item['quantity'];
                    ?>
                    <tr>
                        <td>
                            <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-item-img">
                            <?= htmlspecialchars($item['name']) ?>
                        </td>
                        <td>₽ <?= number_format($item['price'], 2, ',', ' ') ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="item_id" value="<?= $id ?>">
                                <input type="hidden" name="action" value="decrease">
                                <button type="submit" class="qty-btn">–</button>
                            </form>
                            <span class="qty-display"><?= $item['quantity'] ?></span>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="item_id" value="<?= $id ?>">
                                <input type="hidden" name="action" value="increase">
                                <button type="submit" class="qty-btn">+</button>
                            </form>
                        </td>
                        <td>₽ <?= number_format($item_total, 2, ',', ' ') ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="item_id" value="<?= $id ?>">
                                <input type="hidden" name="action" value="remove">
                                <button type="submit" class="remove-btn">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total">
            Итого: <span>₽ <?= number_format($total, 2, ',', ' ') ?></span>
        </div>
    <?php endif; ?>
</div>

</body>
</html>