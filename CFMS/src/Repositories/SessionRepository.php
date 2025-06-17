<?php
namespace Cfms\Repositories;

use Cfms\Core\DBH;
use Cfms\Models\Session;
use PDO;

class SessionRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
    }
    // In SessionRepository.php
    public function findSessionById(int $id): ?Session
    {
        // Call the parent method to avoid repeating code.
        $row = parent::findById('sessions', $id);

        if (!$row) return null;

        $model = new Session();
        return $model->toModel($row);
    }
    public function createSession(array $data): int
    {
        return parent::insert('sessions', $data);
    }

    public function updateSession(int $id, array $data): bool
    {
        return parent::update('sessions', $data, $id);
    }

    public function deleteSession(int $id): bool
    {
        return parent::deleteById('sessions', $id);
    }
    public function getActiveSession(): ?Session
    {
        $stmt = $this->db->prepare('SELECT * FROM sessions WHERE is_active = 1 LIMIT 1');
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        if (!$row) return null;
        $model = new Session();
        return $model->toModel($row);
    }

    public function updateAll(array $data): bool
    {
        $set = [];
        $params = [];
        foreach ($data as $key => $value) {
            $set[] = "$key = ?";
            $params[] = $value;
        }
        $setClause = implode(', ', $set);
        $sql = "UPDATE sessions SET $setClause";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    public function findSessionsByDateRange(string $from, string $to): array
    {
        $sql = 'SELECT * FROM sessions WHERE (start_date >= ? AND start_date <= ?) OR (end_date IS NOT NULL AND end_date >= ? AND end_date <= ?)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$from, $to, $from, $to]);
        $rows = $stmt->fetchAll(PDO::FETCH_OBJ);
        $sessions = [];
        foreach ($rows as $row) {
            $model = new Session();
            $sessions[] = $model->toModel($row);
        }
        return $sessions;
    }



    /**
     * Creates a session and its associated semesters within a single database transaction.
     *
     * @param array $sessionData  Data for the new session.
     * @param array $semestersData Array of data for the new semesters.
     * @return array|null The created session with its semesters, or null on failure.
     */
    public function createSessionAndSemesters(array $sessionData, array $semestersData): ?array
    {

        $this->db->beginTransaction();

        try {

            // We use the insert() method from BaseRepository.
            $newSessionId = $this->insert('sessions', $sessionData);
            if (!$newSessionId) {
                $this->db->rollBack(); // Abort if session creation failed
                return null;
            }

            $createdSemesters = [];

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

            // 5. Prepare the final data to return. This is what the controller will see.
            $sessionData['id'] = $newSessionId;
            $sessionData['semesters'] = $createdSemesters;

            if (isset($sessionData['is_active'])) {
                $sessionData['is_active'] = (bool)$sessionData['is_active'];
            }

            return $sessionData;

        } catch (\Exception $e) {
            // If any error happened at all, roll back.
            $this->db->rollBack();
            error_log("Transaction failed: " . $e->getMessage()); // Log the error for debugging
            return null; // Return null to signal failure
        }
    }
}
