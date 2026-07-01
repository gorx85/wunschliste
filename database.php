<?php
/**
 * Database Class
 */

if (!defined('APP_ROOT')) {
    die('Direct access not allowed.');
}

class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->connection->connect_errno) {
            die('Verbindung zur Datenbank fehlgeschlagen!');
        }
        $this->connection->set_charset('utf8');
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function query($sql, $params = [])
    {
        $stmt = $this->prepare($sql, $params);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function fetchOne($sql, $params = [])
    {
        $result = $this->query($sql, $params);
        if ($result && $row = $result->fetch_assoc()) {
            return $row;
        }
        return null;
    }

    public function fetchAll($sql, $params = [])
    {
        $result = $this->query($sql, $params);
        $rows = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    public function execute($sql, $params = [])
    {
        $stmt = $this->prepare($sql, $params);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }

    public function lastInsertId()
    {
        return (int) $this->connection->insert_id;
    }

    public function escape($value)
    {
        return $this->connection->real_escape_string($value);
    }

    private function prepare($sql, $params)
    {
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            if (DEBUG) {
                die('Prepare failed: ' . $this->connection->error . ' SQL: ' . $sql);
            }
            die('Ein Datenbankfehler ist aufgetreten!');
        }

        if (!empty($params)) {
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            $stmt->bind_param($types, ...$params);
        }

        return $stmt;
    }
}
