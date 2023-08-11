<?php

switch ($requestMethod) {
  case 'get': // Get one or all tasks.
    if (!empty($action)) {
      try {
        $task = Task::get(is_numeric($action) ? (int)$action : 0);
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
  case 'post': // Create a task.
    try {
      $task = new Task(
        $params->label ?? '',
        $params->description ?? '',
        $params->completed ?? false,
        $params->assignee ?? null
      );
      $task = $task->save();
    }
    catch (Exception $e) {
      $code = $e->getCode();
      $message = $e->getMessage();
    }
    $data = ['task' => $task ?? null];
    break;
  case 'put': // Update a task.
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
  case 'delete': // Delete a task.
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

return [$code ?? 0, $message ?? '', $data ?? null];

?>
