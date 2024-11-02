<!-- View/dashboard.php -->
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Особистий Кабінет</title>
    <style>
        /* Додайте базові стилі за потребою */
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .navbar {
            margin-bottom: 20px;
        }
        .navbar a {
            margin-right: 15px;
            text-decoration: none;
            color: #007BFF;
        }
        .navbar a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Особистий Кабінет</h1>
    <p>Вітаємо, <?php echo isset($username) ? htmlspecialchars($username) : 'Користувачу'; ?>!</p>
    <?php
    // Відображення повідомлення з сесії
    if (isset($_SESSION['message'])) {
        echo '<p style="color: green;">' . htmlspecialchars($_SESSION['message']) . '</p>';
        unset($_SESSION['message']);
    }
    ?>
    
    <div class="navbar">
        <a href="index.php?action=listDino">Список динозаврів</a>
        <a href="index.php?action=logout">Вийти</a>
    </div>
    
    
    
    <!-- Додайте інші секції вашого дашборду тут -->
</body>
</html>
