<?php

namespace Cfms\KPI\Repositories;


use Cfms\KPI\KPIDto\LecturerCoursePerformanceDto;
use Cfms\KPI\KPIDto\LecturerDashboardStatsDto;
use Cfms\KPI\KPIDto\LecturerPerformanceOverview;
use Cfms\KPI\KPIDto\RecentFeedbackDto;
use Cfms\KPI\KPIDto\RecentTextFeedbackDto;
use Cfms\Repositories\BaseRepository;
use PDO;

class LecturerKPIRepository extends BaseRepository
{
    /**
     * Gets high-level statistics for a specific lecturer's dashboard.
     *
     * @param int $lecturerId The ID of the lecturer.
     * @return LecturerDashboardStatsDTO
     */
    public function getDashboardStats(int $lecturerId): LecturerDashboardStatsDTO
    {
        $sql = <<<SQL
        SELECT
            u.full_name AS lecturer_name,
            -- 1. AVERAGE RATING (0-5 Scale, Normalized, All-Time)
            (
                SELECT COALESCE(ROUND(AVG(
                    CASE
                        WHEN q.question_type = 'rating' THEN f.answer_value
                        WHEN q.question_type = 'slider' THEN (f.answer_value / 100.0) * 5.0
                        ELSE NULL
                    END
                ), 2), 0)
                FROM course_offerings co
                JOIN questionnaires qn ON co.id = qn.course_offering_id
                JOIN feedbacks f ON qn.id = f.id
                JOIN questions q ON f.question_id = q.id
                WHERE co.lecturer_id = :lecturer_id AND f.answer_value IS NOT NULL AND co.deleted_at IS NULL
            ) AS average_rating,

            -- 2. TOTAL COURSES IN ACTIVE SEMESTER
            (
                SELECT COUNT(DISTINCT co.course_id)
                FROM course_offerings co
                JOIN semesters s ON co.semester_id = s.id
                WHERE co.lecturer_id = :lecturer_id
                  AND CURDATE() BETWEEN s.start_date AND COALESCE(s.end_date, CURDATE())
                  AND co.deleted_at IS NULL
            ) AS active_courses_count,

            -- 3. ACTIVE STUDENTS (Current Semester)
            (
                SELECT COUNT(DISTINCT sp.user_id)
                FROM student_profiles sp
                WHERE (sp.department_id, sp.level) IN (
                    SELECT DISTINCT cd.department_id, c.level
                    FROM course_offerings co
                    JOIN courses c ON co.course_id = c.id
                    JOIN course_departments cd ON c.id = cd.course_id
                    JOIN semesters s ON co.semester_id = s.id
                    WHERE co.lecturer_id = :lecturer_id
                      AND CURDATE() BETWEEN s.start_date AND COALESCE(s.end_date, CURDATE())
                      AND co.deleted_at IS NULL
                ) AND sp.deleted_at IS NULL
            ) AS active_students,

            -- 4. TOTAL STUDENTS (All-Time)
            (
                SELECT COUNT(DISTINCT sp.user_id)
                FROM student_profiles sp
                WHERE (sp.department_id, sp.level) IN (
                    SELECT DISTINCT cd.department_id, c.level
                    FROM course_offerings co
                    JOIN courses c ON co.course_id = c.id
                    JOIN course_departments cd ON c.id = cd.course_id
                    WHERE co.lecturer_id = :lecturer_id AND co.deleted_at IS NULL
                ) AND sp.deleted_at IS NULL
            ) AS total_students_taught,

            -- 5. LECTURER'S RESPONSE RATE (Based on Active Semester)
            (
                (SELECT COUNT(DISTINCT fs.user_id)
                 FROM feedback_submissions fs
                 JOIN questionnaires qn ON fs.questionnaire_id = qn.id
                 JOIN course_offerings co ON qn.course_offering_id = co.id
                 JOIN semesters s ON co.semester_id = s.id
                 WHERE co.lecturer_id = :lecturer_id
                   AND CURDATE() BETWEEN s.start_date AND COALESCE(s.end_date, CURDATE())
                   AND fs.deleted_at IS NULL
                ) /
                NULLIF((SELECT COUNT(DISTINCT sp.user_id)
                     FROM student_profiles sp
                     WHERE (sp.department_id, sp.level) IN (
                         SELECT DISTINCT cd.department_id, c.level
                         FROM course_offerings co
                         JOIN courses c ON co.course_id = c.id
                         JOIN course_departments cd ON c.id = cd.course_id
                         JOIN semesters s ON co.semester_id = s.id
                         WHERE co.lecturer_id = :lecturer_id
                           AND CURDATE() BETWEEN s.start_date AND COALESCE(s.end_date, CURDATE())
                           AND co.deleted_at IS NULL
                     ) AND sp.deleted_at IS NULL
                    ), 0)
            ) * 100.0 AS active_semester_response_rate
        FROM
            users u
        WHERE
            u.id = :lecturer_id;
        SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lecturer_id', $lecturerId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_OBJ);

        // Handle case where lecturer might not be found
        if (!$row) {
            // Or throw a custom NotFoundException
            return new LecturerDashboardStatsDTO();
        }

        return LecturerDashboardStatsDTO::fromDbRow($row);
    }

    /**
     * Gets an overview of performance for all lecturers.
     *
     * @return LecturerPerformanceOverview[]
     */

    public function getLecturerPerformanceOverview(int $lecturerId): ?LecturerPerformanceOverview
    {
        // The SQL is perfect, no changes needed here.
        $sql = <<<SQL
    SELECT
        u.full_name AS lecturer_name,
        d.name AS department,
        COUNT(DISTINCT lc.course_id) AS number_of_courses,
        COUNT(DISTINCT fs.id) AS number_of_reviews,
        COALESCE(
            ROUND(
                AVG(
                    CASE
                        WHEN q.question_type = 'rating' THEN f.answer_value
                        WHEN q.question_type = 'slider' THEN (f.answer_value / 100.0) * 5.0
                        ELSE NULL
                    END
                ), 2
            ), 0
        ) AS average_rating_5_scale
    FROM users u
    JOIN lecturer_profiles lp ON u.id = lp.user_id
    JOIN departments d ON lp.department_id = d.id
    LEFT JOIN lecturer_courses lc ON u.id = lc.user_id AND lc.deleted_at IS NULL
    LEFT JOIN course_offerings co ON u.id = co.lecturer_id AND co.deleted_at IS NULL
    LEFT JOIN questionnaires qn ON co.id = qn.course_offering_id AND qn.deleted_at IS NULL
    LEFT JOIN feedback_submissions fs ON qn.id = fs.questionnaire_id AND fs.deleted_at IS NULL
    LEFT JOIN feedbacks f ON qn.id = f.questionnaire_id AND f.answer_value IS NOT NULL AND f.deleted_at IS NULL
    LEFT JOIN questions q ON f.question_id = q.id AND q.deleted_at IS NULL
    WHERE u.deleted_at IS NULL 
      AND lp.deleted_at IS NULL
      AND u.id = :lecturer_id -- This filter ensures we only get one lecturer
    GROUP BY u.id, u.full_name, d.name;
    SQL;

        // CHANGE 1: Use prepare() for queries with parameters
        $stmt = $this->db->prepare($sql);

        // CHANGE 2: Bind the value to the prepared statement
        $stmt->bindValue(':lecturer_id', $lecturerId, PDO::PARAM_INT);

        // CHANGE 3: Execute the prepared statement
        $stmt->execute();

        // CHANGE 4: Fetch a single row, not all rows
        $row = $stmt->fetch(PDO::FETCH_OBJ);

        // CHANGE 5: Handle the case where the lecturer is not found
        if (!$row) {
            return null;
        }

        // CHANGE 6: Create a single DTO object, no need for array_map
        return LecturerPerformanceOverview::fromDbRow($row);
    }

    /**
     * Gets global statistics for the entire system (all lecturers, students, and response rate).
     *
     * @return LecturerDashboardStatsDto
     */
    public function getGlobalDashboardStats(): LecturerDashboardStatsDto
    {
        $sql = <<<SQL
        SELECT
            (SELECT COUNT(*) FROM lecturer_profiles WHERE deleted_at IS NULL) AS total_lecturers,
            (SELECT COUNT(*) FROM student_profiles WHERE deleted_at IS NULL) AS total_students,
            (
                (SELECT COUNT(DISTINCT user_id) FROM feedback_submissions WHERE deleted_at IS NULL)
                /
                NULLIF((SELECT COUNT(*) FROM student_profiles WHERE deleted_at IS NULL), 0)
            ) * 100.0 AS response_rate_percentage;
        SQL;

        $stmt = $this->db->query($sql);
        $row = $stmt->fetch(PDO::FETCH_OBJ);

        return LecturerDashboardStatsDto::fromDbRow($row);
    }







    /**
     * Gets a detailed list of all courses a lecturer taught within a specific academic session,
     * including performance metrics for each course during its respective semester.
     *
     * @param int $lecturerId The ID of the lecturer.
     * @param int $sessionId The ID of the academic session.
     * @return LecturerCoursePerformanceDTO[] An array of performance data for each course offering.
     */
    public function getLecturerCoursesBySession(int $lecturerId, int $sessionId): array
    {
        $sql = <<<SQL
    SELECT
        c.course_code,
        c.course_title AS course_name,
        sem.name AS semester_name,
        d.name AS department_name,

        -- Count unique submissions for this specific course offering.
        COUNT(DISTINCT fs.id) AS number_of_reviews,

        -- Calculate the normalized 0-5 rating for this specific offering.
        COALESCE(
            ROUND(
                AVG(
                    CASE
                        WHEN q.question_type = 'rating' THEN f.answer_value
                        WHEN q.question_type = 'slider' THEN (f.answer_value / 100.0) * 5.0
                        ELSE NULL
                    END
                ), 2
            ), 0
        ) AS rating
    FROM
        course_offerings co
    -- Core entities linked to the offering
    JOIN courses c ON co.course_id = c.id
    JOIN semesters sem ON co.semester_id = sem.id
    
    -- Get the department associated with the course
    -- Assuming one primary department per course for this report
    LEFT JOIN course_departments cd ON c.id = cd.course_id
    LEFT JOIN departments d ON cd.department_id = d.id

    -- LEFT JOINs to feedback data, so courses with zero reviews are still included
    LEFT JOIN questionnaires qn ON co.id = qn.course_offering_id AND qn.deleted_at IS NULL
    LEFT JOIN feedback_submissions fs ON qn.id = fs.questionnaire_id AND fs.deleted_at IS NULL
    LEFT JOIN feedbacks f ON qn.id = f.questionnaire_id AND f.answer_value IS NOT NULL AND f.deleted_at IS NULL
    LEFT JOIN questions q ON f.question_id = q.id AND q.deleted_at IS NULL
    WHERE
        -- Filter for the specific lecturer and session
        co.lecturer_id = :lecturer_id
        AND sem.session_id = :session_id
        AND co.deleted_at IS NULL
    GROUP BY
        -- The key grouping factor: one row per unique course taught in a unique semester
        co.id, c.course_code, c.course_title, sem.name, d.name, sem.start_date
    ORDER BY
        -- Show the latest semester in the session first, then by course code
        sem.start_date DESC, c.course_code ASC;
    SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lecturer_id', $lecturerId, PDO::PARAM_INT);
        $stmt->bindValue(':session_id', $sessionId, PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_OBJ);

        return array_map(fn($row) => LecturerCoursePerformanceDto::fromDbRow($row), $results);
    }





    /**
     * Fetches the most recent feedback submissions for a given lecturer.
     *
     * @param int $lecturerId The ID of the lecturer.
     * @param int $limit The number of recent feedback items to return.
     * @return RecentFeedbackDto[] An array of recent feedback DTOs.
     */
    public function getRecentFeedback(int $lecturerId, int $limit = 2): array
    {
        // A correlated subquery is used here to calculate the rating for each specific questionnaire.
        $sql = <<<SQL
    SELECT
        c.course_title AS course_name,
        fs.submitted_at,
        -- This subquery calculates the average rating ONLY for the questionnaire
        -- associated with this specific feedback submission (fs.questionnaire_id).
        (
            SELECT
                COALESCE(
                    ROUND(
                        AVG(
                            CASE
                                WHEN q.question_type = 'rating' THEN f.answer_value
                                WHEN q.question_type = 'slider' THEN (f.answer_value / 100.0) * 5.0
                                ELSE NULL
                            END
                        ), 2
                    ), 0
                )
            FROM feedbacks f
            JOIN questions q ON f.question_id = q.id
            -- The correlation: links this inner calculation to the outer row's questionnaire.
            WHERE f.questionnaire_id = qn.id
            AND f.answer_value IS NOT NULL
            AND f.deleted_at IS NULL
            AND q.deleted_at IS NULL
        ) AS rating
    FROM
        feedback_submissions fs
    -- Join back to the course to get the lecturer and course name
    JOIN
        questionnaires qn ON fs.questionnaire_id = qn.id
    JOIN
        course_offerings co ON qn.course_offering_id = co.id
    JOIN
        courses c ON co.course_id = c.id
    WHERE
        co.lecturer_id = :lecturer_id
        -- Ensure we only consider non-deleted records throughout the chain
        AND fs.deleted_at IS NULL
        AND qn.deleted_at IS NULL
        AND co.deleted_at IS NULL
        AND c.deleted_at IS NULL
    ORDER BY
        fs.submitted_at DESC -- Order by most recent first
    LIMIT :limit; -- Limit to the desired number of recent items
    SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lecturer_id', $lecturerId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_OBJ);

        return array_map(fn($row) => RecentFeedbackDto::fromDbRow($row), $results);
    }


    /**
     * Fetches the most recent text-based comments for a given lecturer.
     *
     * @param int $lecturerId The ID of the lecturer.
     * @param int $limit The number of recent comments to return.
     * @return RecentTextFeedbackDto[]
     */
    public function getRecentTextFeedback(int $lecturerId, int $limit = 2): array
    {
        $sql = <<<SQL
    SELECT
        c.course_title AS course_name,
        f.answer_text,
        f.created_at, -- The actual timestamp of the comment
        -- This subquery calculates the overall rating for the questionnaire
        -- that this specific text comment (f.id) belongs to.
        (
            SELECT
                COALESCE(ROUND(AVG(
                    CASE
                        WHEN q_inner.question_type = 'rating' THEN f_inner.answer_value
                        WHEN q_inner.question_type = 'slider' THEN (f_inner.answer_value / 100.0) * 5.0
                        ELSE NULL
                    END
                ), 2), 0)
            FROM feedbacks f_inner
            JOIN questions q_inner ON f_inner.question_id = q_inner.id
            -- Correlate to the outer questionnaire ID
            WHERE f_inner.questionnaire_id = qn.id
              AND f_inner.answer_value IS NOT NULL
        ) AS overall_rating
    FROM
        feedbacks f
    -- Join up to find the lecturer and course context
    JOIN
        questions q ON f.question_id = q.id
    JOIN
        questionnaires qn ON q.questionnaire_id = qn.id
    JOIN
        course_offerings co ON qn.course_offering_id = co.id
    JOIN
        courses c ON co.course_id = c.id
    WHERE
        co.lecturer_id = :lecturer_id
        -- Filter FOR text comments only
        AND f.answer_text IS NOT NULL AND f.answer_text != ''
        AND f.deleted_at IS NULL
    ORDER BY
        f.created_at DESC -- Order by the most recent comment's creation time
    LIMIT :limit;
    SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lecturer_id', $lecturerId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_OBJ);

        return array_map(fn($row) => RecentTextFeedbackDto::fromDbRow($row), $results);
    }
}