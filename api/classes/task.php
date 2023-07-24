<?php

class Task {
  public $id;
  public $label;
  public $description;
  public $completed;
  public $assignee;
  public $created;

  public function __construct(string $label, ?string $description, bool $completed, ?int $assignee, ?int $id = null) {
    $this->id = $id;
    $this->label = $label;
    $this->description = $description;
    $this->completed = $completed;
    $this->assignee = $assignee;
  }

  /**
   * Get all the tasks from the database and return them in an array of Tasks.
   *
   * @return array of Tasks, or an exception will be thrown.
   */
  public static function getAll(): array {
    $db = Database::get();
    $result = $db->query('SELECT * FROM tasks');

    if ($result === false) throw new Exception('Could not retrieve the tasks from the database.', 500);
    else {
      $rows = [];
      while ($object = $result->fetch_object()) {
        $task = new self($object->label, $object->description, (bool)$object->completed, $object->assignee, (int)$object->id);
        $rows[] = $task;
      }
    }

    return $rows ?? [];
  }

  /**
   * Get a task from the database given its id and return it as an object.
   *
   * @param integer $id the id of the task to get.
   * @return Task The task instance if it worked, or an exception will be thrown.
   */
  public static function get(int $id): Task {
    $db = Database::get();
    $result = $db->query("SELECT * FROM `tasks` WHERE `id` = " . (int)$id);
    if ($result === false) throw new Exception('Could not read the task from the database.', 500);

    $task = $result->fetch_object();
    if (!$task) throw new Exception('The task was not found.', 404);
    else return new self($task->label, $task->description, (bool)$task->completed, $task->assignee, (int)$task->id);
  }

  /**
   * Save the current task instance in the database, and return true if it worked or throw an exception.
   *
   * @return Task The task instance if it worked, or an exception will be thrown.
   */
  public function save(): Task {
    $db = Database::get();
    $label = $db->escape($this->label ?? '');
    $description = $db->escape($this->description ?? '');
    $completed = (int)$this->completed;
    $assignee = $this->assignee ?? null ? (int)$this->assignee : 'NULL';
    $sql = <<<SQL
      INSERT INTO `tasks` (`label`, `description`, `completed`, `assignee`)
      VALUES ('$label', '$description', $completed, $assignee)
    SQL;
    $result = $db->query($sql);

    if ($result === false) throw new Exception('The task could not be saved in the database.', 500);
    // Read from DB in case the insertion in DB results in different values of the task (e.g. cascading actions from FK).
    else return self::get($db->getInsertedId());
  }

  /**
   * Delete the current task instance from the database.
   *
   * @return bool true if it worked or an exception will be thrown.
   */
  public function delete(): bool {
    return self::deleteById($this->id);
  }

  /**
   * Delete a task from the database.
   *
   * @param integer $id the id of the task to delete.
   * @return bool true if it worked or an exception will be thrown.
   */
  public static function deleteById(int $id): bool {
    $db = Database::get();
    $result = $db->query("DELETE FROM `tasks` WHERE `id` = $id");

    if ($result === false) throw new Exception('The task could not be deleted from the database.', 500);
    return true;
  }

  /**
   * Update a task in the database.
   *
   * @param string $label the new task label.
   * @param string $description the new task description.
   * @param integer $completed: 0 or 1 for a completed task.
   * @param integer $assignee: the user id.
   * @return Task The task instance if it worked, or an exception will be thrown.
   */
  function update(?string $label, ?string $description, ?bool $completed, ?int $assignee): Task {
    $db = Database::get();

    $changes = [];
    if ($label) {
      $label = $db->escape($label);
      $changes[] = "`label` = '$label'";
    }
    if ($description) {
      $description = $db->escape($description);
      $changes[] = "`description` = '$description'";
    }
    if (is_bool($completed)) $changes[] = "`completed` = " . (int)$completed;
    if (is_int($assignee)) $changes[] = "`assignee` = $assignee";

    $result = $db->query("UPDATE `tasks` SET " . implode(',', $changes) . " WHERE `id` = $this->id");

    if ($result === false) throw new Exception('The task could not be saved in the database.', 500);

    // Read from DB in case the update in DB results in different values of the task (e.g. cascading actions from FK).
    else return self::get($this->id);
  }
}

?>
