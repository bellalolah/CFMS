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
        // This will now automatically ignore soft-deleted offerings because of the BaseRepository update
        $activeSessionId = $this->getActiveSessionId();
        if (!$activeSessionId) return [];
        $sql = "SELECT co.* FROM {$this->table} co JOIN semesters s ON co.semester_id = s.id WHERE s.session_id = ? AND co.deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$activeSessionId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($row) => CourseOffering::toModel($row), $rows);
    }

    public function updateCourseOffering(int $id, array $data): bool
    {
        return $this->update($this->table, $data, $id);
    }

    public function findByLecturerAndSemester(int $lecturerId, int $semesterId): array
    {
        $activeSessionId = $this->getActiveSessionId();
        if (!$activeSessionId) return [];
        $sql = "SELECT co.* FROM {$this->table} co JOIN semesters s ON co.semester_id = s.id WHERE co.lecturer_id = ? AND co.semester_id = ? AND s.session_id = ? AND co.deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$lecturerId, $semesterId, $activeSessionId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($row) => CourseOffering::toModel($row), $rows);
    }

    public function findByLecturerAndSession(int $lecturerId, int $sessionId): array
    {
        $sql = "SELECT co.* FROM {$this->table} co JOIN semesters s ON co.semester_id = s.id WHERE co.lecturer_id = ? AND s.session_id = ? AND co.deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$lecturerId, $sessionId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($row) => CourseOffering::toModel($row), $rows);
    }

    public function findBySession(int $sessionId): array
    {
        $sql = "SELECT co.* FROM {$this->table} co JOIN semesters s ON co.semester_id = s.id WHERE s.session_id = ? AND co.deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sessionId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($row) => CourseOffering::toModel((array)$row), $rows);
    }

    public function findCourseOfferingById(int $id): ?CourseOffering
    {
        // This now correctly uses the overridden findById from BaseRepository
        $row = $this->findById($this->table, $id);
        return $row ? CourseOffering::toModel((array)$row) : null;
    }

    public function findAllCourseOfferings(): array
    {
        return $this->getAllCourseOfferings(); // Re-use the same logic
    }

    public function findByLecturer(int $lecturerId): array
    {
        $activeSessionId = $this->getActiveSessionId();
        if (!$activeSessionId) {
            return [];
        }

        $sql = "SELECT co.* 
            FROM {$this->table} co 
            JOIN semesters s ON co.semester_id = s.id 
            WHERE co.lecturer_id = ? AND s.session_id = ? AND co.deleted_at IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$lecturerId, $activeSessionId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($row) => CourseOffering::toModel($row), $rows);
    }

    /**
     * RENAMED & UPDATED: Soft deletes multiple course offerings based on a list of criteria.
     */
    public function softDeleteBulkByCriteria(array $offerings): int
    {
        $updatedCount = 0;
        $this->db->beginTransaction();
        try {
            $sql = "UPDATE {$this->table} SET deleted_at = NOW() 
                    WHERE course_id = ? AND semester_id = ? AND lecturer_id = ? AND deleted_at IS NULL";
            $stmt = $this->db->prepare($sql);

            foreach ($offerings as $offering) {
                if (isset($offering['course_id'], $offering['semester_id'], $offering['lecturer_id'])) {
                    $stmt->execute([
                        $offering['course_id'],
                        $offering['semester_id'],
                        $offering['lecturer_id']
                    ]);
                    $updatedCount += $stmt->rowCount();
                }
            }
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Bulk course offering soft delete failed: " . $e->getMessage());
            return 0;
        }
        return $updatedCount;
    }

    /**
     * RENAMED & UPDATED: Soft deletes multiple course offerings by their primary key IDs.
     */
    public function softDeleteByIds(array $offeringIds): int
    {
        if (empty($offeringIds)) {
            return 0;
        }
        $sanitizedIds = array_map('intval', $offeringIds);
        $placeholders = implode(',', array_fill(0, count($sanitizedIds), '?'));

        $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id IN ({$placeholders}) AND deleted_at IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($sanitizedIds);

        return $stmt->rowCount();
    }

    /**
     * UPDATED: Checks for an active (non-soft-deleted) offering.
     */
    public function offeringExists(int $courseId, int $lecturerId, int $semesterId): bool
    {
        $sql = "SELECT 1 FROM {$this->table} 
                WHERE course_id = ? AND lecturer_id = ? AND semester_id = ? 
                AND deleted_at IS NULL 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courseId, $lecturerId, $semesterId]);
        return (bool)$stmt->fetchColumn();
    }

    // BaseRepository's findById already handles soft deletes, so these are fine.
    public function isLecturerForOffering(int $offeringId, int $lecturerId): bool
    {
        $row = $this->findById($this->table, $offeringId);
        return $row && $row->lecturer_id == $lecturerId;
    }

    public function exists(int $offeringId): bool
    {
        return (bool)$this->findById($this->table, $offeringId);
    }

    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        // Ensure all IDs are integers for safety
        $sanitizedIds = array_map('intval', $ids);

        // This is the critical fix. array_values() re-indexes the array to be 0, 1, 2...
        // which prevents the PDO error.
        $indexedIds = array_values($sanitizedIds);

        $placeholders = implode(',', array_fill(0, count($indexedIds), '?'));
        $sql = "SELECT * FROM {$this->table} WHERE id IN ({$placeholders}) AND deleted_at IS NULL";

        $stmt = $this->db->prepare($sql);

        // Execute with the guaranteed-to-be-indexed array
        $stmt->execute($indexedIds); // This is the changed line (was line 89)

        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
}