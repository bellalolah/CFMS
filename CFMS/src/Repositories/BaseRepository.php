<?php

namespace Cfms\Repositories;

use PDO;
use Cfms\Core\Dbh;


abstract class BaseRepository extends Dbh
{
    protected $db;

    public function __construct()
    {
        $this->db = (new Dbh())->connect(); // Create a new instance of Dbh and connect
    }

    public function findById(string $table, int $id)
    {
        $sql = "SELECT * FROM {$table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function findAll(string $table): array
    {
        $sql = "SELECT * FROM {$table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function findByColumn(string $table, string $column, $value): array
    {
        $sql = "SELECT * FROM {$table} WHERE {$column} = :value";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':value', $value);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function insert(string $table, array $data): int
    {
        $fields = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));

        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                throw new \InvalidArgumentException("Value for $key cannot be an array.");
            }
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    public function update(string $table, array $data, int $id): bool
    {
        $fields = implode(', ', array_map(fn($key) => "$key = :$key", array_keys($data)));

        $sql = "UPDATE {$table} SET {$fields} WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                throw new \InvalidArgumentException("Value for $key cannot be an array.");
            }
            $stmt->bindValue(":$key", $value);
        }

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function deleteById(string $table, int $id): bool
    {
        $sql = "DELETE FROM {$table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
