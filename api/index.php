<?php

// MAIN.
// --------------------------------------------------------
$params = json_decode(file_get_contents('php://input'));
$endpoint = preg_replace('/^.*\/api\//', '', $_SERVER['REQUEST_URI']);

switch ($_SERVER['REQUEST_METHOD']) {
  case 'GET': // Get one or all tasks.
    if (!empty($endpoint)) getTask(is_numeric($endpoint) ? (int)$endpoint : 0);
    else getTasks();
    break;
  case 'POST': // Create a task.
    addTask($params->label, $params->completed);
    break;
  case 'PUT': // Update a task.
    updateTask($params->id, $params->label ?? '', (bool)$params->completed);
    break;
  case 'DELETE': // Delete a task.
    deleteTask($params->id);
    break;
  default:
    http_response_code(405); // Method not allowed.
    break;
}

header('Content-Type: application/json; charset=utf-8');
// --------------------------------------------------------

// Functions.
// --------------------------------------------------------
/**
 * Connect to the database and return the MySQLi object, or die in case of failure.
 *
 * @return \mysqli the MySQLi object.
 */
function connectToDatabase(): \mysqli {
  $config = (object)parse_ini_file('./config.ini');
  $mysqli = new mysqli(
    $config->databaseHost,
    $config->databaseUsername,
    $config->databasePassword,
    $config->databaseName
  );

  if ($mysqli->connect_error) {
    error_log($mysqli->connect_error);
    die('Could not connect to the database.');
  }

  return $mysqli;
}

/**
 * Get all the tasks from the database and outputs them in an array of objects.
 *
 * @return void
 */
function getTasks(): void {
  $mysqli = connectToDatabase();
  $sql = 'SELECT * FROM tasks';
  $result = $mysqli->query($sql);
  $error = false;
  $message = '';
  $code = 200;

  if ($mysqli->error) {
    $error = true;
    $message = 'Could not retrieve the tasks from the database.';
    $code = 500;
  }
  else {
    $rows = [];
    while ($object = $result->fetch_object()) {
      $object->id = (int)$object->id;
      $object->completed = (bool)$object->completed;
      array_push($rows, $object);
    }
  }

  echo json_encode((object)[
    'error' => $error,
    'message' => $message,
    'tasks' => $rows ?? null
  ]);
  http_response_code($code);

  $mysqli->close();
}

/**
 * Get a task from the database given its id and output it as an object.
 *
 * @param integer $id the id of the task to get.
 * @return bool
 */
function getTask(int $id): bool {
  $mysqli = connectToDatabase();
  $id = (int)$id;
  $sql = "SELECT * FROM `tasks` WHERE `id` = $id";
  $result = $mysqli->query($sql);
  $task = $result->fetch_object();
  $error = false;
  $message = '';
  $code = 200;

  if ($mysqli->error) {
    $error = true;
    $message = 'Could not read the task from the database.';
    $code = 500;
  }
  elseif (!$task) {
    $error = true;
    $message = 'The task was not found.';
    $code = 404;
  }
  else {
    $task->id = (int)$task->id;
    $task->completed = (bool)$task->completed;
  }

  echo json_encode((object)[
    'error' => $error,
    'message' => $message,
    'task' => $task ?? null
  ]);
  http_response_code($code);

  $mysqli->close();
  return true;
}

/**
 * Create a task in the database, and outputs the new task to the frontend.
 *
 * @param string $label the task label.
 * @param integer $completed: 0 or 1 for completed task.
 * @return void
 */
function addTask(string $label, int $completed = 0): void {
  $mysqli = connectToDatabase();
  $label = $mysqli->real_escape_string($label);
  $completed = (int)$completed;
  $sql = "INSERT INTO `tasks` (`label`, `completed`) VALUES ('$label', $completed)";
  $mysqli->query($sql);
  $code = 200;

  if ($mysqli->error) {
    echo json_encode((object)[
      'error' => true,
      'message' => 'The task could not be saved to the database.'
    ]);
    $code = 500;
    $mysqli->close();
  }
  else {
    $success = getTask($mysqli->insert_id); // This will output the task directly to the frontend.
    $code = $success ? 201 : 500; // Override the 200 from getTask.
  }

  http_response_code($code);
}

/**
 * Delete a task from the database.
 *
 * @param integer $id the id of the task to delete.
 * @return void
 */
function deleteTask(int $id): void {
  $mysqli = connectToDatabase();
  $id = (int)$id;
  $sql = "DELETE FROM `tasks` WHERE `id` = $id";
  $mysqli->query($sql);

  echo json_encode((object)[
    'error' => $mysqli->error,
    'message' => $mysqli->error ? 'The task could not be saved to the database.' : ''
  ]);
  http_response_code($mysqli->error ? 500 : 204);

  $mysqli->close();
}

/**
 * Update a task in the database.
 *
 * @param integer $id the id of the task to update.
 * @param string $label the new task label.
 * @param integer $completed: 0 or 1 for a completed task.
 * @return void
 */
function updateTask(int $id, string $label, int $completed): void {
  $mysqli = connectToDatabase();
  $id = (int)$id;
  $changes = [];
  if ($label !== '') {
    $label = $mysqli->real_escape_string($label);
    $changes[] = "`label` = '$label'";
  }
  if ($completed !== '') $changes[] = "`completed` = $completed";
  $sql = "UPDATE `tasks` SET " . implode(',', $changes) . " WHERE `id` = $id";

  $mysqli->query($sql);

  if ($mysqli->error) {
    echo json_encode((object)[
      'error' => true,
      'message' => 'The task could not be saved to the database.'
    ]);
    $mysqli->close();
  }
  else {
    $success = getTask($id); // This will output the task directly to the frontend.
    $code = $success ? 201 : 500; // Override the 200 from getTask.
  }

  http_response_code($code);
}
// --------------------------------------------------------

?>
