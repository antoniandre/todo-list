<?php

/**
 * Singleton pattern.
 */
class Database {
  private static $instance; // Database instance.
  private $mysqli; // mysqli instance.

  private function __construct() {
    $this->connect();
  }

  public static function get(): Database {
    if (!self::$instance) self::$instance = new self();
    return self::$instance;
  }

  /**
   * Connect to the database and the MySQLi object in private class property, or die in case of failure.
   */
  private function connect(): void {
    $config = (object)parse_ini_file(__DIR__ . '/../config.ini');
    $mysqli = new mysqli(
      $config->databaseHost,
      $config->databaseUsername,
      $config->databasePassword,
      $config->databaseName
    );

    if ($mysqli->connect_error) {
      error_log($mysqli->connect_error);
      die('Could not connect to the database.');
    }

    $this->mysqli = $mysqli;
  }

  public function query(string $sql) {
    return $this->mysqli->query($sql);
  }

  public function escape(string $sql): string {
    return $this->mysqli->real_escape_string($sql);
  }

  public function getInsertedId(): int {
    return $this->mysqli->insert_id;
  }

  public function close(): void {
    $this->mysqli->close();
  }
}

?>
