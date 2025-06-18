<?php

namespace Cfms\Repositories;

use Cfms\Repositories\BaseRepository;

class FacultyRepository extends BaseRepository
{
    protected string $table = 'faculties';

    public function findFacultyById(int $id)
    {
        return parent::findById($this->table, $id);
    }

    public function findAllFaculty(): array
    {
        return parent::findAll($this->table);
    }

    public function findFacultyByName(string $name): array
    {
        return parent::findByColumn($this->table, 'name', $name);
    }

    public function createFaculty(array $data): int
    {
        return parent::insert($this->table, $data);
    }

    public function createFaculties(array $faculties): array
    {
        $ids = [];
        foreach ($faculties as $data) {
            $ids[] = $this->createFaculty($data);
        }
        return $ids;
    }

    public function updateFaculty(int $id, array $data): bool
    {
        return parent::update($this->table, $data, $id);
    }

    public function deleteFaculty(int $id): bool
    {
        return parent::deleteById($this->table, $id);
    }

    public function findAllFacultyWithoutDates(): array
    {
        // Only select id and name, not date fields
        $sql = "SELECT id, name FROM {$this->table}";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // In Cfms\Repositories\FacultyRepository.php

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