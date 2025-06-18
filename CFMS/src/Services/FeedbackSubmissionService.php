<?php
namespace Cfms\Services;

use Cfms\Repositories\FeedbackSubmissionRepository;

class FeedbackSubmissionService
{
    public function __construct(private FeedbackSubmissionRepository $submissionRepo) {}

    public function getHistoryForUser(int $userId, int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;

        // Call the new, powerful repository method
        $submissionsData = $this->submissionRepo->findByUserPaginated($userId, $perPage, $offset);
        $total = $this->submissionRepo->countByUser($userId);

        // Map the raw data to our new DTO
        $dtos = array_map(fn($data) => new \Cfms\Dto\SubmissionHistoryDto($data), $submissionsData);

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