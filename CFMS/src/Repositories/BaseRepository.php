<?php

namespace Cfms\Repositories;

use PDO;
use Cfms\Core\DBH;

abstract class BaseRepository extends DBH
{
    protected $db;

    public function __construct()
    {
        $this->db = (new DBH())->connect();
    }

    /**
     * Finds a single, non-deleted record by its ID.
     * Automatically excludes soft-deleted records.
     */
    public function findById(string $table, int $id)
    {
        $sql = "SELECT * FROM `{$table}` WHERE `id` = :id AND `deleted_at` IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Finds all non-deleted records in a table.
     * Automatically excludes soft-deleted records.
     */
    public function findAll(string $table): array
    {
        $sql = "SELECT * FROM `{$table}` WHERE `deleted_at` IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Finds non-deleted records by a specific column and value.
     * Automatically excludes soft-deleted records.
     */
    public function findByColumn(string $table, string $column, $value): array
    {
        $sql = "SELECT * FROM `{$table}` WHERE `{$column}` = :value AND `deleted_at` IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':value', $value);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Inserts a new record into a table.
     */
    public function insert(string $table, array $data): int
    {
        if (empty($data)) {
            throw new \InvalidArgumentException("Insert data array cannot be empty.");
        }
        $fields = implode(", ", array_map(fn($key) => "`$key`", array_keys($data)));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO `{$table}` ({$fields}) VALUES ({$placeholders})";
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

    /**
     * Updates an existing record by its ID.
     */
    public function update(string $table, array $data, int $id): bool
    {
        $fields = implode(', ', array_map(fn($key) => "`$key` = :$key", array_keys($data)));
        $sql = "UPDATE `{$table}` SET {$fields} WHERE `id` = :id";
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

    /**
     * NEW: Performs a soft delete on a record by setting its 'deleted_at' timestamp.
     * This should be the default way to "delete" records.
     */
    public function softDeleteById(string $table, int $id): bool
    {
        // Only update rows that are not already soft-deleted.
        $sql = "UPDATE `{$table}` SET `deleted_at` = NOW() WHERE `id` = :id AND `deleted_at` IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0; // Return true if a row was actually updated
    }

    /**
     * RENAMED: Performs a hard, permanent delete from the database.
     * Use with caution.
     */
    public function forceDeleteById(string $table, int $id): bool
    {
        $sql = "DELETE FROM `{$table}` WHERE `id` = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Your existing updateByColumn method.
     */
    public function updateByColumn(string $table, string $column, $value, array $data): bool
    {
        $fields = implode(', ', array_map(fn($key) => "`$key` = :$key", array_keys($data)));
        $sql = "UPDATE `{$table}` SET {$fields} WHERE `{$column}` = :column_value";
        $stmt = $this->db->prepare($sql);
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                throw new \InvalidArgumentException("Value for $key cannot be an array.");
            }
            $stmt->bindValue(":$key", $val);
        }
        $stmt->bindValue(':column_value', $value);
        return $stmt->execute();
    }
}