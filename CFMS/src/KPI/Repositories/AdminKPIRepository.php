<?php

namespace Dell\Cfms\KPI;

namespace Dell\Cfms\KPI\Repositories;

use Cfms\Repositories\BaseRepository;


use Dell\Cfms\KPI\KPIDto\LecturerPerformanceDto;
use PDO;

class AdminKPIRepository extends BaseRepository
{
    public function getGeneralDashboardStats(): object
    {
        $sql = <<<SQL
            SELECT
                (SELECT COUNT(*) FROM lecturer_profiles WHERE deleted_at IS NULL) AS total_lecturers,
                (SELECT COUNT(*) FROM student_profiles WHERE deleted_at IS NULL) AS total_students,
                (
                    (SELECT COUNT(DISTINCT user_id) FROM feedback_submissions WHERE deleted_at IS NULL)
                    /
                    NULLIF((SELECT COUNT(*) FROM student_profiles WHERE deleted_at IS NULL), 0)
                ) * 100.0 AS response_rate_percentage
            SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }



    public function getLecturerPerformanceOverview(): array
    {
        $sql = <<<SQL
             SELECT
                u.full_name AS lecturer_name,
                d.name AS department,
                -- Count the distinct courses assigned to this lecturer
                COUNT(DISTINCT lc.course_id) AS number_of_courses,
                -- Count the number of unique feedback forms submitted for this lecturer's courses
                COUNT(DISTINCT fs.id) AS number_of_reviews,
                -- Calculate the NORMALIZED average rating, then round it. Show 0 if no ratings exist.
                COALESCE(
                    ROUND(
                        AVG(
                            -- Use a CASE statement to normalize different question types to a 0-5 scale
                            CASE
                                -- For 'rating' type, the value is already on a 1-5 scale, so we use it directly.
                                WHEN q.question_type = 'rating' THEN f.answer_value
                                -- For 'slider' type (1-100), we convert it to a 0-5 scale.
                                WHEN q.question_type = 'slider' THEN (f.answer_value / 100.0) * 5.0
                                -- For any other type (like 'text'), we return NULL so AVG() ignores it.
                                ELSE NULL
                            END
                        ), 2
                    ), 0
                ) AS average_rating_5_scale
            FROM
                users u
            -- We start with users and join to find only the lecturers
            JOIN
                lecturer_profiles lp ON u.id = lp.user_id
            JOIN
                departments d ON lp.department_id = d.id
            -- LEFT JOINs are used below to include lecturers even if they have no courses or reviews yet
            LEFT JOIN
                lecturer_courses lc ON u.id = lc.user_id AND lc.deleted_at IS NULL
            LEFT JOIN
                course_offerings co ON u.id = co.lecturer_id AND co.deleted_at IS NULL
            LEFT JOIN
                questionnaires qn ON co.id = qn.course_offering_id AND qn.deleted_at IS NULL
            LEFT JOIN
                feedback_submissions fs ON qn.id = fs.questionnaire_id AND fs.deleted_at IS NULL
            LEFT JOIN
                -- Join to feedbacks to get the score
                feedbacks f ON qn.id = f.questionnaire_id AND f.answer_value IS NOT NULL AND f.deleted_at IS NULL
            LEFT JOIN
                -- *** CRITICAL NEW JOIN ***: We must join to the questions table to know the question type
                questions q ON f.question_id = q.id AND q.deleted_at IS NULL
            WHERE
                -- Ensure we only select active users and lecturers
                u.deleted_at IS NULL
                AND lp.deleted_at IS NULL
            GROUP BY
                u.id, u.full_name, d.name
            ORDER BY
                average_rating_5_scale DESC, lecturer_name;
            SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        return array_map(fn($row) => LecturerPerformanceDto::fromDbRow($row), $results);
    }

}
