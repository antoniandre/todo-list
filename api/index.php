<?php

die('Hello world!');

function connectToDatabase() {
  $mysqli = new mysqli("127.0.0.1:3306", "root", "root", "todo");

  if ($mysqli->connect_error) {
    error_log($mysqli->connect_error);
    die('Could not connect to the database.');
  }

  return $mysqli;
}

function getTasks () {
  $mysqli = connectToDatabase();
  $sql = "SELECT * FROM tasks";
  $result = $mysqli->query($sql);

  $rows = [];
  while ($obj = $result->fetch_object()) array_push($rows, $obj);

  echo json_encode($rows);
  http_response_code(200);

  $mysqli->close();
}

function getTask(int $id) {
  $mysqli = connectToDatabase();
  $id = (int)$id;
  $sql = "SELECT * FROM `tasks` WHERE `id` = $id";
  $result = $mysqli->query($sql);
  $task = $result->fetch_object();

  echo json_encode($task);
  http_response_code(200);

  $mysqli->close();
}

function addTask(string $label, int $completed = 0) {
  $mysqli = connectToDatabase();
  $label = $mysqli->real_escape_string($label);
  $completed = (int)$completed;
  $sql = "INSERT INTO `tasks` (`label`, `completed`) VALUES ('$label', $completed)";
  $mysqli->query($sql);

  http_response_code($mysqli->error ? 500 : 201);

  $mysqli->close();
}

function deleteTask(int $id) {
  $mysqli = connectToDatabase();
  $id = (int)$id;
  $sql = "DELETE FROM `tasks` WHERE `id` = $id";
  $mysqli->query($sql);

  http_response_code($mysqli->error ? 500 : 204);

  $mysqli->close();
}

function updateTask(int $id, string $label, int $completed) {
  $mysqli = connectToDatabase();
  $id = (int)$id;
  $changes = [];
  if ($label !== null) {
    $label = $mysqli->real_escape_string($label);
    $changes[] = "`label` = '$label'";
  }
  if ($completed !== null) $changes[] = "`completed` = $completed";
  $sql = "UPDATE `tasks` SET " . implode(',', $changes) . " WHERE `id` = $id";

  $mysqli->query($sql);

  // echo json_encode($task);
  http_response_code($mysqli->error ? 500 : 200);

  $mysqli->close();
}

$params = json_decode(file_get_contents('php://input'));

switch ($_SERVER['REQUEST_METHOD']) {
  case 'GET':
    if (isset($params->id)) getTask($params->id);
    else getTasks();
    break;
  case 'POST':
    addTask($params->label, $params->completed);
    break;
  case 'PUT':
    updateTask($params->id, $params->label, $params->completed);
    break;
  case 'DELETE':
    deleteTask($params->id);
    break;
  default:
    http_response_code(405);
    break;
}

header('Content-Type: application/json; charset=utf-8');

?>