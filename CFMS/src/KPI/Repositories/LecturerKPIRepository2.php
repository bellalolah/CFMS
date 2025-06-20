<?php

namespace Dell\Cfms\KPI\Repositories;

use Cfms\KPI\KPIDto\LecturerPerformanceDto;
use Cfms\Repositories\BaseRepository;

use Dell\Cfms\KPI\KPIDto\LecturerKPIDto;

use PDO;

class LecturerKPIRepository2 extends BaseRepository
{
    public function getKPIForLecturer(int $lecturerId): ?LecturerKPIDto
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
            FROM users u
            JOIN course_offerings co ON u.id = co.lecturer_id
            JOIN questionnaires qn ON co.id = qn.course_offering_id
            JOIN questions q ON qn.id = q.questionnaire_id
            JOIN feedbacks f ON q.id = f.question_id
            WHERE u.id = :lecturer_id
              AND q.question_type IN ('rating', 'slider')
              AND f.answer_value IS NOT NULL
              AND u.deleted_at IS NULL
              AND co.deleted_at IS NULL
              AND qn.deleted_at IS NULL
              AND q.deleted_at IS NULL
              AND f.deleted_at IS NULL
            GROUP BY u.id, u.full_name
        SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':lecturer_id', $lecturerId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? new LecturerKPIDto($result) : null;
    }




    public function getLecturerCoursePerformance(int $lecturerId): array
    {
        $sql = <<<SQL
            SELECT
                c.course_code,
                c.course_title,
                c.level,
                COUNT(f.id) AS ratings_for_this_course,
                AVG(
                    CASE
                        WHEN q.question_type = 'rating' THEN ((f.answer_value - 1) / 4.0) * 100
                        WHEN q.question_type = 'slider' THEN f.answer_value
                        ELSE NULL
                    END
                ) AS normalized_avg_score
            FROM
                course_offerings co
            JOIN
                courses c ON co.course_id = c.id
            JOIN
                questionnaires qn ON co.id = qn.course_offering_id
            JOIN
                questions q ON qn.id = q.questionnaire_id
            JOIN
                feedbacks f ON q.id = f.question_id
            WHERE
                co.lecturer_id = :lecturer_id
                AND q.question_type IN ('rating', 'slider') AND f.answer_value IS NOT NULL
                AND co.deleted_at IS NULL
                AND c.deleted_at IS NULL
                AND qn.deleted_at IS NULL
                AND q.deleted_at IS NULL
                AND f.deleted_at IS NULL
            GROUP BY
                c.course_code, c.course_title, c.level
            ORDER BY
                normalized_avg_score DESC
            SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lecturer_id', $lecturerId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }


    public function getLecturerCriteriaPerformance(int $lecturerId): array
    {
        $sql = <<<SQL
        SELECT
            crit.name AS criteria_name,
            crit.description AS criteria_description,
            AVG(
                CASE
                    WHEN q.question_type = 'rating' THEN ((f.answer_value - 1) / 4.0) * 100
                    WHEN q.question_type = 'slider' THEN f.answer_value
                    ELSE NULL
                END
            ) AS normalized_avg_score
        FROM
            course_offerings co
        JOIN
            questionnaires qn ON co.id = qn.course_offering_id
        JOIN
            questions q ON qn.id = q.questionnaire_id
        JOIN
            criteria crit ON q.criteria_id = crit.id
        JOIN
            feedbacks f ON q.id = f.question_id
        WHERE
            co.lecturer_id = :lecturer_id
            AND q.question_type IN ('rating', 'slider') AND f.answer_value IS NOT NULL
            AND co.deleted_at IS NULL
            AND qn.deleted_at IS NULL
            AND q.deleted_at IS NULL
            AND crit.deleted_at IS NULL
            AND f.deleted_at IS NULL
        GROUP BY
            crit.name, crit.description
        ORDER BY
            normalized_avg_score DESC
        SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lecturer_id', $lecturerId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }



    public function getLecturerTextFeedback(int $lecturerId): array
    {
        $sql = <<<SQL
        SELECT
            s.name AS session_name,
            c.course_code,
            q.question_text,
            f.answer_text,
            f.created_at AS feedback_date
        FROM
            feedbacks f
        JOIN
            questions q ON f.question_id = q.id
        JOIN
            questionnaires qn ON q.questionnaire_id = qn.id
        JOIN
            course_offerings co ON qn.course_offering_id = co.id
        JOIN
            courses c ON co.course_id = c.id
        JOIN
            semesters sem ON co.semester_id = sem.id
        JOIN
            sessions s ON sem.session_id = s.id
        WHERE
            co.lecturer_id = :lecturer_id
            AND f.answer_text IS NOT NULL
            AND f.answer_text != ''
            AND f.deleted_at IS NULL
            AND q.deleted_at IS NULL
            AND qn.deleted_at IS NULL
            AND co.deleted_at IS NULL
            AND c.deleted_at IS NULL
            AND s.deleted_at IS NULL
        ORDER BY
            f.created_at DESC
        SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lecturer_id', $lecturerId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }



    public function getLecturerPerformanceOverview(): array
    {
        $sql = <<<SQL
        SELECT
            u.full_name AS lecturer_name,
            d.name AS department,
            COUNT(DISTINCT lc.course_id) AS number_of_courses,
            COUNT(DISTINCT fs.id) AS number_of_reviews,
            COALESCE(ROUND(AVG(f.answer_value), 2), 0) AS average_rating_5_scale
        FROM
            users u
        JOIN
            lecturer_profiles lp ON u.id = lp.user_id
        JOIN
            departments d ON lp.department_id = d.id
        LEFT JOIN
            lecturer_courses lc ON u.id = lc.user_id AND lc.deleted_at IS NULL
        LEFT JOIN
            course_offerings co ON u.id = co.lecturer_id AND co.deleted_at IS NULL
        LEFT JOIN
            questionnaires qn ON co.id = qn.course_offering_id AND qn.deleted_at IS NULL
        LEFT JOIN
            feedback_submissions fs ON qn.id = fs.questionnaire_id AND fs.deleted_at IS NULL
        LEFT JOIN
            feedbacks f ON qn.id = f.questionnaire_id AND f.answer_value IS NOT NULL AND f.deleted_at IS NULL
        WHERE
            u.deleted_at IS NULL AND lp.deleted_at IS NULL
        GROUP BY
            u.id, u.full_name, d.name
        ORDER BY
            lecturer_name
        SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        return array_map(fn($row) => LecturerPerformanceDto::fromDbRow($row), $results);
    }


}
