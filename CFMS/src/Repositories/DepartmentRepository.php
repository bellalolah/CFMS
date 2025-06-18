<?php

namespace Cfms\Repositories;

use Cfms\Repositories\BaseRepository;

class DepartmentRepository extends BaseRepository
{
    protected string $table = 'departments';

    public function findDepartmentById(int $id)
    {
        return parent::findById($this->table, $id);
    }

    public function findAllDepartments(): array
    {
        return parent::findAll($this->table);
    }

    public function findDepartmentByName(string $name): array
    {
        return parent::findByColumn($this->table, 'name', $name);
    }

    public function findDepartmentsByFacultyId(int $facultyId): array
    {
        return parent::findByColumn($this->table, 'faculty_id', $facultyId);
    }

    public function createDepartment(array $data): int
    {
        return parent::insert($this->table, $data);
    }

    public function createDepartments(array $departments): array
    {
        $ids = [];
        foreach ($departments as $data) {
            $ids[] = $this->createDepartment($data);
        }
        return $ids;
    }

    public function updateDepartment(int $id, array $data): bool
    {
        return parent::update($this->table, $data, $id);
    }

    public function deleteDepartment(int $id): bool
    {
        return parent::deleteById($this->table, $id);
    }

    /**
     * Finds all departments that belong to a given list of faculty IDs.
     * @param array $facultyIds An array of faculty IDs.
     * @return array An array of department objects.
     */
    public function findByFacultyIds(array $facultyIds): array
    {
        // If the array of IDs is empty, there's nothing to fetch.
        if (empty($facultyIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($facultyIds), '?'));
        $sql = "SELECT * FROM departments WHERE faculty_id IN ({$placeholders})";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($facultyIds);

        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    // In Cfms\Repositories\DepartmentRepository.php

    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        // Ensure all IDs are integers for safety
        $sanitizedIds = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($sanitizedIds), '?'));
        $sql = "SELECT * FROM {$this->table} WHERE id IN ({$placeholders})";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($sanitizedIds);

        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
}