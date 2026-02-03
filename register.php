<?php
include 'db.php';
session_start();

$errors = [];
$success = false;
$form_data = ['first_name' => '', 'last_name' => '', 'username' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data['first_name'] = trim($_POST['first_name'] ?? '');
    $form_data['last_name']  = trim($_POST['last_name']  ?? '');
    $form_data['username']   = trim($_POST['username']   ?? '');
    $password                = $_POST['password']        ?? '';

    if (empty($form_data['first_name']))   $errors[] = "Укажите имя";
    if (empty($form_data['last_name']))    $errors[] = "Укажите фамилию";
    if (empty($form_data['username']))     $errors[] = "Придумайте логин";
    if (strlen($form_data['username']) < 4) $errors[] = "Логин минимум 4 символа";
    if (empty($password))                  $errors[] = "Введите пароль";
    if (strlen($password) < 6)             $errors[] = "Пароль минимум 6 символов";

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $form_data['username']);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = "Этот логин уже занят";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("
                INSERT INTO users (first_name, last_name, username, password)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("ssss", $form_data['first_name'], $form_data['last_name'], $form_data['username'], $hash);

            if ($stmt->execute()) {
                $success = true;
            } else {
                $errors[] = "Ошибка сервера. Попробуйте позже.";
            }
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
    <title>Регистрация | IT-Universe</title>
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
            --success: #00ff9d;
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
            padding: 100px 1rem 40px; /* запас сверху + снизу */
        }

        .auth-wrapper {
            width: 100%;
            max-width: 480px;
            background: var(--surface);
            border: 1px solid #1f1f2e;
            border-radius: 20px;
            padding: 3.6rem 2.8rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.65),
                        0 0 40px rgba(0,229,255,0.12);
            text-align: center;
        }

        h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.6rem;
            margin-bottom: 2.4rem;
            background: linear-gradient(90deg, #ffffff, var(--accent), #d07fff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 20px var(--accent-glow);
        }

        .field {
            margin-bottom: 1.9rem;
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
            padding: 1.15rem 1.4rem;
            border: 1px solid #2a2a3f;
            border-radius: 10px;
            background: #0d0d16;
            color: white;
            font-size: 1.1rem;
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
            gap: 1.4rem;               /* ← расстояние между кнопками */
            margin-top: 2.2rem;
        }

        .btn {
            width: 100%;
            padding: 1.2rem;
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

        .message {
            padding: 1.2rem;
            border-radius: 10px;
            margin: 1.6rem 0;
            font-size: 1.08rem;
        }

        .error {
            background: rgba(255,107,129,0.12);
            border: 1px solid rgba(255,107,129,0.35);
            color: var(--error);
        }

        .success {
            background: rgba(0,255,157,0.12);
            border: 1px solid rgba(0,255,157,0.35);
            color: var(--success);
        }

        @media (max-width: 480px) {
            .auth-wrapper { padding: 2.8rem 2rem; }
            h1 { font-size: 2.2rem; }
            .buttons { gap: 1.2rem; }
        }
    </style>
</head>
<body>

<div class="auth-wrapper">

    <h1>Новичок?<br>Создай аккаунт</h1>

    <?php if ($success): ?>
        <div class="message success">
            Отлично! Регистрация завершена.<br>
            Теперь можно <a href="login.php" style="color:var(--accent); font-weight:500;">войти</a>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="message error">
            <?php foreach ($errors as $err): ?>
                <div>✗ <?= htmlspecialchars($err) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form method="post" novalidate>
        <div class="field">
            <label>Имя</label>
            <input type="text" name="first_name" required value="<?= htmlspecialchars($form_data['first_name']) ?>">
        </div>

        <div class="field">
            <label>Фамилия</label>
            <input type="text" name="last_name" required value="<?= htmlspecialchars($form_data['last_name']) ?>">
        </div>

        <div class="field">
            <label>Логин</label>
            <input type="text" name="username" required value="<?= htmlspecialchars($form_data['username']) ?>" autocomplete="username">
        </div>

        <div class="field">
            <label>Пароль</label>
            <input type="password" name="password" required autocomplete="new-password">
        </div>

        <div class="buttons">
            <button type="submit" class="btn btn-primary">ЗАРЕГИСТРИРОВАТЬСЯ</button>
            <a href="index.php" class="btn btn-back">НАЗАД</a>
        </div>
    </form>
    <?php endif; ?>

</div>

</body>
</html>