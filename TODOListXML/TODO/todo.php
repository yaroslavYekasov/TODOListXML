<?php
// Ensure no whitespace or output before this line
$jsonFile = 'tasks.json';

// Function to load XML and convert to JSON
function xmlToJson($xmlFile, $jsonFile) {
    if (!file_exists($jsonFile) || filesize($jsonFile) == 0) {
        $xml = simplexml_load_file($xmlFile) or die("Viga: Ei saa XML-faili laadida");
        $tasks = ['tasks' => []];

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

        file_put_contents($jsonFile, json_encode($tasks, JSON_PRETTY_PRINT));
    }
}

xmlToJson('TODO.xml', $jsonFile);

// Function to generate a unique ID
function generateUniqueId($tasks) {
    $ids = array_column($tasks['tasks'], 'id');
    return count($ids) > 0 ? max($ids) + 1 : 1;
}

// Handle adding a task via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST)) {
    $newTask = json_decode(file_get_contents('php://input'), true);
    if ($newTask) {
        $tasks = json_decode(file_get_contents($jsonFile), true);
        $newTask['id'] = generateUniqueId($tasks);
        $tasks['tasks'][] = $newTask;
        file_put_contents($jsonFile, json_encode($tasks, JSON_PRETTY_PRINT));
        echo "Uus ülesanne lisatud";
        exit;
    }
}

// Handle sorting if `sort` and `order` parameters are set
if (isset($_GET['sort']) && isset($_GET['order'])) {
    $tasks = json_decode(file_get_contents($jsonFile), true);

    $sortColumn = $_GET['sort']; // Column to sort by
    $sortOrder = $_GET['order']; // asc or desc

    usort($tasks['tasks'], function ($a, $b) use ($sortColumn, $sortOrder) {
        if ($sortOrder === 'asc') {
            return strcmp($a[$sortColumn], $b[$sortColumn]);
        } else {
            return strcmp($b[$sortColumn], $a[$sortColumn]);
        }
    });

    // Save the sorted tasks back into JSON
    file_put_contents($jsonFile, json_encode($tasks, JSON_PRETTY_PRINT));
}

// Handle JSON view
if (isset($_GET['view']) && $_GET['view'] === 'json') {
    header('Content-Type: application/json');
    echo file_get_contents($jsonFile);
    exit;
}

// Handle XML view
if (isset($_GET['view']) && $_GET['view'] === 'xml') {
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
</head>
<body>
<h2>TODO nimekiri</h2>

<!-- Buttons to view JSON and XML -->
<button onclick="window.open('todo.php?view=json', '_blank')">Näita JSON-i</button>
<button onclick="window.open('todo.php?view=xml', '_blank')">Näita XML-i</button>

<br><br>

<!-- Form for adding a new task -->
<div class="form-card">
    <h3>Lisa uus ülesanne</h3>
    <label>Kuupäev: <input type="date" id="task_date"></label>
    <label>Tähtaeg: <input type="date" id="task_deadline"></label>
    <label>Õppeaine: <input type="text" id="task_subject"></label>
    <label>Teave: <input type="text" id="task_info"></label>
    <label>Kirjeldus: <input type="text" id="task_description"></label>
    <button onclick="addTask()">Lisa ülesanne</button>
</div>

<br><br>

<!-- Display tasks in a table -->
<h3>Praegused ülesanded</h3>
<table>
    <thead>
    <tr>
        <th><a href="?sort=date&order=asc">Kuupäev ↑</a> | <a href="?sort=date&order=desc">↓</a></th>
        <th><a href="?sort=deadline&order=asc">Tähtaeg ↑</a> | <a href="?sort=deadline&order=desc">↓</a></th>
        <th><a href="?sort=subject&order=asc">Õppeaine ↑</a> | <a href="?sort=subject&order=desc">↓</a></th>
        <th>Teave</th>
        <th>Kirjeldus</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (file_exists($jsonFile)) {
        $tasks = json_decode(file_get_contents($jsonFile), true);

        foreach ($tasks['tasks'] as $task) {
            $deadline = strtotime($task['deadline']);
            $isOutdated = $deadline < time() ? "class='outdated'" : "";

            echo "<tr $isOutdated>
                        <td>{$task['date']}</td>
                        <td>{$task['deadline']}</td>
                        <td>{$task['subject']}</td>
                        <td>{$task['info']}</td>
                        <td>{$task['description']}</td>
                      </tr>";
        }
    } else {
        echo "<tr><td colspan='5'>JSON fail puudub.</td></tr>";
    }
    ?>
    </tbody>
</table>

<script>
    function addTask() {
        const newTask = {
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
</body>
</html>
