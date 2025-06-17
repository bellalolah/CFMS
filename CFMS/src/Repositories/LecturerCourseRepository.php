<?php
namespace Cfms\Repositories;

use Cfms\Models\LecturerCourse;

class LecturerCourseRepository extends BaseRepository
{
    protected string $table = 'lecturer_courses';

    public function assignCoursesToLecturer(int $lecturerId, array $courseIds): array
    {
        $ids = [];
        foreach ($courseIds as $courseId) {
            $data = [
                'user_id' => $lecturerId,
                'course_id' => $courseId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $ids[] = $this->insert($this->table, $data);
        }
        return $ids;
    }

    public function unassignCoursesFromLecturer(int $lecturerId, array $courseIds): int
    {
        $count = 0;
        foreach ($courseIds as $courseId) {
            $sql = "DELETE FROM {$this->table} WHERE user_id = :user_id AND course_id = :course_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $lecturerId, \PDO::PARAM_INT);
            $stmt->bindValue(':course_id', $courseId, \PDO::PARAM_INT);
            if ($stmt->execute()) {
                $count += $stmt->rowCount();
            }
        }
        return $count;
    }

    public function getCoursesForLecturer(int $lecturerId): array
    {
        $sql = "SELECT course_id FROM {$this->table} WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $lecturerId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}

