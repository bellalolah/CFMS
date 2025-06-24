<?php

namespace Cfms\Core;

use PDO;
use PDOException;

class DBH
{
    private $dsn = "mysql:host=localhost:3306;dbname=cfms";
    private $dbusername = "root";
    private $dbpassword = "";

    protected function connect()
    {
        try {
            $pdo = new PDO($this->dsn, $this->dbusername, $this->dbpassword);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            throw new \Dell\Cfms\Exceptions\DatabaseConnectionException('Could not connect to the database.', 0, $e);
        }
    }
}
