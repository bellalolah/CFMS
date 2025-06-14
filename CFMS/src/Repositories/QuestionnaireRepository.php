<?php

namespace Cfms\Repositories;

use Cfms\Models\Questionnaire;
use Cfms\Core\BaseRepository;

class QuestionnaireRepository extends BaseRepository
{
    protected string $table = 'questionnaires';

    public function create(array $data): ?Questionnaire
    {
        $id = $this->insert($this->table, $data);

        if (!$id) return null;

        $data['id'] = $id;
        
        return Questionnaire::toModel($data);
    }

    public function findById(int $id): ?Questionnaire
    {
        $record = $this->find($this->table, $id);
        return $record ? Questionnaire::toModel($record) : null;
    }

    public function findAllByCourseOffering(int $courseOfferingId): array
    {
        $results = $this->findByColumn($this->table, 'course_offering_id', $courseOfferingId);
        return array_map(fn($row) => Questionnaire::toModel($row), $results);
    }

    public function update(int $id, array $data): bool
    {
        return $this->updateById($this->table, $id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->deleteById($this->table, $id);
    }
}
