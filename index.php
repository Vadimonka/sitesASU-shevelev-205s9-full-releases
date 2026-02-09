<?php
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
include 'db.php';
session_start();

$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$username = $is_logged_in ? ($_SESSION['username'] ?? 'Геймер') : '';


const RUB_RATE = 90;

$sql = "SELECT * FROM products ORDER BY RAND() LIMIT 12";
$result = $conn->query($sql);

$db_error = '';
if (!$result) {
    $db_error = "Ошибка запроса к базе: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT-Universe • Игровое оборудование</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0a0d18;
            --surface: #121a2a;
            --surface-hover: #1c2541;
            --accent: #00e0ff;
            --accent-glow: #00e0ff33;
            --text: #f0f9ff;
            --text-muted: #a5c0ff;
            --border: #1e2f4e;
            --shadow: 0 10px 30px rgba(0,0,0,0.45);
        }

        * { box-sizing: border-box; margin:0; padding:0; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            padding-top: 100px;
            overflow-x: hidden;
        }

        header {
            position: fixed; top: 0; left: 0; right: 0; height: 80px;
            padding: 0 5%; display: flex; justify-content: space-between; align-items: center;
            background: rgba(10,13,24,0.92); backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border); z-index: 1000;
        }

        .logo { display: flex; align-items: center; gap: 14px; text-decoration: none; }
        .logo-text {
            font-family: 'Orbitron', sans-serif; font-size: 1.8rem; font-weight: 700;
            background: linear-gradient(90deg, #ffffff, var(--accent));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }

        .nav-auth { display: flex; align-items: center; gap: 1.2rem; }

        .btn {
            padding: 0.75rem 1.9rem; border-radius: 12px; font-family: 'Orbitron', sans-serif;
            text-decoration: none; transition: 0.3s;
        }
        .btn-login { color: var(--text); border: 1.5px solid #2c3b5e; }
        .btn-login:hover { border-color: var(--accent); background: rgba(0,224,255,0.08); }

        .profile-container { position: relative; }
        .profile-btn {
            display: flex; align-items: center; gap: 12px; background: none; border: none;
            cursor: pointer; padding: 0.6rem; border-radius: 14px; color: white;
        }

        .dropdown-menu {
            position: absolute; top: 100%; right: 0; background: var(--surface);
            border: 1px solid var(--border); border-radius: 14px; min-width: 200px;
            display: none; z-index: 10;
        }
        .profile-container:hover .dropdown-menu { display: block; }
        .dropdown-item { display: block; padding: 1rem; color: var(--text-muted); text-decoration: none; }
        .dropdown-item:hover { background: rgba(0,224,255,0.1); color: var(--accent); }

        .hero {
            position: relative;
            padding: 4rem 5% 6rem;
            background: linear-gradient(to bottom, rgba(0,224,255,0.04), transparent);
            overflow: hidden;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            max-width: 1700px;
            margin: 0 auto;
            align-items: center;
        }

        .hero-text h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: clamp(3.2rem, 6vw, 5.8rem);
            font-weight: 900;
            background: linear-gradient(90deg, #ffffff, var(--accent), #ffffff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.2rem;
            letter-spacing: -1px;
            text-shadow: 0 0 40px var(--accent-glow);
        }

        .hero-text p {
            font-size: 1.28rem;
            line-height: 1.7;
            color: var(--text-muted);
            max-width: 620px;
            margin-bottom: 2.5rem;
        }

        .hero-text .glow-text {
            color: var(--accent);
            font-weight: 600;
            text-shadow: 0 0 15px var(--accent);
        }

        .hero-video {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.7), 0 0 40px var(--accent-glow);
            background: #000;
        }

        .hero-video video {
            width: 100%;
            height: auto;
            display: block;
        }

        @media (max-width: 1024px) {
            .hero-grid {
                grid-template-columns: 1fr;
                gap: 3rem;
            }
            .hero-video {
                order: -1; 
            }
        }

        .container { padding: 3rem 5%; max-width: 1700px; margin: 0 auto; }
        .catalog-title { 
            font-family: 'Orbitron', sans-serif; 
            text-align: center; 
            margin: 4rem 0 3rem; 
            color: var(--accent);
            font-size: clamp(2.4rem, 5vw, 4rem);
            text-shadow: 0 0 30px var(--accent-glow);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2.5rem;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
            transition: 0.4s;
            text-decoration: none;
            color: inherit;
        }

        .card:hover {
            transform: translateY(-10px);
            border-color: var(--accent);
            box-shadow: 0 10px 30px rgba(0,224,255,0.2);
        }

        .card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .card-content {
            padding: 1.8rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .card h3 {
            font-family: 'Orbitron', sans-serif;
            color: var(--accent);
            margin-bottom: 1rem;
            font-size: 1.4rem;
            min-height: 3.2rem;
        }

        .card p {
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 2rem;
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-footer {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            padding-top: 1rem;
            border-top: 1px solid rgba(255,255,255,0.05);
        }

        .price {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.7rem;
            font-weight: 700;
            color: #fff;
            white-space: nowrap;
        }

        .buy-now {
            background: var(--accent);
            color: #0a0d18;
            padding: 0.8rem 1.4rem;
            border-radius: 10px;
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: 0.3s;
            white-space: nowrap;
        }

        .buy-now:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(0,224,255,0.4);
        }

        @media (max-width: 768px) {
            .grid { grid-template-columns: 1fr; }
            .card h3 { min-height: auto; }
        }
    </style>
</head>
<body>

<header>
    <?php include 'header.php'; ?>
</header>

<div class="hero">
    <div class="hero-grid">
        <div class="hero-text">
            <h1>ЦИФРОВОЙ МИР</h1>
            <p>
                Добро пожаловать в будущее гейминга.<br><br>
                Мы собрали самое мощное, стильное и технологичное игровое оборудование: от <span class="glow-text">RGB-монстров</span> и периферии с механикой до мониторов с частотой 360+ Гц и кастомных сборок.<br><br>
                Здесь каждый найдёт то, что разгонит его до предела — от новичка до киберспортсмена мирового уровня.
            </p>
            <p style="font-size:1.1rem; opacity:0.9;">
                Погрузись. Настройся.Побеждай.
            </p>
        </div>

        <div class="hero-video">
            <video autoplay loop muted playsinline>
                <source src="https://cdn.pixabay.com/video/2025/05/13/278748_large.mp4" type="video/mp4">
                Ваш браузер не поддерживает видео.
            </video>
        </div>
    </div>
</div>

<div class="container">
    <h2 class="catalog-title">РАЗНООБРАЗНЫЕ ТОПОВЫЕ ТОВАРЫ</h2>

    <?php if ($db_error): ?>
        <p style="text-align:center; color:red;"><?= $db_error ?></p>
    <?php elseif ($result && $result->num_rows > 0): ?>
        <div class="grid">
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="card">
                    <img src="<?= htmlspecialchars($row['image_path']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                    
                    <div class="card-content">
                        <h3><?= htmlspecialchars($row['name']) ?></h3>
                        <p><?= htmlspecialchars($row['description']) ?></p>
                        
                        <div class="card-footer">
                            <span class="price"><?= number_format($row['price'], 0, '.', ' ') ?> ₽</span>
                            <a href="product.php?id=<?= $row['id'] ?>" style="text-decoration:none;">
                                <button class="buy-now">В КОРЗИНУ</button>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p style="text-align:center;">Товары не найдены.</p>
    <?php endif; ?>
</div>

</body>
</html>
