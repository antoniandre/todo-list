<?php

/**
 * Singleton pattern.
 */
class Database {
    private static $instance; // Database instance.
    private $pdo; // PDO instance.
    public $lastError;

    private function __construct() {
        $this->pdo = $this->connect();
        $this->lastError = null;
    }

    public static function get(): Database {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    /**
      * Connect to the database and return the PDO object or die in case of failure.
      */
    private function connect(): PDO {
        $config = getConfig();

        try {
            $pdo = new PDO(
                "mysql:host={$config->databaseHost};port={$config->databasePort};dbname={$config->databaseName};charset=utf8mb4",
                $config->databaseUsername,
                $config->databasePassword,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]
            );
        } catch (PDOException $exception) {
            error_log('Database connection error: ' . $exception->getMessage());
            output(500, 'Database connection error.') && exit;
        }

        return $pdo;
    }

    /**
      * Prepare the query and execute it.
      *
      * @param string $query the query to execute.
      * @param array $params the unsafe SQL params to escape.
      * @return PDOStatement|false false in case of failure.
      */
    private function prepareAndExec(string $query, array $params = []): PDOStatement|false {
        $this->lastError = null;
        try {
            $stmt = $this->pdo->prepare($query);
        }
        catch (PDOException $e) {
            error_log("$e\nSQL query was:\n$query\nParams were:\n" . print_r($params, true));
            output(500, 'Oops. An error occurred while reading the database.') && exit;
        }

        foreach ((array)$params as $key => &$value) {
            if (is_null($value)) $type = PDO::PARAM_NULL;
            elseif (is_bool($value)) $type = PDO::PARAM_BOOL;
            elseif (is_int($value)) $type = PDO::PARAM_INT;
            elseif (is_string($value)) $type = PDO::PARAM_STR;
            try {
                $stmt->bindParam(":$key", $value, $type ?? PDO::PARAM_STR);
            }
            catch (PDOException $e) {
                error_log("$e\nSQL query was:\n$query\nParams were:\n" . print_r($params, true));
                output(500, 'Oops. An error occurred while communicating with the database.') && exit;
            }
        }

        try {
            $stmt->execute();
        }
        catch (PDOException $e) {
            $this->lastError = $e->errorInfo;
            error_log("$e\nSQL query was:\n$query\nParams were:\n" . print_r($params, true));
            $stmt = false;
        }

        return $stmt;
    }

    /**
      * Runs the given query and returns an array of information.
      * To be used like that:
      *   list($rowCount, $insertedId, $error) = $db->query($sql, [params to escape]);
      *
      * Notes: $rowCount returns the number of rows affected by the last DELETE, INSERT, or UPDATE.
      * https://www.php.net/manual/en/pdostatement.rowcount.php
      *
      * @param string $query the query to execute.
      * @param array $params the unsafe SQL params to escape.
      * @return array of [int rowsCount, int lastInsertedId, boolean error]
      */
    public function query(string $query, array $params = []): array {
        $stmt = $this->prepareAndExec($query, $params);

        if ($stmt) return [$stmt->rowCount(), (int)$this->pdo->lastInsertId(), !!$this->lastError];
        elseif ($this->lastError) error_log('SQL error detected, check the previous log message.');

        return [0, 0, $this->lastError];
    }

    /**
      * Runs the given query and returns the first column of the first row that was found.
      * To be used like that:
      *   $row = $db->fetchAll($sql, [params to escape]);
      *
      * @param string $query the query to execute.
      * @param array $params the unsafe SQL params to escape.
      * @return mixed the return value from the query, or false in case of failure.
      */
    public function fetchCol(string $query, array $params = []): mixed {
        $stmt = $this->prepareAndExec($query, $params);
        $return = false;
        if ($stmt) $return = $stmt->fetchColumn();
        elseif ($this->lastError) error_log('SQL error detected, check the previous log message.');

        return $return;
    }

    /**
      * Runs the given query and returns the first single row that was found as an object.
      * To be used like that:
      *   $row = $db->fetch($sql, [params to escape]);
      *
      * @param string $query the query to execute.
      * @param array $params the unsafe SQL params to escape.
      * @return object|null|false the row object or null if not found or false in case of failure.
      */
    public function fetch(string $query, array $params = []): object|null|false {
        $stmt = $this->prepareAndExec($query, $params);

        if (!$stmt || $this->lastError) error_log('SQL error detected, check the previous log message.');
        elseif ($stmt) return $stmt->fetchObject() ?: null;

        return false;
    }

    /**
      * Runs the given query and returns all the rows.
      *
      * @param string $query the query to run.
      * @param array $params indexed array of PDO binding params if any. E.g: ['name' => $name]
      * @param array $options indexed array of options. E.g: ['foreach' => public function ($row) {}]
      *              options are:
      *                - foreach: a function to execute for each row that should return the row object.
      *                - index: (string) give a column name to index on.
      *                - unique: (boolean) will index rows on the first column of the query - not compatible with foreach: use index instead.
      * @return array|false the array of rows in case of success, or false in case of failure.
      */
    public function fetchAll(string $query, array $params = [], array $options = []): array|false {
        $return = false;
        $stmt = $this->prepareAndExec($query, $params);

        if ($stmt) {
            $return = [];
            // Extract options into variables and default to false.
            $foreach = $options['foreach'] ?? false;
            $index = $options['index'] ?? false;
            $unique = $options['unique'] ?? false;

            if (($foreach && is_callable($foreach)) || $index) {
                $i = 0;
                while ($row = $stmt->fetchObject()) {
                    $modifiedRow = $foreach ? (object)$foreach($row, $i) : $row;
                    $return[$index ? $modifiedRow->$index : $i] = $modifiedRow;
                    $i++;
                }
            }
            else {
                $return = $stmt->fetchAll($unique ? PDO::FETCH_OBJ|PDO::FETCH_UNIQUE : PDO::FETCH_OBJ);
            }
        }

        elseif ($this->lastError) error_log('SQL error detected, check the previous log message.');

        return $return;
    }

    public function startTransaction(): void {
        $this->pdo->beginTransaction();
    }

    public function endTransaction(): void {
        $this->pdo->commit();
    }

    public function rollback(): void {
        $this->pdo->rollBack();
    }
}