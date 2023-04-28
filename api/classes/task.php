<?php

class Task {
  public function __construct() {

  }

  /**
   * Get all the tasks from the database and outputs them in an array of objects.
   *
   * @return array of [int $code, ?string $message, array|null $data]
   */
  public static function getAll(): array {
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
  public static function get(int $id): array {
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
  public static function create(string $label, int $completed = 0): array {
    $mysqli = connectToDatabase();
    $label = $mysqli->real_escape_string($label);
    $completed = (int)$completed;
    $mysqli->query("INSERT INTO `tasks` (`label`, `completed`) VALUES ('$label', $completed)");

    if ($mysqli->error) {
      $message = 'The task could not be saved in the database.';
      $code = 500;
    }
    else list($code, $message, $task) = self::get($mysqli->insert_id);

    return [$code, $message, $task ?? null];
  }

  /**
   * Delete a task from the database.
   *
   * @param integer $id the id of the task to delete.
   * @return array of [int $code, ?string $message, array|null $data]
   */
  public function delete(int $id): array {
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
  function update(int $id, string $label, int $completed): array {
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
      list($code, $message, $task) = self::get($id);
      $code = $code === 200 ? 201 : 500; // Override the 200 from getTask.
    }

    return [$code, $message, $task ?? null];
  }
}

// new Task(label, );

// Task::get(2);
// Task::getAll();

?>
