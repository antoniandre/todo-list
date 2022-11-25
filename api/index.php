<?php

// MAIN.
// --------------------------------------------------------
$params = json_decode(file_get_contents('php://input'));
$endpoint = preg_replace('/^.*\/api\//', '', $_SERVER['REQUEST_URI']);

switch ($_SERVER['REQUEST_METHOD']) {
  case 'GET':
    if (!empty($endpoint)) getTask((int)$endpoint);
    else getTasks();
    break;
  case 'POST':
    addTask($params->label, $params->completed);
    break;
  case 'PUT':
    updateTask($params->id, $params->label ? $params->label : '', $params->completed);
    break;
  case 'DELETE':
    deleteTask($params->id);
    break;
  default:
    http_response_code(405);
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
function connectToDatabase() {
  $mysqli = new mysqli('127.0.0.1:3306', 'root', 'root', 'todo');

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
function getTasks () {
  $mysqli = connectToDatabase();
  $sql = "SELECT * FROM tasks";
  $result = $mysqli->query($sql);

  $rows = [];
  while ($object = $result->fetch_object()) {
    $object->id = (int)$object->id;
    $object->completed = (bool)$object->completed;
    array_push($rows, $object);
  }

  echo json_encode($rows);
  http_response_code(200);

  $mysqli->close();
}

/**
 * Get a task from the database given its id and output it as an object.
 *
 * @param integer $id the id of the task to get.
 * @return void
 */
function getTask(int $id) {
  $mysqli = connectToDatabase();
  $id = (int)$id;
  $sql = "SELECT * FROM `tasks` WHERE `id` = $id";
  $result = $mysqli->query($sql);
  $task = $result->fetch_object();

  if ($mysqli->error) return http_response_code(500);
  elseif (!$task) return http_response_code(404);

  $task->id = (int)$task->id;
  $task->completed = (int)$task->completed;

  echo json_encode($task);
  http_response_code(200);

  $mysqli->close();
}

/**
 * Create a task in the database.
 *
 * @param string $label the task label.
 * @param integer $completed: 0 or 1 for completed task.
 * @return void
 */
function addTask(string $label, int $completed = 0) {
  $mysqli = connectToDatabase();
  $label = $mysqli->real_escape_string($label);
  $completed = (int)$completed;
  $sql = "INSERT INTO `tasks` (`label`, `completed`) VALUES ('$label', $completed)";
  $mysqli->query($sql);

  http_response_code($mysqli->error ? 500 : 201);

  $mysqli->close();
}

/**
 * Delete a task from the database.
 *
 * @param integer $id the id of the task to delete.
 * @return void
 */
function deleteTask(int $id) {
  $mysqli = connectToDatabase();
  $id = (int)$id;
  $sql = "DELETE FROM `tasks` WHERE `id` = $id";
  $mysqli->query($sql);

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
function updateTask(int $id, string $label, int $completed) {
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

  // echo json_encode($task);
  http_response_code($mysqli->error ? 500 : 200);

  $mysqli->close();
}
// --------------------------------------------------------

?>
