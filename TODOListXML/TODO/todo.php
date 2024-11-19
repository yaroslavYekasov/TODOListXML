<?php
$jsonFile = 'tasks.json';  // TEST commit

// Function to load XML file and convert it to JSON (only if JSON doesn't exist or is empty)
function xmlToJson($xmlFile, $jsonFile) {
    if (!file_exists($jsonFile) || filesize($jsonFile) == 0) {
        $xml = simplexml_load_file($xmlFile) or die("Viga: Ei saa XML-faili laadida");
        $tasks = ['tasks' => []];

        // Convert XML tasks into an array
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

        // Save the data into the JSON file
        file_put_contents($jsonFile, json_encode($tasks, JSON_PRETTY_PRINT));
    }
}

// Convert XML to JSON on the first run, if the JSON file doesn't exist or is empty
xmlToJson('TODO.xml', $jsonFile);

// Function to generate a unique ID
function generateUniqueId($tasks) {
    $ids = array_column($tasks['tasks'], 'id');
    return count($ids) > 0 ? max($ids) + 1 : 1;
}

// Handle adding a new task via POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST)) {
    $newTask = json_decode(file_get_contents('php://input'), true);
    if ($newTask) {
        $tasks = json_decode(file_get_contents($jsonFile), true);

        // Generate a new unique ID for the task
        $newTask['id'] = generateUniqueId($tasks);

        // Add the new task
        $tasks['tasks'][] = $newTask;
        file_put_contents($jsonFile, json_encode($tasks, JSON_PRETTY_PRINT));

        echo "Uus ülesanne lisatud";
        exit;
    }
}

// Handle sorting and return JSON if requested
if (isset($_GET['sort']) && isset($_GET['view']) && $_GET['view'] === 'json') {
    $tasks = json_decode(file_get_contents($jsonFile), true);
    $sortColumn = $_GET['sort'];

    usort($tasks['tasks'], function ($a, $b) use ($sortColumn) {
        return strcmp($a[$sortColumn], $b[$sortColumn]);
    });

    if (isset($_GET['order']) && $_GET['order'] === 'desc') {
        $tasks['tasks'] = array_reverse($tasks['tasks']);
    }

    // Return the sorted tasks as JSON
    header('Content-Type: application/json');
    echo json_encode($tasks);
    exit;
}

// Handle viewing JSON or XML data
if (isset($_GET['view']) && $_GET['view'] == 'json') {
    header('Content-Type: application/json');
    echo file_get_contents($jsonFile);
    exit;
}
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

        function sortTable(column) {
            const currentUrl = new URL(window.location.href);
            const currentSort = currentUrl.searchParams.get("sort");
            const currentOrder = currentUrl.searchParams.get("order") || "asc";

            const newOrder = currentSort === column && currentOrder === "asc" ? "desc" : "asc";

            // Use fetch to load sorted tasks without reloading the page
            fetch(`todo.php?sort=${column}&order=${newOrder}&view=json`)
                .then(response => response.json())
                .then(data => {
                    const tableBody = document.querySelector("table tbody");
                    tableBody.innerHTML = ""; // Clear current table rows

                    // Append sorted tasks to the table
                    data.tasks.forEach(task => {
                        const deadline = new Date(task.deadline);
                        const isOutdated = deadline < new Date() ? "outdated" : "";

                        tableBody.innerHTML += `
                            <tr class="${isOutdated}">
                                <td>${task.date}</td>
                                <td>${task.deadline}</td>
                                <td>${task.subject}</td>
                                <td>${task.info}</td>
                                <td>${task.description}</td>
                            </tr>`;
                    });
                });
        }
    </script>
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
    <label>Kuupäev: <input type="text" id="task_date"></label>
    <label>Tähtaeg: <input type="text" id="task_deadline"></label>
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
        <th onclick="sortTable('date')" class="sortable">Kuupäev</th>
        <th onclick="sortTable('deadline')" class="sortable">Tähtaeg</th>
        <th onclick="sortTable('subject')" class="sortable">Õppeaine</th>
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
</body>
</html>
