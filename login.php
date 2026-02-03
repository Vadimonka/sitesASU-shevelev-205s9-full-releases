<?php
include 'db.php';
session_start();

$error = '';
$username_input = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $username_input = $username;

    if (empty($username) || empty($password)) {
        $error = "Заполните логин и пароль";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id']  = $row['id'];
                $_SESSION['username'] = $row['username'];
                header("Location: index.php");
                exit;
            } else {
                $error = "Неверный пароль";
            }
        } else {
            $error = "Такого логина не существует";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход | IT-Universe</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent: #00e5ff;
            --accent-glow: rgba(0,229,255,0.55);
            --bg: #0a0a0f;
            --surface: #111118;
            --text: #e0f7ff;
            --text-muted: #a0c0ff;
            --error: #ff6b81;
        }

        * { box-sizing: border-box; margin:0; padding:0; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Roboto', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 100px 1rem 40px; /* запас сверху на случай фиксированной шапки */
        }

        .auth-wrapper {
            width: 100%;
            max-width: 460px;
            background: var(--surface);
            border: 1px solid #1f1f2e;
            border-radius: 20px;
            padding: 3.5rem 2.6rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.65),
                        0 0 40px rgba(0,229,255,0.12);
            text-align: center;
        }

        h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.6rem;
            margin-bottom: 2.2rem;
            background: linear-gradient(90deg, #ffffff, var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 20px var(--accent-glow);
        }

        .field {
            margin-bottom: 1.8rem;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 0.7rem;
            font-size: 1rem;
            color: var(--text-muted);
        }

        input {
            width: 100%;
            padding: 1.1rem 1.4rem;
            border: 1px solid #2a2a3f;
            border-radius: 10px;
            background: #0d0d16;
            color: white;
            font-size: 1.08rem;
            transition: all 0.25s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 14px rgba(0,229,255,0.4);
        }

        .buttons {
            display: flex;
            flex-direction: column;
            gap: 1.4rem;          /* ← основной отступ между кнопками */
            margin-top: 2rem;
        }

        .btn {
            width: 100%;
            padding: 1.15rem;
            border-radius: 10px;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.12rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--accent), #00b8cc);
            color: #000;
            border: none;
            box-shadow: 0 8px 25px rgba(0,229,255,0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(0,229,255,0.55);
        }

        .btn-back {
            background: transparent;
            border: 1px solid #444466;
            color: var(--text-muted);
        }

        .btn-back:hover {
            background: #1a1a24;
            color: white;
        }

        .error {
            background: rgba(255,107,129,0.12);
            border: 1px solid rgba(255,107,129,0.35);
            color: var(--error);
            padding: 1.2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            font-size: 1.05rem;
        }

        @media (max-width: 480px) {
            .auth-wrapper { padding: 2.8rem 2rem; }
            h1 { font-size: 2.3rem; }
            .buttons { gap: 1.2rem; }
        }
    </style>
</head>
<body>

<div class="auth-wrapper">

    <h1>Рады видеть тебя снова!</h1>

    <?php if ($error): ?>
        <div class="error">✗ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
        <div class="field">
            <label>Логин</label>
            <input type="text" name="username" required value="<?= htmlspecialchars($username_input) ?>" autocomplete="username">
        </div>

        <div class="field">
            <label>Пароль</label>
            <input type="password" name="password" required autocomplete="current-password">
        </div>

        <div class="buttons">
            <button type="submit" class="btn btn-primary">ВОЙТИ</button>
            <a href="index.php" class="btn btn-back">НАЗАД</a>
        </div>
    </form>

</div>

</body>
</html>