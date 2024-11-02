<!-- View/dinoList.php -->
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Список динозаврів</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #333;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        .message {
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #f2f2f2;
        }
        .add-form {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #fafafa;
        }
    </style>
</head>
<body>
    <h1>Список динозаврів</h1>

    <?php
    // Відображення повідомлення з сесії
    if (isset($_SESSION['message'])) {
        echo "<div class='message'>" . htmlspecialchars($_SESSION['message']) . "</div>";
        unset($_SESSION['message']);
    }
    ?>

    <!-- Форма для додавання динозавра -->
    <div class="add-form">
        <h2>Додати динозавра</h2>
        <form action="index.php?action=addDino" method="POST">
            <label for="dinoName">Ім'я динозавра:</label><br>
            <input type="text" id="dinoName" name="dinoName" required><br><br>

            <label for="dinoType">Тип динозавра:</label><br>
            <input type="text" id="dinoType" name="dinoType" required><br><br>

            <label for="dinoColor">Колір динозавра:</label><br>
            <input type="text" id="dinoColor" name="dinoColor" required><br><br>

            <button type="submit">Додати динозавра</button>
        </form>
    </div>

    <!-- Список динозаврів -->
    <h2>Список динозаврів</h2>
    <?php
    // Використовуємо змінну $dinos, яка передана з контролера
    if (!empty($dinos)) {
        echo "<table>
                <tr>
                    <th>ID</th>
                    <th>Ім'я</th>
                    <th>Тип</th>
                    <th>Колір</th>
                </tr>";
        foreach ($dinos as $dino) {
            echo "<tr>
                    <td>" . htmlspecialchars($dino['idDino']) . "</td>
                    <td>" . htmlspecialchars($dino['Dino_Name']) . "</td>
                    <td>" . htmlspecialchars($dino['Dino_Type']) . "</td>
                    <td>" . htmlspecialchars($dino['Color_Name']) . "</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Немає записів.</p>";
    }
    ?>
</body>
</html>
