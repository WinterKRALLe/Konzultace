<?php

namespace Services;

use PDO;
use PDOException;

class Database
{

    private string $host;
    private string $db_name;
    private string $db_user;
    private string $db_password;
    public PDO|null $conn;

    public function __construct()
    {
        $config = require 'config/db_config.php';
        $this->host = $config['db_host'];
        $this->db_name = $config['db_name'];
        $this->db_user = $config['db_user'];
        $this->db_password = $config['db_password'];
    }

    public function getConnection(): ?PDO
    {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->db_user, $this->db_password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
