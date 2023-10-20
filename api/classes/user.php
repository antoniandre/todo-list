<?php

class User {
  public $id;
  public $username;
  private $password;
  public $firstName;
  public $lastName;
  public $email;

  public function __construct(string $username, string $firstName, string $lastName, ?string $email, ?int $id = null) {
    $this->id = $id;
    $this->username = $username;
    $this->firstName = $firstName;
    $this->lastName = $lastName;
    $this->email = $email;
  }

  /**
   * Get all the users from the database and outputs them in an array of objects.
   *
   * @return array of users, or an exception will be thrown.
   */
  public static function getAll(): array {
    $db = Database::get();
    $result = $db->query('SELECT * FROM users');

    if ($result === false) throw new Exception('Could not retrieve the users from the database.', 500);
    else {
      $rows = [];
      while ($userRow = $result->fetch_object()) {
        $user = new self($userRow->username, $userRow->firstName, $userRow->lastName, $userRow->email, (int)$userRow->id);
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
    $db = Database::get();
    $result = $db->query("SELECT * FROM `users` WHERE `id` = " . (int)$id);
    $user = $result->fetch_object();

    if ($result === false) throw new Exception('Could not read the user from the database.', 500);
    elseif (!$user) throw new Exception('The user was not found.', 404);
    else return new self($user->username, $user->firstName, $user->lastName, $user->email, (int)$user->id);
  }

  /**
   * Saves a user instance in the database, and return an array with a code and $message.
   *
   * @return User The user instance if it worked, or an exception will be thrown.
   */
  public function save(): User {
    $db = Database::get();
    $firstName = $db->escape($this->firstName);
    $lastName = $db->escape($this->lastName);
    $result = $db->query("INSERT INTO `users` (`firstName`, `lastName`) VALUES ('$firstName', '$lastName')");

    if ($result === false) throw new Exception('The user could not be saved in the database.', 500);
    // Read from DB in case the insertion in DB results in different values of the user (e.g. cascading actions from FK).
    else return self::get($db->getInsertedId());
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
    $db = Database::get();
    $result = $db->query("DELETE FROM `users` WHERE `id` = $id");

    if ($result === false) throw new Exception('The user could not be deleted from the database.', 500);
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
    $db = Database::get();

    $changes = [];
    if ($firstName) {
      $firstName = $db->escape($firstName);
      $changes[] = "`firstName` = '$firstName'";
    }
    if (is_bool($lastName)) $changes[] = "`lastName` = " . (int)$lastName;

    $result = $db->query("UPDATE `users` SET " . implode(',', $changes) . " WHERE `id` = $this->id");

    if ($result === false) throw new Exception('The user could not be saved in the database.', 500);

    // Read from DB in case the update in DB results in different values of the user (e.g. cascading actions from FK).
    else return self::get($this->id);
  }

  /**
   * Log the user in.
   *
   * @param string $username
   * @param string $password
   * @todo Use the JWT commented code.
   * @return User|false
   */
  public static function logIn(string $username, string $password): User|false {
    if ($username && $password) {
      $db = Database::get();
      $result = $db->query("SELECT * FROM `users` WHERE `username` = '$username'");
      $user = $result->fetch_object();
      if ($user && password_verify($password, $user->password)) {
        return new self($user->username, $user->firstName, $user->lastName, $user->email, $user->id);
      }
    }

    // use Firebase\JWT\JWT;
    // use Firebase\JWT\Key;

    // $key = 'example_key';
    // $user = new stdClass();
    // $user->id = 3;
    // $user->firstName = 'Antoni';
    // $user->email = 'adsfgshg@sdagfhgs.sadgd';

    // $jwt = JWT::encode(['user' => $user], $key, 'HS256');
    // $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
    // print_r([$jwt, $decoded]);die;

    return false;
  }

  public function logOut() {

  }
}

?>
