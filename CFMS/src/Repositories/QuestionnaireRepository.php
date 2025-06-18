<?php
namespace Cfms\Repositories;

use Cfms\Models\Questionnaire;

class QuestionnaireRepository extends BaseRepository
{
    protected string $table = 'questionnaires';

    /**
     * Finds a single questionnaire by its primary key ID.
     */
    public function findQuestionnaireById(int $id): ?Questionnaire
    {
        $row = parent::findById($this->table, $id);
        return $row ? (new Questionnaire())->toModel($row) : null;
    }

    /**
     * Creates a questionnaire and all its questions within a single database transaction.
     */

    // In Cfms\Repositories\QuestionnaireRepository.php

    public function createWithQuestions(array $questionnaireData, array $questionsData): ?Questionnaire
    {
        $this->db->beginTransaction();
        try {
            // 1. Create the Questionnaire record
            $questionnaireId = $this->insert($this->table, $questionnaireData);
            if (!$questionnaireId) {
                $this->db->rollBack();
                return null;
            }

            // 2. Loop through and create each Question record
            foreach ($questionsData as $question) {
                $question['questionnaire_id'] = $questionnaireId;
                $this->insert('questions', $question);
            }

            // 3. Commit the transaction
            $this->db->commit();

            // 4. Return the newly created questionnaire by fetching it again
            // CORRECTED METHOD CALL
            return $this->findQuestionnaireById($questionnaireId);

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Questionnaire creation transaction failed: " . $e->getMessage());
            return null;
        }
    }

    public function findPaginated(int $limit, int $offset): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
        return array_map(fn($row) => (new Questionnaire())->toModel($row), $rows);
    }

    public function countAll(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        return (int)$stmt->fetchColumn();
    }

    // In Cfms\Repositories\QuestionnaireRepository.php
    public function updateWithQuestions(int $id, array $questionnaireData, array $questionsData): bool
    {
        $this->db->beginTransaction();
        try {
            // 1. Update the main questionnaire details
            $this->update($this->table, $questionnaireData, $id);

            // Delete all existing questions for this questionnaire.
            // This is the simplest, most reliable way to handle adds, updates, and deletes.
            $deleteSql = "DELETE FROM questions WHERE questionnaire_id = ?";
            $deleteStmt = $this->db->prepare($deleteSql);
            $deleteStmt->execute([$id]);

            // 3. Re-insert all the questions with their new or updated data.
            $questionSql = "INSERT INTO questions (questionnaire_id, question_text, question_type, `order`, criteria_id) VALUES (?, ?, ?, ?, ?)";
            $questionStmt = $this->db->prepare($questionSql);
            foreach ($questionsData as $question) {
                $questionStmt->execute([
                    $id,
                    $question['question_text'],
                    $question['question_type'] ?? 'rating',
                    $question['order'],
                    $question['criteria_id']
                ]);
            }

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Questionnaire update transaction failed: " . $e->getMessage());
            return false;
        }
    }

    public function updateStatus(int $id, string $newStatus): bool
    {
        $validStatuses = ['inactive', 'active'];
        if (!in_array($newStatus, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid status: $newStatus");
        }
        $sql = "UPDATE {$this->table} SET status = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$newStatus, $id]);
    }


    /**
     * Counts the total number of questionnaires associated with a specific lecturer.
     *
     * @param int $lecturerId
     * @return int
     */
    public function countByLecturer(int $lecturerId): int
    {
        $sql = "SELECT COUNT(DISTINCT q.id) FROM {$this->table} AS q
            LEFT JOIN course_offerings AS co ON q.course_offering_id = co.id
            WHERE q.created_by_user_id = :lecturer_id OR co.lecturer_id = :lecturer_id_alt";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lecturer_id', $lecturerId, \PDO::PARAM_INT);
        $stmt->bindValue(':lecturer_id_alt', $lecturerId, \PDO::PARAM_INT);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    // In Cfms\Repositories\QuestionnaireRepository.php

    public function findByLecturer(int $lecturerId, int $limit, int $offset): array
    {
        // This query is now more advanced. It joins with both course_offerings and feedbacks.
        $sql = "SELECT 
                q.*, 
                COUNT(f.id) as feedback_count
            FROM {$this->table} AS q
            LEFT JOIN course_offerings AS co ON q.course_offering_id = co.id
            LEFT JOIN feedbacks AS f ON q.id = f.questionnaire_id
            WHERE 
                q.created_by_user_id = :lecturer_id OR co.lecturer_id = :lecturer_id_alt
            GROUP BY q.id
            ORDER BY q.created_at DESC
            LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lecturer_id', $lecturerId, \PDO::PARAM_INT);
        $stmt->bindValue(':lecturer_id_alt', $lecturerId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
        // The model will now be populated with the extra 'feedback_count' property
        return array_map(fn($row) => (new \Cfms\Models\Questionnaire())->toModel($row), $rows);
    }

    // In Cfms\Repositories\QuestionnaireRepository.php

    // In Cfms\Repositories\QuestionnaireRepository.php

    public function findPendingForStudent(int $studentUserId, int $departmentId, int $limit, int $offset): array
    {
        // This query now joins all necessary tables to get the full context.
        $sql = "SELECT 
                q.id AS questionnaire_id,
                q.title,
                q.status,
                q.feedback_round,
                co.id AS course_offering_id,
                c.id AS course_id,
                c.course_code,
                c.course_title,
                u.id AS lecturer_id,
                u.full_name AS lecturer_name
            FROM questionnaires AS q
            
            -- Join to get course offering, lecturer profile, user (lecturer), and course details
            JOIN course_offerings AS co ON q.course_offering_id = co.id
            JOIN lecturer_profiles AS lp ON co.lecturer_id = lp.user_id
            JOIN users AS u ON lp.user_id = u.id
            JOIN courses AS c ON co.course_id = c.id
            
            -- Left join to filter out already submitted questionnaires
            LEFT JOIN feedback_submissions AS fs ON q.id = fs.questionnaire_id AND fs.user_id = :student_id
            
            WHERE 
                q.status = 'active'
                AND lp.department_id = :department_id 
                AND fs.id IS NULL -- The magic: only include if NO submission record exists
            
            ORDER BY q.created_at DESC
            LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':student_id', $studentUserId, \PDO::PARAM_INT);
        $stmt->bindValue(':department_id', $departmentId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        // The result is an array of raw objects, ready to be passed to the new DTO
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    /*public function findPendingForStudent(int $studentUserId, int $departmentId, int $limit, int $offset): array
    {
        $sql = "SELECT q.*, COUNT(f.id) as feedback_count 
            FROM questionnaires AS q
            
            -- Join through the chain to get to the department
            JOIN course_offerings AS co ON q.course_offering_id = co.id
            JOIN lecturer_profiles AS lp ON co.lecturer_id = lp.user_id
            
            -- Left join to check for an existing submission
            LEFT JOIN feedback_submissions AS fs ON q.id = fs.questionnaire_id AND fs.user_id = :student_id
            
            -- Left join to get the feedback count
            LEFT JOIN feedbacks AS f ON q.id = f.questionnaire_id
            
            WHERE 
                q.status = 'active'
                AND lp.department_id = :department_id -- Filter by the department_id from the lecturer's profile
                AND fs.id IS NULL -- Only include if NO submission record exists
            
            GROUP BY q.id
            ORDER BY q.created_at DESC
            LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':student_id', $studentUserId, \PDO::PARAM_INT);
        $stmt->bindValue(':department_id', $departmentId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return array_map(fn($row) => (new \Cfms\Models\Questionnaire())->toModel($row), $stmt->fetchAll(\PDO::FETCH_OBJ));
    }*/

    public function countPendingForStudent(int $studentUserId, int $departmentId): int
    {
        $sql = "SELECT COUNT(DISTINCT q.id) 
            FROM questionnaires AS q
            
            -- Join through the chain
            JOIN course_offerings AS co ON q.course_offering_id = co.id
            JOIN lecturer_profiles AS lp ON co.lecturer_id = lp.user_id
            
            -- Left join to check for an existing submission
            LEFT JOIN feedback_submissions AS fs ON q.id = fs.questionnaire_id AND fs.user_id = :student_id
            
            WHERE 
                q.status = 'active' 
                AND lp.department_id = :department_id 
                AND fs.id IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':student_id', $studentUserId, \PDO::PARAM_INT);
        $stmt->bindValue(':department_id', $departmentId, \PDO::PARAM_INT);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }
}