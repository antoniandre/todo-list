<?php

class Task {
  public ?int $id;
  public ?string $label;
  public ?string $description;
  public string $status;
  public ?int $assignee;
  public string $created;

  public function __construct(string $label, ?string $description, string $status, ?int $assignee, ?int $id = null) {
    $this->id = $id;
    $this->label = $label;
    $this->description = $description;
    $this->status = $status;
    $this->assignee = $assignee;
  }

  /**
   * Get all the tasks from the database and return them in an array of Tasks.
   *
   * @return array of Tasks, or an exception will be thrown.
   */
  public static function getAll(): array {
    $db = Database::get();
    $tasks = $db->fetchAll('SELECT * FROM tasks', [], ['foreach' => function($row) {
      return new self($row->label, $row->description, $row->status, (int)$row->assignee, (int)$row->id);
    }]);

    if ($tasks === false) throw new Exception('Could not retrieve the tasks from the database.', 500);

    return $tasks;
  }

  /**
   * Get a task from the database given its id and return it as an object.
   *
   * @param integer $id the id of the task to get.
   * @return Task The task instance if it worked, or an exception will be thrown.
   */
  public static function get(int $id): Task {
    $db = Database::get();
    $task = $db->fetch("SELECT * FROM `tasks` WHERE `id` = " . (int)$id);
    if ($task === false) throw new Exception('Could not read the task from the database.', 500);

    if (!$task) throw new Exception('The task was not found.', 404);
    else return new self($task->label, $task->description, $task->status, (int)$task->assignee, (int)$task->id);
  }

  /**
   * Save the current task instance in the database, and return true if it worked or throw an exception.
   *
   * @return Task The task instance if it worked, or an exception will be thrown.
   */
  public function save(): Task {
    $db = Database::get();
    list($rowCount, $insertedId, $error) = $db->query(<<<SQL
      INSERT INTO `tasks` (`label`, `description`, `status`, `assignee`)
      VALUES (:label, :description, :status, :assignee)
    SQL, [
      'label' => $this->label ?? '',
      'description' => $this->description ?? '',
      'status' => $this->status ?? 'todo',
      'assignee' => empty($this->assignee) ? null : (int)$this->assignee
    ]);

    if ($error) throw new Exception('The task could not be saved in the database.', 500);
    // Read from DB in case the insertion in DB results in different values of the task (e.g. cascading actions from FK).
    else return self::get($insertedId);
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
    list($rowCount, $insertedId, $error) = $db->query("DELETE FROM `tasks` WHERE `id` = $id");

    if ($error) throw new Exception('The task could not be deleted from the database.', 500);
    return true;
  }

  /**
   * Update a task in the database.
   *
   * @param string $label the new task label.
   * @param string $description the new task description.
   * @param integer $status: 0 or 1 for a status task.
   * @param integer $assignee: the user id.
   * @return Task The task instance if it worked, or an exception will be thrown.
   */
  function update(?string $label, ?string $description, ?string $status, ?int $assignee): Task {
    $db = Database::get();
    $params = [];
    $changes = [];

    if ($label) {
      $changes[] = "`label` = :label";
      $params['label'] = $label;
    }
    if ($description) {
      $changes[] = "`description` = :description";
      $params['description'] = $description;
    }
    if (!empty($status)) {
      $changes[] = "`status` = :status";
      $params['status'] = $status;
    }
    if (is_int($assignee)) {
      $changes[] = "`assignee` = :assignee";
      $params['assignee'] = $assignee;
    }

    if (count($changes)) $changes = implode(',', $changes);
    else throw new Exception('No changes were provided.', 400);

    list($rowCount, $insertedId, $error) = $db->query("UPDATE `tasks` SET $changes WHERE `id` = $this->id", $params);

    if ($error) throw new Exception('The task could not be saved in the database.', 500);

    // Read from DB in case the update in DB results in different values of the task (e.g. cascading actions from FK).
    else return self::get($this->id);
  }
}

?>
