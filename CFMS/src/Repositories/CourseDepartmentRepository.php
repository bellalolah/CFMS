<?php

namespace Cfms\Repositories;

use Cfms\Repositories\BaseRepository;

class CourseDepartmentRepository extends BaseRepository
{
    protected string $table = 'course_departments';

    public function assignCourseToDepartment(int $courseId, int $departmentId): int
    {
        return $this->insert($this->table, [
            'course_id' => $courseId,
            'department_id' => $departmentId
        ]);
    }

    public function removeCourseFromDepartment(int $courseId, int $departmentId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE course_id = :course_id AND department_id = :department_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':course_id', $courseId, \PDO::PARAM_INT);
        $stmt->bindParam(':department_id', $departmentId, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getDepartmentsForCourse(int $courseId): array
    {
        return $this->findByColumn($this->table, 'course_id', $courseId);
    }

    public function getCoursesForDepartment(int $departmentId): array
    {
        return $this->findByColumn($this->table, 'department_id', $departmentId);
    }
}

