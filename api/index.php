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
