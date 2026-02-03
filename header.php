<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$username = $is_logged_in ? ($_SESSION['username'] ?? 'Геймер') : '';
?>

<style>
    :root {
        --bg: #0a0d18;
        --surface: #121a2a;
        --accent: #00e0ff;
        --text: #f0f9ff;
        --text-muted: #a5c0ff;
        --border: #1e2f4e;
        --shadow: 0 10px 30px rgba(0,0,0,0.45);
    }

    header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 80px;
        padding: 0 5%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(10,13,24,0.92);
        backdrop-filter: blur(16px);
        border-bottom: 1px solid var(--border);
        z-index: 1000;
        transition: all 0.35s ease;
    }

    header.scrolled {
        height: 70px;
        background: rgba(10,13,24,0.98);
        box-shadow: var(--shadow);
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 14px;
        text-decoration: none;
    }

    .logo-text {
        font-family: 'Orbitron', sans-serif;
        font-size: 1.9rem;
        font-weight: 700;
        background: linear-gradient(90deg, #ffffff, var(--accent));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .nav-auth {
        display: flex;
        align-items: center;
        gap: 1.2rem;
    }

    .btn {
        padding: 0.75rem 1.9rem;
        border-radius: 12px;
        font-family: 'Orbitron', sans-serif;
        text-decoration: none;
        transition: all 0.25s ease;
        cursor: pointer;
        font-size: 0.95rem;
    }

    .btn-auth {
        color: var(--text);
        border: 1.5px solid #2c3b5e;
        background: none;
    }

    .btn-auth:hover {
        border-color: var(--accent);
        background: rgba(0,224,255,0.08);
        color: var(--accent);
    }

    /* ==================== Профиль и выпадашка ==================== */
    .profile-container {
        position: relative;
    }

    .profile-btn {
        display: flex;
        align-items: center;
        gap: 12px;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0.65rem 1rem;
        border-radius: 12px;
        color: var(--text);
        font-family: 'Inter', sans-serif;
        font-size: 0.97rem;
        transition: all 0.2s ease;
    }

    .profile-btn:hover {
        background: rgba(0,224,255,0.08);
        color: var(--accent);
    }

    .dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 8px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        min-width: 210px;
        overflow: hidden;
        box-shadow: var(--shadow);
        opacity: 0;
        visibility: hidden;
        transform: translateY(-8px);
        transition: all 0.22s ease;
        z-index: 100;
        pointer-events: none;
    }

    .profile-container:hover .dropdown-menu,
    .profile-container:focus-within .dropdown-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
        pointer-events: all;
    }

    .dropdown-item {
        display: block;
        padding: 0.85rem 1.3rem;
        color: var(--text-muted);
        text-decoration: none;
        font-family: 'Inter', sans-serif;
        font-size: 0.95rem;
        transition: all 0.2s ease;
    }

    .dropdown-item:hover {
        background: rgba(0,224,255,0.12);
        color: var(--accent);
    }

    .dropdown-item.logout {
        color: #ff7588;
    }

    .dropdown-item.logout:hover {
        background: rgba(255,117,136,0.12);
        color: #ff7588;
    }

    @media (max-width: 768px) {
        .logo-text { font-size: 1.45rem; }
        .btn { padding: 0.55rem 1.2rem; font-size: 0.85rem; }
        .profile-btn { padding: 0.5rem 0.9rem; }
        .nav-auth { gap: 0.8rem; }
    }
</style>

<header id="mainHeader">
    <a href="index.php" class="logo">
        <span class="logo-text">ЦИФРОВОЙ МИР</span>
    </a>

    <div class="nav-auth">
        <?php if ($is_logged_in): ?>
            <a href="catalog.php" class="btn btn-auth">КАТАЛОГ</a>

            <div class="profile-container">
                <button class="profile-btn">
                    <span>🎮 <?= htmlspecialchars($username) ?></span>
                </button>

                <div class="dropdown-menu">
                    <a href="profile.php" class="dropdown-item">Профиль</a>
                    <a href="cart.php" class="dropdown-item">Корзина</a>
                    <a href="logout.php" class="dropdown-item logout">Выйти</a>
                </div>
            </div>
        <?php else: ?>
            <a href="register.php" class="btn btn-auth">ЗАРЕГИСТРИРОВАТЬСЯ</a>
            <a href="login.php" class="btn btn-auth">ВОЙТИ</a>
        <?php endif; ?>
    </div>
</header>

<script>

    window.addEventListener('scroll', () => {
        const header = document.getElementById('mainHeader');
        header.classList.toggle('scrolled', window.scrollY > 50);
    });
</script>