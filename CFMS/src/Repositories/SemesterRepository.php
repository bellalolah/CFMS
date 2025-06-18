<?php
namespace Cfms\Repositories;

use Cfms\Core\DBH;
use Cfms\Models\Semester;

class SemesterRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    public function findSemesterById(int $id): ?Semester
    {
        $row = parent::findById('semesters', $id);
        if (!$row) return null;
        $model = new Semester();
        return $model->toModel($row);
    }

    public function createSemester(array $data): int
    {
        return parent::insert('semesters', $data);
    }

    public function updateSemester(int $id, array $data): bool
    {
        return parent::update('semesters', $data, $id);
    }

    public function deleteSemester(int $id): bool
    {
        return parent::deleteById('semesters', $id);
    }

    public function findBySessionId(int $sessionId): array
    {
        $rows = parent::findByColumn('semesters', 'session_id', $sessionId);
        $semesters = [];
        foreach ($rows as $row) {
            $model = new Semester();
            $semesters[] = $model->toModel($row);
        }
        return $semesters;
    }

    // In your Cfms\Repositories\SessionRepository.php file

    /**
     * Creates a session and its associated semesters within a single database transaction.
     *
     * @param array $sessionData  Data for the new session.
     * @param array $semestersData Array of data for the new semesters.
     * @return array|null The created session with its semesters, or null on failure.
     */
    public function createSessionAndSemesters(array $sessionData, array $semestersData): ?array
    {

        // This is available because your repository extends BaseRepository.
        $this->db->beginTransaction();

        try {

            // We use the insert() method from BaseRepository.
            $newSessionId = $this->insert('sessions', $sessionData);
            if (!$newSessionId) {
                $this->db->rollBack(); // Abort if session creation failed
                return null;
            }

            $createdSemesters = [];
            // 3. LOOP AND CREATE THE SEMESTERS
            foreach ($semestersData as $semester) {
                $semester['session_id'] = $newSessionId;

                // Use the insert() method from BaseRepository again for the semesters table.
                $newSemesterId = $this->insert('semesters', $semester);

                if (!$newSemesterId) {
                    // If any semester fails, abort the ENTIRE operation
                    $this->db->rollBack();
                    return null;
                }
                $semester['id'] = $newSemesterId;
                $createdSemesters[] = $semester;
            }


            $this->db->commit();


            $sessionData['id'] = $newSessionId;
            $sessionData['semesters'] = $createdSemesters;

            return $sessionData;

        } catch (\Exception $e) {
            // If any error happened at all, roll back.
            $this->db->rollBack();
            error_log("Transaction failed: " . $e->getMessage()); // Log the error for debugging
            return null; // Return null to signal failure
        }
    }
}
