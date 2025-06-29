<?php
namespace Cfms\Repositories;

class FeedbackRepository extends BaseRepository
{
    protected string $table = 'feedbacks';

    /**
     * Inserts a batch of feedback answers for a single questionnaire.
     * This is done in a transaction to ensure all answers are saved or none are.
     *
     * @param int $questionnaireId The ID of the questionnaire being answered.
     * @param array $answers An array of answers to insert.
     * @return bool True on success, false on failure.
     */
    public function createBatch(int $questionnaireId, array $answers): bool
    {
        $this->db->beginTransaction();

        try {
            $sql = "INSERT INTO {$this->table} (questionnaire_id, question_id, answer_value, answer_text) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);

            foreach ($answers as $answer) {
                // Determine which answer column to use
                $answerValue = $answer['answer_value'] ?? null;
                $answerText = $answer['answer_text'] ?? null;

                $stmt->execute([
                    $questionnaireId,
                    $answer['question_id'],
                    $answerValue,
                    $answerText
                ]);
            }

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Feedback batch creation failed: " . $e->getMessage());
            return false;
        }
    }

    // In Cfms\Repositories\FeedbackRepository.php
    public function countByQuestionnaireId(int $questionnaireId): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE questionnaire_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$questionnaireId]);
        return (int)$stmt->fetchColumn();
    }

    // In Cfms\Repositories\FeedbackRepository.php

    /**
     * Finds a paginated list of feedbacks for a specific question within a questionnaire.
     *
     * @param int $questionnaireId
     * @param int $questionId
     * @param int $limit
     * @param int $offset
     * @return array An array of Feedback model objects.
     */
    public function findByQuestion(int $questionnaireId, int $questionId, int $limit, int $offset): array
    {
        $sql = "SELECT * FROM {$this->table} 
            WHERE questionnaire_id = :questionnaire_id AND question_id = :question_id 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':questionnaire_id', $questionnaireId, \PDO::PARAM_INT);
        $stmt->bindValue(':question_id', $questionId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
        return array_map(fn($row) => (new \Cfms\Models\Feedback())->toModel($row), $rows);
    }

    /**
     * Counts the total number of feedbacks for a specific question within a questionnaire.
     *
     * @param int $questionnaireId
     * @param int $questionId
     * @return int The total number of feedback entries.
     */
    public function countByQuestion(int $questionnaireId, int $questionId): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
            WHERE questionnaire_id = :questionnaire_id AND question_id = :question_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':questionnaire_id', $questionnaireId, \PDO::PARAM_INT);
        $stmt->bindValue(':question_id', $questionId, \PDO::PARAM_INT);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    // In Cfms\Repositories\FeedbackRepository.php

// REMOVE the old createBatch method and ADD this one instead:
    public function createSubmissionWithAnswers(int $questionnaireId, int $userId, array $answers): bool
    {
        $this->db->beginTransaction();

        try {
            // 1. Create the submission "receipt" first.
            // This will throw a PDOException if the unique key (user_id, questionnaire_id) already exists.
            $this->insert('feedback_submissions', [
                'questionnaire_id' => $questionnaireId,
                'user_id' => $userId
            ]);

            // 2. If that succeeded, insert all the anonymous answers.
            foreach ($answers as $answer) {
                $this->insert($this->table, [
                    'questionnaire_id' => $questionnaireId,
                    'question_id' => $answer['question_id'],
                    'answer_value' => $answer['answer_value'] ?? null,
                    'answer_text' => $answer['answer_text'] ?? null,
                ]);
            }

            $this->db->commit();
            return true;

        } catch (\PDOException $e) {
            $this->db->rollBack();

            if ($e->getCode() == '23000') {
                // It's a duplicate entry error. Throw our custom exception.
                throw new \Cfms\Exceptions\DuplicateSubmissionException("Feedback has already been submitted for this questionnaire.");
            }

            // For any other error, log it and return false.
            error_log("Feedback submission transaction failed: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Finds all feedback answers for a specific questionnaire.
     * This is crucial for performance calculations.
     * We join with questions to get the question_type for normalization.
     *
     * @param int $questionnaireId
     * @return array An array of feedback objects, each including question_type.
     */
    public function findAnswersByQuestionnaireId(int $questionnaireId): array
    {
        $sql = "SELECT f.question_id, f.answer_value, q.question_type 
                FROM {$this->table} f
                JOIN questions q ON f.question_id = q.id
                WHERE f.questionnaire_id = ? 
                AND f.deleted_at IS NULL 
                AND f.answer_value IS NOT NULL"; // Only get answers with numerical values

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$questionnaireId]);

        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Calculates the average performance score for a specific lecturer,
     * grouped by each criterion, across all of their questionnaires.
     * This is ideal for generating dashboard charts.
     *
     * @param int $lecturerId
     * @return array
     */
    public function getLecturerAverageScoresByCriterion(int $lecturerId): array
    {
        // This query is the heart of the feature. It correctly joins through
        // course_offerings and normalizes scores before averaging.
        $sql = "
            SELECT 
                c.name AS criterion_name,
                ROUND(AVG(
                    CASE 
                        WHEN q.question_type = 'slider' THEN f.answer_value / 20.0
                        WHEN q.question_type = 'rating' THEN f.answer_value
                        ELSE NULL
                    END
                ), 2) AS average_score
            FROM 
                feedbacks f
            JOIN 
                questions q ON f.question_id = q.id
            JOIN 
                questionnaires qn ON q.questionnaire_id = qn.id
            JOIN 
                course_offerings co ON qn.course_offering_id = co.id
            JOIN 
                criteria c ON q.criteria_id = c.id
            WHERE 
                co.lecturer_id = ?
                AND f.answer_value IS NOT NULL
                AND f.deleted_at IS NULL
                AND q.deleted_at IS NULL
                AND qn.deleted_at IS NULL
                AND co.deleted_at IS NULL
            GROUP BY 
                c.id, c.name
            ORDER BY 
                c.name
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$lecturerId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}