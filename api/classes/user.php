<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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
   * @param string $username the new user username.
   * @param string $firstName the new user firstName.
   * @param string $lastName the new user lastName.
   * @param string $email the new user email.
   * @return User The user instance if it worked, or an exception will be thrown.
   */
  function update(?string $username, ?string $firstName, ?string $lastName, ?string $email): User {
    $db = Database::get();

    $changes = [];
    if ($username) {
      $username = $db->escape($username);
      $changes[] = "`username` = '$username'";
    }
    if ($firstName) {
      $firstName = $db->escape($firstName);
      $changes[] = "`firstName` = '$firstName'";
    }
    if ($lastName) {
      $lastName = $db->escape($lastName);
      $changes[] = "`lastName` = '$lastName'";
    }
    if ($email) {
      $email = $db->escape($email);
      $changes[] = "`email` = '$email'";
    }

    $result = $db->query("UPDATE `users` SET " . implode(',', $changes) . " WHERE `id` = $this->id");

    if ($result === false) throw new Exception('The user could not be saved in the database.', 500);

    // Read from DB in case the update in DB results in different values of the user (e.g. cascading actions from FK).
    else return self::get($this->id);
  }

  /**
   * Log the user in.
   *
   * @param string $username the username from the user to try to authenticate with.
   * @param string $password the clear password from the user to try to authenticate with.
   * @todo Use the JWT commented code.
   * @return array|false
   */
  public static function logIn(string $username, string $password): array|false {
    if ($username && $password) {
      $db = Database::get();
      $result = $db->query("SELECT * FROM `users` WHERE `username` = '$username'");
      $user = $result->fetch_object();

      if ($user && password_verify($password, $user->password)) {
        $user = new self($user->username, $user->firstName, $user->lastName, $user->email, $user->id);

        // Store the current user ID in session to check against the JWT user ID.
        session_start();
        $_SESSION['user'] = $user->id;

        return [$user, JWT::encode(['user' => $user], 'example_key', 'HS256')];
      }
    }

    return [false, null];
  }

  public static function authenticate(): true {
    $headers = apache_request_headers();
    $jwt = $headers['authorization'];

    if ($jwt) {
      try {
        $decoded = JWT::decode($jwt, new Key('example_key', 'HS256'));
      }
      catch (Exception $e) {
        output(403, "Access denied: {$e->getMessage()}.") && exit;
      }

      if ($decoded && !empty($decoded->user->id) && self::get((int)$decoded->user->id)) {
        if (!isset($_SESSION)) session_start();
        if ((int)$decoded->user->id === $_SESSION['user']) return true;
      }
    }

    output(403, 'Access denied.') && exit;
  }

  public function logOut(): void {
    if (!isset($_SESSION)) session_start();
    session_unset();
    session_destroy();

    // Delete the session cookie.
    if (ini_get('session.use_cookies')) {
      $params = session_get_cookie_params();
      setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
      );
    }
  }
}

?>
