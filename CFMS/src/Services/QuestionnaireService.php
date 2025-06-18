<?php
namespace Cfms\Services;

use Cfms\Dto\PendingQuestionnaireDto;
use Cfms\Dto\QuestionnaireBasicDto;
use Cfms\Repositories\CourseOfferingRepository;
use Cfms\Repositories\CourseRepository;
use Cfms\Repositories\FeedbackRepository;
use Cfms\Repositories\QuestionnaireRepository;
use Cfms\Repositories\QuestionRepository;
use Cfms\Repositories\CriterionRepository;
use Cfms\Dto\QuestionnaireWithDetailsDto;
use Cfms\Dto\QuestionDto;
use Dell\Cfms\Exceptions\AuthorizationException;

class QuestionnaireService
{
    // Define the allowed question types as a constant for easy validation
    private const ALLOWED_QUESTION_TYPES = ['rating', 'slider', 'text'];

    public function __construct(
        private QuestionnaireRepository $questionnaireRepo,
        private QuestionRepository $questionRepo,
        private CriterionRepository $criterionRepo,
        private CourseOfferingRepository $courseOfferingRepo,
        private FeedbackRepository $feedbackRepo,
        private CourseRepository $courseRepo,
        private StudentProfileService $studentProfileService,
        private \Cfms\Repositories\UserRepository $userRepo,
        private \Cfms\Repositories\SemesterRepository $semesterRepo
    ) {}

    /**
     * Creates a new questionnaire and its questions.
     * @throws AuthorizationException
     */

    public function create(array $input, array $user): ?QuestionnaireWithDetailsDto
    {

        // Authorize the action first. This will throw an exception on failure.
        $this->authorizeQuestionnaireCreation($input, $user);

        //  Prepare the data for creation
        $questionsData = $input['questions'] ?? [];
        unset($input['questions']);
        $questionnaireData = $input;

        // Automatically set the creator's ID
        $questionnaireData['created_by_user_id'] = $user['id'];

        // If a course_offering_id was not provided, ensure it's null.
        if (empty($questionnaireData['course_offering_id'])) {
            $questionnaireData['course_offering_id'] = null;
        }

        // Validate the questions
        if (empty($questionsData)) {
            throw new \InvalidArgumentException("A questionnaire must have at least one question.");
        }
        foreach ($questionsData as $question) {
            $type = $question['question_type'] ?? 'rating';
            if (!in_array($type, self::ALLOWED_QUESTION_TYPES)) {
                throw new \InvalidArgumentException("Invalid question_type: '$type'.");
            }
        }

        // Call the repository to perform the transactional creation
        $createdQuestionnaire = $this->questionnaireRepo->createWithQuestions($questionnaireData, $questionsData);

        // If successful, fetch the full details to build and return the DTO
        if ($createdQuestionnaire) {
            return $this->getWithDetails($createdQuestionnaire->id);
        }

        return null;
    }

    /**
     * Fetches a single questionnaire and all its details (questions, criteria).
     */
    public function getWithDetails(int $id): ?QuestionnaireWithDetailsDto
    {
        // Part 1: Get the base questionnaire (no change)
        $questionnaire = $this->questionnaireRepo->findQuestionnaireById($id);
        if (!$questionnaire) {
            return null;
        }

        // Part 2: Get all related questions and criteria (no change)
        $questions = $this->questionRepo->findByQuestionnaireId($questionnaire->id);
        $criteriaIds = array_unique(array_map(fn($q) => $q->criteria_id, $questions));
        $criteria = !empty($criteriaIds) ? $this->criterionRepo->findCriterionByIds($criteriaIds) : [];
        $criteriaById = array_column($criteria, null, 'id');
        $questionDtos = [];
        foreach ($questions as $question) {
            $criterion = $criteriaById[$question->criteria_id] ?? null;
            if ($criterion) {
                $questionDtos[] = new QuestionDto($question, $criterion);
            }
        }

        // --- START: NEW, VERIFIED LOGIC ---
        $courseOfferingDetails = null;

        // Check if the questionnaire is linked to a course offering
        if ($questionnaire->course_offering_id) {

            // Use the specific repository method names you created
            $offering = $this->courseOfferingRepo->findCourseOfferingById($questionnaire->course_offering_id);

            if ($offering) {
                // Fetch all related entities using your existing, verified methods
                $course = $this->courseRepo->getCourseById($offering->course_id);
                $lecturer = $this->userRepo->getUserById($offering->lecturer_id);
                $semester = $this->semesterRepo->findSemesterById($offering->semester_id);

                // Assemble the final detailed object as a plain array
                $courseOfferingDetails = [
                    'id' => $offering->id,
                    'course' => $course ? ['id' => $course->id, 'course_code' => $course->course_code, 'course_title' => $course->course_title] : null,
                    'lecturer' => $lecturer ? ['id' => $lecturer->id, 'full_name' => $lecturer->full_name] : null,
                    'semester' => $semester ? ['id' => $semester->id, 'name' => $semester->name] : null
                ];
            }
        }
        // --- END: NEW, VERIFIED LOGIC ---

        // Part 3: Assemble the final DTO, passing in the new details object
        return new QuestionnaireWithDetailsDto($questionnaire, $questionDtos, $courseOfferingDetails);
    }

    /**
     * Private helper method to handle all authorization checks for creating a questionnaire.
     * Throws an AuthorizationException if the user is not permitted.
     */
    private function authorizeQuestionnaireCreation(array $input, array $user): void
    {
        $offeringId = $input['course_offering_id'] ?? null;

        // THE FIX: Consistently use the 'role' key.
        $roleId = $user['role_id'] ?? null;

        if ($offeringId) {
            // SCENARIO A: Course-specific questionnaire

            // Check if the course offering exists.
            if (!$this->courseOfferingRepo->exists((int)$offeringId)) {
                throw new \InvalidArgumentException("The specified course_offering_id does not exist.");
            }

            // Admins (role 1) can proceed.
            if ($roleId == 1) {
                return;
            }

            // Lecturers (role 2) must own the course offering.
            if ($roleId == 2) {
                if ($this->courseOfferingRepo->isLecturerForOffering((int)$offeringId, $user['id'])) {
                    return; // Authorized
                }
            }

            // If we reach here, the user is not an admin and not the correct lecturer.
            throw new AuthorizationException("You are not authorized to create a questionnaire for this course offering.");

        } else {
            // SCENARIO B: General/template questionnaire

            // Only administrators can create these.
            if ($roleId != 1) {
                throw new AuthorizationException("Only administrators can create general questionnaires.");
            }
        }
    }

    public function getPaginated(int $page = 1, int $perPage = 15): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        $questionnaires = $this->questionnaireRepo->findPaginated($perPage, $offset);
        $total = $this->questionnaireRepo->countAll();

        // Map the models to our new basic DTO
        $dtos = array_map(fn($q) => new QuestionnaireBasicDto($q), $questionnaires);

        return [
            'data' => $dtos,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int)ceil($total / $perPage),
            ]
        ];
    }

    // In Cfms\Services\QuestionnaireService.php
    public function update(int $id, array $input, array $user): ?QuestionnaireWithDetailsDto
    {
        // 1. Fetch the existing questionnaire
        $questionnaire = $this->questionnaireRepo->findQuestionnaireById($id);
        if (!$questionnaire) {
            throw new \InvalidArgumentException("Questionnaire not found.");
        }

        // 2. Authorization: Only the creator can update.
        if ($questionnaire->created_by_user_id !== $user['id']) {
            throw new  AuthorizationException("You are not authorized to update this questionnaire.");
        }

        // 3. Immutability Rule: Check if any feedback exists.
        $feedbackCount = $this->feedbackRepo->countByQuestionnaireId($id);
        if ($feedbackCount > 0) {
            throw new  AuthorizationException("Cannot update a questionnaire that already has feedback submissions.");
        }

        // 4. Prepare and validate data
        $questionsData = $input['questions'] ?? [];
        unset($input['questions']);
        $questionnaireData = $input; // Only 'title' can be updated here
        if (empty($questionnaireData['title'])) {
            throw new \InvalidArgumentException("Title cannot be empty.");
        }

        // 5. Call the repository to perform the transactional update
        $success = $this->questionnaireRepo->updateWithQuestions($id, ['title' => $questionnaireData['title']], $questionsData);

        if ($success) {
            // Return the updated, detailed DTO
            return $this->getWithDetails($id);
        }

        return null;
    }

    // In Cfms\Services\QuestionnaireService.php
    public function updateStatus(int $id, string $newStatus, array $user): bool
    {
        error_log("Status ... ${newStatus}");
        // 1. Fetch the existing questionnaire
        $questionnaire = $this->questionnaireRepo->findQuestionnaireById($id);
        if (!$questionnaire) {
            throw new \InvalidArgumentException("Questionnaire not found.");
        }

        // 2. Authorization: Only the creator can update.
        if ($questionnaire->created_by_user_id !== $user['id']) {
            throw new  AuthorizationException("You are not authorized to update this questionnaire's status.");
        }

        $allowedStatuses = ['active', 'inactive'];

         if (!in_array($newStatus, $allowedStatuses, true)) {
            // The 'true' at the end enforces a strict type comparison (recommended)
            throw new \InvalidArgumentException("Invalid status provided. Must be one of: active, inactive.");
        }

        // 4. Call the repository to update ONLY the status field
        return $this->questionnaireRepo->updateStatus($id, $newStatus);
    }

    // In Cfms\Services\QuestionnaireService.php

    // In Cfms\Services\QuestionnaireService.php

    public function getByLecturerPaginated(int $lecturerId, int $page = 1, int $perPage = 15): array
    {
        // 1. Get the paginated list of questionnaires (no change here)
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;
        $questionnaires = $this->questionnaireRepo->findByLecturer($lecturerId, $perPage, $offset);
        $total = $this->questionnaireRepo->countByLecturer($lecturerId);

        if (empty($questionnaires)) {
            // Return early if there's nothing to process
            return ['data' => [], 'pagination' => [/* ...pagination data... */]];
        }

        // 2. Efficiently fetch all related data
        // a) Get all unique course_offering_ids from the page of questionnaires
        $offeringIds = array_filter(array_unique(array_map(fn($q) => $q->course_offering_id, $questionnaires)));

        // b) Fetch all needed course offerings and their courses in two queries
        $courseOfferings = !empty($offeringIds) ? $this->courseOfferingRepo->findByIds($offeringIds) : [];
        $courseIds = !empty($courseOfferings) ? array_unique(array_map(fn($co) => $co->course_id, $courseOfferings)) : [];
        // Assumes you have a CourseRepository with findByIds
        $courses = !empty($courseIds) ? $this->courseRepo->findByIds($courseIds) : [];

        // 3. Map the data for easy lookup
        $coursesById = array_column($courses, null, 'id');
        $offeringsById = [];
        foreach ($courseOfferings as $offering) {
            $course = $coursesById[$offering->course_id] ?? null;
            if ($course) {
                // Combine the offering with its course details
                $offering->course_code = $course->course_code;
                $offering->course_title = $course->course_title;
            }
            $offeringsById[$offering->id] = $offering;
        }

        // 4. Build the final DTOs
        $dtos = [];
        foreach ($questionnaires as $q) {
            $offeringDetails = isset($q->course_offering_id) ? ($offeringsById[$q->course_offering_id] ?? null) : null;
            $dtos[] = new \Cfms\Dto\QuestionnaireBasicDto($q, $offeringDetails);
        }

        // 5. Return the final paginated response
        return [
            'data' => $dtos,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int)ceil($total / $perPage),
            ]
        ];
    }


    public function getPendingForStudent(array $studentUser, int $page = 1, int $perPage = 15): array
    {
        // First, we need the student's profile to get their department ID
        // Assumes StudentProfileService is injected and has getProfile method
        $profile = $this->studentProfileService->getProfile($studentUser['id']);
        if (!$profile) {
            // This student hasn't completed their profile, so they have no department yet.
            return ['data' => [], 'pagination' => ['total' => 0, /*...*/]];
        }


        $profile = $this->studentProfileService->getProfile($studentUser['id']);
        if (!$profile) {
            return ['data' => [], 'pagination' => ['total' => 0, /*...*/]];
        }
        $departmentId = $profile['department_id'];
        $studentUserId = $studentUser['id'];

        $offset = ($page - 1) * $perPage;

        // 1. Call the new repository method, which returns raw data objects
        $pendingQuestionnairesData = $this->questionnaireRepo->findPendingForStudent($studentUserId, $departmentId, $perPage, $offset);

        // 2. Get the total count for pagination
        $total = $this->questionnaireRepo->countPendingForStudent($studentUserId, $departmentId);

        // 3. Map the raw data directly into our new, powerful DTO
        $dtos = array_map(fn($data) => new PendingQuestionnaireDto($data), $pendingQuestionnairesData);

        // 4. Return the final paginated response
        return [
            'data' => $dtos,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int)ceil($total / $perPage),
            ]
        ];
    }



}