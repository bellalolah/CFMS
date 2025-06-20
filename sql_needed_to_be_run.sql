

-- =================================================================
-- CFMS SAMPLE DATA GENERATION SCRIPT
-- =================================================================
-- This script populates the database with a rich set of sample data
-- for development and testing purposes.
-- Password for all users is '$2y$12$8fgbRfZsm3E/kj1ufA3lW.DX0oH1V8R6Z9RQvF76cmA3wTEq5zltW'
-- =================================================================

-- Use your database name here
-- USE `your_cfms_database_name`;

SET FOREIGN_KEY_CHECKS=0; -- Disable FK checks to allow arbitrary insertion order if needed, and re-enable at the end.

-- Clear existing data to ensure a clean slate
TRUNCATE TABLE `feedback_submissions`;
TRUNCATE TABLE `feedbacks`;
TRUNCATE TABLE `questions`;
TRUNCATE TABLE `questionnaires`;
TRUNCATE TABLE `criteria`;
TRUNCATE TABLE `course_offerings`;
TRUNCATE TABLE `semesters`;
TRUNCATE TABLE `sessions`;
TRUNCATE TABLE `lecturer_courses`;
TRUNCATE TABLE `course_departments`;
TRUNCATE TABLE `courses`;
TRUNCATE TABLE `lecturer_profiles`;
TRUNCATE TABLE `student_profiles`;
TRUNCATE TABLE `departments`;
TRUNCATE TABLE `users`;
TRUNCATE TABLE `faculties`;
TRUNCATE TABLE `roles`;


-- ============================
-- 1. ROLES
-- ============================
INSERT INTO `roles` (`id`, `name`) VALUES
                                       (1, 'Admin'),
                                       (2, 'Lecturer'),
                                       (3, 'Student');


-- ============================
-- 2. FACULTIES
-- ============================
INSERT INTO `faculties` (`id`, `name`) VALUES
                                           (1, 'Faculty of Science'),
                                           (2, 'Faculty of Engineering'),
                                           (3, 'Faculty of Arts and Humanities'),
                                           (4, 'Faculty of Social Sciences'),
                                           (5, 'Faculty of Law');


-- ============================
-- 3. DEPARTMENTS
-- ============================
INSERT INTO `departments` (`id`, `name`, `faculty_id`, `deleted_at`) VALUES
-- Faculty of Science
(1, 'Computer Science', 1, NULL),
(2, 'Physics', 1, NULL),
(3, 'Mathematics', 1, NULL),
(4, 'Chemistry', 1, NULL),
-- Faculty of Engineering
(5, 'Mechanical Engineering', 2, NULL),
(6, 'Electrical and Electronics Engineering', 2, NULL),
(7, 'Civil Engineering', 2, NULL),
-- Faculty of Arts and Humanities
(8, 'History', 3, NULL),
(9, 'English Literature', 3, NULL),
(10, 'Philosophy', 3, NULL),
-- A soft-deleted department
(11, 'Biochemistry', 1, NOW());


-- ============================
-- 4. USERS (Password for all is '$2y$12$8fgbRfZsm3E/kj1ufA3lW.DX0oH1V8R6Z9RQvF76cmA3wTEq5zltW')
-- ============================
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role_id`, `deleted_at`) VALUES
-- Admins (ID: 1-2)
(1, 'Alice Admin', 'admin.alice@cfms.edu', '$2y$12$8fgbRfZsm3E/kj1ufA3lW.DX0oH1V8R6Z9RQvF76cmA3wTEq5zltW', 1, NULL),
(2, 'Bob Supervisor', 'supervisor.bob@cfms.edu', '$2y$12$8fgbRfZsm3E/kj1ufA3lW.DX0oH1V8R6Z9RQvF76cmA3wTEq5zltW', 1, NULL),
-- Lecturers (ID: 3-10)
(3, 'Dr. Alan Turing', 'alan.turing@cfms.edu', '$2y$12$8fgbRfZsm3E/kj1ufA3lW.DX0oH1V8R6Z9RQvF76cmA3wTEq5zltW', 2, NULL),
(4, 'Prof. Ada Lovelace', 'ada.lovelace@cfms.edu', '$2y$12$8fgbRfZsm3E/kj1ufA3lW.DX0oH1V8R6Z9RQvF76cmA3wTEq5zltW', 2, NULL),
(5, 'Dr. Marie Curie', 'marie.curie@cfms.edu', '$2y$12$8fgbRfZsm3E/kj1ufA3lW.DX0oH1V8R6Z9RQvF76cmA3wTEq5zltW', 2, NULL),
(6, 'Dr. Isaac Newton', 'isaac.newton@cfms.edu', '$2y$12$8fgbRfZsm3E/kj1ufA3lW.DX0oH1V8R6Z9RQvF76cmA3wTEq5zltW', 2, NULL),
(7, 'Prof. Nikola Tesla', 'nikola.tesla@cfms.edu', '$2y$12$8fgbRfZsm3E/kj1ufA3lW.DX0oH1V8R6Z9RQvF76cmA3wTEq5zltW', 2, NULL),
(8, 'Dr. Rosalind Franklin', 'rosalind.franklin@cfms.edu', '$2y$12$8fgbRfZsm3E/kj1ufA3lW.DX0oH1V8R6Z9RQvF76cmA3wTEq5zltW', 2, NULL),
(9, 'Prof. William Shakespeare', 'william.shakespeare@cfms.edu', '$2y$12$8fgbRfZsm3E/kj1ufA3lW.DX0oH1V8R6Z9RQvF76cmA3wTEq5zltW', 2, NULL),
(10, 'Dr. John Locke', 'john.locke@cfms.edu', '$2y$12$8fgbRfZsm3E/kj1ufA3lW.DX0oH1V8R6Z9RQvF76cmA3wTEq5zltW', 2, NULL),
-- Students (ID: 11-30)
(11, 'Charlie Brown', 'charlie.brown@cfms.edu', '$2y$12$8fgbRfZsm3E/kj1ufA3lW.DX0oH1V8R6Z9RQvF76cmA3wTEq5zltW', 3, NULL),
(12, 'Lucy Van Pelt', 'lucy.vanpelt@cfms.edu', '$2y$12$8fgbRfZsm3E/kj1ufA3lW.DX0oH1V8R6Z9RQvF76cmA3wTEq5zltW', 3, NULL),
(13, 'Linus Van Pelt', 'linus.vanpelt@cfms.edu', '$2y$12$8fgbRfZsm3E/kj1ufA3lW.DX0oH1V8R6Z9RQvF76cmA3wTEq5zltW', 3, NULL),
(14, 'Sally Brown', 'sally.brown@cfms.edu', '$2y$12$8fgbRfZsm3E/kj1ufA3lW.DX0oH1V8R6Z9RQvF76cmA3wTEq5zltW', 3, NULL),
(15, 'Peter Parker', 'peter.parker@cfms.edu', '$2y$12$8fgbRfZsm3E/kj1ufA3lW.DX0oH1V8R6Z9RQvF76cmA3wTEq5zltW', 3, NULL),
(16, 'Mary Jane Watson', 'mj.watson@cfms.edu', '$2y$12$8fgbRfZsm3E/kj1ufA3lW.DX0oH1V8R6Z9RQvF76cmA3wTEq5zltW', 3, NULL),
(17, 'Harry Potter', 'harry.potter@cfms.edu', '$2y$12$8fgbRfZsm3E/kj1ufA3lW.DX0oH1V8R6Z9RQvF76cmA3wTEq5zltW', 3, NULL),
(18, 'Hermione Granger', 'hermione.granger@cfms.edu', '$2y$12$8fgbRfZsm3E/kj1ufA3lW.DX0oH1V8R6Z9RQvF76cmA3wTEq5zltW', 3, NULL),
(19, 'Ron Weasley', 'ron.weasley@cfms.edu', '$2y$12$8fgbRfZsm3E/kj1ufA3lW.DX0oH1V8R6Z9RQvF76cmA3wTEq5zltW', 3, NULL),
(20, 'Tony Stark', 'tony.stark@cfms.edu', '$2y$12$8fgbRfZsm3E/kj1ufA3lW.DX0oH1V8R6Z9RQvF76cmA3wTEq5zltW', 3, NULL),
-- A soft-deleted student
(21, 'Bruce Wayne', 'bruce.wayne@cfms.edu', '$2y$12$8fgbRfZsm3E/kj1ufA3lW.DX0oH1V8R6Z9RQvF76cmA3wTEq5zltW', 3, NOW());


-- ============================
-- 5. LECTURER & STUDENT PROFILES
-- ============================
-- Lecturer Profiles
INSERT INTO `lecturer_profiles` (`user_id`, `department_id`, `faculty_id`) VALUES
                                                                               (3, 1, 1), -- Dr. Alan Turing -> Computer Science
                                                                               (4, 1, 1), -- Prof. Ada Lovelace -> Computer Science
                                                                               (5, 2, 1), -- Dr. Marie Curie -> Physics
                                                                               (6, 3, 1), -- Dr. Isaac Newton -> Mathematics
                                                                               (7, 6, 2), -- Prof. Nikola Tesla -> Electrical Engineering
                                                                               (8, 4, 1), -- Dr. Rosalind Franklin -> Chemistry
                                                                               (9, 9, 3), -- Prof. William Shakespeare -> English Literature
                                                                               (10, 10, 3); -- Dr. John Locke -> Philosophy

-- Student Profiles
INSERT INTO `student_profiles` (`user_id`, `matric_number`, `department_id`, `faculty_id`, `level`) VALUES
                                                                                                        (11, 'CSC/2022/001', 1, 1, 200), -- Charlie Brown -> Computer Science
                                                                                                        (12, 'CSC/2022/002', 1, 1, 200), -- Lucy Van Pelt -> Computer Science
                                                                                                        (13, 'PHY/2023/010', 2, 1, 100), -- Linus Van Pelt -> Physics
                                                                                                        (14, 'ENG/2021/015', 9, 3, 300), -- Sally Brown -> English Literature
                                                                                                        (15, 'PHY/2022/021', 2, 1, 200), -- Peter Parker -> Physics
                                                                                                        (16, 'ENG/2021/016', 9, 3, 300), -- Mary Jane Watson -> English Literature
                                                                                                        (17, 'MEC/2020/007', 5, 2, 400), -- Harry Potter -> Mechanical Engineering
                                                                                                        (18, 'LAW/2020/001', 11, 5, 400), -- Hermione Granger -> Law (hypothetical dept for a full suite)
                                                                                                        (19, 'MEC/2020/008', 5, 2, 400), -- Ron Weasley -> Mechanical Engineering
                                                                                                        (20, 'EEE/2020/001', 6, 2, 400), -- Tony Stark -> Electrical Engineering
                                                                                                        (21, 'CSC/2019/001', 1, 1, 400); -- Bruce Wayne -> (Profile for soft-deleted user)


-- ============================
-- 6. COURSES
-- ============================
INSERT INTO `courses` (`id`, `course_code`, `course_title`, `level`, `deleted_at`) VALUES
                                                                                       (1, 'CSC101', 'Introduction to Computer Science', 100, NULL),
                                                                                       (2, 'CSC102', 'Introduction to Programming', 100, NULL),
                                                                                       (3, 'CSC201', 'Data Structures and Algorithms', 200, NULL),
                                                                                       (4, 'CSC202', 'Object-Oriented Programming', 200, NULL),
                                                                                       (5, 'CSC305', 'Operating Systems', 300, NULL),
                                                                                       (6, 'PHY101', 'General Physics I', 100, NULL),
                                                                                       (7, 'PHY203', 'Classical Mechanics', 200, NULL),
                                                                                       (8, 'MTH101', 'Elementary Mathematics I', 100, NULL),
                                                                                       (9, 'MTH201', 'Linear Algebra', 200, NULL),
                                                                                       (10, 'EEE201', 'Circuit Theory I', 200, NULL),
                                                                                       (11, 'MEC202', 'Thermodynamics', 200, NULL),
                                                                                       (12, 'ENG311', 'Shakespearean Drama', 300, NULL),
-- A soft-deleted course
                                                                                       (13, 'CSC499', 'Advanced Topics in AI', 400, NOW());


-- ============================
-- 7. COURSE_DEPARTMENTS (Linking courses to departments)
-- ============================
INSERT INTO `course_departments` (`course_id`, `department_id`) VALUES
-- Computer Science Courses
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
-- Physics Courses
(6, 2),
(7, 2),
-- Mathematics Courses (offered to multiple departments)
(8, 1),
(8, 2),
(8, 3),
(9, 1),
(9, 3),
-- Engineering Courses
(10, 6),
(11, 5),
-- Arts Courses
(12, 9);


-- ============================
-- 8. LECTURER_COURSES (Linking lecturers to courses they can teach)
-- ============================
INSERT INTO `lecturer_courses` (`user_id`, `course_id`) VALUES
-- Dr. Turing
(3, 1), (3, 2), (3, 3), (3, 5),
-- Prof. Lovelace
(4, 3), (4, 4),
-- Dr. Curie
(5, 6), (5, 7),
-- Dr. Newton
(6, 8), (6, 9),
-- Prof. Tesla
(7, 10),
-- Prof. Shakespeare
(9, 12);


-- ============================
-- 9. SESSIONS & SEMESTERS
-- ============================
INSERT INTO `sessions` (`id`, `name`, `start_date`, `end_date`, `status`, `is_active`) VALUES
                                                                                           (1, '2022/2023', '2022-09-01', '2023-07-31', 'closed', 0),
                                                                                           (2, '2023/2024', '2023-09-01', NULL, 'open', 1),
                                                                                           (3, '2024/2025', '2024-09-01', NULL, 'open', 0);

INSERT INTO `semesters` (`id`, `name`, `session_id`, `start_date`, `end_date`) VALUES
-- 2022/2023 Semesters
(1, 'First Semester', 1, '2022-09-15', '2023-01-31'),
(2, 'Second Semester', 1, '2023-02-15', '2023-06-30'),
-- 2023/2024 Semesters (Current)
(3, 'First Semester', 2, '2023-09-15', '2024-01-31'),
(4, 'Second Semester', 2, '2024-02-15', NULL);


-- ============================
-- 10. COURSE OFFERINGS (Specific instance of a course in a semester by a lecturer)
-- ============================
INSERT INTO `course_offerings` (`id`, `course_id`, `semester_id`, `lecturer_id`) VALUES
-- Offerings for CURRENT semester (2023/2024 First Semester, ID: 3)
(1, 1, 3, 3),  -- CSC101 taught by Dr. Alan Turing
(2, 3, 3, 4),  -- CSC201 taught by Prof. Ada Lovelace
(3, 6, 3, 5),  -- PHY101 taught by Dr. Marie Curie
(4, 8, 3, 6),  -- MTH101 taught by Dr. Isaac Newton
(5, 10, 3, 7), -- EEE201 taught by Prof. Nikola Tesla
-- Offerings for a PAST semester (2022/2023 Second Semester, ID: 2)
(6, 2, 2, 3),  -- CSC102 taught by Dr. Alan Turing
(7, 4, 2, 4);  -- CSC202 taught by Prof. Ada Lovelace


-- ============================
-- 11. CRITERIA (For Questionnaires)
-- ============================
INSERT INTO `criteria` (`id`, `name`, `description`) VALUES
                                                         (1, 'Course Content', 'Relevance and organization of the course material.'),
                                                         (2, 'Lecturer''s Teaching', 'Clarity, engagement, and effectiveness of the lecturer.'),
                                                         (3, 'Assessment and Feedback', 'Fairness of exams/assignments and quality of feedback provided.'),
                                                         (4, 'Learning Environment', 'Classroom atmosphere and resource availability.'),
                                                         (5, 'Overall Experience', 'General satisfaction with the course.');


-- ============================
-- 12. QUESTIONNAIRES
-- ============================
INSERT INTO `questionnaires` (`id`, `course_offering_id`, `title`, `status`, `created_by_user_id`) VALUES
                                                                                                       (1, 1, 'Feedback for CSC101 - 2023/2024 First Semester', 'active', 1), -- Active questionnaire for CSC101
                                                                                                       (2, 2, 'Feedback for CSC201 - 2023/2024 First Semester', 'inactive', 1), -- Inactive
                                                                                                       (3, 6, 'Feedback for CSC102 - 2022/2023 Second Semester', 'closed', 2); -- Closed from a past semester


-- ============================
-- 13. QUESTIONS
-- ============================
-- Questions for Questionnaire ID 1 (CSC101)
INSERT INTO `questions` (`id`, `questionnaire_id`, `criteria_id`, `question_text`, `question_type`, `order`) VALUES
                                                                                                                 (1, 1, 1, 'The course content was relevant and up-to-date.', 'rating', 1),
                                                                                                                 (2, 1, 2, 'The lecturer explained concepts clearly.', 'rating', 2),
                                                                                                                 (3, 1, 2, 'The lecturer was engaging and held my interest.', 'rating', 3),
                                                                                                                 (4, 1, 3, 'The assessments fairly reflected the course material.', 'rating', 4),
                                                                                                                 (5, 1, 4, 'I felt comfortable asking questions in class.', 'rating', 5),
                                                                                                                 (6, 1, 5, 'What did you like most about this course?', 'text', 6),
                                                                                                                 (7, 1, 5, 'What could be improved for future offerings?', 'text', 7);

-- Questions for Questionnaire ID 2 (CSC201)
INSERT INTO `questions` (`id`, `questionnaire_id`, `criteria_id`, `question_text`, `question_type`, `order`) VALUES
                                                                                                                 (8, 2, 1, 'The course content was well-structured.', 'rating', 1),
                                                                                                                 (9, 2, 2, 'The lecturer provided useful real-world examples.', 'rating', 2),
                                                                                                                 (10, 2, 3, 'Feedback on assignments was timely and helpful.', 'rating', 3);


-- ============================
-- 14. FEEDBACK SUBMISSIONS & FEEDBACKS
-- Simulating students filling out the questionnaire for CSC101 (Questionnaire ID 1)
-- ============================

-- Submission by Charlie Brown (Student User ID: 11)
INSERT INTO `feedback_submissions` (`id`, `questionnaire_id`, `user_id`) VALUES (1, 1, 11);
INSERT INTO `feedbacks` (`questionnaire_id`, `question_id`, `answer_value`, `answer_text`) VALUES
                                                                                               (1, 1, 5, NULL), -- rating: 5
                                                                                               (1, 2, 5, NULL), -- rating: 5
                                                                                               (1, 3, 4, NULL), -- rating: 4
                                                                                               (1, 4, 5, NULL), -- rating: 5
                                                                                               (1, 5, 4, NULL), -- rating: 4
                                                                                               (1, 6, NULL, 'The practical coding examples were very helpful.'),
                                                                                               (1, 7, NULL, 'More hands-on lab sessions would be great.');

-- Submission by Lucy Van Pelt (Student User ID: 12)
INSERT INTO `feedback_submissions` (`id`, `questionnaire_id`, `user_id`) VALUES (2, 1, 12);
INSERT INTO `feedbacks` (`questionnaire_id`, `question_id`, `answer_value`, `answer_text`) VALUES
                                                                                               (1, 1, 4, NULL), -- rating: 4
                                                                                               (1, 2, 3, NULL), -- rating: 3
                                                                                               (1, 3, 4, NULL), -- rating: 4
                                                                                               (1, 4, 4, NULL), -- rating: 4
                                                                                               (1, 5, 5, NULL), -- rating: 5
                                                                                               (1, 6, NULL, 'I liked the group project.'),
                                                                                               (1, 7, NULL, 'The lecturer sometimes goes too fast.');

-- Submission by another CSC student (hypothetical, let's assume we have more)
-- For variety, let's add one more student who hasn't been defined in the profiles above but could exist
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role_id`) VALUES (31, 'Peppermint Patty', 'patty.p@cfms.edu', '$2y$12$8fgbRfZsm3E/kj1ufA3lW.DX0oH1V8R6Z9RQvF76cmA3wTEq5zltW', 3);
INSERT INTO `student_profiles` (`user_id`, `matric_number`, `department_id`, `faculty_id`, `level`) VALUES (31, 'CSC/2022/003', 1, 1, 200);

INSERT INTO `feedback_submissions` (`id`, `questionnaire_id`, `user_id`) VALUES (3, 1, 31);
INSERT INTO `feedbacks` (`questionnaire_id`, `question_id`, `answer_value`, `answer_text`) VALUES
                                                                                               (1, 1, 5, NULL), -- rating: 5
                                                                                               (1, 2, 5, NULL), -- rating: 5
                                                                                               (1, 3, 5, NULL), -- rating: 5
                                                                                               (1, 4, 4, NULL), -- rating: 4
                                                                                               (1, 5, 5, NULL), -- rating: 5
                                                                                               (1, 6, NULL, 'Excellent course, best one I have taken so far.'),
                                                                                               (1, 7, NULL, 'Nothing, it was perfect.');


SET FOREIGN_KEY_CHECKS=1; -- Re-enable foreign key checks.

-- ============================
-- SCRIPT END
-- ============================