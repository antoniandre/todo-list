<?php

class User {
  public $id;
  public $firstName;
  public $lastName;

  public function __construct(string $firstName, string $lastName, ?int $id = null) {
    $this->id = $id;
    $this->firstName = $firstName;
    $this->lastName = $lastName;
  }

  /**
   * Get all the users from the database and outputs them in an array of objects.
   *
   * @return array of users, or an exception will be thrown.
   */
  public static function getAll(): array {
    $db = connectToDatabase();
    $result = $db->query('SELECT * FROM users');

    if ($db->error) throw new Exception('Could not retrieve the users from the database.', 500);
    else {
      $rows = [];
      while ($object = $result->fetch_object()) {
        $user = new self($object->firstName, $object->lastName, (int)$object->id);
        $rows[] = $user;
      }
    }

    return $rows ?? null;
  }

  /**
   * Get a user from the database given its id and output it as an object.
   *
   * @param integer $id the id of the user to get.
   * @return User The user instance if it worked, or an exception will be thrown.
   */
  public static function get(int $id): User {
    $db = connectToDatabase();
    $result = $db->query("SELECT * FROM `users` WHERE `id` = " . (int)$id);
    $user = $result->fetch_object();

    if ($db->error) throw new Exception('Could not read the user from the database.', 500);
    elseif (!$user) throw new Exception('The user was not found.', 404);
    else return new self($user->firstName, (bool)$user->lastName, (int)$user->id);
  }

  /**
   * Saves a user instance in the database, and return an array with a code and $message.
   *
   * @return User The user instance if it worked, or an exception will be thrown.
   */
  public function save(): User {
    $db = connectToDatabase();
    $firstName = $db->real_escape_string($this->firstName);
    $lastName = $db->real_escape_string($this->lastName);
    $db->query("INSERT INTO `users` (`firstName`, `lastName`) VALUES ('$firstName', '$lastName')");

    if ($db->error) throw new Exception('The user could not be saved in the database.', 500);
    // Read from DB in case the insertion in DB results in different values of the user (e.g. cascading actions from FK).
    else return self::get($db->insert_id);
  }

  /**
   * Delete the current user instance from the database.
   *
   * @return bool true if it worked, or an exception will be thrown.
   */
  public function delete(): bool {
    return self::deleteById($this->id);
  }

  /**
   * Delete a user from the database.
   *
   * @param integer $id the id of the user to delete.
   * @return bool true if it worked, or an exception will be thrown.
   */
  public static function deleteById(int $id): bool {
    $db = connectToDatabase();
    $db->query("DELETE FROM `users` WHERE `id` = $id");

    if ($db->error) throw new Exception('The user could not be deleted from the database.', 500);
    return true;
  }

  /**
   * Update a user in the database.
   *
   * @param string $firstName the new user firstName.
   * @param integer $lastName: 0 or 1 for a lastName user.
   * @return User The user instance if it worked, or an exception will be thrown.
   */
  function update(?string $firstName, ?bool $lastName): User {
    $db = connectToDatabase();

    $changes = [];
    if ($firstName) {
      $firstName = $db->real_escape_string($firstName);
      $changes[] = "`firstName` = '$firstName'";
    }
    if (is_bool($lastName)) $changes[] = "`lastName` = " . (int)$lastName;

    $db->query("UPDATE `users` SET " . implode(',', $changes) . " WHERE `id` = $this->id");

    if ($db->error) throw new Exception('The user could not be saved in the database.', 500);

    // Read from DB in case the update in DB results in different values of the user (e.g. cascading actions from FK).
    else return self::get($this->id);
  }
}

?>
