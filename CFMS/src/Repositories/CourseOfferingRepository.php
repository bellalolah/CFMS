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


    /**
     * Deletes multiple course offerings based on a list of criteria.
     * This operation is performed within a single database transaction.
     *
     * @param array $offerings An array of offerings to delete. Each item should be an
     *                         array with 'course_id', 'semester_id', and 'lecturer_id'.
     * @return int The number of offerings successfully deleted.
     */
    public function deleteBulk(array $offerings): int
    {
        $deletedCount = 0;

        // Use a transaction for safety
        $this->db->beginTransaction();

        try {
            // Prepare the DELETE statement once for efficiency
            $sql = "DELETE FROM {$this->table} WHERE course_id = ? AND semester_id = ? AND lecturer_id = ?";
            $stmt = $this->db->prepare($sql);

            foreach ($offerings as $offering) {
                // Ensure the required keys exist to avoid errors
                if (isset($offering['course_id'], $offering['semester_id'], $offering['lecturer_id'])) {
                    $stmt->execute([
                        $offering['course_id'],
                        $offering['semester_id'],
                        $offering['lecturer_id']
                    ]);
                    // Add the number of affected rows to our counter
                    $deletedCount += $stmt->rowCount();
                }
            }

            // If we get here without errors, commit the changes
            $this->db->commit();

        } catch (\Exception $e) {
            // If any error occurred, roll back the entire transaction
            $this->db->rollBack();
            error_log("Bulk course offering deletion failed: " . $e->getMessage());
            // Return 0 to indicate failure
            return 0;
        }

        return $deletedCount;
    }

    public function deleteByIds(array $offeringIds): int
    {
        // If the array of IDs is empty, there's nothing to do.
        if (empty($offeringIds)) {
            return 0;
        }

        // Ensure all IDs are integers for safety
        $sanitizedIds = array_map('intval', $offeringIds);

        // Create a string of question marks (?,?,?) for the IN clause.
        $placeholders = implode(',', array_fill(0, count($sanitizedIds), '?'));

        $sql = "DELETE FROM {$this->table} WHERE id IN ({$placeholders})";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($sanitizedIds);

        // Return the number of rows that were actually deleted
        return $stmt->rowCount();
    }

    public function offeringExists(int $courseId, int $lecturerId, int $semesterId): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE course_id = ? AND lecturer_id = ? AND semester_id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courseId, $lecturerId, $semesterId]);

        // fetchColumn() returns the value of the first column (our '1') or false if no row is found.
        return (bool)$stmt->fetchColumn();
    }

    // In Cfms\Repositories\CourseOfferingRepository.php

    /**
     * Checks if a specific lecturer is assigned to a specific course offering.
     *
     * @param int $offeringId The ID of the course offering.
     * @param int $lecturerId The ID of the lecturer (user).
     * @return bool True if the lecturer is assigned, false otherwise.
     */
    public function isLecturerForOffering(int $offeringId, int $lecturerId): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE id = ? AND lecturer_id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$offeringId, $lecturerId]);

        return (bool)$stmt->fetchColumn();
    }

    public function exists(int $offeringId): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$offeringId]);
        return (bool)$stmt->fetchColumn();
    }


    // In Cfms\Repositories\CourseOfferingRepository.php

    /**
     * Finds multiple course offerings by their primary key IDs.
     *
     * @param array $ids An array of course offering IDs.
     * @return array An array of CourseOffering model objects.
     */
    public function findByIds(array $ids): array
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
        // You will need to create the CourseOffering model if it doesn't exist.
        return array_map(fn($row) => (new \Cfms\Models\CourseOffering())->toModel((array)$row), $rows);
    }

}
