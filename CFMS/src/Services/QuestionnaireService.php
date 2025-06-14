<?php

namespace Cfms\Services;

use Cfms\Models\Questionnaire;
use Cfms\Repositories\QuestionnaireRepository;

class QuestionnaireService
{
    private QuestionnaireRepository $questionnaireRepository;

    public function __construct(QuestionnaireRepository $repo)
    {
        $this->questionnaireRepository = $repo;
    }

    public function createQuestionnaire(array $data): ?Questionnaire
    {
        // Default values if not passed
        $data['status'] ??= 'draft';
        $data['feedback_round'] ??= 1;

        return $this->questionnaireRepository->create($data);
    }

    public function getQuestionnairesByCourseOffering(int $courseOfferingId): array
    {
        return $this->questionnaireRepository->findAllByCourseOffering($courseOfferingId);
    }

    public function updateQuestionnaire(int $id, array $data): bool
    {
        return $this->questionnaireRepository->update($id, $data);
    }

    public function deleteQuestionnaire(int $id): bool
    {
        return $this->questionnaireRepository->delete($id);
    }

    public function getById(int $id): ?Questionnaire
    {
        return $this->questionnaireRepository->findById($id);
    }
}
