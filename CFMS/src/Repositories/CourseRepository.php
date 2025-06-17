<?php
namespace Cfms\Repositories;

use Cfms\Models\Course;

class CourseRepository extends BaseRepository
{
    protected string $table = 'courses';

    public function getAllCourses(): array
    {
        $records = $this->findAll($this->table);
        $courses = [];
        foreach ($records as $data) {
            $course = new Course();
            $courses[] = $course->toModel((array)$data);
        }
        return $courses;
    }

    public function getCourseById(int $id): ?Course
    {
        $data = $this->findById($this->table, $id);
        if ($data) {
            $course = new Course();
            return $course->toModel((array)$data);
        }
        return null;
    }

    public function createCourse(array $data): int
    {
        return $this->insert($this->table, $data);
    }

    public function updateCourse(int $id, array $data): bool
    {
        return $this->update($this->table, $data, $id);
    }

    public function deleteCourse(int $id): bool
    {
        return $this->deleteById($this->table, $id);
    }


    /**
     * Finds all courses for a given list of department IDs using the pivot table.
     * It also returns the department_id each course is associated with for easy mapping.
     *
     * @param array $departmentIds An array of department IDs.
     * @return array An array of course objects, with an added 'department_id' property.
     * I still dont' understand php arrays and objectts
     */
    public function findByDepartmentIds(array $departmentIds): array
    {
        if (empty($departmentIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($departmentIds), '?'));


        // We select all columns from the `courses` table (c.*)
        // AND we also select the `department_id` from the pivot table (dc.department_id)
        // This is crucial for mapping the results back in the service layer.
        $sql = "SELECT c.*, dc.department_id 
            FROM courses c
            JOIN course_departments dc ON c.id = dc.course_id
            WHERE dc.department_id IN ({$placeholders})";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($departmentIds);

        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
}

