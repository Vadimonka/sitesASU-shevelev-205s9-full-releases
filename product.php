<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$product_id = (int)($_GET['id'] ?? 0);
$product = null;
$error = null;
$success_message = '';

if ($product_id > 0) {
    $stmt = $conn->prepare("
        SELECT name, description, price, image_path, full_description
        FROM products
        WHERE id = ?
    ");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $product = $res->fetch_assoc();
    $stmt->close();

    if (!$product) $error = "Товар не найден";
} else {
    $error = "Некорректный ID товара";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart']) && $product) {
    $qty = max(1, (int)$_POST['quantity']);

    $_SESSION['cart'][$product_id]['quantity'] =
        ($_SESSION['cart'][$product_id]['quantity'] ?? 0) + $qty;

    $_SESSION['cart'][$product_id] += [
        'name' => $product['name'],
        'price' => $product['price'],
        'image_path' => $product['image_path']
    ];

    $success_message = "Добавлено в корзину: {$qty} шт.";
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($product['name'] ?? 'Товар') ?> • Цифровой мир</title>

<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

<style>
:root {
    --bg: #05070f;
    --panel: #0c1222;
    --accent: #00f0ff;
    --text: #eaf6ff;
    --muted: #9fbfff;
    --border: rgba(0,240,255,.15);
    --success: #00ff9d;
    --header-h: 80px;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

html, body {
    height: 100%;
    overflow: hidden;
    background: radial-gradient(circle at 20% 20%, #0b1630, #05070f 70%);
    font-family: Inter, sans-serif;
    color: var(--text);
}

header {
    position: fixed;
    top: 0; left: 0;
    width: 100%;
    height: var(--header-h);
    z-index: 1000;
}

.main-content {
    position: absolute;
    top: var(--header-h);
    left: 0;
    width: 100%;
    height: calc(100vh - var(--header-h));
    padding: 40px 5%;
}

.product-wrapper {
    height: 100%;
    display: grid;
    grid-template-columns: 1.4fr 1fr;
    gap: 70px;
}

.image-column {
    height: 100%;
    display: flex;
    align-items: center;
}

.image-container {
    width: 100%;
    height: 100%;
    border-radius: 30px;
    overflow: hidden;
    box-shadow: 0 40px 120px rgba(0,0,0,.8);
    background: #000;
}

.image-container img {
    width: 100%;
    height: 100%;
    object-fit: contain;           /* ← было cover, теперь contain — лучше для большинства товаров */
    image-rendering: crisp-edges;   /* улучшает чёткость на многих экранах */
    background: #000;               /* запасной фон, если картинка прозрачная */
}

/* ──────────────────────────────────────────────── */

.info-column {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.product-title {
    font-family: Orbitron;
    font-size: clamp(2.4rem, 3.8vw, 3.8rem);
    line-height: 1.05;
    margin-bottom: 25px;
    background: linear-gradient(90deg, #fff, var(--accent));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.description-container {
    flex: 1;
    padding: 28px 34px;
    background: linear-gradient(180deg, rgba(12,18,34,.9), rgba(12,18,34,.6));
    border-left: 5px solid var(--accent);
    border-radius: 0 22px 22px 0;
    line-height: 1.8;
    color: var(--muted);
    overflow-y: auto;
    mask-image: linear-gradient(to bottom, black 92%, transparent);
}

/* ── ЦЕНА И КНОПКА ── теперь компактнее и в одну строку ─────────────────────── */
.purchase-section {
    margin-top: 28px;
    padding: 20px 30px;
    background: linear-gradient(135deg, #0f1a35, #0b1224);
    border: 1px solid var(--border);
    border-radius: 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 20px 60px rgba(0,0,0,.7);
    gap: 20px;
    flex-wrap: nowrap;
}

.price {
    white-space: nowrap;
}

.price-label {
    font-size: 0.9rem;
    color: var(--muted);
    display: block;
}

.price-amount {
    font-family: Orbitron;
    font-size: 2.1rem;              /* ← было 2.9 → уменьшили */
    font-weight: 900;
    color: #fff;
    line-height: 1;
}

.cart-actions {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-shrink: 0;
}

.quantity-field {
    width: 68px;
    height: 48px;
    background: #000;
    border: 1px solid var(--border);
    color: #fff;
    font-family: Orbitron;
    font-size: 1.15rem;
    text-align: center;
    border-radius: 10px;
}

.add-to-cart-btn {
    height: 48px;
    padding: 0 38px;
    font-family: Orbitron;
    font-size: 1.05rem;
    font-weight: 900;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    background: linear-gradient(135deg, var(--accent), #00b8ff);
    color: #001018;
    box-shadow: 0 8px 24px rgba(0,240,255,.45);
}

/* Уведомление */
.success-notification {
    position: fixed;
    right: 40px;
    bottom: 40px;
    padding: 16px 28px;
    border-radius: 16px;
    background: rgba(0,255,157,.15);
    border: 1px solid var(--success);
    color: var(--success);
    font-family: Orbitron;
    z-index: 999;
}
</style>
</head>

<body>

<header>
    <?php include 'header.php'; ?>
</header>

<div class="main-content">
<?php if ($error): ?>
    <div style="margin:auto; font-size:2rem; color:#ff7588; text-align:center;">
        <?= htmlspecialchars($error) ?>
    </div>
<?php else: ?>
<div class="product-wrapper">

    <div class="image-column">
        <div class="image-container">
            <img src="<?= htmlspecialchars($product['image_path']) ?>"
                 alt="<?= htmlspecialchars($product['name']) ?>"
                 onerror="this.src='https://via.placeholder.com/1400x1400/05070f/00f0ff?text=NO+IMAGE'">
        </div>
    </div>

    <div class="info-column">
        <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>

        <div class="description-container">
            <?= nl2br(htmlspecialchars($product['full_description'] ?? $product['description'])) ?>
        </div>

        <div class="purchase-section">
            <div class="price">
                <span class="price-label">Цена</span>
                <span class="price-amount">₽ <?= number_format($product['price'], 2, ',', ' ') ?></span>
            </div>

            <form method="post" class="cart-actions">
                <input type="number" name="quantity" value="1" min="1" class="quantity-field">
                <button type="submit" name="add_to_cart" class="add-to-cart-btn">
                    В КОРЗИНУ
                </button>
            </form>
        </div>
    </div>

</div>
<?php endif; ?>
</div>

<?php if ($success_message): ?>
<div class="success-notification"><?= $success_message ?></div>
<?php endif; ?>

</body>
</html>