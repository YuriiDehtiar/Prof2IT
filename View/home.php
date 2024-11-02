<!-- View/home.php -->
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Головна</title>
</head>
<body>
    <h1><?php echo isset($message) ? htmlspecialchars($message) : 'Ласкаво просимо!'; ?></h1>

    <?php
    if (isset($_SESSION['message'])) {
        echo '<p>' . htmlspecialchars($_SESSION['message']) . '</p>';
        unset($_SESSION['message']);
    }
    ?>

    <a href="index.php?action=registerForm">Реєстрація</a> | 
    <a href="index.php?action=loginForm">Логін</a> | 
    <a href="index.php?action=listDino">Список динозаврів</a>
</body>
</html>
