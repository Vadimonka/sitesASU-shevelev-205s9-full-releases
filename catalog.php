<?php
include 'db.php';
session_start();

$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$username = $is_logged_in ? ($_SESSION['username'] ?? 'Геймер') : '';

$selected_category = isset($_GET['category']) ? trim($_GET['category']) : 'all';


$categories = [
    'all'          => 'Все товары',
    'mouse'        => 'Мыши',
    'keyboard'     => 'Клавиатуры',
    'headphones'   => 'Наушники',
    'rgb_lighting' => 'RGB-подсветка',
    'webcam'       => 'Веб-камеры',
    'gaming_pc'    => 'Готовые ПК'
];

if ($selected_category === 'all' || !isset($categories[$selected_category])) {
    $sql = "SELECT * FROM products ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
} else {
    $sql = "SELECT * FROM products WHERE category = ? ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selected_category);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог | IT-Universe</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0a0d18;
            --surface: #121a2a;
            --surface-hover: #1c2541;
            --accent: #00e0ff;
            --text: #f0f9ff;
            --text-muted: #a5c0ff;
            --border: #1e2f4e;
            --shadow: 0 10px 30px rgba(0,0,0,0.45);
        }

        * { box-sizing: border-box; margin:0; padding:0; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            padding-top: 100px;
        }

        header {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 80px;
            padding: 0 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(10,13,24,0.92);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            z-index: 1000;
        }

        .logo { display: flex; align-items: center; gap: 14px; }
        .logo img { height: 48px; }
        .logo-text {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(90deg, #ffffff, var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-auth { display: flex; align-items: center; gap: 1.2rem; }

        .btn {
            padding: 0.75rem 1.9rem;
            border-radius: 12px;
            font-family: 'Orbitron', sans-serif;
            font-weight: 600;
            font-size: 0.97rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-login { color: var(--text); border: 1.5px solid #2c3b5e; background: transparent; }
        .btn-login:hover { border-color: var(--accent); background: rgba(0,224,255,0.08); }

        .btn-reg { background: var(--accent); color: #0a0d18; box-shadow: 0 0 20px rgba(0,224,255,0.35); }
        .btn-reg:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(0,224,255,0.5); }

        .profile-container { position: relative; }
        .profile-btn { display: flex; align-items: center; gap: 12px; background: none; border: none; cursor: pointer; padding: 0.6rem; border-radius: 14px; transition: all 0.3s; }
        .profile-btn:hover { background: rgba(0,224,255,0.08); }

        .profile-icon {
            width: 46px; height: 46px;
            background: linear-gradient(135deg, var(--accent), #0099cc);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            border: 2px solid var(--accent);
            box-shadow: 0 0 20px rgba(0,224,255,0.4);
        }

        .username { font-family: 'Orbitron'; font-size: 1.1rem; font-weight: 500; }

        .dropdown-menu {
            position: absolute; top: 100%; right: 0; margin-top: 12px;
            background: var(--surface); border: 1px solid var(--border); border-radius: 14px;
            min-width: 220px; box-shadow: var(--shadow), 0 0 35px rgba(0,224,255,0.2);
            opacity: 0; visibility: hidden; transform: translateY(-10px);
            transition: all 0.25s ease; z-index: 10;
        }

        .profile-container:hover .dropdown-menu,
        .profile-container:focus-within .dropdown-menu {
            opacity: 1; visibility: visible; transform: translateY(0);
        }

        .dropdown-item {
            padding: 1rem 1.5rem; color: var(--text-muted); text-decoration: none; font-size: 1rem;
            transition: all 0.2s;
        }

        .dropdown-item:hover { background: rgba(0,224,255,0.12); color: var(--accent); }

        .dropdown-item.logout { color: #ff7588; border-top: 1px solid var(--border); }

        .container { padding: 5rem 5% 10rem; max-width: 1700px; margin: 0 auto; }

        .catalog-title {
            font-family: 'Orbitron', sans-serif;
            font-size: clamp(2.8rem, 6vw, 4.2rem);
            font-weight: 800;
            text-align: center;
            margin-bottom: 3rem;
            color: var(--accent);
        }

        .category-filter {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 4rem;
        }

        .category-btn {
            padding: 0.8rem 1.6rem;
            border-radius: 12px;
            background: transparent;
            border: 1.5px solid var(--border);
            color: var(--text-muted);
            font-family: 'Orbitron', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .category-btn:hover,
        .category-btn.active {
            background: var(--accent);
            color: #0a0d18;
            border-color: var(--accent);
            box-shadow: 0 0 15px rgba(0,224,255,0.4);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 2.6rem;
        }

        .card {
            background: var(--surface);
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid var(--border);
            transition: all 0.4s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .card:hover {
            transform: translateY(-12px);
            border-color: var(--accent);
            box-shadow: var(--shadow), 0 0 35px rgba(0,224,255,0.2);
        }

        .card img {
            width: 100%;
            height: 280px;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .card:hover img { transform: scale(1.08); }

        .card-content {
            padding: 1.8rem 2rem 1.2rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .card h3 {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.5rem;
            color: var(--accent);
            margin-bottom: 0.9rem;
            font-weight: 600;
        }

        .card p {
            color: var(--text-muted);
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 1.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 2rem;
            border-top: 1px solid var(--border);
            background: rgba(0,0,0,0.15);
            margin-top: auto;
        }

        .price {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: #ffffff;
        }

        .btn-more {
            background: transparent;
            color: var(--accent);
            padding: 0.7rem 1.4rem;
            border-radius: 10px;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.85rem;
            font-weight: 700;
            border: 1.5px solid var(--accent);
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .card:hover .btn-more {
            background: var(--accent);
            color: #0a0d18;
            box-shadow: 0 0 15px rgba(0,224,255,0.4);
        }

        @media (max-width: 768px) {
            body { padding-top: 90px; }
            .grid { gap: 2rem; }
            .card img { height: 240px; }
            .price { font-size: 1.6rem; }
        }
    </style>
</head>
<body>

<header>
    <?php include 'header.php'; ?>
</header>

<div class="container">
    <h2 class="catalog-title">КАТАЛОГ ТОВАРОВ</h2>

    <div class="category-filter">
        <?php foreach ($categories as $cat_key => $cat_name): ?>
            <a href="?category=<?= $cat_key ?>"
               class="category-btn <?= ($selected_category === $cat_key) ? 'active' : '' ?>">
                <?= $cat_name ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                $price_display = (floor($row['price']) == $row['price'])
                    ? number_format($row['price'], 0, ',', ' ')
                    : number_format($row['price'], 2, ',', ' ');
                ?>
                <a href="product.php?id=<?= $row['id'] ?>" class="card">
                    <img src="<?= htmlspecialchars($row['image_path']) ?>"
                         alt="<?= htmlspecialchars($row['name']) ?>"
                         loading="lazy"
                         onerror="this.src='https://via.placeholder.com/500x280/121a2a/00e0ff?text=<?= urlencode($row['name'] ?? 'Gear') ?>'">

                    <div class="card-content">
                        <h3><?= htmlspecialchars($row['name']) ?></h3>
                        <p><?= htmlspecialchars($row['description']) ?></p>
                    </div>

                    <div class="card-footer">
                        <span class="price">₽<?= $price_display ?></span>
                        <div class="btn-more">Подробнее</div>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-products">
            <h3>Товары не найдены</h3>
            <p>В этой категории пока нет товаров. Выберите другую категорию.</p>
        </div>
    <?php endif; ?>
</div>

<script>
window.addEventListener('scroll', () => {
    document.querySelector('header').classList.toggle('scrolled', window.scrollY > 80);
});
</script>

</body>
</html>
