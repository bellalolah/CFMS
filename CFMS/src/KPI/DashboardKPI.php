<?php

namespace Dell\Cfms\KPI;

use Cfms\Repositories\BaseRepository;

use PDO;

class DashboardKPI extends BaseRepository
{
    public function getLecturerRatingsSummary(): array
    {
        $sql = <<<SQL
            SELECT
                u.id AS lecturer_id,
                u.full_name AS lecturer_name,
                COUNT(f.id) AS total_ratings_received,
                AVG(
                    CASE
                        WHEN q.question_type = 'rating' THEN ((f.answer_value - 1) / 4.0) * 100
                        WHEN q.question_type = 'slider' THEN f.answer_value
                        ELSE NULL
                    END
                ) AS normalized_avg_score
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
            WHERE
                q.question_type IN ('rating', 'slider')
                AND f.answer_value IS NOT NULL
                AND u.deleted_at IS NULL
                AND co.deleted_at IS NULL
                AND qn.deleted_at IS NULL
                AND q.deleted_at IS NULL
                AND f.deleted_at IS NULL
            GROUP BY
                u.id, u.full_name
            ORDER BY
                normalized_avg_score DESC
        SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ); // you can change this to FETCH_ASSOC if you prefer arrays
    }




    // get lecturer rating per course

    public function getLecturerCourseRatings(): array
    {
        $sql = <<<SQL
        SELECT
            u.id AS lecturer_id,
            u.full_name AS lecturer_name,
            c.course_code,
            c.course_title,
            AVG(
                CASE
                    WHEN q.question_type = 'rating' THEN ((f.answer_value - 1) / 4.0) * 100
                    WHEN q.question_type = 'slider' THEN f.answer_value
                    ELSE NULL
                END
            ) AS normalized_avg_score
        FROM
            users u
        JOIN
            course_offerings co ON u.id = co.lecturer_id
        JOIN
            courses c ON co.course_id = c.id
        JOIN
            questionnaires qn ON co.id = qn.course_offering_id
        JOIN
            questions q ON qn.id = q.questionnaire_id
        JOIN
            feedbacks f ON q.id = f.question_id
        WHERE
            q.question_type IN ('rating', 'slider') AND f.answer_value IS NOT NULL
            AND u.deleted_at IS NULL
            AND co.deleted_at IS NULL
            AND c.deleted_at IS NULL
            AND qn.deleted_at IS NULL
            AND q.deleted_at IS NULL
            AND f.deleted_at IS NULL
        GROUP BY
            u.id, u.full_name, c.course_code, c.course_title
        ORDER BY
            u.full_name, normalized_avg_score DESC
        SQL;

                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }


    public function getLecturerSessionRatings(): array
    {
        $sql = <<<SQL
            SELECT
                u.id AS lecturer_id,
                u.full_name AS lecturer_name,
                s.name AS session_name,
                AVG(
                    CASE
                        WHEN q.question_type = 'rating' THEN ((f.answer_value - 1) / 4.0) * 100
                        WHEN q.question_type = 'slider' THEN f.answer_value
                        ELSE NULL
                    END
                ) AS normalized_avg_score
            FROM
                users u
            JOIN
                course_offerings co ON u.id = co.lecturer_id
            JOIN
                semesters sem ON co.semester_id = sem.id
            JOIN
                sessions s ON sem.session_id = s.id
            JOIN
                questionnaires qn ON co.id = qn.course_offering_id
            JOIN
                questions q ON qn.id = q.questionnaire_id
            JOIN
                feedbacks f ON q.id = f.question_id
            WHERE
                q.question_type IN ('rating', 'slider') AND f.answer_value IS NOT NULL
                AND u.deleted_at IS NULL
                AND co.deleted_at IS NULL
                AND sem.deleted_at IS NULL
                AND s.deleted_at IS NULL
                AND qn.deleted_at IS NULL
                AND q.deleted_at IS NULL
                AND f.deleted_at IS NULL
            GROUP BY
                u.id, u.full_name, s.name, s.start_date
            ORDER BY
                u.full_name, s.start_date
            SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }


}
