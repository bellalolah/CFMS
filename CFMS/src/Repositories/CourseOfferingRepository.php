<?php

namespace Cfms\Repositories;

use Cfms\Models\CourseOffering;
use Cfms\Repositories\SessionRepository;

class CourseOfferingRepository extends BaseRepository
{
    protected string $table = 'course_offerings';

    public function createCourseOffering(array $data): int
    {
        return $this->insert($this->table, $data);
    }

    public function getCourseOfferingById(int $id): ?CourseOffering
    {
        $row = $this->findById($this->table, $id);
        return $row ? CourseOffering::toModel((array)$row) : null;
    }

    private function getActiveSessionId(): ?int
    {
        $sessionRepo = new SessionRepository();
        $activeSession = $sessionRepo->getActiveSession();
        return $activeSession ? $activeSession->id : null;
    }

    public function getAllCourseOfferings(): array
    {
        $activeSessionId = $this->getActiveSessionId();
        if (!$activeSessionId) return [];
        $sql = "SELECT co.* FROM {$this->table} co JOIN semesters s ON co.semester_id = s.id WHERE s.session_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$activeSessionId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($row) => CourseOffering::toModel($row), $rows);
    }

    public function updateCourseOffering(int $id, array $data): bool
    {
        return $this->update($this->table, $data, $id);
    }

    public function deleteCourseOffering(int $id): bool
    {
        return $this->deleteById($this->table, $id);
    }

    public function findByLecturerAndSemester(int $lecturerId, int $semesterId): array
    {
        $activeSessionId = $this->getActiveSessionId();
        if (!$activeSessionId) return [];
        $sql = "SELECT co.* FROM {$this->table} co JOIN semesters s ON co.semester_id = s.id WHERE co.lecturer_id = ? AND co.semester_id = ? AND s.session_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$lecturerId, $semesterId, $activeSessionId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($row) => CourseOffering::toModel($row), $rows);
    }

    public function findByLecturerAndSession(int $lecturerId, int $sessionId): array
    {
        $sql = "SELECT co.* FROM {$this->table} co JOIN semesters s ON co.semester_id = s.id WHERE co.lecturer_id = ? AND s.session_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$lecturerId, $sessionId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($row) => CourseOffering::toModel($row), $rows);
    }

    // In CourseOfferingRepository.php

    public function findBySession(int $sessionId): array
    {
        // This query is correct.
        $sql = "SELECT co.* FROM {$this->table} co JOIN semesters s ON co.semester_id = s.id WHERE s.session_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sessionId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($row) => CourseOffering::toModel($row), $rows);
    }
    public function findCourseOfferingById(int $id): ?CourseOffering
    {
        $row = $this->findById($this->table, $id);
        return $row ? CourseOffering::toModel((array)$row) : null;
    }

    public function findAllCourseOfferings(): array
    {
        $activeSessionId = $this->getActiveSessionId();
        if (!$activeSessionId) return [];
        $sql = "SELECT co.* FROM {$this->table} co JOIN semesters s ON co.semester_id = s.id WHERE s.session_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$activeSessionId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($row) => CourseOffering::toModel($row), $rows);
    }

    // In CourseOfferingRepository.php

    // In CourseOfferingRepository.php

    public function findByLecturer(int $lecturerId): array
    {
        // Filter by the currently active session
        $activeSessionId = $this->getActiveSessionId();
        if (!$activeSessionId) {
            return [];
        }

        $sql = "SELECT co.* 
            FROM {$this->table} co 
            JOIN semesters s ON co.semester_id = s.id 
            WHERE co.lecturer_id = ? AND s.session_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$lecturerId, $activeSessionId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($row) => CourseOffering::toModel($row), $rows);
    }

}
