<?php

namespace Cfms\Repositories;

use PDO;


abstract class BaseRepository extends Dbh
{
    protected $db;

    public function __construct()
    {
        $this->db = (new Dbh())->connect();
    }

    // Find a record by ID
    public function findById(string $table, int $id)
    {
        $sql = "SELECT * FROM {$table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ); // Use FETCH_OBJ to return an object
    }

    // Get all records from a table
    public function findAll(string $table)
    {
        $sql = "SELECT * FROM {$table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ); // Use FETCH_OBJ to return an array of objects
    }

    public function findByColumn(string $table, string $column, $value)
    {
        $sql = "SELECT * FROM {$table} WHERE {$column} = :value";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':value', $value, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ); // Use FETCH_OBJ to return an array of objects
    }

    // Insert a new record into a table
    public function insert(string $table, array $data)
    {
        $fields = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));

        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Handle arrays as needed; for now, throw an exception
                throw new \InvalidArgumentException("Value for $key cannot be an array.");
            }
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
        return $this->db->lastInsertId();
    }

    // Update a record in a table
    public function update(string $table, array $data, int $id)
    {
        $fields = "";
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                throw new \InvalidArgumentException("Value for $key cannot be an array.");
            }
            $fields .= "{$key} = :{$key}, ";
        }
        $fields = rtrim($fields, ", ");

        $sql = "UPDATE {$table} SET {$fields} WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Delete a record by ID
    public function deleteById(string $table, int $id)
    {
        $sql = "DELETE FROM {$table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
