<?php
namespace Cfms\Repositories;

use Cfms\Models\Criterion;

class CriterionRepository extends BaseRepository
{
    protected string $table = 'criteria';

    /**
     * Creates a new criterion record.
     */
    public function createCriterion(array $data): int
    {
        return $this->insert($this->table, $data);
    }

    /**
     * Finds a single criterion by its primary key ID.
     */
    public function findCriterionById(int $id): ?Criterion
    {
        $row = parent::findById($this->table, $id);
        return $row ? (new Criterion())->toModel($row) : null;
    }

    /**
     * Finds all criteria.
     */
    public function findAllCriterion(): array
    {
        $rows = parent::findAll($this->table);
        return array_map(fn($row) => (new Criterion())->toModel($row), $rows);
    }

    /**
     * Finds multiple criteria by their primary key IDs.
     * This will be very useful later for building DTOs efficiently.
     */
    public function findCriterionByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        $sanitizedIds = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($sanitizedIds), '?'));
        $sql = "SELECT * FROM {$this->table} WHERE id IN ({$placeholders})";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($sanitizedIds);

        $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
        return array_map(fn($row) => (new Criterion())->toModel($row), $rows);
    }
}