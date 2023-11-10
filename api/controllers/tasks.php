<?php

User::authenticate();

define('ROUTES', [
  'get:tasks' => 'getAllTasks',
  'get:tasks/{id}' => 'getTask',
  'post:tasks' => 'createTask',
  'put:tasks' => 'updateTask',
  'delete:tasks' => 'deleteTask'
]);

function getAllTasks () {
  try {
    $tasks = Task::getAll();
    $code = 200;
  }
  catch (Exception $e) {
    $code = $e->getCode();
    $message = $e->getMessage();
  }
  $data = ['tasks' => $tasks ?? null];

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

  return [$code, $message ?? '', $data];
}

function getTask ($id) {
  try {
    $task = Task::get(is_numeric($id) ? (int)$id : 0);
    $code = 200;
  }
  catch (Exception $e) {
    $code = $e->getCode();
    $message = $e->getMessage();
  }

  $data = ['task' => $task ?? null];

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

  return [$code ?? 200, $message ?? '', $data];
}

function createTask () {
  try {
    $task = new Task(
      INPUT->label ?? '',
      INPUT->description ?? '',
      INPUT->completed ?? false,
      INPUT->assignee ?? null
    );
    $task = $task->save();
  }
  catch (Exception $e) {
    $code = $e->getCode();
    $message = $e->getMessage();
  }
  $data = ['task' => $task ?? null];

  return [$code ?? 200, $message ?? '', $data];
}

function deleteTask () {
  try {
    Task::deleteById(INPUT->id);
  }
  catch (Exception $e) {
    $code = $e->getCode();
    $message = $e->getMessage();
  }

  return [$code ?? 200, $message ?? '', null];
}

function updateTask () {
  try {
    $task = Task::get(INPUT->id);
    $task = $task->update(INPUT->label, INPUT->description, INPUT->completed, INPUT->assignee);
  }
  catch (Exception $e) {
    $code = $e->getCode();
    $message = $e->getMessage();
  }
  $data = ['task' => $task ?? null];

  return [$code ?? 200, $message ?? '', $data];
}

?>
