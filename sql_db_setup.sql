-- This script creates the full database schema for the CFMS application.
-- This version implements a system-wide SOFT DELETE pattern.

-- --------------------------------------------------------

CREATE TABLE `roles`
(
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL UNIQUE,
    `created_at` TIMESTAMP         DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP         DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP    NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;

-- --------------------------------------------------------

CREATE TABLE `faculties`
(
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(255) NOT NULL UNIQUE,
    `created_at` TIMESTAMP         DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP         DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP    NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;

-- --------------------------------------------------------

CREATE TABLE `users`
(
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `full_name`  VARCHAR(255) NOT NULL,
    `email`      VARCHAR(255) NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL,
    `role_id`    INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP         DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP         DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP    NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE = InnoDB;

-- --------------------------------------------------------

CREATE TABLE `departments`
(
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(255) NOT NULL,
    `faculty_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP         DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP         DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP    NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_department_in_faculty` (`name`, `faculty_id`),
    FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE RESTRICT -- Changed from CASCADE to RESTRICT for safety
) ENGINE = InnoDB;

-- --------------------------------------------------------

CREATE TABLE `student_profiles`
(
    `user_id`       INT UNSIGNED NOT NULL,
    `matric_number` VARCHAR(100) NOT NULL UNIQUE,
    `department_id` INT UNSIGNED NOT NULL,
    `faculty_id`    INT UNSIGNED NOT NULL,
    `level`         INT          NOT NULL,
    `created_at`    TIMESTAMP         DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP         DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`    TIMESTAMP    NULL DEFAULT NULL,
    -- No deleted_at here, as its lifecycle is tied to the user table's ON DELETE CASCADE
    PRIMARY KEY (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE RESTRICT
) ENGINE = InnoDB;

-- --------------------------------------------------------

CREATE TABLE `lecturer_profiles`
(
    `user_id`       INT UNSIGNED NOT NULL,
    `department_id` INT UNSIGNED NOT NULL,
    `faculty_id`    INT UNSIGNED NOT NULL,
    `created_at`    TIMESTAMP         DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP         DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`    TIMESTAMP    NULL DEFAULT NULL,
    -- No deleted_at here, as its lifecycle is tied to the user table's ON DELETE CASCADE
    PRIMARY KEY (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE RESTRICT
) ENGINE = InnoDB;

-- --------------------------------------------------------

CREATE TABLE `courses`
(
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_code`  VARCHAR(50)  NOT NULL UNIQUE,
    `course_title` VARCHAR(255) NOT NULL,
    `level`        INT          NOT NULL,
    `created_at`   TIMESTAMP         DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP         DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`   TIMESTAMP    NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;

-- --------------------------------------------------------

CREATE TABLE `course_departments`
(
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_id`     INT UNSIGNED NOT NULL,
    `department_id` INT UNSIGNED NOT NULL,
    `created_at`    TIMESTAMP         DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP         DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`    TIMESTAMP    NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
    UNIQUE KEY `uq_course_department` (`course_id`, `department_id`)
) ENGINE = InnoDB;

-- --------------------------------------------------------

CREATE TABLE `lecturer_courses`
(
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED NOT NULL,
    `course_id`  INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP         DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP         DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP    NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
    UNIQUE KEY `uq_lecturer_course` (`user_id`, `course_id`)
) ENGINE = InnoDB;

-- --------------------------------------------------------

CREATE TABLE `sessions`
(
    `id`         INT UNSIGNED            NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100)            NOT NULL UNIQUE,
    `start_date` DATE                    NOT NULL,
    `end_date`   DATE                             DEFAULT NULL,
    `status`     ENUM ('open', 'closed') NOT NULL DEFAULT 'open',
    `is_active`  BOOLEAN                 NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP                        DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP                        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP               NULL     DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;

-- --------------------------------------------------------

CREATE TABLE `semesters`
(
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL,
    `session_id` INT UNSIGNED NOT NULL,
    `start_date` DATE         NOT NULL,
    `end_date`   DATE              DEFAULT NULL,
    `created_at` TIMESTAMP         DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP         DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP    NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE RESTRICT, -- Changed to RESTRICT
    UNIQUE KEY `uq_semester_in_session` (`name`, `session_id`)
) ENGINE = InnoDB;

-- --------------------------------------------------------

CREATE TABLE `course_offerings`
(
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_id`   INT UNSIGNED NOT NULL,
    `semester_id` INT UNSIGNED NOT NULL,
    `lecturer_id` INT UNSIGNED NOT NULL,
    `created_at`  TIMESTAMP         DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP         DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`  TIMESTAMP    NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_deleted_at` (`deleted_at`),
    FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE RESTRICT, -- Changed to RESTRICT
    FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
    UNIQUE KEY `uq_course_offering_active` (`course_id`, `semester_id`, `lecturer_id`, `deleted_at`)
) ENGINE = InnoDB;

-- --------------------------------------------------------

CREATE TABLE `criteria`
(
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT         NULL,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`  TIMESTAMP    NULL     DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `questionnaires`
(
    `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_offering_id` INT UNSIGNED NULL     DEFAULT NULL,
    `title`              VARCHAR(255) NOT NULL,
    `status`             VARCHAR(50)  NOT NULL DEFAULT 'inactive',
    `created_by_user_id` INT UNSIGNED NULL     DEFAULT NULL,
    `feedback_round`     INT UNSIGNED NOT NULL DEFAULT 1,
    `created_at`         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`         TIMESTAMP    NULL     DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_questionnaires_status` (`status`),
    CONSTRAINT `fk_questionnaires_course_offering`
        FOREIGN KEY (`course_offering_id`)
            REFERENCES `course_offerings` (`id`)
            ON DELETE SET NULL, -- Simpler is better
    CONSTRAINT `fk_questionnaires_created_by`
        FOREIGN KEY (`created_by_user_id`)
            REFERENCES `users` (`id`)
            ON DELETE SET NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `questions`
(
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `questionnaire_id` INT UNSIGNED NOT NULL,
    `criteria_id`      INT UNSIGNED NOT NULL,
    `question_text`    TEXT         NOT NULL,
    `question_type`    VARCHAR(50)  NOT NULL DEFAULT 'rating',
    `order`            INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`       TIMESTAMP    NULL     DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_questions_questionnaire_id` (`questionnaire_id`),
    CONSTRAINT `fk_questions_questionnaire`
        FOREIGN KEY (`questionnaire_id`)
            REFERENCES `questionnaires` (`id`)
            ON DELETE CASCADE,
    CONSTRAINT `fk_questions_criterion`
        FOREIGN KEY (`criteria_id`)
            REFERENCES `criteria` (`id`)
            ON DELETE RESTRICT
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- (Feedbacks and Feedback Submissions tables are unchanged as they should be hard-deleted)
-- ...

CREATE TABLE `feedbacks`
(
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `questionnaire_id` INT UNSIGNED NOT NULL,
    `question_id`      INT UNSIGNED NOT NULL,
    `answer_value`     INT          NULL     DEFAULT NULL,
    `answer_text`      TEXT         NULL     DEFAULT NULL,
    `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `deleted_at`       TIMESTAMP    NULL     DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_feedbacks_questionnaire_id` (`questionnaire_id`),
    INDEX `idx_feedbacks_question_id` (`question_id`),
    CONSTRAINT `fk_feedbacks_questionnaire`
        FOREIGN KEY (`questionnaire_id`)
            REFERENCES `questionnaires` (`id`)
            ON DELETE CASCADE,
    CONSTRAINT `fk_feedbacks_question`
        FOREIGN KEY (`question_id`)
            REFERENCES `questions` (`id`)
            ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE `feedback_submissions`
(
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `questionnaire_id` INT UNSIGNED NOT NULL,
    `user_id`          INT UNSIGNED NOT NULL,
    `submitted_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `deleted_at`       TIMESTAMP    NULL     DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_user_questionnaire_submission` (`user_id`, `questionnaire_id`),
    CONSTRAINT `fk_submission_questionnaire`
        FOREIGN KEY (`questionnaire_id`)
            REFERENCES `questionnaires` (`id`)
            ON DELETE CASCADE,
    CONSTRAINT `fk_submission_user`
        FOREIGN KEY (`user_id`)
            REFERENCES `users` (`id`)
            ON DELETE CASCADE
) ENGINE = InnoDB;