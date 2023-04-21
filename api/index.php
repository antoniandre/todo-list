<?php

// MAIN.
// --------------------------------------------------------
$params = json_decode(file_get_contents('php://input'));
$endpoint = preg_replace('/^.*\/api\//', '', $_SERVER['REQUEST_URI']);

switch ($_SERVER['REQUEST_METHOD']) {
  case 'GET': // Get one or all tasks.
    if (!empty($endpoint)) {
      list($code, $message, $task) = getTask(is_numeric($endpoint) ? (int)$endpoint : 0);
      $data = ['task' => $task ?? null];
    }
    else {
      list($code, $message, $tasks) = getTasks();
      $data = ['tasks' => $tasks ?? null];
    }

    if ($code >= 200 && $code < 300) {
      list($code, $message, $users) = getUsers();
      $data['users'] = $users;
    }
    break;
  case 'POST': // Create a task.
    list($code, $message, $task) = addTask($params->label, $params->completed);
    $data = ['task' => $task ?? null];
    break;
  case 'PUT': // Update a task.
    list($code, $message, $task) = updateTask($params->id, $params->label ?? '', (bool)$params->completed);
    $data = ['task' => $task ?? null];
    break;
  case 'DELETE': // Delete a task.
    list($code, $message) = deleteTask($params->id);
    break;
  default:
    $code = 405; // Method not allowed.
    $message = 'Method not allowed.';
    break;
}

output($code, $message, $data ?? []);
connectToDatabase()->close();
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
 * Outputs the JSON payload to the frontend with correct response code.
 *
 * @param integer $code the response code to send back to the frontend.
 * @param string $message a potential message in case of error.
 * @param array $data some data to send back to the frontend if any.
 * @return void
 */
function output(int $code, string $message, array $data = []): void {
  $output = ['error' => $code < 200 || $code >= 300, 'message' => $message];
  $output = array_merge($output, $data);

  echo json_encode((object)$output);
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
}

/**
 * Get all the tasks from the database and outputs them in an array of objects.
 *
 * @return array of [int $code, ?string $message, array|null $data]
 */
function getTasks(): array {
  $mysqli = connectToDatabase();
  $sql = 'SELECT * FROM tasks';
  $result = $mysqli->query($sql);

  if ($mysqli->error) {
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

  return [$code ?? 200, $message ?? '', $rows ?? null];
}

/**
 * Get a task from the database given its id and output it as an object.
 *
 * @param integer $id the id of the task to get.
 * @return array
 */
function getTask(int $id): array {
  $mysqli = connectToDatabase();
  $result = $mysqli->query("SELECT * FROM `tasks` WHERE `id` = " . (int)$id);
  $task = $result->fetch_object();

  if ($mysqli->error) {
    $message = 'Could not read the task from the database.';
    $code = 500;
  }
  elseif (!$task) {
    $message = 'The task was not found.';
    $code = 404;
  }
  else {
    $task->id = (int)$task->id;
    $task->completed = (bool)$task->completed;
  }

  return [$code ?? 200, $message ?? '', $task];
}

/**
 * Create a task in the database, and outputs the new task to the frontend.
 *
 * @param string $label the task label.
 * @param integer $completed: 0 or 1 for completed task.
 * @return array of [int $code, ?string $message, array|null $data]
 */
function addTask(string $label, int $completed = 0): array {
  $mysqli = connectToDatabase();
  $label = $mysqli->real_escape_string($label);
  $completed = (int)$completed;
  $mysqli->query("INSERT INTO `tasks` (`label`, `completed`) VALUES ('$label', $completed)");

  if ($mysqli->error) {
    $message = 'The task could not be saved in the database.';
    $code = 500;
  }
  else list($code, $message, $task) = getTask($mysqli->insert_id);

  return [$code, $message, $task ?? null];
}

/**
 * Delete a task from the database.
 *
 * @param integer $id the id of the task to delete.
 * @return array of [int $code, ?string $message, array|null $data]
 */
function deleteTask(int $id): array {
  $mysqli = connectToDatabase();
  $mysqli->query("DELETE FROM `tasks` WHERE `id` = ". (int)$id);

  return [$mysqli->error ? 500 : 204, $mysqli->error ? 'The task could not be deleted from the database.' : ''];
}

/**
 * Update a task in the database.
 *
 * @param integer $id the id of the task to update.
 * @param string $label the new task label.
 * @param integer $completed: 0 or 1 for a completed task.
 * @return array of [int $code, ?string $message, array|null $data]
 */
function updateTask(int $id, string $label, int $completed): array {
  $mysqli = connectToDatabase();
  $id = (int)$id;
  $changes = [];
  if ($label !== '') {
    $label = $mysqli->real_escape_string($label);
    $changes[] = "`label` = '$label'";
  }
  if ($completed !== '') $changes[] = "`completed` = $completed";

  $mysqli->query("UPDATE `tasks` SET " . implode(',', $changes) . " WHERE `id` = $id");

  if ($mysqli->error) {
    $message = 'The task could not be saved in the database.';
    $code = 500;
  }
  else {
    list($code, $message, $task) = getTask($id);
    $code = $code === 200 ? 201 : 500; // Override the 200 from getTask.
  }

  return [$code, $message, $task ?? null];
}

/**
 * Get all the users from the database and outputs them in an array of objects.
 *
 * @return array of [int $code, ?string $message, array|null $data]
 */
function getUsers(): array {
  $mysqli = connectToDatabase();
  $result = $mysqli->query('SELECT * FROM users');

  if ($mysqli->error) {
    $message = 'Could not retrieve the users from the database.';
    $code = 500;
  }
  else {
    $rows = [];
    while ($object = $result->fetch_object()) {
      $object->id = (int)$object->id;
      array_push($rows, $object);
    }
  }

  return [$code ?? 200, $message ?? '', $rows ?? []];
}

// --------------------------------------------------------

?>
