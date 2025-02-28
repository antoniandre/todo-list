<?php

// @todo: This causes to not send the payload on task/87 when the response code is 404.
// User::authenticate();

define('ENDPOINTS', [
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

  output($code, $message ?? '', $data) && exit;
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

  output($code ?? 200, $message ?? '', $data) && exit;
}

function createTask () {
  try {
    $task = new Task(
      INPUT->label ?? '',
      INPUT->description ?? '',
      INPUT->status ?? 'todo',
      INPUT->assignee ?? null
    );
    $task = $task->save();
  }
  catch (Exception $e) {
    $code = $e->getCode();
    $message = $e->getMessage();
  }
  $data = ['task' => $task ?? null];

  output($code ?? 200, $message ?? '', $data) && exit;
}

function deleteTask () {
  try {
    Task::deleteById(INPUT->id);
  }
  catch (Exception $e) {
    $code = $e->getCode();
    $message = $e->getMessage();
  }

  output($code ?? 200, $message ?? '', null) && exit;
}

function updateTask () {
  try {
    $task = Task::get(INPUT->id);
    $task = $task->update(
      INPUT->label ?? null,
      INPUT->description ?? null,
      INPUT->status ?? 'todo',
      INPUT->assignee ?? null
    );
  }
  catch (Exception $e) {
    $code = $e->getCode();
    $message = $e->getMessage();
  }
  $data = ['task' => $task ?? null];

  output($code ?? 200, $message ?? '', $data) && exit;
}

?>