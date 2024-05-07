<?php
class Database {
    private $host = 'localhost';
    private $dbname = 'spendSmart';
    private $username = 'root';
    private $password = '';
    private $pdo;

    public function __construct() {
        try {
            // Establish the database connection
            $this->pdo = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->username, $this->password);
            // Set the PDO error mode to exception
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Optionally, set the default fetch mode to associative array
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            // Optionally, set character set to utf8mb4
            $this->pdo->exec("SET NAMES utf8mb4");
        } catch (PDOException $e) {
            // Display error message if connection fails
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function getPDO() {
        return $this->pdo;
    }
}

?>
