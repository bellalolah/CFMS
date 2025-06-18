<?php

namespace Cfms\Repositories;

use Cfms\Models\FeedbackSubmission;

class FeedbackSubmissionRepository extends BaseRepository
{

    protected string $table = 'feedback_submissions'; // <-- Define the table name

    // In Cfms\Repositories\FeedbackSubmissionRepository.php

    public function findByUserPaginated(int $userId, int $limit, int $offset): array
    {
        // This query now joins through multiple tables to get all context.
        $sql = "SELECT 
                fs.id AS submission_id,
                fs.submitted_at,
                q.id AS questionnaire_id,
                q.title AS questionnaire_title,
                co.id AS course_offering_id,
                c.course_code,
                c.course_title,
                u.full_name AS lecturer_name
            FROM feedback_submissions AS fs
            JOIN questionnaires AS q ON fs.questionnaire_id = q.id
            -- We can use LEFT JOINs here to be safe, in case a course offering was deleted
            LEFT JOIN course_offerings AS co ON q.course_offering_id = co.id
            LEFT JOIN courses AS c ON co.course_id = c.id
            LEFT JOIN users AS u ON co.lecturer_id = u.id
            WHERE fs.user_id = :user_id
            ORDER BY fs.submitted_at DESC
            LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        // The result is an array of raw objects, ready for a DTO
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    // You will also need a corresponding count method for pagination
    public function countByUser(int $userId): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

}