<?php

include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$user_id = $_SESSION['user_id'];
$success_msg = ''; $error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_bio = trim($_POST['bio']);
    $dir = 'uploads/avatars/';
    if (!is_dir($dir)) { mkdir($dir, 0777, true); }
    if (!empty($_FILES['avatar_file']['name'])) {
        $file = $_FILES['avatar_file'];
        $allow_types = ['image/jpeg', 'image/png', 'image/webp'];
        $file_type = mime_content_type($file['tmp_name']);
        if (in_array($file_type, $allow_types) && $file['size'] < 2000000) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = "avatar_" . $user_id . "_" . time() . "." . $ext;
            $upload_path = $dir . $new_filename;
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $stmt = $conn->prepare("UPDATE users SET bio = ?, avatar = ? WHERE id = ?");
                $stmt->bind_param("ssi", $new_bio, $upload_path, $user_id);
                $stmt->execute();
                $success_msg = "ПРОФИЛЬ ОБНОВЛЕН";
            }
        } else { $error_msg = "ОШИБКА ФАЙЛА"; }
    } else {
        $stmt = $conn->prepare("UPDATE users SET bio = ? WHERE id = ?");
        $stmt->bind_param("si", $new_bio, $user_id);
        $stmt->execute();
        $success_msg = "Изменения сохранены";
    }
}
$stmt = $conn->prepare("SELECT username, avatar, bio FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$has_avatar = !empty($user_data['avatar']) && file_exists($user_data['avatar']);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>USER_PROFILE // <?= $user_data['username'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #05060a;
            --card: #0d1117;
            --accent: #00e0ff;
            --accent-dim: rgba(0, 224, 255, 0.15);
            --border: rgba(255, 255, 255, 0.1);
            --text-main: #ffffff;
            --text-dim: #8b949e;
        }

        body {
            background-color: var(--bg);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-image: 
                radial-gradient(circle at 10% 10%, var(--accent-dim) 0%, transparent 30%),
                radial-gradient(circle at 90% 90%, var(--accent-dim) 0%, transparent 30%);
        }

        .profile-container {
            width: 100%;
            max-width: 440px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 40px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
            text-align: center;
        }

        /* Аватар с кнопкой быстрой загрузки */
        .avatar-section {
            position: relative;
            width: 140px;
            height: 140px;
            margin: 0 auto 30px;
        }

        .avatar-main {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 3px solid var(--accent);
            padding: 4px;
            background: var(--bg);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
        }

        .avatar-main img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .avatar-placeholder {
            font-family: 'Orbitron';
            font-size: 3.5rem;
            color: var(--accent);
        }

        /* Кнопка "Плюс" поверх аватара */
        .upload-badge {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: var(--accent);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 4px solid var(--card);
            color: #000;
            font-weight: bold;
            font-size: 20px;
            transition: 0.2s;
        }

        .upload-badge:hover {
            transform: scale(1.1);
            filter: brightness(1.2);
        }

        .username {
            font-family: 'Orbitron';
            font-size: 1.8rem;
            font-weight: 900;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }

        .user-status {
            font-size: 0.75rem;
            color: var(--accent);
            font-family: 'Orbitron';
            margin-bottom: 25px;
            opacity: 0.8;
        }

        /* Блок BIO */
        .bio-display {
            background: rgba(0,0,0,0.2);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            text-align: left;
            margin-bottom: 30px;
        }

        .label {
            font-family: 'Orbitron';
            font-size: 0.6rem;
            color: var(--text-dim);
            display: block;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .bio-text {
            font-size: 0.95rem;
            line-height: 1.6;
            color: #d1d5db;
        }

        /* Поля ввода */
        .input-group {
            text-align: left;
            margin-bottom: 20px;
        }

        textarea {
            width: 100%;
            background: #161b22;
            border: 1px solid var(--border);
            border-radius: 12px;
            color: #fff;
            padding: 15px;
            font-family: inherit;
            resize: none;
            box-sizing: border-box;
            transition: 0.3s;
        }

        textarea:focus {
            outline: none;
            border-color: var(--accent);
            background: #1c2128;
        }

        .save-button {
            width: 100%;
            background: var(--accent);
            color: #000;
            border: none;
            padding: 18px;
            border-radius: 14px;
            font-family: 'Orbitron';
            font-weight: 900;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
            text-transform: uppercase;
        }

        .save-button:hover {
            box-shadow: 0 0 30px rgba(0, 224, 255, 0.4);
            transform: translateY(-2px);
        }

        .alert {
            padding: 12px;
            border-radius: 10px;
            font-size: 0.8rem;
            font-family: 'Orbitron';
            margin-bottom: 20px;
        }
        .success { background: rgba(0, 224, 255, 0.1); color: var(--accent); border: 1px solid var(--accent); }

        .back-nav {
            margin-top: 25px;
            display: block;
            color: var(--text-dim);
            text-decoration: none;
            font-size: 0.8rem;
            transition: 0.2s;
        }
        .back-nav:hover { color: var(--accent); }
    </style>
</head>
<body>

<div class="profile-container">
    <?php if($success_msg): ?>
        <div class="alert success"><?= $success_msg ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="avatar-section">
            <div class="avatar-main">
                <?php if($has_avatar): ?>
                    <img src="<?= $user_data['avatar'] ?>" id="preview">
                <?php else: ?>
                    <div class="avatar-placeholder"><?= mb_substr($user_data['username'], 0, 1) ?></div>
                <?php endif; ?>
            </div>
            <label class="upload-badge" title="Сменить аватар">
                +
                <input type="file" name="avatar_file" style="display:none" onchange="previewImage(this)">
            </label>
        </div>

        <div class="username"><?= htmlspecialchars($user_data['username']) ?></div>
        <div class="user-status"></div>

        <div class="bio-display">
            <span class="label">Описание</span>
            <div class="bio-text">
                <?= $user_data['bio'] ? nl2br(htmlspecialchars($user_data['bio'])) : "Информации нет..." ?>
            </div>
        </div>

        <div class="input-group">
            <span class="label">Обновить описание</span>
            <textarea name="bio" rows="3" placeholder="Расскажите о себе..."><?= htmlspecialchars($user_data['bio']) ?></textarea>
        </div>

        <button type="submit" name="update_profile" class="save-button">Сохранить</button>
    </form>

    <a href="index.php" class="back-nav">← Вернуться в систему</a>
</div>

<script>
    
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                const avatarMain = document.querySelector('.avatar-main');
                avatarMain.innerHTML = '<img src="' + e.target.result + '" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

</body>
</html>