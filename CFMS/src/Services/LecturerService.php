<?php

namespace Cfms\Services;

use Cfms\Repositories\CourseOfferingRepository;
use Cfms\Repositories\QuestionnaireRepository;

class LecturerService
{
    private CourseOfferingRepository $courseOfferingRepo;
    private QuestionnaireRepository $questionnaireRepo;

    public function __construct(
        CourseOfferingRepository $courseOfferingRepo,
        QuestionnaireRepository $questionnaireRepo
    ) {
        $this->courseOfferingRepo = $courseOfferingRepo;
        $this->questionnaireRepo = $questionnaireRepo;
    }

    public function getCoursesByLecturer(int $lecturerId): array
    {
        return $this->courseOfferingRepo->findByLecturer($lecturerId);
    }

    public function getQuestionnaires(int $courseOfferingId): array
    {
        return $this->questionnaireRepo->findAllByCourseOffering($courseOfferingId);
    }

    public function getQuestionnaireById(int $id)
    {
        return $this->questionnaireRepo->findById($id);
    }
}
