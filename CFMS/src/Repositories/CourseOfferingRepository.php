<?php

namespace Cfms\Repositories;


use Cfms\Models\CourseOffering;

class CourseOfferingRepository extends BaseRepository
{
    protected string $table = 'course_offerings';

    public function createCourseOffering(array $data): ?CourseOffering
    {
        $id = $this->insert($this->table, $data);
        return $id ? $this->findById($id) : null;
    }

    public function findById(int $id): ?CourseOffering
    {
        $row = $this->findByIdFromTable($this->table, $id);
        return $row ? CourseOffering::toModel($row) : null;
    }

    public function getOfferingsBySemester(int $semesterId): array
    {
        $rows = $this->findByColumn($this->table, 'semester_id', $semesterId);
        
        return array_map([CourseOffering::class, 'toModel'], $rows);
    }

    public function updateOffering(int $id, array $data): bool
    {
        return $this->update($this->table, $id, $data);
    }

    public function deleteOffering(int $id): bool
    {
        return $this->deleteById($this->table, $id);
    }
}
