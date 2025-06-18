<?php
namespace Cfms\Services;

use Cfms\Repositories\FeedbackRepository;
use Cfms\Repositories\QuestionnaireRepository;
use Dell\Cfms\Exceptions\AuthorizationException;

class FeedbackService
{
    public function __construct(
        private FeedbackRepository $feedbackRepo,
        private QuestionnaireRepository $questionnaireRepo
    ) {}

    /**
     * Submits feedback for a questionnaire.
     *
     * @param int $questionnaireId The ID of the questionnaire.
     * @param array $answers The array of answers from the student.
     * @param array $user The authenticated user data from the JWT.
     * @return bool True if the submission was successful.
     */
    public function submitFeedback(int $questionnaireId, array $answers, array $user): bool
    {

        // 1. Authorization: ONLY students can submit feedback.
        if (($user['role_id'] ?? null) != 3) {
            throw new  AuthorizationException("Only students can submit feedback.");
        }

        // 2. Validation: Check if the questionnaire exists and is active.
        $questionnaire = $this->questionnaireRepo->findQuestionnaireById($questionnaireId);
        if (!$questionnaire) {
            throw new \InvalidArgumentException("Questionnaire not found.");
        }
        if ($questionnaire->status !== 'active') {
            throw new \InvalidArgumentException("This questionnaire is not currently active for feedback.");
        }

        // You could add more validation here, like ensuring all questions are answered.

        // 3. Call the repository to save the batch of answers.
        return $this->feedbackRepo->createSubmissionWithAnswers($questionnaireId, $user['id'],$answers);
    }

    // In Cfms\Services\FeedbackService.php

    /**
     * Gets a paginated list of feedbacks for a specific question.
     *
     * @param int $questionnaireId
     * @param int $questionId
     * @param array $user The authenticated user trying to access the results.
     * @param int $page
     * @param int $perPage
     * @return array The paginated result set.
     */
    public function getFeedbacksForQuestion(int $questionnaireId, int $questionId, array $user, int $page = 1, int $perPage = 25): array
    {
        // Authorization: Check if the user is allowed to see these results.
        $questionnaire = $this->questionnaireRepo->findQuestionnaireById($questionnaireId);
        if (!$questionnaire) {
            throw new \InvalidArgumentException("Questionnaire not found.");
        }
        // Only the creator or an admin can view results.
       /* if ($user['role_id'] != 1 && $questionnaire->created_by_user_id !== $user['id']) {
            throw new AuthorizationException("You are not authorized to view these feedback results.");
        }*/

        // Pagination Logic
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        // Fetch the data from the repository
        $feedbacks = $this->feedbackRepo->findByQuestion($questionnaireId, $questionId, $perPage, $offset);
        $total = $this->feedbackRepo->countByQuestion($questionnaireId, $questionId);

        // Map the Feedback models to FeedbackDto objects
        $dtos = array_map(fn($f) => new \Cfms\Dto\FeedbackDto($f), $feedbacks);

        // Return the final, structured paginated response
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