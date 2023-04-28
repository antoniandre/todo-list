<?php

class Task {
  public $id;
  public $label;
  public $completed;
  public $assignee;

  public function __construct(string $label, bool $completed, ?int $assignee, ?int $id = null) {
    $this->id = $id;
    $this->label = $label;
    $this->completed = $completed;
    $this->assignee = $assignee;
  }

  /**
   * Get all the tasks from the database and outputs them in an array of objects.
   *
   * @return array of [int $code, ?string $message, array|null $tasks]
   */
  public static function getAll(): array {
    $db = connectToDatabase();
    $result = $db->query('SELECT * FROM tasks');

    if ($db->error) {
      $message = 'Could not retrieve the tasks from the database.';
      $code = 500;
    }
    else {
      $rows = [];
      while ($object = $result->fetch_object()) {
        $task = new self($object->label, (bool)$object->completed, $object->assignee, (int)$object->id);
        $rows[] = $task;
      }
    }

    return [$code ?? 200, $message ?? '', $rows ?? null];
  }

  /**
   * Get a task from the database given its id and output it as an object.
   *
   * @param integer $id the id of the task to get.
   * @return Task|array The task instance if it worked, or an error of [int $error, string $message].
   */
  public static function get(int $id): Task|array {
    $db = connectToDatabase();
    $result = $db->query("SELECT * FROM `tasks` WHERE `id` = " . (int)$id);
    $task = $result->fetch_object();

    if ($db->error) return [500, 'Could not read the task from the database.'];
    elseif (!$task) return [404, 'The task was not found.'];
    else return new self($task->label, (bool)$task->completed, $task->assignee, (int)$task->id);
  }

  /**
   * Saves a task instance in the database, and return an array with a code and $message.
   *
   * @return Task|array The task instance if it worked, or an error of [int $error, string $message].
   */
  public function save(): Task|array {
    $db = connectToDatabase();
    $label = $db->real_escape_string($this->label);
    $completed = (int)$this->completed;
    $db->query("INSERT INTO `tasks` (`label`, `completed`) VALUES ('$label', $completed)");

    if ($db->error) {
      return [500, 'The task could not be saved in the database.'];
    }
    // Read from DB in case the insertion in DB results in different values of the task (e.g. cascading actions from FK).
    else return self::get($db->insert_id);
  }

  /**
   * Delete the current task instance from the database.
   *
   * @return array of [int $code, ?string $message]
   */
  public function delete(): array {
    return self::deleteById($this->id);
  }

  /**
   * Delete a task from the database.
   *
   * @param integer $id the id of the task to delete.
   * @return array of [int $code, ?string $message]
   */
  public static function deleteById(int $id): array {
    $db = connectToDatabase();
    $db->query("DELETE FROM `tasks` WHERE `id` = $id");

    return [$db->error ? 500 : 204, $db->error ? 'The task could not be deleted from the database.' : ''];
  }

  /**
   * Update a task in the database.
   *
   * @param string $label the new task label.
   * @param integer $completed: 0 or 1 for a completed task.
   * @param integer $assignee: the user id.
   * @return Task|array The task instance if it worked, or an error of [int $error, string $message].
   */
  function update(?string $label, ?bool $completed, ?int $assignee): Task|array {
    $db = connectToDatabase();

    $changes = [];
    if ($label) {
      $label = $db->real_escape_string($label);
      $changes[] = "`label` = '$label'";
    }
    if (is_bool($completed)) $changes[] = "`completed` = " . (int)$completed;
    if (is_int($assignee)) $changes[] = "`assignee` = $assignee";

    $db->query("UPDATE `tasks` SET " . implode(',', $changes) . " WHERE `id` = $this->id");

    if ($db->error) return [500, 'The task could not be saved in the database.'];

    // Read from DB in case the update in DB results in different values of the task (e.g. cascading actions from FK).
    else return self::get($this->id);
  }
}

?>
