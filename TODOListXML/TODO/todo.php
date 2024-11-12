<?php
$jsonFile = 'tasks.json';  // Файл, где будем хранить задачи в формате JSON

// Функция для загрузки XML файла и преобразования его в JSON (только если JSON еще не создан)
function xmlToJson($xmlFile, $jsonFile) {
    if (!file_exists($jsonFile) || filesize($jsonFile) == 0) {
        $xml = simplexml_load_file($xmlFile) or die("Viga: Ei saa XML-faili laadida");
        $tasks = ['tasks' => []];

        // Конвертируем XML задачи в массив
        foreach ($xml->task as $task) {
            $tasks['tasks'][] = [
                'id' => (string)$task['id'],
                'date' => (string)$task->date,
                'deadline' => (string)$task->deadline,
                'subject' => (string)$task->subject,
                'info' => (string)$task->info,
                'description' => (string)$task->description
            ];
        }

        // Сохраняем данные в JSON файл
        file_put_contents($jsonFile, json_encode($tasks, JSON_PRETTY_PRINT));
    }
}

// Преобразуем XML в JSON при первом запуске, если JSON файл не существует или пуст
xmlToJson('TODO.xml', $jsonFile);

// Обработка добавления новой задачи через POST запрос
if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST)) {
    $newTask = json_decode(file_get_contents('php://input'), true);
    if ($newTask) {
        $tasks = json_decode(file_get_contents($jsonFile), true);
        $tasks['tasks'][] = $newTask;
        file_put_contents($jsonFile, json_encode($tasks, JSON_PRETTY_PRINT));
        echo "Uus ülesanne lisatud!";
        exit;
    }
}

// Обработка кнопки для отображения JSON
if (isset($_GET['view']) && $_GET['view'] == 'json') {
    header('Content-Type: application/json');
    echo file_get_contents($jsonFile);
    exit;
}

// Обработка кнопки для отображения XML
if (isset($_GET['view']) && $_GET['view'] == 'xml') {
    header('Content-Type: text/xml');
    echo file_get_contents('TODO.xml');
    exit;
}
?>

<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TODO nimekiri</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function addTask() {
            const newTask = {
                id: document.getElementById("task_id").value,
                date: document.getElementById("task_date").value,
                deadline: document.getElementById("task_deadline").value,
                subject: document.getElementById("task_subject").value,
                info: document.getElementById("task_info").value,
                description: document.getElementById("task_description").value
            };

            fetch('todo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(newTask)
            }).then(response => response.text())
            .then(data => {
                alert(data);
                location.reload();
            });
        }
    </script>
</head>
<body>
    <h2>TODO nimekiri</h2>

    <!-- Кнопки для отображения JSON и XML в новой вкладке -->
    <button onclick="window.open('todo.php?view=json', '_blank')">Näita JSON-i</button>
    <button onclick="window.open('todo.php?view=xml', '_blank')">Näita XML-i</button>

    <br><br>

    <!-- Форма для добавления новой задачи в виде карточки -->
    <div class="form-card">
        <h3>Lisa uus ülesanne</h3>
        <label>ID: <input type="text" id="task_id"></label>
        <label>Kuupäev: <input type="text" id="task_date"></label>
        <label>Tähtaeg: <input type="text" id="task_deadline"></label>
        <label>Õppeaine: <input type="text" id="task_subject"></label>
        <label>Teave: <input type="text" id="task_info"></label>
        <label>Kirjeldus: <input type="text" id="task_description"></label>
        <button onclick="addTask()">Lisa ülesanne</button>
    </div>

    <br><br>

    <!-- Отображение задач в таблице -->
    <h3>Praegused ülesanded</h3>
    <?php
    if (file_exists($jsonFile)) {
        $tasks = json_decode(file_get_contents($jsonFile), true);
        echo "<table>
                <tr>
                    <th>ID</th>
                    <th>Kuupäev</th>
                    <th>Tähtaeg</th>
                    <th>Õppeaine</th>
                    <th>Teave</th>
                    <th>Kirjeldus</th>
                </tr>";

        foreach ($tasks['tasks'] as $task) {
            $deadline = strtotime($task['deadline']);
            $isOutdated = $deadline < time() ? "class='outdated'" : "";

            echo "<tr $isOutdated>
                    <td>{$task['id']}</td>
                    <td>{$task['date']}</td>
                    <td>{$task['deadline']}</td>
                    <td>{$task['subject']}</td>
                    <td>{$task['info']}</td>
                    <td>{$task['description']}</td>
                  </tr>";
        }

        echo "</table>";
    } else {
        echo "JSON fail puudub.";
    }
    ?>
</body>
</html>
