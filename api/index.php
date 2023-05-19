<?php

// Autoload the PHP classes.
spl_autoload_register(function ($className) {
  include __DIR__ . '/classes/' . strtolower($className) . '.php';
});

// MAIN.
// --------------------------------------------------------
$params = json_decode(file_get_contents('php://input'));
$endpoint = preg_replace('/^.*\/api\//', '', $_SERVER['REQUEST_URI']);

switch ($_SERVER['REQUEST_METHOD']) {
  case 'GET': // Get one or all tasks.
    if (!empty($endpoint)) {
      try {
        $task = Task::get(is_numeric($endpoint) ? (int)$endpoint : 0);
        $code = 200;
      }
      catch (Exception $e) {
        $code = $e->getCode();
        $message = $e->getMessage();
      }
      $data = ['task' => $task ?? null];
    }
    else {
      try {
        $tasks = Task::getAll();
        $code = 200;
      }
      catch (Exception $e) {
        $code = $e->getCode();
        $message = $e->getMessage();
      }
      $data = ['tasks' => $tasks ?? null];
    }

    if ($code === 200) {
      try {
        $users = User::getAll();
      }
      catch (Exception $e) {
        $code = $e->getCode();
        $message = $e->getMessage();
      }
      $data['users'] = $users ?? [];
    }
    break;
  case 'POST': // Create a task.
    try {
      $task = new Task($params->label, $params->description, $params->completed, $params->assignee ?? null);
      $task = $task->save();
    }
    catch (Exception $e) {
      $code = $e->getCode();
      $message = $e->getMessage();
    }
    $data = ['task' => $task ?? null];
    break;
  case 'PUT': // Update a task.
    try {
      $task = Task::get($params->id);
      $task = $task->update($params->label, $params->description, $params->completed, $params->assignee);
    }
    catch (Exception $e) {
      $code = $e->getCode();
      $message = $e->getMessage();
    }
    $data = ['task' => $task ?? null];
    break;
  case 'DELETE': // Delete a task.
    try {
      Task::deleteById($params->id);
    }
    catch (Exception $e) {
      $code = $e->getCode();
      $message = $e->getMessage();
    }
    break;
  default:
    $code = 405; // Method not allowed.
    $message = 'Method not allowed.';
    break;
}

output($code ?? 200, $message ?? '', $data ?? []);
Database::get()->close();
// --------------------------------------------------------

// Functions.
// --------------------------------------------------------
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
// --------------------------------------------------------

?>
