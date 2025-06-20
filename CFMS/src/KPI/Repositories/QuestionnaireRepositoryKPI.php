<?php

namespace Dell\Cfms\KPI;

namespace Dell\Cfms\KPI\Repositories;

use Cfms\Repositories\BaseRepository;
use PDO;

class QuestionnaireRepositoryKPI extends BaseRepository
{
    public function getQuestionnairePerformance(int $questionnaireId): array
    {
        $sql = <<<SQL
        SELECT
            q.id AS question_id,
            q.question_text,
            q.question_type,
            crit.name AS criteria_name,
            COUNT(f.id) AS response_count,
            AVG(
                CASE
                    WHEN q.question_type = 'rating' THEN ((f.answer_value - 1) / 4.0) * 100
                    WHEN q.question_type = 'slider' THEN f.answer_value
                    ELSE NULL
                END
            ) AS normalized_avg_score
        FROM
            questions q
        JOIN
            feedbacks f ON q.id = f.question_id
        JOIN
            criteria crit ON q.criteria_id = crit.id
        WHERE
            q.questionnaire_id = :questionnaire_id
            AND q.question_type IN ('rating', 'slider') AND f.answer_value IS NOT NULL
            AND q.deleted_at IS NULL
            AND f.deleted_at IS NULL
            AND crit.deleted_at IS NULL
        GROUP BY
            q.id, q.question_text, q.question_type, crit.name , q.`order`
        ORDER BY
            q.`order`
        SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':questionnaire_id', $questionnaireId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }


    public function getLecturerQuestionnaireSummary(int $lecturerId): array
    {
        $sql = <<<SQL
        SELECT
            qn.id AS questionnaire_id,
            qn.title AS questionnaire_title,
            c.course_code,
            c.course_title,
            s.name AS session_name,
            sem.name AS semester_name,
            COUNT(DISTINCT fs.user_id) AS total_submissions,
            AVG(
                CASE
                    WHEN q.question_type = 'rating' THEN ((f.answer_value - 1) / 4.0) * 100
                    WHEN q.question_type = 'slider' THEN f.answer_value
                    ELSE NULL
                END
            ) AS overall_normalized_score
        FROM
            users u
        JOIN
            course_offerings co ON u.id = co.lecturer_id
        JOIN
            questionnaires qn ON co.id = qn.course_offering_id
        JOIN
            questions q ON qn.id = q.questionnaire_id
        JOIN
            feedbacks f ON q.id = f.question_id
        JOIN
            feedback_submissions fs ON qn.id = fs.questionnaire_id
        JOIN
            courses c ON co.course_id = c.id
        JOIN
            semesters sem ON co.semester_id = sem.id
        JOIN
            sessions s ON sem.session_id = s.id
        WHERE
            u.id = :lecturer_id
            AND q.question_type IN ('rating', 'slider') AND f.answer_value IS NOT NULL
            AND u.deleted_at IS NULL
            AND co.deleted_at IS NULL
            AND qn.deleted_at IS NULL
            AND q.deleted_at IS NULL
            AND f.deleted_at IS NULL
            AND fs.deleted_at IS NULL
            AND c.deleted_at IS NULL
            AND sem.deleted_at IS NULL
            AND s.deleted_at IS NULL
        GROUP BY
            qn.id, qn.title, c.course_code, c.course_title, s.name, sem.name, s.start_date
        ORDER BY
            s.start_date DESC, c.course_code
        SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lecturer_id', $lecturerId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }


    public function getLecturerQuestionnaireRounds(int $lecturerId): array
    {
        $sql = <<<SQL
            SELECT
                qn.id AS questionnaire_id,
                c.course_code,
                c.course_title,
                s.name AS session_name,
                qn.feedback_round,
                COUNT(DISTINCT fs.user_id) AS total_submissions,
                AVG(
                    CASE
                        WHEN q.question_type = 'rating' THEN ((f.answer_value - 1) / 4.0) * 100
                        WHEN q.question_type = 'slider' THEN f.answer_value
                        ELSE NULL
                    END
                ) AS overall_normalized_score
            FROM
                users u
            JOIN
                course_offerings co ON u.id = co.lecturer_id
            JOIN
                questionnaires qn ON co.id = qn.course_offering_id
            JOIN
                questions q ON qn.id = q.questionnaire_id
            JOIN
                feedbacks f ON q.id = f.question_id
            JOIN
                feedback_submissions fs ON qn.id = fs.questionnaire_id
            JOIN
                courses c ON co.course_id = c.id
            JOIN
                semesters sem ON co.semester_id = sem.id
            JOIN
                sessions s ON sem.session_id = s.id
            WHERE
                u.id = :lecturer_id
                AND q.question_type IN ('rating', 'slider') AND f.answer_value IS NOT NULL
                AND u.deleted_at IS NULL
                AND co.deleted_at IS NULL
                AND qn.deleted_at IS NULL
                AND q.deleted_at IS NULL
                AND f.deleted_at IS NULL
                AND fs.deleted_at IS NULL
                AND c.deleted_at IS NULL
                AND sem.deleted_at IS NULL
                AND s.deleted_at IS NULL
            GROUP BY
                qn.id, c.course_code, c.course_title, s.name, qn.feedback_round
            ORDER BY
                c.course_code, qn.feedback_round
            SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lecturer_id', $lecturerId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }


}
