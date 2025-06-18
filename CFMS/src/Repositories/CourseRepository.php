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

    // In Cfms\Repositories\CourseRepository.php
    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        $sanitizedIds = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($sanitizedIds), '?'));
        $sql = "SELECT * FROM courses WHERE id IN ({$placeholders})"; // Make sure table name is correct

        $stmt = $this->db->prepare($sql);
        $stmt->execute($sanitizedIds);

        $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
        // You will need a Course model
        return array_map(fn($row) => (new \Cfms\Models\Course())->toModel((array)$row), $rows);
    }


    // In Cfms\Repositories\CourseRepository.php

    /**
     * Finds a paginated list of courses for a specific department.
     *
     * @param int $departmentId
     * @param int $limit
     * @param int $offset
     * @return array An array of Course model objects.
     */
    // In Cfms\Repositories\CourseRepository.php

    public function findByDepartment(int $departmentId, ?int $activeSessionId, int $limit, int $offset): array
    {
        // The query is now simplified by removing the GROUP BY clause.
        $sql = "SELECT 
                c.*,
                co.lecturer_id,
                u.full_name AS lecturer_name
            FROM courses AS c
            -- Find the department this course belongs to
            JOIN course_departments AS cd ON c.id = cd.course_id
            -- LEFT JOIN to find the CURRENT offering for this course, if it exists
            LEFT JOIN course_offerings AS co ON c.id = co.course_id
            LEFT JOIN semesters AS s ON co.semester_id = s.id AND s.session_id = :active_session_id
            -- LEFT JOIN to get the lecturer's name for that offering
            LEFT JOIN users AS u ON co.lecturer_id = u.id
            WHERE 
                cd.department_id = :department_id
            ORDER BY c.level ASC, c.course_code ASC
            LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':department_id', $departmentId, \PDO::PARAM_INT);

        // We must handle the case where there is no active session.
        // PDO converts null to an empty string in some cases, which can cause issues.
        // It's safer to bind it as an explicit NULL type if it is null.
        if ($activeSessionId === null) {
            $stmt->bindValue(':active_session_id', null, \PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':active_session_id', $activeSessionId, \PDO::PARAM_INT);
        }

        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Counts the total number of courses for a specific department.
     *
     * @param int $departmentId
     * @return int
     */
    public function countByDepartment(int $departmentId): int
    {
        $sql = "SELECT COUNT(c.id) FROM {$this->table} AS c
            JOIN course_departments AS cd ON c.id = cd.course_id
            WHERE cd.department_id = :department_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':department_id', $departmentId, \PDO::PARAM_INT);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }
}

